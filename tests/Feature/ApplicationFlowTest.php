<?php

use App\Livewire\ApplicationForm;
use App\Livewire\ApplicationStatus;
use App\Models\Application;
use App\Models\Program;
use App\Models\School;
use App\Models\User;
use App\Services\ApplicationService;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    $this->school = School::create(['name' => 'Test School', 'slug' => 'test', 'primary_color' => '#000']);
    $this->program = Program::create([
        'school_id'     => $this->school->id,
        'name'          => 'Test Program',
        'description'   => 'A test program',
        'duration_weeks' => 12,
        'tuition_cost'  => 5000,
        'is_active'     => true,
    ]);
    $this->admin = User::create([
        'name'      => 'Admin',
        'email'     => 'admin@test.test',
        'password'  => bcrypt('password'),
        'school_id' => $this->school->id,
    ]);
});

test('application form renders', function () {
    Livewire::test(ApplicationForm::class)
        ->assertSee('Apply for Enrollment')
        ->assertSee($this->program->name);
});

test('student can submit an application', function () {
    Livewire::test(ApplicationForm::class)
        ->set('first_name', 'Jane')
        ->set('last_name', 'Doe')
        ->set('email', 'jane@test.test')
        ->set('program_id', (string) $this->program->id)
        ->call('submit')
        ->assertSet('submitted', true);

    $this->assertDatabaseHas('applications', [
        'email'  => 'jane@test.test',
        'status' => 'pending',
    ]);

    expect(Application::where('email', 'jane@test.test')->first()->application_token)->not->toBeNull();
});

test('application status page shows pending state', function () {
    $application = Application::create([
        'school_id'         => $this->school->id,
        'program_id'        => $this->program->id,
        'first_name'        => 'John',
        'last_name'         => 'Smith',
        'email'             => 'john@test.test',
        'application_token' => Str::uuid(),
        'status'            => 'pending',
    ]);

    Livewire::test(ApplicationStatus::class, ['token' => $application->application_token])
        ->assertSee('John Smith')
        ->assertSee('Pending');
});

test('approving application creates student and enrollment', function () {
    $application = Application::create([
        'school_id'         => $this->school->id,
        'program_id'        => $this->program->id,
        'first_name'        => 'Lisa',
        'last_name'         => 'Park',
        'email'             => 'lisa@test.test',
        'application_token' => Str::uuid(),
        'status'            => 'pending',
    ]);

    $service = app(ApplicationService::class);
    $student = $service->approve($application, $this->admin);

    expect($student->email)->toBe('lisa@test.test');
    expect($student->portal_token)->not->toBeNull();

    $this->assertDatabaseHas('applications', ['id' => $application->id, 'status' => 'approved']);
    $this->assertDatabaseHas('enrollments', ['student_id' => $student->id, 'program_id' => $this->program->id]);
});

test('denying application sets status and notes', function () {
    $application = Application::create([
        'school_id'         => $this->school->id,
        'program_id'        => $this->program->id,
        'first_name'        => 'Bob',
        'last_name'         => 'Jones',
        'email'             => 'bob@test.test',
        'application_token' => Str::uuid(),
        'status'            => 'pending',
    ]);

    app(ApplicationService::class)->deny($application, $this->admin, 'Incomplete documents.');

    $this->assertDatabaseHas('applications', [
        'id'     => $application->id,
        'status' => 'denied',
        'notes'  => 'Incomplete documents.',
    ]);
});

test('application status page throws for unknown token', function () {
    expect(fn () => Livewire::test(ApplicationStatus::class, ['token' => 'bad-token']))
        ->toThrow(Illuminate\Database\Eloquent\ModelNotFoundException::class);
});
