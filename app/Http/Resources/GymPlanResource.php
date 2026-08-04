<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GymPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'gym_id' => $this->gym_id,
            'name' => $this->name,
            'duration_type' => $this->duration_type,
            'duration_days' => $this->duration_days,
            'price' => (float) $this->price,
            'discount_price' => $this->discount_price ? (float) $this->discount_price : null,
            'effective_price' => $this->effectivePrice(),
            'features' => $this->features,
            'is_popular' => (bool) $this->is_popular,
        ];
    }
}
