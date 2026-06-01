<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentPlan extends Model
{
    protected $fillable = [
        'enrollment_id',
        'school_id',
        'total_amount',
        'amount_per_installment',
        'installment_count',
        'frequency',
        'start_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'float',
            'amount_per_installment' => 'float',
            'start_date' => 'date',
            'status' => 'string',
            'frequency' => 'string',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
