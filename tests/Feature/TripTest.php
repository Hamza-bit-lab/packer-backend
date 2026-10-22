<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\Good;
use App\Models\Trip;
use App\Models\User;
use App\Notifications\MatchingRouteListing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TripTest extends TestCase
{
    use RefreshDatabase;

    public function test_anyone_can_list_trips_with_filters(): void
    {
        Trip::factory()->create(['from_city' => 'Karachi', 'to_city' => 'Riyadh']);
        Trip::factory()->create(['from_city' => 'Sao Paulo', 'to_city' => 'New York']);

        $response = $this->getJson('/api/v1/trips?from_city=Karachi');

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_trip_listing_exposes_travelers_phone_number_for_call_and_whatsapp(): void
    {
        $traveler = User::factory()->create(['phone_number' => '+15559998888']);
        $trip = Trip::factory()->create(['user_id' => $traveler->id]);

        $response = $this->getJson("/api/v1/trips/{$trip->id}");

        $response->assertOk()->assertJsonPath('data.traveler_phone_number', '+15559998888');
    }

    public function test_viewing_a_trip_increments_its_view_count(): void
    {
        $trip = Trip::factory()->create(['views_count' => 0]);
        $viewer = User::factory()->create();

        // Deliberately a real bearer token (not Sanctum::actingAs, which sets
        // the guard directly and would mask a bug this route is prone to):
        // /trips/{trip} is a public route outside the auth:sanctum middleware
        // group, so $request->user() alone doesn't resolve the token — only
        // $request->user('sanctum') does. actingAs bypasses that distinction
        // entirely, so it can't catch a regression back to the unguarded call.
        $token = $viewer->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")->getJson("/api/v1/trips/{$trip->id}")
            ->assertOk()->assertJsonPath('data.views_count', 1);
        $this->withHeader('Authorization', "Bearer {$token}")->getJson("/api/v1/trips/{$trip->id}")
            ->assertOk()->assertJsonPath('data.views_count', 2);
    }

    public function test_the_owner_viewing_their_own_trip_does_not_increment_its_view_count(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id, 'views_count' => 0]);

        // Real bearer token, not Sanctum::actingAs — see comment above.
        $token = $owner->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson("/api/v1/trips/{$trip->id}");

        $response->assertOk()->assertJsonPath('data.views_count', 0);
    }

    public function test_trips_from_a_blocked_user_are_excluded_when_authenticated(): void
    {
        $viewer = User::factory()->create();
        $blocked = User::factory()->create();
        Block::factory()->create(['blocker_id' => $viewer->id, 'blocked_user_id' => $blocked->id]);
        Trip::factory()->create(['user_id' => $blocked->id]);
        $visible = Trip::factory()->create();

        // Real bearer token, not Sanctum::actingAs — /trips is a public route
        // outside auth:sanctum, so only $request->user('sanctum') resolves
        // the token there; actingAs would mask a regression to the plain call.
        $token = $viewer->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/v1/trips');

        $response->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $visible->id);
    }

    public function test_trips_can_be_filtered_by_user_id(): void
    {
        $owner = User::factory()->create();
        Trip::factory()->create(['user_id' => $owner->id]);
        Trip::factory()->create();

        $response = $this->getJson("/api/v1/trips?user_id={$owner->id}");

        $response->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.traveler.id', $owner->id);
    }

    public function test_trips_whose_departure_date_has_passed_are_excluded_from_listings(): void
    {
        $owner = User::factory()->create();
        Trip::factory()->create(['user_id' => $owner->id, 'departure_date_time' => now()->subDay()]);
        $upcoming = Trip::factory()->create(['user_id' => $owner->id, 'departure_date_time' => now()->addDay()]);

        $response = $this->getJson("/api/v1/trips?user_id={$owner->id}");

        $response->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $upcoming->id);
    }

    public function test_authenticated_user_can_create_trip(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/trips', [
            'from_city' => 'Lahore',
            'to_city' => 'Jeddah',
            'departure_date_time' => now()->addWeek()->toDateTimeString(),
            'airline_name' => 'PIA',
            'max_weight_kg' => 15,
            'rate_per_kg' => 10,
            'currency_type' => 'USD',
            'delivery_method' => 'receiver_pickup',
            'pickup_method' => 'traveler_picks_up',
            'terms_accepted' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.from_city', 'Lahore')
            ->assertJsonPath('data.pickup_method', 'traveler_picks_up');
        $this->assertDatabaseHas('trips', ['from_city' => 'Lahore', 'user_id' => $user->id]);
    }

    public function test_posting_a_trip_notifies_senders_already_needing_that_route(): void
    {
        Notification::fake();

        $sender = User::factory()->create();
        Good::factory()->create(['user_id' => $sender->id, 'from_city' => 'Lahore', 'to_city' => 'Jeddah']);
        $otherRouteSender = User::factory()->create();
        Good::factory()->create(['user_id' => $otherRouteSender->id, 'from_city' => 'Karachi', 'to_city' => 'Dubai']);

        $traveler = User::factory()->create();
        Sanctum::actingAs($traveler);

        $this->postJson('/api/v1/trips', [
            'from_city' => 'Lahore',
            'to_city' => 'Jeddah',
            'departure_date_time' => now()->addWeek()->toDateTimeString(),
            'airline_name' => 'PIA',
            'max_weight_kg' => 15,
            'rate_per_kg' => 10,
            'currency_type' => 'USD',
            'delivery_method' => 'receiver_pickup',
            'pickup_method' => 'traveler_picks_up',
            'terms_accepted' => true,
        ])->assertCreated();

        Notification::assertSentTo($sender, MatchingRouteListing::class);
        Notification::assertNotSentTo($otherRouteSender, MatchingRouteListing::class);
    }

    public function test_creating_trip_requires_a_valid_delivery_method(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/trips', [
            'from_city' => 'Lahore',
            'to_city' => 'Jeddah',
            'departure_date_time' => now()->addWeek()->toDateTimeString(),
            'airline_name' => 'PIA',
            'max_weight_kg' => 15,
            'rate_per_kg' => 10,
            'currency_type' => 'USD',
            'delivery_method' => 'carrier_pigeon',
            'pickup_method' => 'traveler_picks_up',
            'terms_accepted' => true,
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['delivery_method']);
    }

    public function test_creating_trip_requires_a_valid_pickup_method(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/trips', [
            'from_city' => 'Lahore',
            'to_city' => 'Jeddah',
            'departure_date_time' => now()->addWeek()->toDateTimeString(),
            'airline_name' => 'PIA',
            'max_weight_kg' => 15,
            'rate_per_kg' => 10,
            'currency_type' => 'USD',
            'delivery_method' => 'receiver_pickup',
            'pickup_method' => 'carrier_pigeon',
            'terms_accepted' => true,
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['pickup_method']);
    }

    public function test_trips_can_be_sorted_by_travelers_experience(): void
    {
        $novice = User::factory()->create(['completed_deliveries_count' => 1]);
        $veteran = User::factory()->create(['completed_deliveries_count' => 20]);
        Trip::factory()->create(['user_id' => $novice->id, 'from_city' => 'Lahore', 'to_city' => 'Jeddah']);
        Trip::factory()->create(['user_id' => $veteran->id, 'from_city' => 'Lahore', 'to_city' => 'Jeddah']);

        $response = $this->getJson('/api/v1/trips?sort=experience');

        $response->assertOk()->assertJsonPath('data.0.traveler.id', $veteran->id);
    }

    public function test_creating_trip_requires_terms_accepted(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/trips', [
            'from_city' => 'Lahore',
            'to_city' => 'Jeddah',
            'departure_date_time' => now()->addWeek()->toDateTimeString(),
            'airline_name' => 'PIA',
            'max_weight_kg' => 15,
            'rate_per_kg' => 10,
            'currency_type' => 'USD',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['terms_accepted']);
    }

    public function test_only_owner_can_update_trip(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($other);
        $this->patchJson("/api/v1/trips/{$trip->id}", ['from_city' => 'Hacked'])->assertForbidden();

        Sanctum::actingAs($owner);
        $this->patchJson("/api/v1/trips/{$trip->id}", ['from_city' => 'Updated'])
            ->assertOk()
            ->assertJsonPath('data.from_city', 'Updated');
    }

    public function test_only_owner_can_delete_trip(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($other);
        $this->deleteJson("/api/v1/trips/{$trip->id}")->assertForbidden();

        Sanctum::actingAs($owner);
        $this->deleteJson("/api/v1/trips/{$trip->id}")->assertNoContent();
        $this->assertDatabaseMissing('trips', ['id' => $trip->id]);
    }

    public function test_creating_trip_requires_authentication(): void
    {
        $this->postJson('/api/v1/trips', [])->assertStatus(401);
    }
}
