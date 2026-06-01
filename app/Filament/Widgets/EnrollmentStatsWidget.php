<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use App\Models\Enrollment;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EnrollmentStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $pendingApplications = Application::where('status', 'pending')->count();
        $activeEnrollments = Enrollment::where('status', 'active')->count();
        $overduePayments = Payment::where('status', 'overdue')->count();
        $collectedThisMonth = Payment::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        return [
            Stat::make('Pending Applications', $pendingApplications)
                ->description('Awaiting review')
                ->color($pendingApplications > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-document-text'),

            Stat::make('Active Enrollments', $activeEnrollments)
                ->description('Currently enrolled students')
                ->color('success')
                ->icon('heroicon-o-academic-cap'),

            Stat::make('Overdue Payments', $overduePayments)
                ->description('Accounts needing attention')
                ->color($overduePayments > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-exclamation-circle'),

            Stat::make('Revenue This Month', '$' . number_format($collectedThisMonth, 2))
                ->description('Payments collected')
                ->color('info')
                ->icon('heroicon-o-banknotes'),
        ];
    }
}
