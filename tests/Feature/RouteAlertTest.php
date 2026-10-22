<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RouteAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_list_and_delete_a_route_alert(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/route-alerts', [
            'watch_type' => 'travelers',
            'from_city' => 'Lahore',
            'to_city' => 'London',
        ]);
        $response->assertCreated()->assertJsonPath('data.from_city', 'Lahore');

        $alertId = $response->json('data.id');

        $this->getJson('/api/v1/route-alerts')->assertOk()->assertJsonCount(1, 'data');

        $this->deleteJson("/api/v1/route-alerts/{$alertId}")->assertNoContent();
        $this->getJson('/api/v1/route-alerts')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_user_cannot_delete_someone_elses_route_alert(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        Sanctum::actingAs($owner);
        $alertId = $this->postJson('/api/v1/route-alerts', [
            'watch_type' => 'goods',
            'from_city' => 'Dubai',
            'to_city' => 'Karachi',
        ])->json('data.id');

        Sanctum::actingAs($stranger);
        $this->deleteJson("/api/v1/route-alerts/{$alertId}")->assertForbidden();
    }

    public function test_posting_a_matching_trip_notifies_watchers(): void
    {
        $watcher = User::factory()->create();
        Sanctum::actingAs($watcher);
        $this->postJson('/api/v1/route-alerts', [
            'watch_type' => 'travelers',
            'from_city' => 'Lahore',
            'to_city' => 'London',
        ])->assertCreated();

        $traveler = User::factory()->create();
        Sanctum::actingAs($traveler);
        $this->postJson('/api/v1/trips', [
            'from_city' => 'Lahore',
            'to_city' => 'London',
            'departure_date_time' => now()->addWeek()->toDateTimeString(),
            'airline_name' => 'PIA',
            'max_weight_kg' => 20,
            'rate_per_kg' => 5,
            'currency_type' => 'USD',
            'delivery_method' => 'receiver_pickup',
            'pickup_method' => 'traveler_picks_up',
            'terms_accepted' => true,
        ])->assertCreated();

        $this->assertEquals(1, $watcher->fresh()->unreadNotifications()->count());
    }
}
