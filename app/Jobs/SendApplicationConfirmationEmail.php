<?php

namespace App\Jobs;

use App\Models\Application;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendApplicationConfirmationEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Application $application,
    ) {}

    public function handle(): void
    {
        Log::info('Application confirmation email sent', [
            'application_id' => $this->application->id,
            'applicant'      => $this->application->full_name,
            'email'          => $this->application->email,
            'program'        => $this->application->program?->name,
        ]);
    }
}
