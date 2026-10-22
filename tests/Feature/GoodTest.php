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

class GoodTest extends TestCase
{
    use RefreshDatabase;

    public function test_anyone_can_list_goods_with_filters(): void
    {
        Good::factory()->create(['from_city' => 'Karachi', 'to_city' => 'Riyadh']);
        Good::factory()->create(['from_city' => 'Sao Paulo', 'to_city' => 'New York']);

        $response = $this->getJson('/api/v1/goods?from_city=Karachi');

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_good_listing_exposes_senders_phone_number_for_call_and_whatsapp(): void
    {
        $sender = User::factory()->create(['phone_number' => '+15559998888']);
        $good = Good::factory()->create(['user_id' => $sender->id]);

        $response = $this->getJson("/api/v1/goods/{$good->id}");

        $response->assertOk()->assertJsonPath('data.sender_phone_number', '+15559998888');
    }

    public function test_viewing_a_good_increments_its_view_count(): void
    {
        $good = Good::factory()->create(['views_count' => 0]);
        $viewer = User::factory()->create();

        // Deliberately a real bearer token (not Sanctum::actingAs, which sets
        // the guard directly and would mask a bug this route is prone to):
        // /goods/{good} is a public route outside the auth:sanctum middleware
        // group, so $request->user() alone doesn't resolve the token — only
        // $request->user('sanctum') does. actingAs bypasses that distinction
        // entirely, so it can't catch a regression back to the unguarded call.
        $token = $viewer->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")->getJson("/api/v1/goods/{$good->id}")
            ->assertOk()->assertJsonPath('data.views_count', 1);
        $this->withHeader('Authorization', "Bearer {$token}")->getJson("/api/v1/goods/{$good->id}")
            ->assertOk()->assertJsonPath('data.views_count', 2);
    }

    public function test_the_owner_viewing_their_own_good_does_not_increment_its_view_count(): void
    {
        $owner = User::factory()->create();
        $good = Good::factory()->create(['user_id' => $owner->id, 'views_count' => 0]);

        // Real bearer token, not Sanctum::actingAs — see comment above.
        $token = $owner->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson("/api/v1/goods/{$good->id}");

        $response->assertOk()->assertJsonPath('data.views_count', 0);
    }

    public function test_goods_from_a_blocked_user_are_excluded_when_authenticated(): void
    {
        $viewer = User::factory()->create();
        $blocked = User::factory()->create();
        Block::factory()->create(['blocker_id' => $viewer->id, 'blocked_user_id' => $blocked->id]);
        Good::factory()->create(['user_id' => $blocked->id]);
        $visible = Good::factory()->create();

        // Real bearer token, not Sanctum::actingAs — /goods is a public route
        // outside auth:sanctum, so only $request->user('sanctum') resolves
        // the token there; actingAs would mask a regression to the plain call.
        $token = $viewer->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/v1/goods');

        $response->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $visible->id);
    }

    public function test_goods_can_be_filtered_by_user_id(): void
    {
        $owner = User::factory()->create();
        Good::factory()->create(['user_id' => $owner->id]);
        Good::factory()->create();

        $response = $this->getJson("/api/v1/goods?user_id={$owner->id}");

        $response->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.sender.id', $owner->id);
    }

    public function test_authenticated_user_can_create_good(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/goods', [
            'from_city' => 'Lahore',
            'to_city' => 'Jeddah',
            'item_description' => 'A small box of documents',
            'receiver_email' => 'receiver@example.com',
            'weight_kg' => 2.5,
            'offered_reward' => 20,
            'currency_type' => 'USD',
            'pickup_preference' => 'sender_delivers',
            'terms_accepted' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.from_city', 'Lahore')
            ->assertJsonPath('data.pickup_preference', 'sender_delivers')
            ->assertJsonPath('data.receiver_email', 'receiver@example.com');
        $this->assertDatabaseHas('goods', ['from_city' => 'Lahore', 'user_id' => $user->id, 'receiver_email' => 'receiver@example.com']);
    }

    public function test_receiver_email_is_hidden_from_a_guest(): void
    {
        $good = Good::factory()->create(['receiver_email' => 'receiver@example.com']);

        $this->getJson("/api/v1/goods/{$good->id}")
            ->assertOk()->assertJsonPath('data.receiver_email', null);
    }

    public function test_receiver_email_is_hidden_from_a_stranger(): void
    {
        $good = Good::factory()->create(['receiver_email' => 'receiver@example.com']);
        $stranger = User::factory()->create();

        // Real bearer token, not Sanctum::actingAs — see comment above.
        $token = $stranger->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer {$token}")->getJson("/api/v1/goods/{$good->id}")
            ->assertOk()->assertJsonPath('data.receiver_email', null);
    }

    public function test_receiver_email_is_visible_to_the_sender(): void
    {
        $sender = User::factory()->create();
        $good = Good::factory()->create(['user_id' => $sender->id, 'receiver_email' => 'receiver@example.com']);

        // Real bearer token, not Sanctum::actingAs — see comment above.
        $token = $sender->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer {$token}")->getJson("/api/v1/goods/{$good->id}")
            ->assertOk()->assertJsonPath('data.receiver_email', 'receiver@example.com');
    }

    public function test_posting_a_good_notifies_travelers_already_flying_that_route(): void
    {
        Notification::fake();

        $traveler = User::factory()->create();
        Trip::factory()->create(['user_id' => $traveler->id, 'from_city' => 'Lahore', 'to_city' => 'Jeddah']);
        $otherRouteTraveler = User::factory()->create();
        Trip::factory()->create(['user_id' => $otherRouteTraveler->id, 'from_city' => 'Karachi', 'to_city' => 'Dubai']);

        $sender = User::factory()->create();
        Sanctum::actingAs($sender);

        $this->postJson('/api/v1/goods', [
            'from_city' => 'Lahore',
            'to_city' => 'Jeddah',
            'item_description' => 'A small box of documents',
            'receiver_email' => 'receiver@example.com',
            'weight_kg' => 2.5,
            'offered_reward' => 20,
            'currency_type' => 'USD',
            'pickup_preference' => 'sender_delivers',
            'terms_accepted' => true,
        ])->assertCreated();

        Notification::assertSentTo($traveler, MatchingRouteListing::class);
        Notification::assertNotSentTo($otherRouteTraveler, MatchingRouteListing::class);
    }

    public function test_creating_good_requires_a_valid_pickup_preference(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/goods', [
            'from_city' => 'Lahore',
            'to_city' => 'Jeddah',
            'item_description' => 'A small box of documents',
            'weight_kg' => 2.5,
            'offered_reward' => 20,
            'currency_type' => 'USD',
            'pickup_preference' => 'carrier_pigeon',
            'terms_accepted' => true,
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['pickup_preference']);
    }

    public function test_creating_good_requires_terms_accepted(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/goods', [
            'from_city' => 'Lahore',
            'to_city' => 'Jeddah',
            'item_description' => 'A small box of documents',
            'weight_kg' => 2.5,
            'offered_reward' => 20,
            'currency_type' => 'USD',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['terms_accepted']);
    }

    public function test_only_owner_can_update_good(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $good = Good::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($other);
        $this->patchJson("/api/v1/goods/{$good->id}", ['from_city' => 'Hacked'])->assertForbidden();

        Sanctum::actingAs($owner);
        $this->patchJson("/api/v1/goods/{$good->id}", ['from_city' => 'Updated'])
            ->assertOk()
            ->assertJsonPath('data.from_city', 'Updated');
    }

    public function test_only_owner_can_delete_good(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $good = Good::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($other);
        $this->deleteJson("/api/v1/goods/{$good->id}")->assertForbidden();

        Sanctum::actingAs($owner);
        $this->deleteJson("/api/v1/goods/{$good->id}")->assertNoContent();
        $this->assertDatabaseMissing('goods', ['id' => $good->id]);
    }

    public function test_creating_good_requires_authentication(): void
    {
        $this->postJson('/api/v1/goods', [])->assertStatus(401);
    }
}
