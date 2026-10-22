<?php

namespace App\Console\Commands;

use App\Enums\IdVerificationStatus;
use App\Models\IdVerification;
use Illuminate\Console\Command;

class ListIdVerifications extends Command
{
    protected $signature = 'id-verifications:list {--status=pending : pending, approved, rejected, or all}';

    protected $description = 'List ID verification submissions awaiting (or by) review';

    public function handle(): int
    {
        $status = $this->option('status');

        $query = IdVerification::query()->with('user')->latest();

        if ($status !== 'all') {
            $query->where('status', IdVerificationStatus::from($status));
        }

        $verifications = $query->get();

        if ($verifications->isEmpty()) {
            $this->info('Nothing to show.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'User', 'Document', 'Status', 'Submitted'],
            $verifications->map(fn (IdVerification $v) => [
                $v->id,
                "{$v->user->name} ({$v->user->email})",
                $v->document_type->value,
                $v->status->value,
                $v->created_at->diffForHumans(),
            ]),
        );

        return self::SUCCESS;
    }
}
