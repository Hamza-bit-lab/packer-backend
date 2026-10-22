<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\FeatureRequestStatus;
use App\Enums\Payer;
use App\Enums\RouteAlertType;
use App\Enums\VoteDirection;
use App\Models\Booking;
use App\Models\FeatureRequest;
use App\Models\FeatureRequestVote;
use App\Models\Good;
use App\Models\IdVerification;
use App\Models\Message;
use App\Models\Rating;
use App\Models\RouteAlert;
use App\Models\Trip;
use App\Models\User;
use App\Notifications\BookingAccepted;
use App\Notifications\BookingArrived;
use App\Notifications\BookingCancelled;
use App\Notifications\BookingCompleted;
use App\Notifications\BookingInTransit;
use App\Notifications\BookingRejected;
use App\Notifications\BookingRequested;
use App\Notifications\IdVerificationReviewed;
use App\Notifications\MatchingRouteListing;
use App\Notifications\RouteAlertMatched;
use Illuminate\Database\Seeder;
use Illuminate\Notifications\Notification;
use Throwable;

/**
 * One-time, safe-to-rerun demo data for the real kept account
 * (hamzarashid7574@gmail.com) so every screen in the app has something to
 * show while testing the production build — other travelers/senders to
 * browse, a mix of bookings in every status, notifications of every type,
 * a couple of conversations, and route alerts. Doesn't touch the real
 * account's own profile fields (email verification, ID verification,
 * rating) — only adds data around it.
 *
 * Cities/descriptions/currencies are deliberately overridden with real
 * English values instead of the base factories' defaults — Faker's plain
 * `city()`/`sentence()` providers generate fictional place names and Latin
 * lorem-ipsum text ("Hendersonfurt", "Suscipit saepe quo magni corporis"),
 * which reads as gibberish/foreign rather than believable demo content.
 *
 * Run once via `php artisan db:seed --class=DemoDataSeeder --force`, then
 * remove the cron entry that triggered it (see deployment notes) — running
 * it again just adds a second batch on top rather than erroring, but
 * there's no reason to let it re-run indefinitely.
 */
class DemoDataSeeder extends Seeder
{
    private const CITY_PAIRS = [
        ['Lahore', 'Dubai'],
        ['Karachi', 'Jeddah'],
        ['Islamabad', 'London'],
        ['Lahore', 'Riyadh'],
        ['Karachi', 'Toronto'],
        ['Islamabad', 'Manchester'],
        ['Multan', 'Doha'],
        ['Faisalabad', 'Abu Dhabi'],
        ['Peshawar', 'Sharjah'],
        ['Sialkot', 'Manchester'],
    ];

    private const ITEM_DESCRIPTIONS = [
        'A box of family documents and photo albums',
        'Wedding gifts for family abroad',
        'A carton of books and stationery',
        'Branded clothing items for resale',
        'Electronics accessories — chargers and cables',
        'Home-made food items, sealed and labeled',
        "Kids' toys and school supplies",
        'Cosmetics and skincare products',
        "Prescribed medicine, doctor's letter included",
        'Handicrafts and gift items',
    ];

    private const CURRENCIES = ['PKR', 'USD', 'GBP', 'SAR', 'AED'];

    private const FEATURE_REQUESTS = [
        [
            'title' => 'Add an in-app currency converter',
            'description' => "It's hard to compare rates across USD, PKR, and AED without switching apps — a quick converter on the trip and item forms would help.",
            'status' => FeatureRequestStatus::Open,
        ],
        [
            'title' => 'Support multiple drop-off cities per trip',
            'description' => 'Some flights have a layover where I could also carry items — letting a trip list more than one destination would open up more matches.',
            'status' => FeatureRequestStatus::InProgress,
        ],
        [
            'title' => 'Let senders filter travelers by rating',
            'description' => 'When there are several travelers on the same route, a minimum rating filter would help me pick someone trustworthy faster.',
            'status' => FeatureRequestStatus::Open,
        ],
        [
            'title' => 'Add SMS as a backup for push notifications',
            'description' => 'Push notifications sometimes arrive late on my phone — an SMS fallback for booking updates would give extra peace of mind.',
            'status' => FeatureRequestStatus::Open,
        ],
        [
            'title' => 'Show real-time delivery tracking on a map',
            'description' => "Once a trip is marked in transit, showing the traveler's general location on a map would make waiting a lot less stressful.",
            'status' => FeatureRequestStatus::Open,
        ],
        [
            'title' => 'Add a favorites list for frequent travelers',
            'description' => "I've sent items with the same couple of travelers before — a favorites list would make it quicker to book with them again.",
            'status' => FeatureRequestStatus::Completed,
        ],
    ];

    public function run(): void
    {
        $me = User::where('email', 'hamzarashid7574@gmail.com')->firstOrFail();

        $others = collect([
            ['name' => 'Ayesha Khan', 'email' => 'demo.ayesha@packer.test'],
            ['name' => 'Bilal Ahmed', 'email' => 'demo.bilal@packer.test'],
            ['name' => 'Sara Malik', 'email' => 'demo.sara@packer.test'],
            ['name' => 'Usman Tariq', 'email' => 'demo.usman@packer.test'],
            ['name' => 'Fatima Sheikh', 'email' => 'demo.fatima@packer.test'],
        ])->map(fn (array $person) => User::firstOrCreate(
            ['email' => $person['email']],
            // firstOrCreate(attrs, values) creates array_merge(attrs, values)
            // — values (this array) wins on key collision, so 'email' must
            // be repeated here too or the factory's own random email wins
            // and this becomes a brand-new duplicate user every run.
            User::factory()->make([
                'name' => $person['name'],
                'email' => $person['email'],
                'country' => 'Pakistan',
                'average_rating' => fake()->randomFloat(1, 3.5, 5),
                'total_reviews' => fake()->numberBetween(3, 40),
                'completed_deliveries_count' => fake()->numberBetween(0, 15),
            ])->getAttributes(),
        ));

        IdVerification::factory()->approved()->for($others[0])->create();
        IdVerification::factory()->approved()->for($others[2])->create();

        // Other people's listings — populates Featured Travelers/Items and the Feed.
        $otherTrips = $others->flatMap(fn (User $user) => collect(range(1, 2))->map(fn () => $this->makeTrip($user)));
        $otherGoods = $others->flatMap(fn (User $user) => collect(range(1, 2))->map(fn () => $this->makeGood($user)));

        // My own listings — populates My Posts.
        $myTrips = collect(range(1, 2))->map(fn () => $this->makeTrip($me));
        $myGoods = collect(range(1, 2))->map(fn () => $this->makeGood($me));

        // Bookings covering every status, each firing the same notification
        // the real controller would — populates Shipments/History/Notifications.
        $bookingPlan = [
            ['status' => BookingStatus::Pending, 'meIsTraveler' => true],
            ['status' => BookingStatus::Accepted, 'meIsTraveler' => false],
            ['status' => BookingStatus::Rejected, 'meIsTraveler' => false],
            ['status' => BookingStatus::InTransit, 'meIsTraveler' => false],
            ['status' => BookingStatus::ArrivedAtDestination, 'meIsTraveler' => false],
            ['status' => BookingStatus::Completed, 'meIsTraveler' => false],
            ['status' => BookingStatus::Completed, 'meIsTraveler' => true],
            ['status' => BookingStatus::Cancelled, 'meIsTraveler' => true],
        ];

        foreach ($bookingPlan as $i => $plan) {
            $other = $others[$i % $others->count()];

            if ($plan['meIsTraveler']) {
                $traveler = $me;
                $sender = $other;
                $trip = $myTrips[$i % $myTrips->count()];
                $good = Good::factory()->for($other)->create([
                    'from_city' => $trip->from_city,
                    'to_city' => $trip->to_city,
                    'item_description' => self::ITEM_DESCRIPTIONS[array_rand(self::ITEM_DESCRIPTIONS)],
                    'currency_type' => self::CURRENCIES[array_rand(self::CURRENCIES)],
                ]);
            } else {
                $traveler = $other;
                $sender = $me;
                $good = $myGoods[$i % $myGoods->count()];
                $trip = Trip::factory()->for($other)->create([
                    'from_city' => $good->from_city,
                    'to_city' => $good->to_city,
                    'currency_type' => self::CURRENCIES[array_rand(self::CURRENCIES)],
                ]);
            }

            $booking = Booking::factory()->create([
                'trip_id' => $trip->id,
                'good_id' => $good->id,
                'sender_id' => $sender->id,
                'traveler_id' => $traveler->id,
                'status' => $plan['status'],
                'payer' => Payer::Sender,
                'inspection_confirmed_at' => $plan['status'] === BookingStatus::Completed ? now() : null,
            ]);

            match ($plan['status']) {
                BookingStatus::Pending => $this->notifySafely($traveler, new BookingRequested($booking)),
                BookingStatus::Accepted => $this->notifySafely($sender, new BookingAccepted($booking)),
                BookingStatus::Rejected => $this->notifySafely($sender, new BookingRejected($booking)),
                BookingStatus::InTransit => $this->notifySafely($sender, new BookingInTransit($booking)),
                BookingStatus::ArrivedAtDestination => $this->notifySafely($sender, new BookingArrived($booking)),
                BookingStatus::Completed => $this->notifySafely($sender, new BookingCompleted($booking)),
                // Notify me specifically (not $sender/$traveler, whichever
                // happens to be the other party) — this is the one status
                // where the real controller notifies "whoever didn't
                // cancel", and for the demo that should always be me.
                BookingStatus::Cancelled => $this->notifySafely($me, new BookingCancelled($booking, $plan['meIsTraveler'] ? $sender : $traveler)),
            };

            // Rate the one completed booking where I was the traveler — the
            // Rating model's observer sends the ratee (me) a notification.
            if ($plan['status'] === BookingStatus::Completed && $plan['meIsTraveler']) {
                Rating::create([
                    'booking_id' => $booking->id,
                    'rater_id' => $sender->id,
                    'ratee_id' => $me->id,
                    'stars' => 5,
                    'comment' => 'Great communication and safe delivery!',
                ]);
            }
        }

        // Route alerts + the "something matching your alert" notifications.
        RouteAlert::create(['user_id' => $me->id, 'watch_type' => RouteAlertType::Travelers, 'from_city' => 'Lahore', 'to_city' => 'Dubai']);
        RouteAlert::create(['user_id' => $me->id, 'watch_type' => RouteAlertType::Goods, 'from_city' => 'Karachi', 'to_city' => 'London']);
        $this->notifySafely($me, new RouteAlertMatched($otherTrips->first()));
        $this->notifySafely($me, new MatchingRouteListing($otherGoods->first()));

        // An ID verification review notification — cosmetic only, doesn't
        // touch my real verification status (this IdVerification is never
        // saved to the database).
        $this->notifySafely($me, new IdVerificationReviewed(IdVerification::factory()->approved()->make(['user_id' => $me->id])));

        // A couple of conversations — populates the Inbox.
        Message::create(['sender_id' => $others[0]->id, 'receiver_id' => $me->id, 'body' => 'Hi! Is your trip to Dubai still on for next week?', 'read_at' => null]);
        Message::create(['sender_id' => $me->id, 'receiver_id' => $others[0]->id, 'body' => 'Yes, still on! How much are you looking to send?', 'read_at' => now()]);
        Message::create(['sender_id' => $others[1]->id, 'receiver_id' => $me->id, 'body' => 'Thanks for carrying my package, really appreciate it!', 'read_at' => null]);

        // Feature Requests + votes — populates the board with believable
        // English suggestions instead of the factory's default Faker
        // lorem-ipsum (see the class doc comment).
        $this->seedFeatureRequests($me, $others);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $others
     */
    private function seedFeatureRequests(User $me, \Illuminate\Support\Collection $others): void
    {
        $everyone = $others->push($me)->values();

        foreach (self::FEATURE_REQUESTS as $i => $data) {
            $request = FeatureRequest::create([
                'user_id' => $everyone[$i % $everyone->count()]->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'status' => $data['status'],
            ]);

            // A handful of votes per request, mostly up, so the board shows
            // varied, organic-looking counts rather than all zeros.
            foreach ($everyone->shuffle()->take(random_int(2, $everyone->count())) as $voter) {
                FeatureRequestVote::create([
                    'feature_request_id' => $request->id,
                    'user_id' => $voter->id,
                    'direction' => fake()->boolean(85) ? VoteDirection::Up : VoteDirection::Down,
                ]);
            }
        }
    }

    /**
     * The demo only needs the database notification record — a transient
     * mail/push failure on the other channels (e.g. local SMTP hiccups)
     * shouldn't abort the whole seeding run over a channel nobody's
     * checking here.
     */
    private function notifySafely(User $user, Notification $notification): void
    {
        try {
            $user->notify($notification);
        } catch (Throwable $e) {
            $this->command?->warn('Notification delivery failed (continuing): '.$e->getMessage());
        }
    }

    private function makeTrip(User $user): Trip
    {
        [$from, $to] = self::CITY_PAIRS[array_rand(self::CITY_PAIRS)];

        return Trip::factory()->for($user)->create([
            'from_city' => $from,
            'to_city' => $to,
            'currency_type' => self::CURRENCIES[array_rand(self::CURRENCIES)],
        ]);
    }

    private function makeGood(User $user): Good
    {
        [$from, $to] = self::CITY_PAIRS[array_rand(self::CITY_PAIRS)];

        return Good::factory()->for($user)->create([
            'from_city' => $from,
            'to_city' => $to,
            'item_description' => self::ITEM_DESCRIPTIONS[array_rand(self::ITEM_DESCRIPTIONS)],
            'currency_type' => self::CURRENCIES[array_rand(self::CURRENCIES)],
        ]);
    }
}
