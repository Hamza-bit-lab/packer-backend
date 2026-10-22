<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\FeatureRequest;
use App\Models\Good;
use App\Models\Setting;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardPagesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return tap(User::factory()->create(['is_admin' => true]), fn ($u) => $this->actingAs($u));
    }

    public function test_non_admins_cannot_open_the_new_pages(): void
    {
        $this->actingAs(User::factory()->create());

        foreach (['/admin/feature-requests', '/admin/listings', '/admin/settings'] as $url) {
            $this->get($url)->assertForbidden();
        }
    }

    public function test_the_feature_request_board_lists_votes_and_filters_by_status(): void
    {
        $this->admin();
        $open = FeatureRequest::factory()->create(['title' => 'Alpha idea']);
        FeatureRequest::factory()->create(['title' => 'Beta idea', 'status' => 'completed']);
        $open->votes()->create(['user_id' => User::factory()->create()->id, 'direction' => 'up']);

        $this->get('/admin/feature-requests')->assertOk()->assertSee('Alpha idea')->assertSee('Beta idea')->assertSee('▲ 1');
        $this->get('/admin/feature-requests?status=open')->assertOk()->assertSee('Alpha idea')->assertDontSee('Beta idea');
        $this->get('/admin/feature-requests?search=Beta')->assertOk()->assertSee('Beta idea')->assertDontSee('Alpha idea');
    }

    public function test_an_admin_can_delete_a_feature_request(): void
    {
        $this->admin();
        $request = FeatureRequest::factory()->create();

        $this->delete("/admin/feature-requests/{$request->id}")->assertRedirect();

        $this->assertDatabaseMissing('feature_requests', ['id' => $request->id]);
    }

    public function test_the_listings_page_shows_trips_and_items(): void
    {
        $this->admin();
        Trip::factory()->create(['from_city' => 'Karachi', 'to_city' => 'Jeddah']);
        Good::factory()->create(['from_city' => 'Multan', 'to_city' => 'Doha', 'item_description' => 'Wedding gifts']);

        $this->get('/admin/listings')->assertOk()->assertSee('Karachi → Jeddah');
        $this->get('/admin/listings?type=goods')->assertOk()->assertSee('Multan → Doha')->assertSee('Wedding gifts');
    }

    public function test_removing_a_trip_notifies_the_owner_and_the_sender_with_a_live_booking(): void
    {
        $admin = $this->admin();
        $traveler = User::factory()->create();
        $sender = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $traveler->id]);
        Booking::factory()->accepted()->create(['trip_id' => $trip->id, 'traveler_id' => $traveler->id, 'sender_id' => $sender->id]);

        $this->delete("/admin/listings/trips/{$trip->id}")->assertRedirect();

        $this->assertDatabaseMissing('trips', ['id' => $trip->id]);
        $this->assertStringContainsString('moderation', $traveler->fresh()->notifications->first()->data['body']);
        $this->assertStringContainsString('moderation', $sender->fresh()->notifications->first()->data['body']);
        $this->assertSame([], $admin->fresh()->notifications->all());
    }

    public function test_removing_an_item_works_from_the_dashboard(): void
    {
        $this->admin();
        $good = Good::factory()->create();

        $this->delete("/admin/listings/goods/{$good->id}")->assertRedirect();

        $this->assertDatabaseMissing('goods', ['id' => $good->id]);
    }

    public function test_settings_default_to_on_and_can_be_switched_off_and_on(): void
    {
        $this->admin();

        $this->get('/admin/settings')->assertOk()->assertSee('Admin alerts');
        $this->assertTrue(Setting::enabled('admin_alert_new_report'));

        $this->put('/admin/settings', ['admin_alert_new_id_verification' => '1'])->assertRedirect();

        $this->assertFalse(Setting::enabled('admin_alert_new_report'));
        $this->assertTrue(Setting::enabled('admin_alert_new_id_verification'));
        $this->assertFalse(Setting::enabled('admin_alert_email'));

        $this->put('/admin/settings', array_fill_keys(array_keys(Setting::DEFAULTS), '1'));
        $this->assertTrue(Setting::enabled('admin_alert_new_report'));
    }

    public function test_an_admin_can_grant_and_remove_admin_access_but_not_their_own(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();

        $this->patch("/admin/users/{$user->id}/admin")->assertRedirect();
        $this->assertTrue($user->fresh()->is_admin);

        $this->patch("/admin/users/{$user->id}/admin")->assertRedirect();
        $this->assertFalse($user->fresh()->is_admin);

        $this->patch("/admin/users/{$admin->id}/admin")->assertSessionHasErrors('user');
        $this->assertTrue($admin->fresh()->is_admin);
    }

    public function test_the_dashboard_shows_open_feature_requests(): void
    {
        $this->admin();
        FeatureRequest::factory()->count(2)->create();

        $this->get('/admin')->assertOk()->assertSee('Open Feature Requests');
    }
}
