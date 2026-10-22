<?php

namespace Tests\Feature;

use App\Models\Good;
use App\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_feed_defaults_to_travelers(): void
    {
        Trip::factory()->count(2)->create();
        Good::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/feed');

        $response->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_feed_can_toggle_to_goods(): void
    {
        Trip::factory()->count(2)->create();
        Good::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/feed?type=goods');

        $response->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_feed_rejects_invalid_type(): void
    {
        $this->getJson('/api/v1/feed?type=bogus')->assertUnprocessable();
    }
}
