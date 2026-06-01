<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'enrollment_id',
        'school_id',
        'payment_plan_id',
        'amount',
        'due_date',
        'paid_at',
        'status',
        'payment_method',
        'transaction_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'status' => 'string',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function paymentPlan(): BelongsTo
    {
        return $this->belongsTo(PaymentPlan::class);
    }
}
