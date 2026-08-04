<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GymPlan extends Model
{
    protected $fillable = [
        'gym_id', 'name', 'duration_type', 'duration_days',
        'price', 'discount_price', 'description', 'features',
        'is_active', 'is_popular',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'price' => 'decimal:2',
            'discount_price' => 'decimal:2',
        ];
    }

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    public function effectivePrice(): float
    {
        return (float) ($this->discount_price ?? $this->price);
    }
}
