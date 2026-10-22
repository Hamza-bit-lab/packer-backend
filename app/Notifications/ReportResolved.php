<?php

namespace App\Notifications;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\Notification;

/**
 * Closes the loop with the person who filed a report once an admin has
 * dealt with it — they otherwise never learn whether anything happened.
 */
class ReportResolved extends Notification
{
    public function __construct(public Report $report) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->report->loadMissing('reportedUser');
        $name = $this->report->reportedUser?->name ?? 'that user';

        return [
            'type' => 'report_resolved',
            'title' => 'Your report was reviewed',
            'body' => $this->report->status === ReportStatus::Reviewed
                ? "Thanks — we reviewed your report about {$name} and took action."
                : "We reviewed your report about {$name} and didn't find a rule violation.",
        ];
    }
}
