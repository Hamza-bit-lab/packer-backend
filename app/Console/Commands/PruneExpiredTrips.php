<?php

namespace App\Console\Commands;

use App\Models\Trip;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:prune-expired-trips')]
#[Description('Delete trips whose departure date has passed and that were never booked, to free up storage')]
class PruneExpiredTrips extends Command
{
    /**
     * Trips past their departure date already stop appearing anywhere in
     * the app (see TripController::index's date filter) the moment they
     * expire. This command is the follow-up cleanup: it only removes the
     * ones nobody ever booked, since a booked trip's row must stay put —
     * the booking (and its place in the traveler's/sender's history) keeps
     * referencing it via a foreign key.
     */
    public function handle(): int
    {
        $deleted = Trip::query()
            ->where('departure_date_time', '<', now())
            ->doesntHave('bookings')
            ->delete();

        $this->info("Deleted {$deleted} expired, never-booked trip(s).");

        return self::SUCCESS;
    }
}
