<?php

namespace App\Livewire;

use App\Models\Application;
use Livewire\Component;

class ApplicationStatus extends Component
{
    public Application $application;

    public function mount(string $token): void
    {
        $this->application = Application::where('application_token', $token)->firstOrFail();
    }

    public function render()
    {
        return view('livewire.application-status')->layout('layouts.public');
    }
}
