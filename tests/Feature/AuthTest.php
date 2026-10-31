<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Jane Traveler',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '+15551234567',
            'country' => 'Pakistan',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['user' => ['id', 'name', 'email', 'phone_number', 'country'], 'token'])
            ->assertJsonPath('user.average_rating', 0)
            ->assertJsonPath('user.total_reviews', 0);

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com', 'country' => 'Pakistan']);
    }

    public function test_registration_requires_unique_email_and_phone(): void
    {
        User::factory()->create(['email' => 'taken@example.com', 'phone_number' => '+15550000000']);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Jane',
            'email' => 'taken@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '+15550000000',
            'country' => 'Pakistan',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['email', 'phone_number']);
    }

    public function test_registration_requires_a_country(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Jane',
            'email' => 'jane2@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '+15551239999',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['country']);
    }

    public function test_registration_accepts_any_country_name_from_the_picker(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Jane',
            'email' => 'jane3@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '+15551238888',
            'country' => 'São Tomé and Príncipe',
        ]);

        $response->assertCreated();
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret123')]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertOk()->assertJsonStructure(['user', 'token']);
    }

    public function test_login_fails_with_incorrect_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret123')]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_fetch_and_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('mobile')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout')
            ->assertOk();
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/v1/me')->assertStatus(401);
    }

    private function fakeGoogleToken(array $overrides = []): void
    {
        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(array_merge([
                'aud' => config('services.google.client_id'),
                'sub' => 'google-123',
                'email' => 'jane@example.com',
                'email_verified' => 'true',
                'name' => 'Jane Traveler',
            ], $overrides)),
        ]);
    }

    public function test_google_sign_in_creates_a_new_verified_user(): void
    {
        $this->fakeGoogleToken();

        $response = $this->postJson('/api/v1/auth/google', ['id_token' => 'fake-token']);

        $response->assertOk()->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);
        $this->assertDatabaseHas('users', ['email' => 'jane@example.com', 'google_id' => 'google-123']);
        $this->assertNotNull(User::where('email', 'jane@example.com')->first()->email_verified_at);
    }

    public function test_google_sign_in_links_to_an_existing_account_by_email(): void
    {
        $user = User::factory()->create(['email' => 'jane@example.com', 'google_id' => null]);
        $this->fakeGoogleToken();

        $response = $this->postJson('/api/v1/auth/google', ['id_token' => 'fake-token']);

        $response->assertOk()->assertJsonPath('user.id', $user->id);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'google_id' => 'google-123']);
    }

    public function test_google_sign_in_rejects_a_token_for_a_different_app(): void
    {
        $this->fakeGoogleToken(['aud' => 'someone-elses-client-id']);

        $this->postJson('/api/v1/auth/google', ['id_token' => 'fake-token'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['id_token']);
    }

    public function test_google_sign_in_rejects_an_unverified_email(): void
    {
        $this->fakeGoogleToken(['email_verified' => 'false']);

        $this->postJson('/api/v1/auth/google', ['id_token' => 'fake-token'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['id_token']);
    }
}
