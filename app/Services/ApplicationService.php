<?php

namespace App\Services;

use App\Jobs\SendApplicationConfirmationEmail;
use App\Models\Application;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Str;

class ApplicationService
{
    /**
     * Submit a new application record, generating a unique status-check token.
     *
     * @param  array<string, mixed>  $data
     */
    public function submit(array $data): Application
    {
        $data['application_token'] = Str::uuid();

        $application = Application::create($data);

        SendApplicationConfirmationEmail::dispatch($application);

        return $application;
    }

    /**
     * Approve an application: create/find the Student, create an Enrollment,
     * and assign a portal token so the student can access their payment portal.
     */
    public function approve(Application $application, User $reviewer): Student
    {
        $student = Student::firstOrCreate(
            ['email' => $application->email, 'school_id' => $application->school_id],
            [
                'school_id'     => $application->school_id,
                'first_name'    => $application->first_name,
                'last_name'     => $application->last_name,
                'email'         => $application->email,
                'phone'         => $application->phone,
                'date_of_birth' => $application->date_of_birth,
                'portal_token'  => Str::uuid(),
            ]
        );

        // Ensure every student has a portal token even if they existed before this feature.
        if (! $student->portal_token) {
            $student->update(['portal_token' => Str::uuid()]);
        }

        $application->update([
            'status'      => 'approved',
            'student_id'  => $student->id,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        // Create an enrollment for the approved program.
        Enrollment::firstOrCreate(
            ['student_id' => $student->id, 'program_id' => $application->program_id],
            [
                'school_id'  => $application->school_id,
                'enrolled_at' => now()->toDateString(),
                'status'     => 'active',
            ]
        );

        return $student;
    }

    /**
     * Deny an application with reviewer notes.
     */
    public function deny(Application $application, User $reviewer, string $notes): void
    {
        $application->update([
            'status'      => 'denied',
            'notes'       => $notes,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);
    }
}
