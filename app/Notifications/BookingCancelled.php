<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\User;
use App\Notifications\Channels\FcmChannel;

class BookingCancelled extends PreferenceGatedNotification
{
    public function __construct(public Booking $booking, public User $cancelledBy) {}

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
        return [
            'type' => 'booking_cancelled',
            'title' => 'Request cancelled',
            'body' => "{$this->cancelledBy->name} cancelled the request for this shipment.",
            'booking_id' => $this->booking->id,
        ];
    }
}
