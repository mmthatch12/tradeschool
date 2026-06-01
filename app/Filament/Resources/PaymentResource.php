<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Services\PaymentService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Payments';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('enrollment.student.full_name')
                    ->label('Student')
                    ->searchable(['students.first_name', 'students.last_name'])
                    ->sortable(),
                TextColumn::make('enrollment.program.name')
                    ->label('Program')
                    ->sortable(),
                TextColumn::make('amount')
                    ->money('usd')
                    ->sortable(),
                TextColumn::make('due_date')->date()->sortable(),
                TextColumn::make('paid_at')->dateTime()->sortable()->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid'    => 'success',
                        'pending' => 'warning',
                        'overdue' => 'danger',
                        'failed'  => 'danger',
                        default   => 'gray',
                    }),
                TextColumn::make('transaction_id')->label('Txn ID')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'paid'    => 'Paid',
                    'overdue' => 'Overdue',
                    'failed'  => 'Failed',
                ]),
            ])
            ->recordActions([
                Action::make('mark_paid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Payment $record): bool => in_array($record->status, ['pending', 'overdue', 'failed']))
                    ->requiresConfirmation()
                    ->form([
                        Select::make('payment_method')
                            ->options([
                                'card'         => 'Credit/Debit Card',
                                'bank_transfer' => 'Bank Transfer',
                                'cash'         => 'Cash',
                                'check'        => 'Check',
                            ])
                            ->required()
                            ->default('card'),
                    ])
                    ->action(function (Payment $record, array $data) {
                        app(PaymentService::class)->processPayment($record, $data);
                        Notification::make()->title('Payment recorded')->success()->send();
                    }),
            ])
            ->defaultSort('due_date', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
        ];
    }
}
