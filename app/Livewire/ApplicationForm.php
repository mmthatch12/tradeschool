<?php

namespace App\Livewire;

use App\Models\Program;
use App\Models\School;
use App\Services\ApplicationService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class ApplicationForm extends Component
{
    use WithFileUploads;

    #[Rule('required|string|max:255')]
    public string $first_name = '';

    #[Rule('required|string|max:255')]
    public string $last_name = '';

    #[Rule('required|email')]
    public string $email = '';

    #[Rule('nullable|string|max:20')]
    public string $phone = '';

    #[Rule('required|exists:programs,id')]
    public string $program_id = '';

    #[Rule('nullable|date|before:-16 years')]
    public string $date_of_birth = '';

    #[Rule('nullable|date|after:today')]
    public string $desired_start_date = '';

    #[Rule('nullable|file|mimes:pdf,jpg,jpeg,png|max:5120')]
    public $id_document = null;

    #[Rule('nullable|file|mimes:pdf,jpg,jpeg,png|max:5120')]
    public $transcript = null;

    public bool $submitted = false;

    public string $applicationToken = '';

    public function submit(ApplicationService $service): void
    {
        $this->validate();

        $school = School::first();

        $data = [
            'school_id'          => $school->id,
            'program_id'         => $this->program_id,
            'first_name'         => $this->first_name,
            'last_name'          => $this->last_name,
            'email'              => $this->email,
            'phone'              => $this->phone ?: null,
            'date_of_birth'      => $this->date_of_birth ?: null,
            'desired_start_date' => $this->desired_start_date ?: null,
        ];

        if ($this->id_document) {
            $data['id_document_path'] = $this->id_document->store('documents', 'local');
        }

        if ($this->transcript) {
            $data['transcript_path'] = $this->transcript->store('documents', 'local');
        }

        $application = $service->submit($data);

        $this->applicationToken = $application->application_token;
        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.application-form', [
            'programs' => Program::where('is_active', true)->orderBy('name')->get(),
        ])->layout('layouts.public');
    }
}
