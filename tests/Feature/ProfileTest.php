<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_profile_hides_email_and_phone(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/v1/users/{$user->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonMissingPath('data.email')
            ->assertJsonMissingPath('data.phone_number');
    }

    public function test_user_can_update_own_profile(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);
        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/v1/me', [
            'name' => 'New Name',
        ]);

        $response->assertOk()->assertJsonPath('data.name', 'New Name');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name']);
    }

    public function test_user_can_upload_profile_picture(): void
    {
        Storage::fake('uploads');
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/v1/me', [
            'profile_picture' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

        $response->assertOk();
        $this->assertNotNull($response->json('data.profile_pic_url'));
    }

    public function test_update_profile_requires_authentication(): void
    {
        $this->patchJson('/api/v1/me', ['name' => 'X'])->assertStatus(401);
    }

    public function test_authenticated_requests_mark_the_user_online(): void
    {
        $user = User::factory()->create(['last_seen_at' => null]);
        $viewer = User::factory()->create();

        Sanctum::actingAs($user);
        $this->getJson('/api/v1/me')->assertOk();

        $this->assertNotNull($user->fresh()->last_seen_at);

        Sanctum::actingAs($viewer);
        $this->getJson("/api/v1/users/{$user->id}")
            ->assertOk()
            ->assertJsonPath('data.is_online', true);
    }

    public function test_a_user_inactive_for_a_while_shows_as_offline(): void
    {
        $user = User::factory()->create(['last_seen_at' => now()->subMinutes(10)]);

        $response = $this->getJson("/api/v1/users/{$user->id}");

        $response->assertOk()
            ->assertJsonPath('data.is_online', false)
            ->assertJsonPath('data.last_seen_at', fn ($value) => $value !== null);
    }
}
