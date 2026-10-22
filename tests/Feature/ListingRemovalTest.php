<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Good;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ListingRemovalTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<int, string> */
    private function types(User $user): array
    {
        return $user->fresh()->notifications->map(fn ($n) => $n->data['type'])->all();
    }

    public function test_deleting_a_trip_with_a_live_booking_tells_the_sender(): void
    {
        $traveler = User::factory()->create();
        $sender = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $traveler->id, 'from_city' => 'Lahore', 'to_city' => 'Dubai']);
        $booking = Booking::factory()->accepted()->create(['trip_id' => $trip->id, 'traveler_id' => $traveler->id, 'sender_id' => $sender->id]);

        Sanctum::actingAs($traveler);
        $this->deleteJson("/api/v1/trips/{$trip->id}")->assertNoContent();

        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
        $this->assertContains('listing_removed', $this->types($sender));
        $body = $sender->fresh()->notifications->first()->data['body'];
        $this->assertStringContainsString('Lahore → Dubai', $body);
        $this->assertSame([], $this->types($traveler));
    }

    public function test_deleting_a_trip_with_only_finished_bookings_notifies_nobody(): void
    {
        $traveler = User::factory()->create();
        $sender = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $traveler->id]);
        Booking::factory()->create(['trip_id' => $trip->id, 'traveler_id' => $traveler->id, 'sender_id' => $sender->id, 'status' => BookingStatus::Completed]);

        Sanctum::actingAs($traveler);
        $this->deleteJson("/api/v1/trips/{$trip->id}")->assertNoContent();

        $this->assertSame([], $this->types($sender));
    }

    public function test_deleting_an_item_with_a_live_booking_tells_the_traveler(): void
    {
        $traveler = User::factory()->create();
        $sender = User::factory()->create();
        $good = Good::factory()->create(['user_id' => $sender->id, 'item_description' => 'Box of books']);
        Booking::factory()->accepted()->create(['good_id' => $good->id, 'traveler_id' => $traveler->id, 'sender_id' => $sender->id]);

        Sanctum::actingAs($sender);
        $this->deleteJson("/api/v1/goods/{$good->id}")->assertNoContent();

        $this->assertContains('listing_removed', $this->types($traveler));
        $this->assertStringContainsString('Box of books', $traveler->fresh()->notifications->first()->data['body']);
    }

    public function test_a_user_who_muted_booking_notifications_is_not_notified(): void
    {
        $traveler = User::factory()->create();
        $sender = User::factory()->create(['notify_bookings' => false]);
        $trip = Trip::factory()->create(['user_id' => $traveler->id]);
        Booking::factory()->accepted()->create(['trip_id' => $trip->id, 'traveler_id' => $traveler->id, 'sender_id' => $sender->id]);

        Sanctum::actingAs($traveler);
        $this->deleteJson("/api/v1/trips/{$trip->id}")->assertNoContent();

        $this->assertSame([], $this->types($sender));
    }

    public function test_deleting_a_trip_refreshes_the_rating_stats_of_the_people_rated(): void
    {
        $traveler = User::factory()->create();
        $sender = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $traveler->id]);
        $booking = Booking::factory()->create(['trip_id' => $trip->id, 'traveler_id' => $traveler->id, 'sender_id' => $sender->id, 'status' => BookingStatus::Completed]);
        $booking->ratings()->create(['rater_id' => $sender->id, 'ratee_id' => $traveler->id, 'stars' => 5]);
        $this->assertSame(1, $traveler->fresh()->total_reviews);

        Sanctum::actingAs($traveler);
        $this->deleteJson("/api/v1/trips/{$trip->id}")->assertNoContent();

        $this->assertSame(0, $traveler->fresh()->total_reviews);
    }
}
