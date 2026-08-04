<?php

namespace App\Providers;

use App\Events\BookingConfirmed;
use App\Events\GymApproved;
use App\Events\ReviewSubmitted;
use App\Listeners\CreateMembershipRecord;
use App\Listeners\NotifyGymOwnerApproval;
use App\Listeners\RecalculateGymRating;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        BookingConfirmed::class => [CreateMembershipRecord::class],
        GymApproved::class => [NotifyGymOwnerApproval::class],
        ReviewSubmitted::class => [RecalculateGymRating::class],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
