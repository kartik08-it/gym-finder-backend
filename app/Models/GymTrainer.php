<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GymTrainer extends Model
{
    protected $fillable = [
        'gym_id', 'name', 'avatar_url', 'gender',
        'experience_years', 'specialization', 'bio', 'rating_avg',
    ];

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }
}
