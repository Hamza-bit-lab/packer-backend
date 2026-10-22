<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetOtp extends Notification
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
            ->subject('Your Packer password reset code')
            ->greeting("Hi {$notifiable->name},")
            ->line('Use this code to reset your Packer password:')
            ->line("# {$this->code}")
            ->line('This code expires in 15 minutes.')
            ->line("If you didn't ask to reset your password, you can safely ignore this email — your password won't change.");
    }
}
