<?php

namespace App\Services;

use App\Jobs\SendApplicationConfirmationEmail;
use App\Models\Application;
use App\Models\Student;
use App\Models\User;

class ApplicationService
{
    /**
     * Submit a new application record.
     *
     * @param  array<string, mixed>  $data
     */
    public function submit(array $data): Application
    {
        $application = Application::create($data);

        SendApplicationConfirmationEmail::dispatch($application);

        return $application;
    }

    /**
     * Approve an application, creating or finding the associated Student record.
     *
     * @throws \RuntimeException if the application is not in pending/waitlisted status
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
            ]
        );

        $application->update([
            'status'      => 'approved',
            'student_id'  => $student->id,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

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
