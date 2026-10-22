<?php

namespace App\Notifications;

use App\Models\Setting;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells admins something needs their attention (a new report, an ID
 * awaiting review, a new feature request). Always lands in the admin's
 * in-app notifications + push; the email copy can be switched off from the
 * dashboard's Settings page.
 */
class AdminAlert extends Notification
{
    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(public string $title, public string $body, public array $extra = []) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return Setting::enabled('admin_alert_email')
            ? ['database', 'mail', FcmChannel::class]
            : ['database', FcmChannel::class];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return ['type' => 'admin_alert', 'title' => $this->title, 'body' => $this->body, ...$this->extra];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[Packer Admin] {$this->title}")
            ->greeting("Hi {$notifiable->name},")
            ->line($this->body)
            ->action('Open admin dashboard', url('/admin'));
    }
}
