<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Gym;
use App\Models\GymReview;
use App\Policies\BookingPolicy;
use App\Policies\GymPolicy;
use App\Policies\ReviewPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Gym::class => GymPolicy::class,
        Booking::class => BookingPolicy::class,
        GymReview::class => ReviewPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
