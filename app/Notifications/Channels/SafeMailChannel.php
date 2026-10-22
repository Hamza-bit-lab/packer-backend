<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Notification;

/**
 * Replaces Laravel's built-in `mail` channel (see AppServiceProvider) so an
 * SMTP failure or rate limit can never bubble up into the request that
 * triggered the notification. The email is sent after the HTTP response has
 * already gone out (Laravel's `defer()`), and any exception is caught and
 * logged there instead of turning a successful action — a booking request,
 * a signup — into a 500.
 */
class SafeMailChannel
{
    public function __construct(private MailChannel $mail) {}

    public function send(mixed $notifiable, Notification $notification): void
    {
        defer(fn () => $this->mail->send($notifiable, $notification));
    }
}
