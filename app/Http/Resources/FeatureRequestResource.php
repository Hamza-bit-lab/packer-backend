<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeatureRequestResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value,
            'submitted_by' => $this->whenLoaded('user', fn () => $this->user->name),
            'is_mine' => $this->user_id === $request->user()?->id,
            'upvotes_count' => $this->upvotes_count ?? 0,
            'downvotes_count' => $this->downvotes_count ?? 0,
            // `votes` is always eager-loaded pre-filtered to just the
            // current user's own vote (see FeatureRequestController), so
            // there's at most one row here.
            'my_vote' => $this->whenLoaded('votes', fn () => $this->votes->first()?->direction->value),
            'created_at' => $this->created_at,
        ];
    }
}
