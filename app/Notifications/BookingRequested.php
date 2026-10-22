<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\Messages\MailMessage;

class BookingRequested extends PreferenceGatedNotification
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
        return ['database', 'mail', FcmChannel::class];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->booking->loadMissing('sender', 'good');

        return [
            'type' => 'booking_requested',
            'title' => 'New shipping request',
            'body' => "{$this->booking->sender->name} wants to send \"{$this->booking->good->item_description}\" on your trip.",
            'booking_id' => $this->booking->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->booking->loadMissing('sender', 'good', 'trip');

        return (new MailMessage)
            ->subject('New shipping request on Pack&Carry')
            ->greeting("Hi {$notifiable->name},")
            ->line("{$this->booking->sender->name} wants to send an item on your trip from {$this->booking->trip->from_city} to {$this->booking->trip->to_city}.")
            ->line("Item: {$this->booking->good->item_description} ({$this->booking->good->weight_kg} kg)")
            ->line("Estimated cost: {$this->booking->estimated_cost} {$this->booking->trip->currency_type}")
            ->line('Please review the request in the app before accepting.')
            ->line('---')
            ->line('**Safety reminder:** Never accept illegal, prohibited, or undeclared items (e.g. weapons, drugs, counterfeit goods, or undisclosed valuables). Always inspect what you are carrying. If you choose to accept an item against this advice, you are personally and solely responsible for its contents.')
            ->action('Open Pack&Carry', url('/'))
            ->line('Thank you for using Pack&Carry.');
    }
}
