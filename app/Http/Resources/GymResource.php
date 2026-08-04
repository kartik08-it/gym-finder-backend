<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GymResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'address' => $this->address,
            'area' => $this->area,
            'city' => $this->whenLoaded('city', fn () => [
                'id' => $this->city->id,
                'name' => $this->city->name,
                'slug' => $this->city->slug,
            ]),
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'cover_image' => $this->cover_image,
            'opening_time' => $this->opening_time,
            'closing_time' => $this->closing_time,
            'is_24x7' => (bool) $this->is_24x7,
            'ladies_only' => (bool) $this->ladies_only,
            'gender_preference' => $this->gender_preference,
            'trainer_count' => $this->trainer_count,
            'crowd_level' => $this->crowd_level,
            'starting_price' => (float) $this->starting_price,
            'rating_avg' => (float) $this->rating_avg,
            'rating_count' => $this->rating_count,
            'is_verified' => (bool) $this->is_verified,
            'is_featured' => (bool) $this->is_featured,
            'status' => $this->status?->value ?? $this->status,
            'distance_km' => $this->when(isset($this->distance_km), fn () => round((float) $this->distance_km, 2)),
            'amenities' => AmenityResource::collection($this->whenLoaded('amenities')),
            'images' => GymImageResource::collection($this->whenLoaded('images')),
            'videos' => $this->whenLoaded('videos'),
            'trainers' => $this->whenLoaded('trainers'),
            'plans' => GymPlanResource::collection($this->whenLoaded('plans')),
            'offers' => $this->whenLoaded('offers'),
            'owner' => $this->whenLoaded('owner', fn () => [
                'id' => $this->owner->id,
                'name' => $this->owner->name,
                'avatar_url' => $this->owner->avatar_url,
            ]),
        ];
    }
}
