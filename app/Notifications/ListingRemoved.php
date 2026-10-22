<?php

namespace App\Notifications;

use App\Notifications\Channels\FcmChannel;

/**
 * A trip, item or account went away while a booking on it was still live
 * (pending / accepted / in transit / arrived). The booking is deleted along
 * with it, so this is the only way the other party ever finds out — and it
 * deliberately carries no booking_id, since that booking no longer exists to
 * open.
 */
class ListingRemoved extends PreferenceGatedNotification
{
    public function __construct(public string $title, public string $body) {}

    protected function preferenceColumn(): string
    {
        return 'notify_bookings';
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
        return ['type' => 'listing_removed', 'title' => $this->title, 'body' => $this->body];
    }
}
