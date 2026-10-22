<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PruneExpiredTripsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_expired_trips_that_were_never_booked(): void
    {
        $expired = Trip::factory()->create(['departure_date_time' => now()->subDay()]);

        $this->artisan('app:prune-expired-trips')->assertSuccessful();

        $this->assertDatabaseMissing('trips', ['id' => $expired->id]);
    }

    public function test_it_keeps_expired_trips_that_have_a_booking(): void
    {
        $booking = Booking::factory()->create();
        $booking->trip->update(['departure_date_time' => now()->subDay()]);

        $this->artisan('app:prune-expired-trips')->assertSuccessful();

        $this->assertDatabaseHas('trips', ['id' => $booking->trip_id]);
    }

    public function test_it_keeps_trips_that_have_not_departed_yet(): void
    {
        $upcoming = Trip::factory()->create(['departure_date_time' => now()->addWeek()]);

        $this->artisan('app:prune-expired-trips')->assertSuccessful();

        $this->assertDatabaseHas('trips', ['id' => $upcoming->id]);
    }
}
