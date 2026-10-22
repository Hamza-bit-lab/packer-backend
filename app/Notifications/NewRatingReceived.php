<?php

namespace App\Notifications;

use App\Models\Rating;
use App\Notifications\Channels\FcmChannel;

class NewRatingReceived extends PreferenceGatedNotification
{
    public function __construct(public Rating $rating) {}

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
        $this->rating->loadMissing('rater');

        return [
            'type' => 'new_rating',
            'title' => 'New rating',
            'body' => "{$this->rating->rater->name} rated you {$this->rating->stars} star".($this->rating->stars === 1 ? '' : 's').'.',
            'booking_id' => $this->rating->booking_id,
        ];
    }
}
