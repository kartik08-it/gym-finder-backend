<?php

namespace App\Policies;

use App\Models\GymReview;
use App\Models\User;

class ReviewPolicy
{
    public function create(User $user): bool
    {
        return $user->isCustomer();
    }

    public function update(User $user, GymReview $review): bool
    {
        return $user->id === $review->user_id;
    }

    public function delete(User $user, GymReview $review): bool
    {
        return $user->id === $review->user_id || $user->isAdmin();
    }

    public function reply(User $user, GymReview $review): bool
    {
        return $user->id === $review->gym->owner_id || $user->isAdmin();
    }
}
