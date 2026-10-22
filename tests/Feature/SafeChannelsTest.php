<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Good;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class SafeChannelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_broken_mail_server_does_not_break_signup(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => '127.0.0.1',
            'mail.mailers.smtp.port' => 1,
            'mail.mailers.smtp.timeout' => 1,
        ]);
        Mail::purge();

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Sam', 'email' => 'sam@example.com', 'password' => 'password123', 'password_confirmation' => 'password123',
            'phone_number' => '+923001234567', 'country' => 'Pakistan',
        ])->assertCreated();

        $this->assertDatabaseHas('users', ['email' => 'sam@example.com']);
    }

    public function test_a_broken_mail_server_does_not_break_a_booking_request(): void
    {
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => '127.0.0.1', 'mail.mailers.smtp.port' => 1, 'mail.mailers.smtp.timeout' => 1]);
        Mail::purge();
        $traveler = User::factory()->create();
        $sender = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $traveler->id, 'max_weight_kg' => 20]);
        $good = Good::factory()->create(['user_id' => $sender->id, 'weight_kg' => 2]);
        Sanctum::actingAs($sender);

        $this->postJson('/api/v1/bookings', ['trip_id' => $trip->id, 'good_id' => $good->id])->assertCreated();

        $this->assertSame(1, Booking::count());
        $this->assertCount(1, $traveler->fresh()->notifications);
    }

    private function bookViaApi(User $traveler): void
    {
        $sender = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $traveler->id, 'max_weight_kg' => 20]);
        $good = Good::factory()->create(['user_id' => $sender->id, 'weight_kg' => 2]);
        Sanctum::actingAs($sender);

        $this->postJson('/api/v1/bookings', ['trip_id' => $trip->id, 'good_id' => $good->id])->assertCreated();
    }

    public function test_a_token_fcm_calls_invalid_is_deleted(): void
    {
        $traveler = User::factory()->create();
        $traveler->deviceTokens()->create(['token' => 'bad-token', 'platform' => 'android']);
        $messaging = Mockery::mock();
        $messaging->shouldReceive('send')->andThrow(new InvalidMessage('The registration token is not a valid FCM registration token'));
        Firebase::shouldReceive('messaging')->andReturn($messaging);

        $this->bookViaApi($traveler);

        $this->assertDatabaseMissing('device_tokens', ['token' => 'bad-token']);
    }

    public function test_a_token_is_kept_when_fcm_rejects_the_payload_rather_than_the_token(): void
    {
        $traveler = User::factory()->create();
        $traveler->deviceTokens()->create(['token' => 'good-token', 'platform' => 'android']);
        $messaging = Mockery::mock();
        $messaging->shouldReceive('send')->andThrow(new InvalidMessage('Invalid JSON payload received.'));
        Firebase::shouldReceive('messaging')->andReturn($messaging);

        $this->bookViaApi($traveler);

        $this->assertDatabaseHas('device_tokens', ['token' => 'good-token']);
    }

    public function test_an_unexpected_fcm_failure_never_breaks_the_request(): void
    {
        $traveler = User::factory()->create();
        $traveler->deviceTokens()->create(['token' => 'tok', 'platform' => 'ios']);
        $messaging = Mockery::mock();
        $messaging->shouldReceive('send')->andThrow(new \RuntimeException('network down'));
        Firebase::shouldReceive('messaging')->andReturn($messaging);

        $this->bookViaApi($traveler);

        $this->assertDatabaseHas('device_tokens', ['token' => 'tok']);
        $this->assertCount(1, $traveler->fresh()->notifications);
    }
}
