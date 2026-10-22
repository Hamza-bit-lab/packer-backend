<?php

namespace App\Notifications;

use App\Enums\FeatureRequestStatus;
use App\Models\FeatureRequest;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\Notification;

/**
 * Sent to the author when an admin moves their feature request to a new
 * status (Open / In Progress / Completed). Not mutable from Settings —
 * it's a rare, direct reply to something the user themselves submitted.
 */
class FeatureRequestStatusChanged extends Notification
{
    public function __construct(public FeatureRequest $featureRequest, public FeatureRequestStatus $status) {}

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
        $body = match ($this->status) {
            FeatureRequestStatus::InProgress => "We've started working on \"{$this->featureRequest->title}\".",
            FeatureRequestStatus::Completed => "\"{$this->featureRequest->title}\" is now live — thanks for the idea!",
            FeatureRequestStatus::Open => "\"{$this->featureRequest->title}\" was moved back to Open.",
        };

        return [
            'type' => 'feature_request_status',
            'title' => 'Your feature request was updated',
            'body' => $body,
            'feature_request_id' => $this->featureRequest->id,
        ];
    }
}
