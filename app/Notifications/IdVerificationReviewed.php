<?php

namespace App\Notifications;

use App\Enums\IdVerificationStatus;
use App\Models\IdVerification;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IdVerificationReviewed extends Notification
{
    public function __construct(public IdVerification $verification) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail', FcmChannel::class];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $approved = $this->verification->status === IdVerificationStatus::Approved;

        return [
            'type' => 'id_verification_reviewed',
            'title' => $approved ? 'ID verification approved' : 'ID verification rejected',
            'body' => $approved
                ? "You're now ID verified — a badge is shown on your profile."
                : "Your ID submission was rejected: {$this->verification->rejection_reason}",
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $approved = $this->verification->status === IdVerificationStatus::Approved;

        $message = (new MailMessage)
            ->subject($approved ? "You're ID verified on Packer" : 'Your ID verification was not approved');

        if ($approved) {
            return $message
                ->greeting("Hi {$notifiable->name},")
                ->line('Your government ID has been reviewed and approved.')
                ->line('An ID Verified badge now shows on your profile, helping other travelers and senders trust you.');
        }

        return $message
            ->greeting("Hi {$notifiable->name},")
            ->line('Your government ID submission could not be approved.')
            ->line("Reason: {$this->verification->rejection_reason}")
            ->line('You can submit a new document from your profile at any time.');
    }
}
