<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationResource\Pages;
use App\Models\Application;
use App\Models\Program;
use App\Services\ApplicationService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Applications';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->nullable(),
                Select::make('program_id')
                    ->label('Program')
                    ->options(Program::where('is_active', true)->pluck('name', 'id'))
                    ->required(),
                DatePicker::make('date_of_birth')
                    ->nullable(),
                DatePicker::make('desired_start_date')
                    ->nullable(),
                Select::make('status')
                    ->options([
                        'pending'    => 'Pending',
                        'approved'   => 'Approved',
                        'denied'     => 'Denied',
                        'waitlisted' => 'Waitlisted',
                    ])
                    ->required(),
                Textarea::make('notes')
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->label('First Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_name')
                    ->label('Last Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('program.name')
                    ->label('Program')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'    => 'warning',
                        'approved'   => 'success',
                        'denied'     => 'danger',
                        'waitlisted' => 'info',
                        default      => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'    => 'Pending',
                        'approved'   => 'Approved',
                        'denied'     => 'Denied',
                        'waitlisted' => 'Waitlisted',
                    ]),
                SelectFilter::make('program_id')
                    ->label('Program')
                    ->relationship('program', 'name'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Application $record): bool => $record->status === 'pending' || $record->status === 'waitlisted')
                    ->requiresConfirmation()
                    ->action(function (Application $record) {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        app(ApplicationService::class)->approve($record, $user);
                        Notification::make()
                            ->title('Application approved')
                            ->success()
                            ->send();
                    }),

                Action::make('deny')
                    ->label('Deny')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Application $record): bool => $record->status === 'pending' || $record->status === 'waitlisted')
                    ->form([
                        Textarea::make('notes')
                            ->label('Denial reason')
                            ->required(),
                    ])
                    ->action(function (Application $record, array $data) {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        app(ApplicationService::class)->deny($record, $user, $data['notes']);
                        Notification::make()
                            ->title('Application denied')
                            ->success()
                            ->send();
                    }),

                Action::make('edit')
                    ->url(fn (Application $record): string => ApplicationResource::getUrl('edit', ['record' => $record]))
                    ->icon('heroicon-o-pencil'),

                Action::make('view')
                    ->url(fn (Application $record): string => ApplicationResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListApplications::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'edit'   => Pages\EditApplication::route('/{record}/edit'),
            'view'   => Pages\ViewApplication::route('/{record}'),
        ];
    }
}
