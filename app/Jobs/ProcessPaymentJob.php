<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessPaymentJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public readonly Payment $payment,
        public readonly array $data,
    ) {}

    public function handle(PaymentService $paymentService): void
    {
        $paymentService->processPayment($this->payment, $this->data);
    }
}
