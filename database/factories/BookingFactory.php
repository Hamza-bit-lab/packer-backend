<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Enums\Payer;
use App\Models\Booking;
use App\Models\Good;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trip_id' => Trip::factory(),
            'good_id' => Good::factory(),
            'sender_id' => User::factory(),
            'traveler_id' => User::factory(),
            'status' => BookingStatus::Pending,
            'estimated_cost' => fake()->randomFloat(2, 5, 500),
            'payer' => Payer::Sender,
            'inspection_confirmed_at' => null,
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => ['status' => BookingStatus::Accepted]);
    }

    public function inTransit(): static
    {
        return $this->state(fn (array $attributes) => ['status' => BookingStatus::InTransit]);
    }

    public function arrived(): static
    {
        return $this->state(fn (array $attributes) => ['status' => BookingStatus::ArrivedAtDestination]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::Completed,
            'inspection_confirmed_at' => now(),
        ]);
    }
}
