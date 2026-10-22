<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Notifications\Channels\FcmChannel;

class BookingCompleted extends PreferenceGatedNotification
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
            'type' => 'booking_completed',
            'title' => 'Delivered!',
            'body' => "{$this->booking->traveler->name} marked your item as delivered. Don't forget to rate them.",
            'booking_id' => $this->booking->id,
        ];
    }
}
