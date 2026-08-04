<?php

namespace App\Repositories\Contracts;

use App\Models\Gym;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface GymRepositoryInterface extends BaseRepositoryInterface
{
    public function search(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function nearby(float $lat, float $lng, float $radiusKm = 10, int $limit = 20): Collection;

    public function findBySlug(string $slug): ?Gym;

    public function forOwner(int $ownerId, int $perPage = 15): LengthAwarePaginator;

    public function recalculateRating(int $gymId): void;
}
