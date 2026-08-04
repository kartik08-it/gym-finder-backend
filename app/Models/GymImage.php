<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GymImage extends Model
{
    protected $fillable = ['gym_id', 'url', 'public_id', 'caption', 'sort_order'];

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }
}
