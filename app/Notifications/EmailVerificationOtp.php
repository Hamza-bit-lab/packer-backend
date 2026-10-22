<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerificationOtp extends Notification
{
    public function __construct(public string $code) {}

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
            ->subject('Your Packer verification code')
            ->greeting("Hi {$notifiable->name},")
            ->line('Use this code to verify your email address on Packer:')
            ->line("# {$this->code}")
            ->line('This code expires in 15 minutes.')
            ->line("If you didn't create a Packer account, you can safely ignore this email.");
    }
}
