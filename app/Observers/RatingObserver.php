<?php

namespace App\Observers;

use App\Models\Rating;
use App\Notifications\NewRatingReceived;

class RatingObserver
{
    /**
     * Handle the Rating "created" event.
     */
    public function created(Rating $rating): void
    {
        $ratee = $rating->ratee;

        $ratee->total_reviews = $ratee->ratingsReceived()->count();
        $ratee->average_rating = $ratee->ratingsReceived()->avg('stars') ?? 0;
        $ratee->save();

        $ratee->notify(new NewRatingReceived($rating));
    }

    /**
     * Handle the Rating "updated" event.
     */
    public function updated(Rating $rating): void
    {
        //
    }

    /**
     * Handle the Rating "deleted" event.
     */
    public function deleted(Rating $rating): void
    {
        //
    }

    /**
     * Handle the Rating "restored" event.
     */
    public function restored(Rating $rating): void
    {
        //
    }

    /**
     * Handle the Rating "force deleted" event.
     */
    public function forceDeleted(Rating $rating): void
    {
        //
    }
}
