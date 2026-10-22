<?php

namespace Database\Factories;

use App\Enums\DeliveryMethod;
use App\Enums\PickupMethod;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trip>
 */
class TripFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'from_city' => fake()->city(),
            'to_city' => fake()->city(),
            'departure_date_time' => fake()->dateTimeBetween('+1 day', '+3 months'),
            'airline_name' => fake()->company().' Airlines',
            'max_weight_kg' => fake()->randomFloat(2, 5, 30),
            'rate_per_kg' => fake()->randomFloat(2, 5, 50),
            'currency_type' => fake()->randomElement(['USD', 'PKR', 'SAR', 'BRL', 'GBP']),
            'delivery_method' => fake()->randomElement(DeliveryMethod::cases()),
            'pickup_method' => fake()->randomElement(PickupMethod::cases()),
            'terms_accepted_at' => now(),
        ];
    }
}
