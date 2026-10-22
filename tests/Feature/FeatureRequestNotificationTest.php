<?php

namespace Tests\Feature;

use App\Enums\FeatureRequestStatus;
use App\Models\FeatureRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FeatureRequestNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<int, string> */
    private function types(User $user): array
    {
        return $user->fresh()->notifications->map(fn ($n) => $n->data['type'])->all();
    }

    public function test_changing_the_status_notifies_the_author(): void
    {
        $author = User::factory()->create();
        $request = FeatureRequest::factory()->create(['user_id' => $author->id, 'title' => 'Offline mode']);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $this->patch("/admin/feature-requests/{$request->id}", ['status' => 'in_progress'])->assertRedirect();

        $this->assertSame(FeatureRequestStatus::InProgress, $request->fresh()->status);
        $notification = $author->fresh()->notifications->first();
        $this->assertSame('feature_request_status', $notification->data['type']);
        $this->assertSame($request->id, $notification->data['feature_request_id']);
        $this->assertStringContainsString('Offline mode', $notification->data['body']);
    }

    public function test_setting_the_same_status_again_does_not_notify(): void
    {
        $author = User::factory()->create();
        $request = FeatureRequest::factory()->create(['user_id' => $author->id, 'status' => FeatureRequestStatus::Completed]);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $this->patch("/admin/feature-requests/{$request->id}", ['status' => 'completed'])->assertRedirect();

        $this->assertSame([], $this->types($author));
    }

    public function test_an_invalid_status_is_rejected(): void
    {
        $request = FeatureRequest::factory()->create();
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $this->patch("/admin/feature-requests/{$request->id}", ['status' => 'banana'])->assertSessionHasErrors('status');
    }

    public function test_the_author_is_told_when_an_idea_reaches_five_upvotes_and_only_then(): void
    {
        $author = User::factory()->create();
        $request = FeatureRequest::factory()->create(['user_id' => $author->id]);

        foreach (range(1, 4) as $ignored) {
            Sanctum::actingAs(User::factory()->create());
            $this->postJson("/api/v1/feature-requests/{$request->id}/vote", ['direction' => 'up'])->assertOk();
        }
        $this->assertSame([], $this->types($author));

        Sanctum::actingAs(User::factory()->create());
        $this->postJson("/api/v1/feature-requests/{$request->id}/vote", ['direction' => 'up'])->assertOk();
        $this->assertSame(['feature_request_votes'], $this->types($author));
        $this->assertStringContainsString('5 upvotes', $author->fresh()->notifications->first()->data['body']);

        Sanctum::actingAs(User::factory()->create());
        $this->postJson("/api/v1/feature-requests/{$request->id}/vote", ['direction' => 'up'])->assertOk();
        $this->assertCount(1, $author->fresh()->notifications);
    }

    public function test_downvotes_and_self_votes_never_trigger_a_milestone(): void
    {
        $author = User::factory()->create();
        $request = FeatureRequest::factory()->create(['user_id' => $author->id]);

        foreach (range(1, 5) as $ignored) {
            Sanctum::actingAs(User::factory()->create());
            $this->postJson("/api/v1/feature-requests/{$request->id}/vote", ['direction' => 'down'])->assertOk();
        }
        Sanctum::actingAs($author);
        $this->postJson("/api/v1/feature-requests/{$request->id}/vote", ['direction' => 'up'])->assertOk();

        $this->assertSame([], $this->types($author));
    }
}
