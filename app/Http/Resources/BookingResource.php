<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_number' => $this->booking_number,
            'status' => $this->status?->value ?? $this->status,
            'starts_on' => $this->starts_on?->toDateString(),
            'ends_on' => $this->ends_on?->toDateString(),
            'base_amount' => (float) $this->base_amount,
            'discount_amount' => (float) $this->discount_amount,
            'tax_amount' => (float) $this->tax_amount,
            'total_amount' => (float) $this->total_amount,
            'gym' => $this->whenLoaded('gym', fn () => [
                'id' => $this->gym->id,
                'name' => $this->gym->name,
                'slug' => $this->gym->slug,
                'cover_image' => $this->gym->cover_image,
            ]),
            'plan' => $this->whenLoaded('plan', fn () => new GymPlanResource($this->plan)),
            'payment' => $this->whenLoaded('payment', fn () => [
                'status' => $this->payment?->status,
                'gateway' => $this->payment?->gateway,
                'gateway_order_id' => $this->payment?->gateway_order_id,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
