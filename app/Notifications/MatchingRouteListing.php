<?php

namespace App\Notifications;

use App\Models\Good;
use App\Models\Trip;
use App\Notifications\Channels\FcmChannel;

/**
 * Sent the moment a new Trip/Good is posted to everyone who already has the
 * complementary listing (a Trip if this is a Good, a Good if this is a
 * Trip) on the exact same route — so a match doesn't depend on either side
 * having set up an explicit route alert first.
 */
class MatchingRouteListing extends PreferenceGatedNotification
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
        $this->listing->loadMissing('user');
        $isTrip = $this->listing instanceof Trip;
        $posterName = $this->listing->user->name;
        $route = "{$this->listing->from_city} \u{2192} {$this->listing->to_city}";

        return [
            'type' => 'matching_route_listing',
            'title' => $isTrip ? 'A traveler is going your way' : 'Someone needs your route',
            'body' => $isTrip
                ? "{$posterName} is flying {$route} — perfect timing for the package you're sending on this route."
                : "{$posterName} wants to send a package {$route} — you're already flying this route, so this could be worth a look.",
            'trip_id' => $isTrip ? $this->listing->id : null,
            'good_id' => $isTrip ? null : $this->listing->id,
        ];
    }
}
