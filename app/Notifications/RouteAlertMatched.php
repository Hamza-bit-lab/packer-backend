<?php

namespace App\Notifications;

use App\Models\Good;
use App\Models\Trip;
use App\Notifications\Channels\FcmChannel;

class RouteAlertMatched extends PreferenceGatedNotification
{
    public function __construct(public Trip|Good $listing) {}

    protected function preferenceColumn(): string
    {
        return 'notify_route_alerts';
    }

    /**
     * @return array<int, string>
     */
    protected function allChannels(): array
    {
        return ['database', FcmChannel::class];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $isTrip = $this->listing instanceof Trip;

        return [
            'type' => 'route_alert_matched',
            'title' => $isTrip ? 'New trip on your route' : 'New item on your route',
            'body' => "Someone just posted {$this->listing->from_city} \u{2192} {$this->listing->to_city} \u{2014} matching one of your route alerts.",
            'trip_id' => $isTrip ? $this->listing->id : null,
            'good_id' => $isTrip ? null : $this->listing->id,
        ];
    }
}
