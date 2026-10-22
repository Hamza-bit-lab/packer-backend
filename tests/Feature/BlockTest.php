<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BlockTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_block_and_unblock_another_user(): void
    {
        $blocker = User::factory()->create();
        $blocked = User::factory()->create();

        Sanctum::actingAs($blocker);
        $this->postJson("/api/v1/users/{$blocked->id}/block")->assertCreated();

        $this->getJson('/api/v1/blocks')->assertOk()->assertJsonCount(1, 'data');

        $this->deleteJson("/api/v1/users/{$blocked->id}/block")->assertOk();
        $this->getJson('/api/v1/blocks')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_user_cannot_block_themself(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $this->postJson("/api/v1/users/{$user->id}/block")->assertUnprocessable();
    }

    public function test_blocked_users_trips_are_hidden_from_each_others_feed(): void
    {
        $blocker = User::factory()->create();
        $blocked = User::factory()->create();
        Trip::factory()->create(['user_id' => $blocked->id]);

        Sanctum::actingAs($blocker);
        $this->postJson("/api/v1/users/{$blocked->id}/block")->assertCreated();

        $this->getJson('/api/v1/trips')->assertOk()->assertJsonCount(0, 'data');
    }
}
