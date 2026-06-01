<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnrollmentResource\Pages;
use App\Models\Enrollment;
use App\Models\Program;
use App\Models\Student;
use App\Services\PaymentService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Enrollments';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('student_id')
                ->label('Student')
                ->options(Student::orderBy('last_name')->get()->pluck('full_name', 'id'))
                ->searchable()
                ->required(),
            Select::make('program_id')
                ->label('Program')
                ->options(Program::where('is_active', true)->pluck('name', 'id'))
                ->required(),
            DatePicker::make('enrolled_at')->required()->default(now()),
            DatePicker::make('expected_graduation_date')->nullable(),
            Select::make('status')
                ->options([
                    'active'    => 'Active',
                    'graduated' => 'Graduated',
                    'withdrawn' => 'Withdrawn',
                    'suspended' => 'Suspended',
                ])
                ->required()
                ->default('active'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.full_name')->label('Student')->searchable(['students.first_name', 'students.last_name'])->sortable(),
                TextColumn::make('program.name')->label('Program')->sortable(),
                TextColumn::make('enrolled_at')->date()->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'    => 'success',
                        'graduated' => 'info',
                        'withdrawn' => 'danger',
                        'suspended' => 'warning',
                        default     => 'gray',
                    }),
                TextColumn::make('paymentPlan.status')
                    ->label('Payment Plan')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'active'    => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    })
                    ->default('None'),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'active'    => 'Active',
                    'graduated' => 'Graduated',
                    'withdrawn' => 'Withdrawn',
                    'suspended' => 'Suspended',
                ]),
            ])
            ->recordActions([
                Action::make('setup_plan')
                    ->label('Setup Payment Plan')
                    ->icon('heroicon-o-credit-card')
                    ->color('info')
                    ->visible(fn (Enrollment $record): bool => $record->paymentPlan === null)
                    ->form([
                        TextInput::make('total_amount')
                            ->label('Total Tuition ($)')
                            ->numeric()
                            ->required()
                            ->prefix('$'),
                        TextInput::make('installment_count')
                            ->label('Number of Installments')
                            ->numeric()
                            ->required()
                            ->default(12)
                            ->minValue(1)
                            ->maxValue(60),
                        Select::make('frequency')
                            ->options([
                                'monthly'  => 'Monthly',
                                'biweekly' => 'Bi-Weekly',
                                'weekly'   => 'Weekly',
                            ])
                            ->required()
                            ->default('monthly'),
                        DatePicker::make('start_date')
                            ->required()
                            ->default(now()->addMonth()->startOfMonth()),
                    ])
                    ->action(function (Enrollment $record, array $data) {
                        app(PaymentService::class)->createPlan($record, $data);
                        Notification::make()->title('Payment plan created')->success()->send();
                    }),

                Action::make('view')
                    ->url(fn (Enrollment $record): string => EnrollmentResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEnrollments::route('/'),
            'create' => Pages\CreateEnrollment::route('/create'),
            'view'   => Pages\ViewEnrollment::route('/{record}'),
        ];
    }
}
