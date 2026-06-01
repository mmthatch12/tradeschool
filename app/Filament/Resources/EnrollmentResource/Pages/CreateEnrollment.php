<?php

namespace App\Filament\Resources\EnrollmentResource\Pages;

use App\Filament\Resources\EnrollmentResource;
use App\Models\School;
use Filament\Resources\Pages\CreateRecord;

class CreateEnrollment extends CreateRecord
{
    protected static string $resource = EnrollmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['school_id'] = School::first()->id;

        return $data;
    }
}
