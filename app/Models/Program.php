<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'description',
        'duration_weeks',
        'tuition_cost',
        'start_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tuition_cost' => 'float',
            'start_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }
}
