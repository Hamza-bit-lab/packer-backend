<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Notifications\Channels\FcmChannel;

class BookingAccepted extends PreferenceGatedNotification
{
    public function __construct(public Booking $booking) {}

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
        $this->booking->loadMissing('traveler');

        return [
            'type' => 'booking_accepted',
            'title' => 'Request accepted',
            'body' => "{$this->booking->traveler->name} accepted your request. Contact details are now unlocked.",
            'booking_id' => $this->booking->id,
        ];
    }
}
