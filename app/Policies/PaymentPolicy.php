<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->school_id === $payment->school_id;
    }

    public function pay(User $user, Payment $payment): bool
    {
        return $user->school_id === $payment->school_id;
    }
}
