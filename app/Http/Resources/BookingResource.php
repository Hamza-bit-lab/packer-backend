<?php

namespace App\Http\Resources;

use App\Enums\BookingStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $phoneRevealed = in_array($this->status, [
            BookingStatus::Accepted,
            BookingStatus::InTransit,
            BookingStatus::ArrivedAtDestination,
            BookingStatus::Completed,
        ], true);

        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'estimated_cost' => (float) $this->estimated_cost,
            'payer' => $this->payer->value,
            'trip' => new TripResource($this->whenLoaded('trip')),
            'good' => new GoodResource($this->whenLoaded('good')),
            'sender' => new UserResource($this->whenLoaded('sender')),
            'traveler' => new UserResource($this->whenLoaded('traveler')),
            'sender_phone_number' => $phoneRevealed ? $this->sender?->phone_number : null,
            'traveler_phone_number' => $phoneRevealed ? $this->traveler?->phone_number : null,
            'inspection_confirmed_at' => $this->inspection_confirmed_at,
            'delivery_proof_photo_url' => $this->delivery_proof_photo_url,
            'created_at' => $this->created_at,
        ];
    }
}
