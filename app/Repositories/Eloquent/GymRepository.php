<?php

namespace App\Repositories\Eloquent;

use App\Enums\GymStatus;
use App\Models\Gym;
use App\Repositories\Contracts\GymRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class GymRepository extends BaseRepository implements GymRepositoryInterface
{
    public function __construct(Gym $model)
    {
        parent::__construct($model);
    }

    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $q = $this->query()->with(['city', 'amenities', 'images'])->approved();

        if (! empty($filters['q'])) {
            $term = $filters['q'];
            $q->where(function ($sub) use ($term) {
                $sub->where('name', 'like', "%$term%")
                    ->orWhere('description', 'like', "%$term%")
                    ->orWhere('address', 'like', "%$term%");
            });
        }

        if (! empty($filters['city_id'])) {
            $q->where('city_id', $filters['city_id']);
        }

        if (! empty($filters['min_rating'])) {
            $q->where('rating_avg', '>=', $filters['min_rating']);
        }

        if (! empty($filters['max_price'])) {
            $q->where('starting_price', '<=', $filters['max_price']);
        }

        if (! empty($filters['min_price'])) {
            $q->where('starting_price', '>=', $filters['min_price']);
        }

        if (! empty($filters['gender']) && $filters['gender'] !== 'unisex') {
            $q->where('gender_preference', $filters['gender']);
        }

        if (! empty($filters['ladies_only'])) {
            $q->where('ladies_only', true);
        }

        if (! empty($filters['is_24x7'])) {
            $q->where('is_24x7', true);
        }

        if (! empty($filters['verified'])) {
            $q->where('is_verified', true);
        }

        if (! empty($filters['open_now'])) {
            $now = now()->format('H:i:s');
            $q->where(function ($sub) use ($now) {
                $sub->where('is_24x7', true)
                    ->orWhere(function ($x) use ($now) {
                        $x->where('opening_time', '<=', $now)
                          ->where('closing_time', '>=', $now);
                    });
            });
        }

        if (! empty($filters['amenity_ids']) && is_array($filters['amenity_ids'])) {
            $ids = $filters['amenity_ids'];
            $count = count($ids);
            $q->whereHas('amenities', fn ($a) => $a->whereIn('amenities.id', $ids), '=', $count);
        }

        if (! empty($filters['lat']) && ! empty($filters['lng'])) {
            $radius = $filters['radius'] ?? 10;
            $q->nearby((float) $filters['lat'], (float) $filters['lng'], (float) $radius);
        } else {
            $sort = $filters['sort'] ?? 'rating';
            match ($sort) {
                'price_asc' => $q->orderBy('starting_price'),
                'price_desc' => $q->orderByDesc('starting_price'),
                'newest' => $q->latest(),
                default => $q->orderByDesc('rating_avg')->orderByDesc('rating_count'),
            };
        }

        return $q->paginate($perPage)->withQueryString();
    }

    public function nearby(float $lat, float $lng, float $radiusKm = 10, int $limit = 20): Collection
    {
        return $this->query()->approved()
            ->with(['city', 'images'])
            ->nearby($lat, $lng, $radiusKm)
            ->limit($limit)
            ->get();
    }

    public function findBySlug(string $slug): ?Gym
    {
        return $this->query()
            ->with(['city.state', 'owner:id,name,avatar_url', 'amenities', 'images', 'videos',
                    'trainers', 'plans', 'offers'])
            ->where('slug', $slug)
            ->first();
    }

    public function forOwner(int $ownerId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()->where('owner_id', $ownerId)
            ->with('city')->latest()->paginate($perPage);
    }

    public function recalculateRating(int $gymId): void
    {
        $gym = $this->findOrFail($gymId);
        $agg = $gym->reviews()->where('status', 'published')
            ->selectRaw('AVG(rating) as avg_r, COUNT(*) as cnt')->first();
        $gym->update([
            'rating_avg' => round((float) ($agg->avg_r ?? 0), 2),
            'rating_count' => (int) ($agg->cnt ?? 0),
        ]);
    }
}
