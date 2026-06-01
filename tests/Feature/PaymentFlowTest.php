<?php

use App\Livewire\StudentPortal;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentPlan;
use App\Models\Program;
use App\Models\School;
use App\Models\Student;
use App\Services\PaymentService;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    $this->school = School::create(['name' => 'Test School', 'slug' => 'test', 'primary_color' => '#000']);
    $this->program = Program::create([
        'school_id'     => $this->school->id,
        'name'          => 'Test Program',
        'description'   => 'A test program',
        'duration_weeks' => 12,
        'tuition_cost'  => 6000,
        'is_active'     => true,
    ]);
    $this->student = Student::create([
        'school_id'    => $this->school->id,
        'first_name'   => 'Alice',
        'last_name'    => 'Test',
        'email'        => 'alice@test.test',
        'portal_token' => Str::uuid(),
    ]);
    $this->enrollment = Enrollment::create([
        'school_id'  => $this->school->id,
        'student_id' => $this->student->id,
        'program_id' => $this->program->id,
        'enrolled_at' => now()->toDateString(),
        'status'     => 'active',
    ]);
});

test('student portal renders with student name', function () {
    Livewire::test(StudentPortal::class, ['token' => $this->student->portal_token])
        ->assertSee('Alice');
});

test('student portal throws for unknown token', function () {
    expect(fn () => Livewire::test(StudentPortal::class, ['token' => 'bad-token']))
        ->toThrow(Illuminate\Database\Eloquent\ModelNotFoundException::class);
});

test('payment service creates a plan with correct installments', function () {
    $service = app(PaymentService::class);

    $plan = $service->createPlan($this->enrollment, [
        'total_amount'      => 6000,
        'installment_count' => 6,
        'frequency'         => 'monthly',
        'start_date'        => now()->addMonth()->startOfMonth()->toDateString(),
    ]);

    expect($plan->total_amount)->toBe(6000.0);
    expect($plan->installment_count)->toBe(6);
    expect($this->enrollment->payments()->count())->toBe(6);
    expect($this->enrollment->payments()->first()->amount)->toBe(1000.0);
});

test('processing a payment marks it paid with transaction id', function () {
    $service = app(PaymentService::class);

    $plan = $service->createPlan($this->enrollment, [
        'total_amount'      => 6000,
        'installment_count' => 3,
        'frequency'         => 'monthly',
        'start_date'        => now()->toDateString(),
    ]);

    $payment = $this->enrollment->payments()->first();

    $service->processPayment($payment, ['payment_method' => 'card']);

    $payment->refresh();
    expect($payment->status)->toBe('paid');
    expect($payment->transaction_id)->toStartWith('MOCK-');
    expect($payment->paid_at)->not->toBeNull();
});

test('payment plan is marked completed when all payments are paid', function () {
    $service = app(PaymentService::class);

    $plan = $service->createPlan($this->enrollment, [
        'total_amount'      => 300,
        'installment_count' => 2,
        'frequency'         => 'monthly',
        'start_date'        => now()->toDateString(),
    ]);

    foreach ($this->enrollment->payments as $payment) {
        $service->processPayment($payment, ['payment_method' => 'card']);
    }

    $plan->refresh();
    expect($plan->status)->toBe('completed');
});

test('student portal shows setup plan button when no plan exists', function () {
    Livewire::test(StudentPortal::class, ['token' => $this->student->portal_token])
        ->assertSee('Set Up Payment Plan');
});

test('student can set up a payment plan via portal', function () {
    Livewire::test(StudentPortal::class, ['token' => $this->student->portal_token])
        ->set('total_amount', '6000')
        ->set('installment_count', 6)
        ->set('frequency', 'monthly')
        ->set('start_date', now()->addMonth()->startOfMonth()->toDateString())
        ->call('setupPlan');

    $this->assertDatabaseHas('payment_plans', [
        'enrollment_id' => $this->enrollment->id,
        'total_amount'  => 6000,
    ]);
});
