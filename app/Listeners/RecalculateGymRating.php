<?php

namespace App\Listeners;

use App\Events\ReviewSubmitted;
use App\Repositories\Contracts\GymRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecalculateGymRating implements ShouldQueue
{
    public function __construct(protected GymRepositoryInterface $gyms) {}

    public function handle(ReviewSubmitted $event): void
    {
        $this->gyms->recalculateRating($event->review->gym_id);
    }
}
