<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\EmailVerificationOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registering_sends_a_verification_code(): void
    {
        Notification::fake();

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Jamie Doe',
            'email' => 'jamie@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '+15550001234',
            'country' => 'United States',
        ])->assertCreated()->assertJsonPath('user.email_verified', false);

        $user = User::where('email', 'jamie@example.com')->first();
        Notification::assertSentTo($user, EmailVerificationOtp::class);
        $this->assertNotNull($user->fresh()->email_verification_code);
    }

    public function test_verifying_with_the_correct_code_marks_email_verified(): void
    {
        $user = User::factory()->unverified()->create();
        $code = $user->generateEmailVerificationCode();

        Sanctum::actingAs($user);
        $this->postJson('/api/v1/auth/verify-email', ['code' => $code])
            ->assertOk()
            ->assertJsonPath('user.email_verified', true);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertNull($user->fresh()->email_verification_code);
    }

    public function test_verifying_with_an_incorrect_code_fails(): void
    {
        $user = User::factory()->unverified()->create();
        $user->generateEmailVerificationCode();

        Sanctum::actingAs($user);
        $this->postJson('/api/v1/auth/verify-email', ['code' => '000000'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_verifying_with_an_expired_code_fails(): void
    {
        $user = User::factory()->unverified()->create();
        $code = $user->generateEmailVerificationCode();
        $user->forceFill(['email_verification_code_expires_at' => now()->subMinute()])->save();

        Sanctum::actingAs($user);
        $this->postJson('/api/v1/auth/verify-email', ['code' => $code])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    public function test_resend_verification_endpoint_requires_auth_and_skips_if_already_verified(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $this->postJson('/api/v1/auth/resend-verification')
            ->assertOk()
            ->assertJsonPath('message', 'Your email is already verified.');
    }

    public function test_resend_generates_a_fresh_code(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();
        $originalCode = $user->generateEmailVerificationCode();

        Sanctum::actingAs($user);
        $this->postJson('/api/v1/auth/resend-verification')->assertOk();

        Notification::assertSentTo($user, EmailVerificationOtp::class);
        $this->assertNotEquals($originalCode, $user->fresh()->email_verification_code);
    }
}
