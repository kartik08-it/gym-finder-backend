<?php

namespace App\Http\Controllers\Api\V1\Owner;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\BookingResource;
use App\Http\Resources\GymResource;
use App\Http\Resources\ReviewResource;
use App\Models\Booking;
use App\Models\Gym;
use App\Models\GymPlan;
use App\Models\GymReview;
use App\Repositories\Contracts\GymRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerController extends Controller
{
    public function __construct(protected GymRepositoryInterface $gyms) {}

    public function dashboard(Request $request): JsonResponse
    {
        $ownerId = $request->user()->id;

        $gymIds = Gym::where('owner_id', $ownerId)->pluck('id');
        $stats = [
            'total_gyms' => $gymIds->count(),
            'total_bookings' => Booking::whereIn('gym_id', $gymIds)->count(),
            'active_members' => Booking::whereIn('gym_id', $gymIds)
                ->whereIn('status', ['confirmed', 'active'])->count(),
            'revenue' => (float) Booking::whereIn('gym_id', $gymIds)
                ->whereIn('status', ['confirmed', 'active', 'expired'])->sum('total_amount'),
            'avg_rating' => (float) round(Gym::whereIn('id', $gymIds)->avg('rating_avg') ?? 0, 2),
        ];

        return $this->ok([
            'stats' => $stats,
            'my_gyms' => GymResource::collection(Gym::whereIn('id', $gymIds)->with('city')->get()),
        ]);
    }

    public function myGyms(Request $request): JsonResponse
    {
        return $this->ok(GymResource::collection(
            $this->gyms->forOwner($request->user()->id)
        ));
    }

    public function bookings(Request $request): JsonResponse
    {
        $gymIds = Gym::where('owner_id', $request->user()->id)->pluck('id');
        $bookings = Booking::with(['user:id,name,email,phone', 'plan', 'gym:id,name'])
            ->whereIn('gym_id', $gymIds)
            ->latest()->paginate(15);

        return $this->ok([
            'items' => BookingResource::collection($bookings->items()),
            'meta' => [
                'total' => $bookings->total(),
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
            ],
        ]);
    }

    public function reviews(Request $request): JsonResponse
    {
        $gymIds = Gym::where('owner_id', $request->user()->id)->pluck('id');
        $reviews = GymReview::whereIn('gym_id', $gymIds)
            ->with(['user', 'images'])->latest()->paginate(15);
        return $this->ok(ReviewResource::collection($reviews));
    }

    public function replyReview(Request $request, GymReview $review): JsonResponse
    {
        $this->authorize('reply', $review);
        $data = $request->validate(['reply' => 'required|string|max:1000']);
        $review->update([
            'owner_reply' => $data['reply'],
            'owner_replied_at' => now(),
        ]);
        return $this->ok(new ReviewResource($review->load(['user', 'images'])));
    }

    // ---------- Plans ----------
    public function storePlan(Request $request, Gym $gym): JsonResponse
    {
        $this->authorize('update', $gym);
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'duration_type' => 'required|in:daily,weekly,monthly,quarterly,half_yearly,yearly,personal_training,diet_consultation',
            'duration_days' => 'required|integer|min:1|max:730',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'features' => 'nullable|array',
            'is_popular' => 'boolean',
        ]);
        $plan = $gym->plans()->create($data + ['is_active' => true]);
        return $this->ok($plan, 'Plan created.', 201);
    }

    public function updatePlan(Request $request, GymPlan $plan): JsonResponse
    {
        $this->authorize('update', $plan->gym);
        $plan->update($request->only([
            'name', 'duration_type', 'duration_days', 'price', 'discount_price',
            'description', 'features', 'is_popular', 'is_active',
        ]));
        return $this->ok($plan->fresh());
    }

    public function deletePlan(GymPlan $plan): JsonResponse
    {
        $this->authorize('update', $plan->gym);
        $plan->delete();
        return $this->ok(null, 'Plan deleted.');
    }
}
