<?php

namespace Tests\Feature;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminAlertsTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<int, array<string, mixed>> */
    private function alerts(User $user): array
    {
        return $user->fresh()->notifications->map(fn ($n) => $n->data)->where('type', 'admin_alert')->values()->all();
    }

    public function test_a_new_report_alerts_every_admin_but_not_regular_users(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $otherAdmin = User::factory()->create(['is_admin' => true]);
        $reporter = User::factory()->create();
        $reported = User::factory()->create();
        Sanctum::actingAs($reporter);

        $this->postJson("/api/v1/users/{$reported->id}/reports", ['reason' => 'scam_or_fraud'])->assertCreated();

        foreach ([$admin, $otherAdmin] as $a) {
            $alerts = $this->alerts($a);
            $this->assertCount(1, $alerts);
            $this->assertSame('New user report', $alerts[0]['title']);
            $this->assertStringContainsString($reported->name, $alerts[0]['body']);
        }
        $this->assertSame([], $this->alerts($reporter));
    }

    public function test_switching_the_alert_off_in_settings_silences_it(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Setting::put('admin_alert_new_report', false);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/users/'.User::factory()->create()->id.'/reports', ['reason' => 'other'])->assertCreated();

        $this->assertSame([], $this->alerts($admin));
    }

    public function test_submitting_an_id_document_alerts_admins(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/id-verification', [
            'document_type' => 'cnic',
            'photo' => UploadedFile::fake()->image('id.jpg'),
        ])->assertCreated();

        $alerts = $this->alerts($admin);
        $this->assertCount(1, $alerts);
        $this->assertSame('New ID verification', $alerts[0]['title']);
    }

    public function test_a_new_feature_request_alerts_admins(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/feature-requests', ['title' => 'Offline mode', 'description' => 'Please.'])->assertCreated();

        $alerts = $this->alerts($admin);
        $this->assertCount(1, $alerts);
        $this->assertStringContainsString('Offline mode', $alerts[0]['body']);
    }

    public function test_deciding_a_report_tells_the_reporter(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $reporter = User::factory()->create();
        $report = Report::factory()->create(['reporter_id' => $reporter->id]);
        $this->actingAs($admin);

        $this->patch("/admin/reports/{$report->id}", ['decision' => 'reviewed'])->assertRedirect();

        $this->assertSame(ReportStatus::Reviewed, $report->fresh()->status);
        $notification = $reporter->fresh()->notifications->first();
        $this->assertSame('report_resolved', $notification->data['type']);
        $this->assertStringContainsString('took action', $notification->data['body']);
    }

    public function test_a_dismissed_report_uses_different_wording_and_is_only_announced_once(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $reporter = User::factory()->create();
        $report = Report::factory()->create(['reporter_id' => $reporter->id]);
        $this->actingAs($admin);

        $this->patch("/admin/reports/{$report->id}", ['decision' => 'dismissed']);
        $this->patch("/admin/reports/{$report->id}", ['decision' => 'reviewed']);

        $notifications = $reporter->fresh()->notifications;
        $this->assertCount(1, $notifications);
        $this->assertStringContainsString("didn't find", $notifications->first()->data['body']);
    }
}
