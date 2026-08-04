<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'title' => $this->title,
            'comment' => $this->comment,
            'likes_count' => $this->likes_count,
            'is_verified' => (bool) $this->is_verified,
            'owner_reply' => $this->owner_reply,
            'owner_replied_at' => $this->owner_replied_at,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar_url' => $this->user->avatar_url,
            ]),
            'images' => $this->whenLoaded('images', fn () => $this->images->pluck('url')),
            'created_at' => $this->created_at,
        ];
    }
}
