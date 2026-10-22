<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\Messages\MailMessage;

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
        // Delivery confirmation also goes by mail — it's the sender's
        // receipt that their item actually arrived, worth having outside
        // the app even if push/in-app are muted.
        return ['database', 'mail', FcmChannel::class];
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

    public function toMail(object $notifiable): MailMessage
    {
        $this->booking->loadMissing('traveler', 'good', 'trip');

        $mail = (new MailMessage)
            ->subject('Your item was delivered — Pack&Carry')
            ->greeting("Hi {$notifiable->name},")
            ->line("{$this->booking->traveler->name} marked your item as delivered on the trip from {$this->booking->trip->from_city} to {$this->booking->trip->to_city}.")
            ->line("Item: {$this->booking->good->item_description} ({$this->booking->good->weight_kg} kg)");

        if ($this->booking->delivery_proof_photo_url) {
            $mail->line('A delivery photo was attached as proof in the app.');
        }

        return $mail
            ->action('Rate your experience', url('/'))
            ->line('Thank you for using Pack&Carry.');
    }
}
