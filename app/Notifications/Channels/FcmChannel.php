<?php

namespace App\Notifications\Channels;

use App\Models\DeviceToken;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;
use Kreait\Laravel\Firebase\Facades\Firebase;

/**
 * Delivers a real phone push (banner/sound/lock-screen) for any notification
 * that lists 'fcm' in its via(), reusing the same title/body/data it already
 * builds for the in-app database notification — so adding push support to a
 * notification class is a one-line via() change, not a second copy of its
 * content.
 *
 * The actual network calls to Firebase run after the HTTP response has been
 * sent (Laravel's `defer()`), so a slow or failing push never delays or
 * breaks the request that triggered it.
 */
class FcmChannel
{
    /**
     * Must match the high-importance channel the app creates on launch
     * (see PushNotificationService in the Flutter app) — without this,
     * Android buckets the push into an auto-created default channel that
     * doesn't reliably heads-up/wake a locked screen.
     */
    private const ANDROID_CHANNEL_ID = 'packer_notifications';

    public function send(object $notifiable, Notification $notification): void
    {
        defer(fn () => $this->deliver($notifiable, $notification));
    }

    private function deliver(object $notifiable, Notification $notification): void
    {
        $tokens = $notifiable->deviceTokens()->pluck('token', 'id');

        if ($tokens->isEmpty()) {
            return;
        }

        $payload = method_exists($notification, 'toArray') ? $notification->toArray($notifiable) : [];
        $title = (string) ($payload['title'] ?? 'Packer');
        $body = (string) ($payload['body'] ?? '');

        // FCM's data payload only accepts flat string values.
        $data = collect($payload)
            ->except(['title', 'body'])
            ->map(fn ($value) => $value === null ? '' : (string) $value)
            ->toArray();

        // Opt-in: a notification class can implement groupKey() (e.g. one
        // key per conversation) so repeated pushes collapse into a single
        // notification — an Android `tag` replaces the previous one in
        // place, an APNs `thread-id` stacks them — instead of a new popup
        // for every single message, the way WhatsApp behaves.
        $groupKey = method_exists($notification, 'groupKey') ? $notification->groupKey($notifiable) : null;

        // Every platform-specific setting has to be built into a single
        // AndroidConfig/ApnsConfig up front — withAndroidConfig()/
        // withApnsConfig() *replace* whatever config CloudMessage was
        // carrying, so chaining them after withHighestPossiblePriority()/
        // withDefaultSounds() silently discarded the priority and sound
        // those set, which is why pushes weren't waking a locked screen or
        // playing a sound.
        $androidConfig = AndroidConfig::fromArray([
            'priority' => 'high',
            'notification' => [
                'channel_id' => self::ANDROID_CHANNEL_ID,
                'sound' => 'default',
                'notification_priority' => 'PRIORITY_HIGH',
                'visibility' => 'PUBLIC',
                ...($groupKey !== null ? ['tag' => $groupKey] : []),
            ],
        ]);

        $apnsConfig = ApnsConfig::fromArray([
            'headers' => [
                'apns-priority' => '10',
                'apns-push-type' => 'alert',
            ],
            'payload' => [
                'aps' => [
                    'sound' => 'default',
                    ...($groupKey !== null ? ['thread-id' => $groupKey] : []),
                ],
            ],
        ]);

        $message = CloudMessage::new()
            ->withNotification(FcmNotification::create($title, $body))
            ->withData($data)
            ->withAndroidConfig($androidConfig)
            ->withApnsConfig($apnsConfig);

        foreach ($tokens as $deviceTokenId => $token) {
            try {
                Firebase::messaging()->send($message->withToken($token));
            } catch (NotFound) {
                // The device uninstalled the app or the token expired —
                // clean it up so we stop trying.
                DeviceToken::whereKey($deviceTokenId)->delete();
            } catch (InvalidMessage $e) {
                // FCM answers a malformed/never-valid token with this rather
                // than NotFound — same outcome, the token is useless. Any
                // other InvalidMessage (a bad payload) is our bug, not the
                // token's, so it's reported instead of deleting a good token.
                if (str_contains($e->getMessage(), 'registration token')) {
                    DeviceToken::whereKey($deviceTokenId)->delete();
                } else {
                    report($e);
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
