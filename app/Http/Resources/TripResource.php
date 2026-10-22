<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripResource extends JsonResource
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
            'departure_date_time' => $this->departure_date_time,
            'airline_name' => $this->airline_name,
            'max_weight_kg' => (float) $this->max_weight_kg,
            'rate_per_kg' => (float) $this->rate_per_kg,
            'currency_type' => $this->currency_type,
            'photo_url' => $this->photo_url,
            'views_count' => $this->views_count,
            'delivery_method' => $this->delivery_method,
            'pickup_method' => $this->pickup_method,
            'traveler' => new UserResource($this->whenLoaded('user')),
            'traveler_phone_number' => $this->whenLoaded('user', fn () => $this->user->phone_number),
            'created_at' => $this->created_at,
        ];
    }
}
