<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_report_another_user(): void
    {
        $reporter = User::factory()->create();
        $reported = User::factory()->create();

        Sanctum::actingAs($reporter);
        $this->postJson("/api/v1/users/{$reported->id}/reports", [
            'reason' => 'scam_or_fraud',
            'details' => 'Never showed up to hand over the item.',
        ])->assertCreated();

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $reporter->id,
            'reported_user_id' => $reported->id,
            'reason' => 'scam_or_fraud',
        ]);
    }

    public function test_user_cannot_report_themself(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $this->postJson("/api/v1/users/{$user->id}/reports", ['reason' => 'other'])
            ->assertUnprocessable();
    }

    public function test_reason_must_be_a_known_value(): void
    {
        $reporter = User::factory()->create();
        $reported = User::factory()->create();

        Sanctum::actingAs($reporter);
        $this->postJson("/api/v1/users/{$reported->id}/reports", ['reason' => 'made_up_reason'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['reason']);
    }
}
