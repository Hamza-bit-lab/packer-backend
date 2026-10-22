<?php

namespace Tests\Feature;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeviceTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_a_device_token(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $this->postJson('/api/v1/device-tokens', ['token' => 'fcm-token-abc', 'platform' => 'android'])
            ->assertCreated();

        $this->assertDatabaseHas('device_tokens', ['token' => 'fcm-token-abc', 'user_id' => $user->id]);
    }

    public function test_registering_a_token_already_owned_by_someone_else_reassigns_it(): void
    {
        // Simulates logging out of one account and into another on the same
        // physical device — the previous owner must stop receiving this
        // device's pushes once the token moves to the new user.
        $previousOwner = User::factory()->create();
        $newOwner = User::factory()->create();

        DeviceToken::factory()->create(['user_id' => $previousOwner->id, 'token' => 'shared-device-token']);

        Sanctum::actingAs($newOwner);
        $this->postJson('/api/v1/device-tokens', ['token' => 'shared-device-token', 'platform' => 'ios'])
            ->assertCreated();

        $this->assertDatabaseCount('device_tokens', 1);
        $this->assertDatabaseHas('device_tokens', ['token' => 'shared-device-token', 'user_id' => $newOwner->id]);
        $this->assertDatabaseMissing('device_tokens', ['token' => 'shared-device-token', 'user_id' => $previousOwner->id]);
    }

    public function test_user_can_unregister_their_own_device_token_on_logout(): void
    {
        $user = User::factory()->create();
        DeviceToken::factory()->create(['user_id' => $user->id, 'token' => 'to-remove']);

        Sanctum::actingAs($user);
        $this->deleteJson('/api/v1/device-tokens', ['token' => 'to-remove'])->assertOk();

        $this->assertDatabaseMissing('device_tokens', ['token' => 'to-remove']);
    }

    public function test_cannot_unregister_someone_elses_device_token(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        DeviceToken::factory()->create(['user_id' => $owner->id, 'token' => 'someone-elses-token']);

        Sanctum::actingAs($stranger);
        $this->deleteJson('/api/v1/device-tokens', ['token' => 'someone-elses-token'])->assertOk();

        // No error (idempotent), but the row must survive since it isn't theirs.
        $this->assertDatabaseHas('device_tokens', ['token' => 'someone-elses-token', 'user_id' => $owner->id]);
    }
}
