<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RouteAlertResource extends JsonResource
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
            'watch_type' => $this->watch_type,
            'from_city' => $this->from_city,
            'to_city' => $this->to_city,
            'created_at' => $this->created_at,
        ];
    }
}
