<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\GymResource;
use App\Models\Gym;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->favorites()->with(['city', 'images'])->get();
        return $this->ok(GymResource::collection($items));
    }

    public function toggle(Request $request, Gym $gym): JsonResponse
    {
        $result = $request->user()->favorites()->toggle($gym->id);
        $favorited = ! empty($result['attached']);
        return $this->ok(['favorited' => $favorited]);
    }
}
