<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
}
