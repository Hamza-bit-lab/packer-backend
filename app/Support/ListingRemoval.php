<?php

namespace App\Support;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Good;
use App\Models\Rating;
use App\Models\Trip;
use App\Models\User;
use App\Notifications\ListingRemoved;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Deleting a trip, an item or a whole account cascades away every booking
 * hanging off it. Bookings that are already finished (completed / rejected /
 * cancelled) are just history, but a live one means someone was counting on
 * it — so the other party is told before it silently disappears.
 */
class ListingRemoval
{
    /** @var array<int, BookingStatus> */
    private const LIVE = [
        BookingStatus::Pending,
        BookingStatus::Accepted,
        BookingStatus::InTransit,
        BookingStatus::ArrivedAtDestination,
    ];

    /**
     * @param  User|null  $actor  Who is deleting it — null / the owner means
     *                            the owner did it themselves, anyone else
     *                            (an admin) is treated as moderation.
     */
    public static function deleteTrip(Trip $trip, ?User $actor = null): void
    {
        $owner = $trip->user;
        $route = "{$trip->from_city} → {$trip->to_city}";
        $byOwner = $actor === null || $actor->id === $trip->user_id;

        $senders = $trip->bookings()->whereIn('status', self::LIVE)->with('sender')->get()
            ->pluck('sender')->filter()->unique('id');
        $ratees = Rating::whereIn('booking_id', $trip->bookings()->select('id'))->pluck('ratee_id')->unique();

        $trip->delete();
        self::refreshRatings($ratees);

        $reason = $byOwner
            ? "{$owner->name} removed their trip {$route}"
            : "The trip {$route} was removed by our moderation team";

        foreach ($senders as $sender) {
            $sender->notify(new ListingRemoved('Booking cancelled', "{$reason}, so your shipment request was cancelled."));
        }

        if (! $byOwner) {
            $owner->notify(new ListingRemoved('Your trip was removed', "Your trip {$route} was removed by our moderation team for breaking the rules."));
        }
    }

    public static function deleteGood(Good $good, ?User $actor = null): void
    {
        $owner = $good->user;
        $item = Str::limit($good->item_description, 60);
        $byOwner = $actor === null || $actor->id === $good->user_id;

        $travelers = $good->bookings()->whereIn('status', self::LIVE)->with('traveler')->get()
            ->pluck('traveler')->filter()->unique('id');
        $ratees = Rating::whereIn('booking_id', $good->bookings()->select('id'))->pluck('ratee_id')->unique();

        $good->delete();
        self::refreshRatings($ratees);

        $reason = $byOwner
            ? "{$owner->name} removed their item \"{$item}\""
            : "The item \"{$item}\" was removed by our moderation team";

        foreach ($travelers as $traveler) {
            $traveler->notify(new ListingRemoved('Booking cancelled', "{$reason}, so this booking was cancelled."));
        }

        if (! $byOwner) {
            $owner->notify(new ListingRemoved('Your item was removed', "Your item \"{$item}\" was removed by our moderation team for breaking the rules."));
        }
    }

    /**
     * Permanently deletes a user and everything of theirs (trips, items,
     * bookings, messages, ratings, tokens, notifications, uploaded ID and
     * profile photos) — the counterparties of any live booking are told why
     * it vanished.
     */
    public static function deleteAccount(User $user): void
    {
        $others = Booking::query()
            ->whereIn('status', self::LIVE)
            ->where(fn ($q) => $q->where('sender_id', $user->id)->orWhere('traveler_id', $user->id))
            ->with(['sender', 'traveler'])
            ->get()
            ->map(fn (Booking $booking) => $booking->sender_id === $user->id ? $booking->traveler : $booking->sender)
            ->filter()->unique('id');

        $name = $user->name;
        $ratees = Rating::where('rater_id', $user->id)->pluck('ratee_id')->unique();

        $user->idVerifications->each(fn ($v) => Storage::disk('local')->delete((string) $v->document_photo_url));

        // Their public uploads (profile picture, trip/item photos, and the
        // delivery-proof photos they took as a traveller) go with them.
        self::deleteUploads([
            $user->profile_pic_url,
            ...$user->trips()->pluck('photo_url')->all(),
            ...$user->goods()->pluck('photo_url')->all(),
            ...Booking::where('traveler_id', $user->id)->pluck('delivery_proof_photo_url')->all(),
        ]);

        DB::transaction(function () use ($user) {
            // Not covered by a foreign key, so they'd otherwise be orphaned.
            $user->notifications()->delete();
            $user->tokens()->delete();
            $user->delete();
        });
        self::refreshRatings($ratees);

        foreach ($others as $other) {
            $other->notify(new ListingRemoved('Booking cancelled', "{$name} deleted their account, so your booking was cancelled."));
        }
    }

    /**
     * Ratings vanish with the booking/user they hung off, and the database
     * cascade doesn't fire model observers — so the cached average and
     * review count on the people who were rated would go stale.
     *
     * @param  Collection<int, int>  $userIds
     */
    private static function refreshRatings(Collection $userIds): void
    {
        User::query()->whereIn('id', $userIds)->get()->each(function (User $user) {
            $user->forceFill([
                'total_reviews' => $user->ratingsReceived()->count(),
                'average_rating' => $user->ratingsReceived()->avg('stars') ?? 0,
            ])->save();
        });
    }

    /**
     * @param  array<int, string|null>  $urls  Public URLs under /uploads/.
     */
    private static function deleteUploads(array $urls): void
    {
        foreach (array_filter($urls) as $url) {
            if (str_contains($url, '/uploads/')) {
                Storage::disk('uploads')->delete(Str::after($url, '/uploads/'));
            }
        }
    }
}
