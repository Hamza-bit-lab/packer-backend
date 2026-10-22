<?php

namespace Tests\Feature;

use App\Models\IdVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IdVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_an_id_document(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $this->post('/api/v1/id-verification', [
            'document_type' => 'cnic',
            'photo' => UploadedFile::fake()->image('cnic.jpg'),
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.document_type', 'cnic');

        $this->assertDatabaseHas('id_verifications', ['user_id' => $user->id, 'status' => 'pending']);
        Storage::disk('local')->assertExists(
            IdVerification::first()->document_photo_url
        );
    }

    public function test_cannot_resubmit_while_a_submission_is_still_pending(): void
    {
        $user = User::factory()->create();
        IdVerification::factory()->for($user)->create();

        Sanctum::actingAs($user);
        $this->post('/api/v1/id-verification', [
            'document_type' => 'passport',
            'photo' => UploadedFile::fake()->image('passport.jpg'),
        ])->assertUnprocessable();
    }

    public function test_can_resubmit_after_a_rejection(): void
    {
        $user = User::factory()->create();
        IdVerification::factory()->for($user)->rejected()->create();

        Sanctum::actingAs($user);
        $this->post('/api/v1/id-verification', [
            'document_type' => 'passport',
            'photo' => UploadedFile::fake()->image('passport.jpg'),
        ])->assertCreated();
    }

    public function test_cannot_resubmit_once_approved(): void
    {
        $user = User::factory()->create();
        IdVerification::factory()->for($user)->approved()->create();

        Sanctum::actingAs($user);
        $this->post('/api/v1/id-verification', [
            'document_type' => 'passport',
            'photo' => UploadedFile::fake()->image('passport.jpg'),
        ])->assertUnprocessable();
    }

    public function test_show_returns_the_latest_submission(): void
    {
        $user = User::factory()->create();
        IdVerification::factory()->for($user)->rejected()->create(['created_at' => now()->subDay()]);
        $latest = IdVerification::factory()->for($user)->create();

        Sanctum::actingAs($user);
        $this->getJson('/api/v1/id-verification')
            ->assertOk()
            ->assertJsonPath('data.id', $latest->id)
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_approved_verification_shows_as_id_verified_on_the_public_profile(): void
    {
        $user = User::factory()->create();
        IdVerification::factory()->for($user)->approved()->create();

        $this->getJson("/api/v1/users/{$user->id}")
            ->assertOk()
            ->assertJsonPath('data.id_verified', true);
    }

    public function test_rejection_reason_is_visible_to_the_owner(): void
    {
        $user = User::factory()->create();
        IdVerification::factory()->for($user)->rejected()->create();

        Sanctum::actingAs($user);
        $this->getJson('/api/v1/id-verification')
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath('data.rejection_reason', 'Document photo was blurry.');
    }
}
