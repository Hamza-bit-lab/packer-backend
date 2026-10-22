<?php

namespace Tests\Feature;

use App\Enums\DocumentType;
use App\Enums\IdVerificationStatus;
use App\Enums\ReportStatus;
use App\Models\IdVerification;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_guest_is_redirected_to_login_when_visiting_the_dashboard(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_a_non_admin_cannot_log_into_the_admin_panel(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->post('/admin/login', ['email' => $user->email, 'password' => 'password123']);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_an_admin_can_log_in_and_view_the_dashboard(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'password' => bcrypt('password123')]);

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password123'])
            ->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin);
        $this->get('/admin')->assertOk()->assertSee('Dashboard');
    }

    public function test_an_admin_can_log_out(): void
    {
        $this->actingAs($this->admin());

        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_an_authenticated_non_admin_is_forbidden_from_admin_routes(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin')->assertForbidden();
    }

    public function test_admin_can_see_pending_reports_and_mark_one_reviewed(): void
    {
        $this->actingAs($this->admin());
        $report = Report::factory()->create();

        $this->get('/admin/reports')->assertOk()->assertSee($report->reportedUser->name);

        $this->patch("/admin/reports/{$report->id}", ['decision' => 'reviewed'])->assertRedirect();

        $this->assertSame(ReportStatus::Reviewed, $report->fresh()->status);
    }

    public function test_admin_can_approve_a_pending_id_verification(): void
    {
        Storage::fake('local');
        $this->actingAs($this->admin());

        $verification = IdVerification::factory()->create([
            'document_type' => DocumentType::Cnic,
            'status' => IdVerificationStatus::Pending,
            'document_photo_url' => UploadedFile::fake()->image('id.jpg')->store('id-verification-photos', 'local'),
        ]);

        $this->get('/admin/id-verifications')->assertOk()->assertSee($verification->user->name);
        $this->get(route('admin.id-verifications.photo', $verification))->assertOk();

        $this->patch("/admin/id-verifications/{$verification->id}", ['decision' => 'approve'])->assertRedirect();

        $verification->refresh();
        $this->assertSame(IdVerificationStatus::Approved, $verification->status);
        $this->assertNotNull($verification->reviewed_at);
    }

    public function test_admin_rejecting_an_id_verification_requires_a_reason(): void
    {
        $this->actingAs($this->admin());
        $verification = IdVerification::factory()->create(['status' => IdVerificationStatus::Pending]);

        $this->patch("/admin/id-verifications/{$verification->id}", ['decision' => 'reject'])
            ->assertSessionHasErrors('rejection_reason');

        $this->patch("/admin/id-verifications/{$verification->id}", [
            'decision' => 'reject',
            'rejection_reason' => 'Photo is blurry.',
        ])->assertRedirect();

        $verification->refresh();
        $this->assertSame(IdVerificationStatus::Rejected, $verification->status);
        $this->assertSame('Photo is blurry.', $verification->rejection_reason);
    }

    public function test_banning_a_user_via_the_panel_marks_them_banned_and_revokes_their_tokens(): void
    {
        $this->actingAs($this->admin());
        $user = User::factory()->create();
        $user->createToken('mobile');

        $this->patch("/admin/users/{$user->id}/ban")->assertRedirect();

        $this->assertTrue($user->fresh()->is_banned);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_a_banned_users_old_token_is_rejected_as_unauthenticated(): void
    {
        // Deliberately not using actingAs() here: within a single test
        // method it leaves the "web" guard resolved for the rest of the
        // test, and Sanctum's guard checks that guard before the bearer
        // token — which would make this assertion pass for the wrong
        // reason. Banning is done directly on the model instead, matching
        // exactly what the admin controller itself calls.
        $user = User::factory()->create();
        $token = $user->createToken('mobile')->plainTextToken;

        $user->ban();
        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v1/me')
            ->assertStatus(401);
    }

    public function test_a_banned_users_still_live_token_is_rejected_by_the_not_banned_middleware(): void
    {
        // Simulates the flag being set some other way than User::ban(),
        // leaving the token row in place — the not-banned middleware is
        // the defensive backstop for that case.
        $user = User::factory()->create(['is_banned' => true]);
        $token = $user->createToken('mobile')->plainTextToken;

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v1/me')
            ->assertStatus(403);
    }

    public function test_a_banned_user_cannot_log_in_via_the_api(): void
    {
        $user = User::factory()->create(['is_banned' => true, 'password' => bcrypt('password123')]);

        $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'password123'])
            ->assertStatus(401);
    }

    public function test_admin_cannot_ban_another_admin(): void
    {
        $this->actingAs($this->admin());
        $otherAdmin = User::factory()->create(['is_admin' => true]);

        $this->patch("/admin/users/{$otherAdmin->id}/ban")->assertSessionHasErrors('user');
        $this->assertFalse($otherAdmin->fresh()->is_banned);
    }

    public function test_admin_can_search_users_by_name_or_email(): void
    {
        $this->actingAs($this->admin());
        $match = User::factory()->create(['name' => 'Findable Traveler']);
        User::factory()->create(['name' => 'Someone Else']);

        $this->get('/admin/users?search=Findable')
            ->assertOk()
            ->assertSee($match->name)
            ->assertDontSee('Someone Else');
    }
}
