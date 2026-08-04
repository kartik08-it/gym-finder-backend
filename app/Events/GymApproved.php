<?php

namespace App\Events;

use App\Models\Gym;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GymApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(public Gym $gym) {}
}
