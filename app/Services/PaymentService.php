<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentPlan;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Create a payment plan for an enrollment and generate all installment Payment records.
     *
     * @param  array<string, mixed>  $data  Must include: total_amount, installment_count, frequency, start_date
     */
    public function createPlan(Enrollment $enrollment, array $data): PaymentPlan
    {
        $totalAmount = (float) $data['total_amount'];
        $installmentCount = (int) $data['installment_count'];
        $amountPerInstallment = round($totalAmount / $installmentCount, 2);
        $startDate = Carbon::parse($data['start_date']);
        $frequency = $data['frequency'] ?? 'monthly';

        $plan = PaymentPlan::create([
            'enrollment_id'          => $enrollment->id,
            'school_id'              => $enrollment->school_id,
            'total_amount'           => $totalAmount,
            'amount_per_installment' => $amountPerInstallment,
            'installment_count'      => $installmentCount,
            'frequency'              => $frequency,
            'start_date'             => $startDate->toDateString(),
            'status'                 => 'active',
        ]);

        for ($i = 0; $i < $installmentCount; $i++) {
            $dueDate = match ($frequency) {
                'weekly'   => $startDate->copy()->addWeeks($i),
                'biweekly' => $startDate->copy()->addWeeks($i * 2),
                default    => $startDate->copy()->addMonths($i),
            };

            $status = $dueDate->isPast() ? 'overdue' : 'pending';

            Payment::create([
                'enrollment_id'   => $enrollment->id,
                'school_id'       => $enrollment->school_id,
                'payment_plan_id' => $plan->id,
                'amount'          => $amountPerInstallment,
                'due_date'        => $dueDate->toDateString(),
                'status'          => $status,
            ]);
        }

        return $plan;
    }

    /**
     * Process (mock) a payment — always succeeds.
     * Sets paid_at, transaction_id, and status to paid.
     *
     * @param  array<string, mixed>  $data  Must include: payment_method
     */
    public function processPayment(Payment $payment, array $data): Payment
    {
        $payment->update([
            'status'         => 'paid',
            'paid_at'        => now(),
            'payment_method' => $data['payment_method'],
            'transaction_id' => 'MOCK-' . strtoupper(Str::random(12)),
        ]);

        $this->checkAndCompletePaymentPlan($payment);

        return $payment->fresh();
    }

    /**
     * Return all overdue payments for a given school.
     */
    public function getOverduePayments(School $school): Collection
    {
        return Payment::where('school_id', $school->id)
            ->where('status', 'overdue')
            ->with(['enrollment.student', 'enrollment.program'])
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Mark a payment plan as completed if all its payments are paid.
     */
    private function checkAndCompletePaymentPlan(Payment $payment): void
    {
        if (! $payment->payment_plan_id) {
            return;
        }

        $plan = $payment->paymentPlan;

        $unpaid = $plan->payments()->whereNotIn('status', ['paid'])->count();

        if ($unpaid === 0) {
            $plan->update(['status' => 'completed']);
        }
    }
}
