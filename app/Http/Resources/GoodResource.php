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
