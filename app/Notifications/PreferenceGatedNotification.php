<?php

namespace App\Notifications;

use App\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\Notification;

/**
 * Base for any notification the user can mute from Settings → Notifications
 * (see the User::notify_* columns). Muting a category only ever drops its
 * in-app (`database`) and push (FcmChannel) legs — a subclass that also
 * sends mail (e.g. BookingRequested) keeps sending that email regardless,
 * since it's the one channel a muted user can still act on from outside
 * the app (a traveler who muted bookings still needs to know a request is
 * waiting on them).
 */
abstract class PreferenceGatedNotification extends Notification
{
    /**
     * The User column gating this notification, e.g. 'notify_bookings'.
     */
    abstract protected function preferenceColumn(): string;

    /**
     * The channels this notification uses when its preference is on — same
     * shape a plain via() would return.
     *
     * @return array<int, string>
     */
    abstract protected function allChannels(): array;

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Fail open (`?? true`) rather than treating an unset/null value as
        // muted — the column defaults to true for every user, so null only
        // ever means "this in-memory model instance hasn't loaded the
        // column", never "the user actually turned this off".
        if ($notifiable->{$this->preferenceColumn()} ?? true) {
            return $this->allChannels();
        }

        return array_values(array_diff($this->allChannels(), ['database', FcmChannel::class]));
    }
}
