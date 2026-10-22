<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'country' => $this->country,
            'profile_pic_url' => $this->profile_pic_url,
            'average_rating' => (float) $this->average_rating,
            'total_reviews' => $this->total_reviews,
            'completed_deliveries_count' => $this->completed_deliveries_count,
            'email_verified' => $this->hasVerifiedEmail(),
            'id_verified' => $this->isIdVerified(),
            'created_at' => $this->created_at,
            'is_online' => $this->isOnline(),
            'last_seen_at' => $this->last_seen_at,
        ];
    }
}
