<?php

namespace App\Notifications;

use App\Models\FeatureRequest;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\Notification;

/**
 * Sent to the author when their feature request reaches a round number of
 * upvotes — one notification per milestone rather than one per vote, which
 * would just be noise on a popular idea.
 */
class FeatureRequestMilestone extends Notification
{
    public function __construct(public FeatureRequest $featureRequest, public int $upvotes) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'feature_request_votes',
            'title' => 'People like your idea',
            'body' => "\"{$this->featureRequest->title}\" just reached {$this->upvotes} upvotes.",
            'feature_request_id' => $this->featureRequest->id,
        ];
    }
}
