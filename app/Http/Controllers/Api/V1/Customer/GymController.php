<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\Gym\StoreGymRequest;
use App\Http\Resources\GymResource;
use App\Repositories\Contracts\GymRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GymController extends Controller
{
    public function __construct(protected GymRepositoryInterface $gyms) {}

    /**
     * GET /api/v1/gyms — searchable / filterable listing.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'q' => 'nullable|string|max:120',
            'city_id' => 'nullable|integer',
            'min_rating' => 'nullable|numeric|min:0|max:5',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'gender' => 'nullable|in:unisex,male,female',
            'ladies_only' => 'nullable|boolean',
            'is_24x7' => 'nullable|boolean',
            'verified' => 'nullable|boolean',
            'open_now' => 'nullable|boolean',
            'amenity_ids' => 'nullable|array',
            'amenity_ids.*' => 'integer',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0.1|max:100',
            'sort' => 'nullable|in:rating,price_asc,price_desc,newest',
            'per_page' => 'nullable|integer|min:1|max:60',
        ]);

        $perPage = $filters['per_page'] ?? 15;
        $result = $this->gyms->search($filters, $perPage);

        return $this->ok([
            'items' => GymResource::collection($result->items()),
            'meta' => [
                'current_page' => $result->currentPage(),
                'last_page' => $result->lastPage(),
                'per_page' => $result->perPage(),
                'total' => $result->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/gyms/nearby?lat=..&lng=..&radius=..
     */
    public function nearby(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0.1|max:100',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $items = $this->gyms->nearby(
            (float) $data['lat'],
            (float) $data['lng'],
            (float) ($data['radius'] ?? 10),
            (int) ($data['limit'] ?? 20),
        );

        return $this->ok(GymResource::collection($items));
    }

    /**
     * GET /api/v1/gyms/{slug}
     */
    public function show(string $slug): JsonResponse
    {
        $gym = $this->gyms->findBySlug($slug);
        if (! $gym) {
            return $this->fail('Gym not found.', 404);
        }
        $this->authorize('view', $gym);
        $gym->increment('view_count');
        return $this->ok(new GymResource($gym));
    }

    /**
     * POST /api/v1/gyms — gym owners create a new listing (goes to PENDING).
     */
    public function store(StoreGymRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['owner_id'] = $request->user()->id;
        $data['slug'] = \Str::slug($data['name']).'-'.substr(bin2hex(random_bytes(3)), 0, 5);
        $amenityIds = $data['amenity_ids'] ?? [];
        unset($data['amenity_ids']);

        $gym = $this->gyms->create($data);
        if ($amenityIds) {
            $gym->amenities()->sync($amenityIds);
        }

        return $this->ok(new GymResource($gym->load(['city', 'amenities'])), 'Gym submitted for approval.', 201);
    }

    public function compare(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => 'required|array|min:2|max:4',
            'ids.*' => 'integer|exists:gyms,id',
        ]);
        $gyms = \App\Models\Gym::with(['city', 'amenities', 'plans', 'images'])
            ->whereIn('id', $data['ids'])->get();
        return $this->ok(GymResource::collection($gyms));
    }
}
