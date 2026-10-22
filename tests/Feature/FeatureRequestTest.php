<?php

namespace Tests\Feature;

use App\Enums\FeatureRequestStatus;
use App\Models\FeatureRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FeatureRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_a_feature_request(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/feature-requests', [
            'title' => 'Dark mode',
            'description' => 'Please add a dark theme.',
        ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'Dark mode')
            ->assertJsonPath('data.status', 'open')
            ->assertJsonPath('data.submitted_by', $user->name)
            ->assertJsonPath('data.is_mine', true);

        $this->assertDatabaseHas('feature_requests', [
            'user_id' => $user->id,
            'title' => 'Dark mode',
            'status' => 'open',
        ]);
    }

    public function test_title_and_description_are_required(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/feature-requests', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'description']);
    }

    public function test_index_lists_every_users_requests_newest_first(): void
    {
        $author = User::factory()->create();
        $viewer = User::factory()->create();

        $older = FeatureRequest::factory()->for($author)->create(['title' => 'Older request']);
        $newer = FeatureRequest::factory()->for($author)->create(['title' => 'Newer request']);

        Sanctum::actingAs($viewer);
        $response = $this->getJson('/api/v1/feature-requests')->assertOk();
        $response->assertJsonPath('data.0.id', $newer->id);
        $response->assertJsonPath('data.1.id', $older->id);
        $response->assertJsonPath('data.1.is_mine', false);
    }

    public function test_index_can_search_by_title_or_description(): void
    {
        $user = User::factory()->create();
        FeatureRequest::factory()->for($user)->create(['title' => 'Dark mode toggle', 'description' => 'Nice to have.']);
        FeatureRequest::factory()->for($user)->create(['title' => 'Multi-currency support', 'description' => 'Would help travelers.']);

        Sanctum::actingAs($user);
        $this->getJson('/api/v1/feature-requests?search=dark')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Dark mode toggle');
    }

    public function test_status_is_included_for_in_progress_and_completed_requests(): void
    {
        $user = User::factory()->create();
        FeatureRequest::factory()->for($user)->create(['status' => FeatureRequestStatus::InProgress]);
        FeatureRequest::factory()->for($user)->create(['status' => FeatureRequestStatus::Completed]);

        Sanctum::actingAs($user);
        $response = $this->getJson('/api/v1/feature-requests')->assertOk();
        $statuses = collect($response->json('data'))->pluck('status')->sort()->values();
        $this->assertSame(['completed', 'in_progress'], $statuses->all());
    }

    public function test_new_request_starts_with_zero_votes(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/feature-requests', ['title' => 'Dark mode', 'description' => 'Please.'])
            ->assertCreated()
            ->assertJsonPath('data.upvotes_count', 0)
            ->assertJsonPath('data.downvotes_count', 0)
            ->assertJsonPath('data.my_vote', null);
    }

    public function test_user_can_upvote_a_request(): void
    {
        $author = User::factory()->create();
        $voter = User::factory()->create();
        $featureRequest = FeatureRequest::factory()->for($author)->create();

        Sanctum::actingAs($voter);
        $this->postJson("/api/v1/feature-requests/{$featureRequest->id}/vote", ['direction' => 'up'])
            ->assertOk()
            ->assertJsonPath('data.upvotes_count', 1)
            ->assertJsonPath('data.downvotes_count', 0)
            ->assertJsonPath('data.my_vote', 'up');

        $this->assertDatabaseHas('feature_request_votes', [
            'feature_request_id' => $featureRequest->id,
            'user_id' => $voter->id,
            'direction' => 'up',
        ]);
    }

    public function test_voting_the_same_direction_again_removes_the_vote(): void
    {
        $author = User::factory()->create();
        $voter = User::factory()->create();
        $featureRequest = FeatureRequest::factory()->for($author)->create();

        Sanctum::actingAs($voter);
        $this->postJson("/api/v1/feature-requests/{$featureRequest->id}/vote", ['direction' => 'up'])->assertOk();
        $this->postJson("/api/v1/feature-requests/{$featureRequest->id}/vote", ['direction' => 'up'])
            ->assertOk()
            ->assertJsonPath('data.upvotes_count', 0)
            ->assertJsonPath('data.my_vote', null);

        $this->assertDatabaseCount('feature_request_votes', 0);
    }

    public function test_voting_the_other_direction_switches_the_vote(): void
    {
        $author = User::factory()->create();
        $voter = User::factory()->create();
        $featureRequest = FeatureRequest::factory()->for($author)->create();

        Sanctum::actingAs($voter);
        $this->postJson("/api/v1/feature-requests/{$featureRequest->id}/vote", ['direction' => 'up'])->assertOk();
        $this->postJson("/api/v1/feature-requests/{$featureRequest->id}/vote", ['direction' => 'down'])
            ->assertOk()
            ->assertJsonPath('data.upvotes_count', 0)
            ->assertJsonPath('data.downvotes_count', 1)
            ->assertJsonPath('data.my_vote', 'down');

        $this->assertDatabaseCount('feature_request_votes', 1);
    }

    public function test_vote_counts_are_shared_across_viewers_but_my_vote_is_per_user(): void
    {
        $author = User::factory()->create();
        $voter = User::factory()->create();
        $otherViewer = User::factory()->create();
        $featureRequest = FeatureRequest::factory()->for($author)->create();

        Sanctum::actingAs($voter);
        $this->postJson("/api/v1/feature-requests/{$featureRequest->id}/vote", ['direction' => 'up'])->assertOk();

        Sanctum::actingAs($otherViewer);
        $this->getJson('/api/v1/feature-requests')
            ->assertOk()
            ->assertJsonPath('data.0.upvotes_count', 1)
            ->assertJsonPath('data.0.my_vote', null);
    }
}
