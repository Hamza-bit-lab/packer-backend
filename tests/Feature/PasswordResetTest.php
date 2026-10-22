<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\PasswordResetOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private function requestCode(User $user): string
    {
        Notification::fake();
        $this->postJson('/api/v1/auth/forgot-password', ['email' => $user->email])->assertOk();

        $code = null;
        Notification::assertSentTo($user, PasswordResetOtp::class, function (PasswordResetOtp $n) use (&$code) {
            $code = $n->code;

            return true;
        });

        return $code;
    }

    public function test_a_registered_user_gets_a_reset_code_by_email(): void
    {
        $user = User::factory()->create();

        $code = $this->requestCode($user);

        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);
    }

    public function test_an_unknown_email_gets_the_same_answer_and_no_email(): void
    {
        Notification::fake();

        $this->postJson('/api/v1/auth/forgot-password', ['email' => 'nobody@example.com'])
            ->assertOk()
            ->assertJsonPath('message', 'If that email is registered, we sent a reset code to it.');

        Notification::assertNothingSent();
    }

    public function test_a_banned_user_does_not_get_a_code(): void
    {
        $user = User::factory()->create(['is_banned' => true]);
        Notification::fake();

        $this->postJson('/api/v1/auth/forgot-password', ['email' => $user->email])->assertOk();

        Notification::assertNothingSent();
    }

    public function test_the_correct_code_sets_the_new_password_and_signs_out_other_sessions(): void
    {
        $user = User::factory()->create(['password' => 'old-password-1']);
        $user->createToken('mobile');
        $code = $this->requestCode($user);

        $this->postJson('/api/v1/auth/reset-password', [
            'email' => $user->email,
            'code' => $code,
            'password' => 'brand-new-pass',
            'password_confirmation' => 'brand-new-pass',
        ])->assertOk();

        $this->assertTrue(Hash::check('brand-new-pass', $user->fresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'brand-new-pass'])->assertOk();
    }

    public function test_a_code_can_only_be_used_once(): void
    {
        $user = User::factory()->create();
        $code = $this->requestCode($user);
        $payload = ['email' => $user->email, 'code' => $code, 'password' => 'brand-new-pass', 'password_confirmation' => 'brand-new-pass'];

        $this->postJson('/api/v1/auth/reset-password', $payload)->assertOk();
        $this->postJson('/api/v1/auth/reset-password', $payload)->assertUnprocessable();
    }

    public function test_a_wrong_code_is_rejected_and_the_password_is_unchanged(): void
    {
        $user = User::factory()->create(['password' => 'old-password-1']);
        $this->requestCode($user);

        $this->postJson('/api/v1/auth/reset-password', [
            'email' => $user->email,
            'code' => '000000',
            'password' => 'brand-new-pass',
            'password_confirmation' => 'brand-new-pass',
        ])->assertUnprocessable()->assertJsonValidationErrors('code');

        $this->assertTrue(Hash::check('old-password-1', $user->fresh()->password));
    }

    public function test_the_code_is_thrown_away_after_too_many_wrong_guesses(): void
    {
        // The route-level throttle would otherwise stop us at the sixth
        // request — this test is about the per-code attempt limit instead.
        $this->withoutMiddleware(ThrottleRequests::class);
        $user = User::factory()->create();
        $code = $this->requestCode($user);
        $wrong = $code === '111111' ? '222222' : '111111';

        foreach (range(1, 5) as $ignored) {
            $this->postJson('/api/v1/auth/reset-password', ['email' => $user->email, 'code' => $wrong, 'password' => 'brand-new-pass', 'password_confirmation' => 'brand-new-pass'])->assertUnprocessable();
        }

        // Even the right code no longer works.
        $this->postJson('/api/v1/auth/reset-password', ['email' => $user->email, 'code' => $code, 'password' => 'brand-new-pass', 'password_confirmation' => 'brand-new-pass'])->assertUnprocessable();
    }

    public function test_an_expired_code_is_rejected(): void
    {
        $user = User::factory()->create();
        $code = $this->requestCode($user);

        $this->travel(16)->minutes();

        $this->postJson('/api/v1/auth/reset-password', ['email' => $user->email, 'code' => $code, 'password' => 'brand-new-pass', 'password_confirmation' => 'brand-new-pass'])
            ->assertUnprocessable();
    }

    public function test_the_new_password_must_be_confirmed_and_long_enough(): void
    {
        $user = User::factory()->create();
        $code = $this->requestCode($user);

        $this->postJson('/api/v1/auth/reset-password', ['email' => $user->email, 'code' => $code, 'password' => 'short', 'password_confirmation' => 'different'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }
}
