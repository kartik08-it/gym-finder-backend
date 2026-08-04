<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipHistory extends Model
{
    protected $table = 'membership_history';

    protected $fillable = [
        'user_id', 'booking_id', 'gym_id', 'gym_plan_id',
        'starts_on', 'ends_on', 'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }
}
