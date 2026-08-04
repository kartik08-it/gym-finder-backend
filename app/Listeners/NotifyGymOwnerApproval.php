<?php

namespace App\Listeners;

use App\Events\GymApproved;
use App\Notifications\GymApprovedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyGymOwnerApproval implements ShouldQueue
{
    public function handle(GymApproved $event): void
    {
        $event->gym->owner?->notify(new GymApprovedNotification($event->gym));
    }
}
