<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoodResource extends JsonResource
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
            'from_city' => $this->from_city,
            'to_city' => $this->to_city,
            'item_description' => $this->item_description,
            // Only the sender needs this — it's a third party's address, not
            // theirs to hand out to everyone browsing the feed. Named guard
            // since this route sits outside auth:sanctum (guests can browse).
            'receiver_email' => $request->user('sanctum')?->id === $this->user_id
                ? $this->receiver_email
                : null,
            'weight_kg' => (float) $this->weight_kg,
            'offered_reward' => (float) $this->offered_reward,
            'currency_type' => $this->currency_type,
            'photo_url' => $this->photo_url,
            'views_count' => $this->views_count,
            'pickup_preference' => $this->pickup_preference,
            'sender' => new UserResource($this->whenLoaded('user')),
            'sender_phone_number' => $this->whenLoaded('user', fn () => $this->user->phone_number),
            'created_at' => $this->created_at,
        ];
    }
}
