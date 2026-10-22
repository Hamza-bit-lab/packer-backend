<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Notifications\Channels\FcmChannel;

class BookingInTransit extends PreferenceGatedNotification
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
        $this->booking->loadMissing('traveler', 'trip');

        return [
            'type' => 'booking_in_transit',
            'title' => 'Your item is on its way',
            'body' => "{$this->booking->traveler->name} has departed for {$this->booking->trip->to_city} with your item.",
            'booking_id' => $this->booking->id,
        ];
    }
}
