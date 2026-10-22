<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_party_can_rate_the_other_after_completion_and_average_updates(): void
    {
        $sender = User::factory()->create();
        $traveler = User::factory()->create();
        $booking = Booking::factory()->completed()->create([
            'sender_id' => $sender->id,
            'traveler_id' => $traveler->id,
        ]);

        Sanctum::actingAs($sender);
        $response = $this->postJson("/api/v1/bookings/{$booking->id}/ratings", [
            'stars' => 5,
            'comment' => 'Great traveler, very reliable!',
        ]);

        $response->assertCreated()->assertJsonPath('data.stars', 5);

        $traveler->refresh();
        $this->assertEquals(1, $traveler->total_reviews);
        $this->assertEquals(5.00, (float) $traveler->average_rating);
    }

    public function test_cannot_rate_before_booking_is_completed(): void
    {
        $sender = User::factory()->create();
        $booking = Booking::factory()->create(['sender_id' => $sender->id]);

        Sanctum::actingAs($sender);
        $this->postJson("/api/v1/bookings/{$booking->id}/ratings", ['stars' => 5])
            ->assertForbidden();
    }

    public function test_cannot_rate_the_same_booking_twice(): void
    {
        $sender = User::factory()->create();
        $booking = Booking::factory()->completed()->create(['sender_id' => $sender->id]);

        Sanctum::actingAs($sender);
        $this->postJson("/api/v1/bookings/{$booking->id}/ratings", ['stars' => 4])->assertCreated();
        $this->postJson("/api/v1/bookings/{$booking->id}/ratings", ['stars' => 3])->assertForbidden();
    }

    public function test_non_party_cannot_rate_a_booking(): void
    {
        $booking = Booking::factory()->completed()->create();
        $stranger = User::factory()->create();

        Sanctum::actingAs($stranger);
        $this->postJson("/api/v1/bookings/{$booking->id}/ratings", ['stars' => 5])
            ->assertForbidden();
    }
}
