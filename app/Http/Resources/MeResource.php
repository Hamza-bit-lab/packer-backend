<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeResource extends JsonResource
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
            'email' => $this->email,
            'email_verified' => $this->hasVerifiedEmail(),
            'phone_number' => $this->phone_number,
            'country' => $this->country,
            'profile_pic_url' => $this->profile_pic_url,
            'average_rating' => (float) $this->average_rating,
            'total_reviews' => $this->total_reviews,
            'completed_deliveries_count' => $this->completed_deliveries_count,
            'id_verification_status' => $this->latestIdVerification()?->status->value,
            'created_at' => $this->created_at,
            'notify_bookings' => $this->notify_bookings,
            'notify_messages' => $this->notify_messages,
            'notify_route_alerts' => $this->notify_route_alerts,
        ];
    }
}
