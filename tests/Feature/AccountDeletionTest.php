<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Good;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_delete_their_account_with_their_password(): void
    {
        $user = User::factory()->create(['password' => 'secret-pass-1']);
        Trip::factory()->create(['user_id' => $user->id]);
        Good::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->deleteJson('/api/v1/me', ['password' => 'secret-pass-1'])->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('trips', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('goods', ['user_id' => $user->id]);
    }

    public function test_a_wrong_password_does_not_delete_anything(): void
    {
        $user = User::factory()->create(['password' => 'secret-pass-1']);
        Sanctum::actingAs($user);

        $this->deleteJson('/api/v1/me', ['password' => 'nope'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_the_password_is_required(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson('/api/v1/me')->assertUnprocessable()->assertJsonValidationErrors('password');
    }

    public function test_admin_accounts_cannot_be_deleted_from_the_app(): void
    {
        $admin = User::factory()->create(['password' => 'secret-pass-1', 'is_admin' => true]);
        Sanctum::actingAs($admin);

        $this->deleteJson('/api/v1/me', ['password' => 'secret-pass-1'])->assertUnprocessable();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_the_other_party_of_a_live_booking_is_told_when_an_account_is_deleted(): void
    {
        $leaving = User::factory()->create(['password' => 'secret-pass-1', 'name' => 'Sam Leaver']);
        $staying = User::factory()->create();
        Booking::factory()->accepted()->create(['traveler_id' => $leaving->id, 'sender_id' => $staying->id]);
        Booking::factory()->create(['traveler_id' => $leaving->id, 'sender_id' => $staying->id, 'status' => BookingStatus::Completed]);
        Sanctum::actingAs($leaving);

        $this->deleteJson('/api/v1/me', ['password' => 'secret-pass-1'])->assertOk();

        $notifications = $staying->fresh()->notifications;
        $this->assertCount(1, $notifications);
        $this->assertSame('listing_removed', $notifications->first()->data['type']);
        $this->assertStringContainsString('Sam Leaver', $notifications->first()->data['body']);
    }

    public function test_deleting_an_account_removes_its_tokens_notifications_and_device_tokens(): void
    {
        $user = User::factory()->create(['password' => 'secret-pass-1']);
        $user->createToken('mobile');
        $user->deviceTokens()->create(['token' => 'abc', 'platform' => 'android']);
        $other = User::factory()->create();
        $other->notify(new \App\Notifications\ListingRemoved('t', 'b'));
        $user->notify(new \App\Notifications\ListingRemoved('t', 'b'));
        Sanctum::actingAs($user);

        $this->deleteJson('/api/v1/me', ['password' => 'secret-pass-1'])->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertDatabaseMissing('device_tokens', ['token' => 'abc']);
        $this->assertSame(1, \DB::table('notifications')->count());
    }

    public function test_deleting_an_account_removes_their_uploaded_photos(): void
    {
        Storage::fake('uploads');
        Storage::disk('uploads')->put('profile-pictures/me.jpg', 'x');
        Storage::disk('uploads')->put('trip-photos/t.jpg', 'x');
        Storage::disk('uploads')->put('trip-photos/other.jpg', 'x');
        $user = User::factory()->create(['password' => 'secret-pass-1', 'profile_pic_url' => 'http://x.test/uploads/profile-pictures/me.jpg']);
        Trip::factory()->create(['user_id' => $user->id, 'photo_url' => 'http://x.test/uploads/trip-photos/t.jpg']);
        Trip::factory()->create(['photo_url' => 'http://x.test/uploads/trip-photos/other.jpg']);
        Sanctum::actingAs($user);

        $this->deleteJson('/api/v1/me', ['password' => 'secret-pass-1'])->assertOk();

        Storage::disk('uploads')->assertMissing('profile-pictures/me.jpg');
        Storage::disk('uploads')->assertMissing('trip-photos/t.jpg');
        Storage::disk('uploads')->assertExists('trip-photos/other.jpg');
    }
}
