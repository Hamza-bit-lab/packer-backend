<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Good;
use App\Models\Trip;
use App\Models\User;
use App\Notifications\BookingRequested;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_request_notifies_the_traveler(): void
    {
        $traveler = User::factory()->create();
        $sender = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $traveler->id, 'max_weight_kg' => 20]);
        $good = Good::factory()->create(['user_id' => $sender->id, 'weight_kg' => 2]);

        Sanctum::actingAs($sender);
        $this->postJson('/api/v1/bookings', ['trip_id' => $trip->id, 'good_id' => $good->id])->assertCreated();

        Sanctum::actingAs($traveler);
        $this->getJson('/api/v1/notifications/unread-count')->assertOk()->assertJsonPath('unread_count', 1);

        $response = $this->getJson('/api/v1/notifications');
        $response->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'booking_requested');
    }

    public function test_mark_all_read_clears_unread_count(): void
    {
        $traveler = User::factory()->create();
        $booking = Booking::factory()->create(['traveler_id' => $traveler->id]);
        $traveler->notify(new BookingRequested($booking));

        Sanctum::actingAs($traveler);
        $this->postJson('/api/v1/notifications/read-all')->assertNoContent();
        $this->getJson('/api/v1/notifications/unread-count')->assertOk()->assertJsonPath('unread_count', 0);
    }
}
