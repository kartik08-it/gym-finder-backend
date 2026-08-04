<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\AmenityResource;
use App\Models\Amenity;
use App\Models\City;
use Illuminate\Http\JsonResponse;

class ReferenceController extends Controller
{
    public function amenities(): JsonResponse
    {
        return $this->ok(AmenityResource::collection(
            Amenity::where('is_active', true)->orderBy('category')->orderBy('name')->get()
        ));
    }

    public function cities(): JsonResponse
    {
        return $this->ok(City::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'latitude', 'longitude']));
    }
}
