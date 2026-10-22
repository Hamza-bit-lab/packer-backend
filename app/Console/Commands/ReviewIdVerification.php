<?php

namespace App\Console\Commands;

use App\Enums\IdVerificationStatus;
use App\Models\IdVerification;
use App\Notifications\IdVerificationReviewed;
use Illuminate\Console\Command;

/**
 * There's no admin panel yet, so this is how ID verification submissions
 * get approved or rejected for now: `php artisan id-verifications:list` to
 * see what's pending, then `php artisan id-verifications:review {id} approve`.
 */
class ReviewIdVerification extends Command
{
    protected $signature = 'id-verifications:review {id} {decision : approve or reject} {--reason=}';

    protected $description = 'Approve or reject a pending ID verification submission';

    public function handle(): int
    {
        $verification = IdVerification::find($this->argument('id'));

        if ($verification === null) {
            $this->error('No ID verification found with that id.');

            return self::FAILURE;
        }

        $decision = strtolower((string) $this->argument('decision'));

        if (! in_array($decision, ['approve', 'reject'], true)) {
            $this->error('Decision must be "approve" or "reject".');

            return self::FAILURE;
        }

        $verification->loadMissing('user');

        if ($decision === 'approve') {
            $verification->update(['status' => IdVerificationStatus::Approved, 'reviewed_at' => now()]);
            $this->info("Approved ID verification #{$verification->id} for {$verification->user->name}.");
        } else {
            $reason = $this->option('reason') ?? $this->ask('Rejection reason (shown to the user)');
            $verification->update([
                'status' => IdVerificationStatus::Rejected,
                'rejection_reason' => $reason,
                'reviewed_at' => now(),
            ]);
            $this->info("Rejected ID verification #{$verification->id} for {$verification->user->name}.");
        }

        $verification->user->notify(new IdVerificationReviewed($verification));

        return self::SUCCESS;
    }
}
