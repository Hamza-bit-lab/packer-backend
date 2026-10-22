<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Good;
use App\Models\Message;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationPreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_preferences_default_to_true(): void
    {
        $user = User::factory()->create();

        // Eloquent doesn't know the DB just applied the column defaults
        // for the fields the factory left unset — refetch so the in-memory
        // model (which Sanctum::actingAs hands straight to the request)
        // reflects them, the same way a real authenticated request would.
        Sanctum::actingAs($user->fresh());
        $this->getJson('/api/v1/notification-preferences')
            ->assertOk()
            ->assertJsonPath('data.notify_bookings', true)
            ->assertJsonPath('data.notify_messages', true)
            ->assertJsonPath('data.notify_route_alerts', true);
    }

    public function test_user_can_update_notification_preferences(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user->fresh());
        $this->patchJson('/api/v1/notification-preferences', [
            'notify_bookings' => false,
            'notify_messages' => true,
            'notify_route_alerts' => false,
        ])
            ->assertOk()
            ->assertJsonPath('data.notify_bookings', false)
            ->assertJsonPath('data.notify_messages', true)
            ->assertJsonPath('data.notify_route_alerts', false);

        $this->assertFalse($user->fresh()->notify_bookings);
        $this->assertTrue($user->fresh()->notify_messages);
        $this->assertFalse($user->fresh()->notify_route_alerts);
    }

    public function test_muting_bookings_suppresses_booking_notifications_but_not_messages(): void
    {
        $traveler = User::factory()->create(['notify_bookings' => false]);
        $sender = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $traveler->id, 'max_weight_kg' => 20]);
        $good = Good::factory()->create(['user_id' => $sender->id, 'weight_kg' => 2]);

        Sanctum::actingAs($sender);
        $this->postJson('/api/v1/bookings', ['trip_id' => $trip->id, 'good_id' => $good->id])->assertCreated();

        // Muted: the booking request notification isn't created in-app.
        $this->assertSame(0, $traveler->fresh()->notifications()->count());

        // A different, unmuted category still comes through for the same user.
        $booking = Booking::factory()->create(['traveler_id' => $traveler->id, 'sender_id' => $sender->id]);
        $traveler->fresh()->notify(new \App\Notifications\NewMessage(
            Message::factory()->create([
                'sender_id' => $sender->id,
                'receiver_id' => $traveler->id,
                'booking_id' => $booking->id,
                'body' => 'Hello!',
            ])
        ));

        Sanctum::actingAs($traveler);
        $this->getJson('/api/v1/notifications/unread-count')->assertOk()->assertJsonPath('unread_count', 1);
    }
}
