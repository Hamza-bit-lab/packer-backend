<?php

namespace App\Notifications;

use App\Models\Message;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Support\Str;

class NewMessage extends PreferenceGatedNotification
{
    public function __construct(public Message $message) {}

    protected function preferenceColumn(): string
    {
        return 'notify_messages';
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
        $this->message->loadMissing('sender');

        return [
            'type' => 'new_message',
            'title' => $this->message->sender->name,
            'body' => Str::limit($this->message->body, 120),
            'sender_id' => $this->message->sender_id,
            'message_id' => $this->message->id,
        ];
    }

    /**
     * One key per pair of users, regardless of who sent last — so a back-
     * and-forth conversation collapses into a single updating notification
     * instead of a fresh popup per message.
     */
    public function groupKey(object $notifiable): string
    {
        [$low, $high] = [
            min($this->message->sender_id, $notifiable->id),
            max($this->message->sender_id, $notifiable->id),
        ];

        return "chat-{$low}-{$high}";
    }
}
