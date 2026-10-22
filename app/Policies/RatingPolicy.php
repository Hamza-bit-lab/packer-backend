<?php

namespace App\Policies;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Rating;
use App\Models\User;

class RatingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Rating $rating): bool
    {
        return $user->id === $rating->rater_id || $user->id === $rating->ratee_id;
    }

    /**
     * The user must be a party to a completed booking, and not have rated it yet.
     */
    public function createForBooking(User $user, Booking $booking): bool
    {
        if ($booking->status !== BookingStatus::Completed) {
            return false;
        }

        if ($user->id !== $booking->sender_id && $user->id !== $booking->traveler_id) {
            return false;
        }

        return ! $booking->ratings()->where('rater_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Rating $rating): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Rating $rating): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Rating $rating): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Rating $rating): bool
    {
        return false;
    }
}
