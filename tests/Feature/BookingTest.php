<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Good;
use App\Models\Trip;
use App\Models\User;
use App\Notifications\BookingCancelled;
use App\Notifications\DeliveryOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_sender_can_create_booking_with_computed_cost(): void
    {
        $traveler = User::factory()->create();
        $sender = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $traveler->id, 'rate_per_kg' => 10, 'max_weight_kg' => 20]);
        $good = Good::factory()->create(['user_id' => $sender->id, 'weight_kg' => 3]);

        Sanctum::actingAs($sender);

        $response = $this->postJson('/api/v1/bookings', [
            'trip_id' => $trip->id,
            'good_id' => $good->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.estimated_cost', 30);
    }

    public function test_traveler_can_initiate_a_booking_from_the_goods_feed(): void
    {
        $traveler = User::factory()->create();
        $sender = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $traveler->id, 'max_weight_kg' => 20]);
        $good = Good::factory()->create(['user_id' => $sender->id, 'weight_kg' => 3]);

        Sanctum::actingAs($traveler);

        $this->postJson('/api/v1/bookings', ['trip_id' => $trip->id, 'good_id' => $good->id])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_payer_defaults_to_sender_but_can_be_set_to_receiver(): void
    {
        $traveler = User::factory()->create();
        $sender = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $traveler->id, 'max_weight_kg' => 20]);
        $good = Good::factory()->create(['user_id' => $sender->id, 'weight_kg' => 3]);

        Sanctum::actingAs($sender);
        $this->postJson('/api/v1/bookings', ['trip_id' => $trip->id, 'good_id' => $good->id])
            ->assertCreated()
            ->assertJsonPath('data.payer', 'sender');

        $good2 = Good::factory()->create(['user_id' => $sender->id, 'weight_kg' => 3]);
        $this->postJson('/api/v1/bookings', ['trip_id' => $trip->id, 'good_id' => $good2->id, 'payer' => 'receiver'])
            ->assertCreated()
            ->assertJsonPath('data.payer', 'receiver');
    }

    public function test_cannot_book_a_pair_you_have_no_relation_to(): void
    {
        $traveler = User::factory()->create();
        $sender = User::factory()->create();
        $stranger = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $traveler->id]);
        $good = Good::factory()->create(['user_id' => $sender->id]);

        Sanctum::actingAs($stranger);
        $this->postJson('/api/v1/bookings', ['trip_id' => $trip->id, 'good_id' => $good->id])
            ->assertUnprocessable();
    }

    public function test_cannot_book_own_trip_with_own_good(): void
    {
        $user = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $user->id]);
        $good = Good::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);
        $this->postJson('/api/v1/bookings', ['trip_id' => $trip->id, 'good_id' => $good->id])
            ->assertUnprocessable();
    }

    public function test_cannot_exceed_trip_weight_capacity(): void
    {
        $traveler = User::factory()->create();
        $sender = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $traveler->id, 'max_weight_kg' => 5]);
        $good = Good::factory()->create(['user_id' => $sender->id, 'weight_kg' => 10]);

        Sanctum::actingAs($sender);
        $this->postJson('/api/v1/bookings', ['trip_id' => $trip->id, 'good_id' => $good->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['good_id']);
    }

    public function test_only_traveler_can_accept_booking_then_phone_is_revealed(): void
    {
        $traveler = User::factory()->create(['phone_number' => '+15550001111']);
        $sender = User::factory()->create(['phone_number' => '+15550002222']);
        $trip = Trip::factory()->create(['user_id' => $traveler->id]);
        $good = Good::factory()->create(['user_id' => $sender->id]);
        $booking = Booking::factory()->create([
            'trip_id' => $trip->id,
            'good_id' => $good->id,
            'sender_id' => $sender->id,
            'traveler_id' => $traveler->id,
        ]);

        Sanctum::actingAs($sender);
        $this->postJson("/api/v1/bookings/{$booking->id}/accept")->assertForbidden();

        Sanctum::actingAs($traveler);
        $response = $this->postJson("/api/v1/bookings/{$booking->id}/accept");

        $response->assertOk()
            ->assertJsonPath('data.status', 'accepted')
            ->assertJsonPath('data.sender_phone_number', '+15550002222')
            ->assertJsonPath('data.traveler_phone_number', '+15550001111');
    }

    public function test_phone_number_hidden_while_pending(): void
    {
        $traveler = User::factory()->create();
        $sender = User::factory()->create();
        $booking = Booking::factory()->create(['sender_id' => $sender->id, 'traveler_id' => $traveler->id]);

        Sanctum::actingAs($sender);
        $this->getJson("/api/v1/bookings/{$booking->id}")
            ->assertOk()
            ->assertJsonPath('data.sender_phone_number', null)
            ->assertJsonPath('data.traveler_phone_number', null);
    }

    public function test_completing_requires_the_full_tracking_flow_and_inspection_confirmation(): void
    {
        Storage::fake('uploads');
        Notification::fake();

        $traveler = User::factory()->create();
        $booking = Booking::factory()->create(['traveler_id' => $traveler->id]);

        Sanctum::actingAs($traveler);

        $this->postJson("/api/v1/bookings/{$booking->id}/complete", ['inspection_confirmed' => true])
            ->assertUnprocessable();

        $booking->accept();

        $this->postJson("/api/v1/bookings/{$booking->id}/complete", ['inspection_confirmed' => true])
            ->assertUnprocessable();

        $this->postJson("/api/v1/bookings/{$booking->id}/mark-in-transit")
            ->assertOk()
            ->assertJsonPath('data.status', 'in_transit');

        $this->postJson("/api/v1/bookings/{$booking->id}/complete", ['inspection_confirmed' => true])
            ->assertUnprocessable();

        $this->postJson("/api/v1/bookings/{$booking->id}/mark-arrived")
            ->assertOk()
            ->assertJsonPath('data.status', 'arrived_at_destination');

        $this->postJson("/api/v1/bookings/{$booking->id}/complete", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['inspection_confirmed', 'otp']);

        $this->post("/api/v1/bookings/{$booking->id}/complete", [
            'inspection_confirmed' => true,
            'otp' => '000000',
            'photo' => UploadedFile::fake()->image('proof.jpg'),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['otp']);

        $this->postJson("/api/v1/bookings/{$booking->id}/send-delivery-otp")->assertOk();

        $code = null;
        Notification::assertSentOnDemand(DeliveryOtp::class, function ($notification) use (&$code) {
            $code = $notification->code;

            return true;
        });

        $this->post("/api/v1/bookings/{$booking->id}/complete", [
            'inspection_confirmed' => true,
            'otp' => $code,
            'photo' => UploadedFile::fake()->image('proof.jpg'),
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.delivery_proof_photo_url', fn ($url) => str_contains($url, 'delivery-proof-photos'));

        $this->assertEquals(1, $traveler->fresh()->completed_deliveries_count);
        $this->assertNull($booking->fresh()->delivery_otp_hash);
    }

    public function test_delivery_otp_is_emailed_to_the_receiver_and_can_be_wrong(): void
    {
        Notification::fake();
        Storage::fake('uploads');

        $traveler = User::factory()->create();
        $good = Good::factory()->create(['receiver_email' => 'receiver@example.com']);
        $booking = Booking::factory()->arrived()->create(['traveler_id' => $traveler->id, 'good_id' => $good->id]);

        Sanctum::actingAs($traveler);

        $this->postJson("/api/v1/bookings/{$booking->id}/send-delivery-otp")->assertOk();

        Notification::assertSentOnDemand(
            DeliveryOtp::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'receiver@example.com',
        );

        $this->post("/api/v1/bookings/{$booking->id}/complete", [
            'inspection_confirmed' => true,
            'otp' => '111111',
            'photo' => UploadedFile::fake()->image('proof.jpg'),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['otp']);
    }

    public function test_only_the_traveler_can_request_a_delivery_otp(): void
    {
        $traveler = User::factory()->create();
        $sender = User::factory()->create();
        $booking = Booking::factory()->arrived()->create(['traveler_id' => $traveler->id, 'sender_id' => $sender->id]);

        Sanctum::actingAs($sender);
        $this->postJson("/api/v1/bookings/{$booking->id}/send-delivery-otp")->assertForbidden();
    }

    public function test_only_the_traveler_can_advance_tracking_status(): void
    {
        $traveler = User::factory()->create();
        $sender = User::factory()->create();
        $booking = Booking::factory()->accepted()->create(['traveler_id' => $traveler->id, 'sender_id' => $sender->id]);

        Sanctum::actingAs($sender);
        $this->postJson("/api/v1/bookings/{$booking->id}/mark-in-transit")->assertForbidden();

        Sanctum::actingAs($traveler);
        $this->postJson("/api/v1/bookings/{$booking->id}/mark-in-transit")->assertOk();
    }

    public function test_either_party_can_cancel_a_pending_booking(): void
    {
        Notification::fake();

        $sender = User::factory()->create();
        $traveler = User::factory()->create();
        $booking = Booking::factory()->create(['sender_id' => $sender->id, 'traveler_id' => $traveler->id]);

        Sanctum::actingAs($sender);
        $this->postJson("/api/v1/bookings/{$booking->id}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        Notification::assertSentTo($traveler, BookingCancelled::class);
        Notification::assertNotSentTo($sender, BookingCancelled::class);
    }

    public function test_third_party_cannot_view_booking(): void
    {
        $booking = Booking::factory()->create();
        $stranger = User::factory()->create();

        Sanctum::actingAs($stranger);
        $this->getJson("/api/v1/bookings/{$booking->id}")->assertForbidden();
    }
}
