<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Notifications\Channels\FcmChannel;

class BookingArrived extends PreferenceGatedNotification
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
            'type' => 'booking_arrived',
            'title' => 'Your item has arrived',
            'body' => "{$this->booking->traveler->name} has arrived in {$this->booking->trip->to_city}. Coordinate the handover to receive it.",
            'booking_id' => $this->booking->id,
        ];
    }
}
