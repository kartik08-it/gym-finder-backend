<?php

namespace App\Listeners;

use App\Events\BookingConfirmed;
use App\Models\MembershipHistory;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateMembershipRecord implements ShouldQueue
{
    public function handle(BookingConfirmed $event): void
    {
        $b = $event->booking;
        MembershipHistory::create([
            'user_id' => $b->user_id,
            'booking_id' => $b->id,
            'gym_id' => $b->gym_id,
            'gym_plan_id' => $b->gym_plan_id,
            'starts_on' => $b->starts_on,
            'ends_on' => $b->ends_on,
            'status' => 'active',
        ]);
    }
}
