<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Events\ReviewSubmitted;
use App\Models\Gym;
use App\Models\GymReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request, Gym $gym): JsonResponse
    {
        $reviews = $gym->reviews()->with(['user', 'images'])->latest()->paginate(10);
        return $this->ok([
            'items' => ReviewResource::collection($reviews->items()),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    public function store(StoreReviewRequest $request): JsonResponse
    {
        $user = $request->user();

        // Only allow reviews from users who have booked & confirmed at this gym.
        $isVerified = $user->bookings()
            ->where('gym_id', $request->gym_id)
            ->whereIn('status', ['confirmed', 'active', 'expired'])
            ->exists();

        if (! $isVerified) {
            return $this->fail('Only verified members can review this gym.', 403);
        }

        $review = GymReview::updateOrCreate(
            ['gym_id' => $request->gym_id, 'user_id' => $user->id],
            [
                'rating' => $request->rating,
                'title' => $request->title,
                'comment' => $request->comment,
                'is_verified' => true,
                'status' => 'published',
            ]
        );

        if ($request->filled('images')) {
            $review->images()->delete();
            foreach ($request->images as $url) {
                $review->images()->create(['url' => $url]);
            }
        }

        event(new ReviewSubmitted($review));
        return $this->ok(new ReviewResource($review->load(['user', 'images'])), 'Review saved.', 201);
    }

    public function like(Request $request, GymReview $review): JsonResponse
    {
        $userId = $request->user()->id;
        $exists = \DB::table('review_likes')
            ->where('review_id', $review->id)->where('user_id', $userId)->exists();

        if ($exists) {
            \DB::table('review_likes')->where('review_id', $review->id)->where('user_id', $userId)->delete();
            $review->decrement('likes_count');
        } else {
            \DB::table('review_likes')->insert(['review_id' => $review->id, 'user_id' => $userId]);
            $review->increment('likes_count');
        }
        return $this->ok(['likes_count' => $review->fresh()->likes_count]);
    }

    public function report(Request $request, GymReview $review): JsonResponse
    {
        $data = $request->validate([
            'reason' => 'required|string|max:180',
            'details' => 'nullable|string|max:1000',
        ]);
        \DB::table('review_reports')->insert([
            'review_id' => $review->id,
            'user_id' => $request->user()->id,
            'reason' => $data['reason'],
            'details' => $data['details'] ?? null,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return $this->ok(null, 'Report submitted.');
    }
}
