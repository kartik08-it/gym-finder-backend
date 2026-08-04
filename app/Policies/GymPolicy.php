<?php

namespace App\Policies;

use App\Enums\GymStatus;
use App\Models\Gym;
use App\Models\User;

class GymPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Gym $gym): bool
    {
        if ($gym->status === GymStatus::APPROVED) return true;
        return $user && ($user->isAdmin() || $user->id === $gym->owner_id);
    }

    public function create(User $user): bool
    {
        return $user->isGymOwner() || $user->isAdmin();
    }

    public function update(User $user, Gym $gym): bool
    {
        return $user->isAdmin() || $user->id === $gym->owner_id;
    }

    public function delete(User $user, Gym $gym): bool
    {
        return $user->isAdmin() || $user->id === $gym->owner_id;
    }

    public function approve(User $user): bool
    {
        return $user->isAdmin();
    }
}
