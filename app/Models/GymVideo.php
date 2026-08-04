<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GymVideo extends Model
{
    protected $fillable = ['gym_id', 'url', 'thumbnail_url', 'title'];

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }
}
