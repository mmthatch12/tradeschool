<?php

namespace App\Livewire;

use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Student;
use App\Services\PaymentService;
use Livewire\Attributes\Rule;
use Livewire\Component;

class StudentPortal extends Component
{
    public Student $student;

    // Payment plan setup form
    public bool $showPlanForm = false;

    #[Rule('required|numeric|min:1')]
    public string $total_amount = '';

    #[Rule('required|integer|min:1|max:60')]
    public int $installment_count = 12;

    #[Rule('required|in:monthly,biweekly,weekly')]
    public string $frequency = 'monthly';

    #[Rule('required|date')]
    public string $start_date = '';

    // One-time payment
    public ?int $payingPaymentId = null;

    #[Rule('required|in:card,bank_transfer,cash,check')]
    public string $payment_method = 'card';

    public function mount(string $token): void
    {
        $this->student = Student::where('portal_token', $token)
            ->with(['enrollments.program', 'enrollments.paymentPlan', 'enrollments.payments'])
            ->firstOrFail();

        $this->start_date = now()->addMonth()->startOfMonth()->toDateString();
    }

    public function setupPlan(PaymentService $service): void
    {
        $this->validateOnly('total_amount,installment_count,frequency,start_date');

        $enrollment = $this->student->enrollments()->where('status', 'active')->first();

        if (! $enrollment || $enrollment->paymentPlan) {
            return;
        }

        $service->createPlan($enrollment, [
            'total_amount'      => $this->total_amount,
            'installment_count' => $this->installment_count,
            'frequency'         => $this->frequency,
            'start_date'        => $this->start_date,
        ]);

        $this->showPlanForm = false;
        $this->student->refresh()->load(['enrollments.program', 'enrollments.paymentPlan', 'enrollments.payments']);
        session()->flash('success', 'Payment plan created successfully.');
    }

    public function pay(PaymentService $service, int $paymentId): void
    {
        $payment = Payment::where('id', $paymentId)
            ->whereHas('enrollment', fn ($q) => $q->where('student_id', $this->student->id))
            ->firstOrFail();

        $service->processPayment($payment, ['payment_method' => $this->payment_method]);

        $this->payingPaymentId = null;
        $this->student->refresh()->load(['enrollments.program', 'enrollments.paymentPlan', 'enrollments.payments']);
        session()->flash('success', 'Payment of $' . number_format($payment->amount, 2) . ' processed successfully.');
    }

    public function render()
    {
        return view('livewire.student-portal')->layout('layouts.public');
    }
}
