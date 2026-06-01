<?php

namespace App\Filament\Resources\ApplicationResource\Pages;

use App\Filament\Resources\ApplicationResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewApplication extends ViewRecord
{
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_id')
                ->label('Download ID')
                ->icon('heroicon-o-identification')
                ->color('gray')
                ->visible(fn () => (bool) $this->record->id_document_path)
                ->action(fn () => Storage::disk('local')->download($this->record->id_document_path)),

            Action::make('download_transcript')
                ->label('Download Transcript')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->visible(fn () => (bool) $this->record->transcript_path)
                ->action(fn () => Storage::disk('local')->download($this->record->transcript_path)),

            EditAction::make(),
        ];
    }
}
