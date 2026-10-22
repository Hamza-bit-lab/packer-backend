<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RatingResource extends JsonResource
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
            'booking_id' => $this->booking_id,
            'stars' => $this->stars,
            'comment' => $this->comment,
            'rater' => new UserResource($this->whenLoaded('rater')),
            'ratee' => new UserResource($this->whenLoaded('ratee')),
            'created_at' => $this->created_at,
        ];
    }
}
