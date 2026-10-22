<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to a Good's receiver_email — not a registered User, so there's no
 * name to greet them by and this only ever goes by mail (via
 * Notification::route('mail', ...)).
 */
class DeliveryOtp extends Notification
{
    public function __construct(
        public string $code,
        public string $senderName,
        public string $itemDescription,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Confirm your delivery — Packer')
            ->greeting('Hi,')
            ->line("{$this->senderName} sent you something via Packer, and the traveler carrying it says it has arrived: \"{$this->itemDescription}\".")
            ->line('Once you have the item in hand, give the traveler this code to confirm the delivery:')
            ->line("# {$this->code}")
            ->line('This code expires in 15 minutes.')
            ->line("Don't share this code before you've actually received the item — it's what releases the delivery as confirmed.");
    }
}
