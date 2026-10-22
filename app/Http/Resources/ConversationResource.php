<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'other_user' => new UserResource($this->otherUser),
            'last_message' => new MessageResource($this->lastMessage),
            'unread_count' => $this->unreadCount,
        ];
    }
}
