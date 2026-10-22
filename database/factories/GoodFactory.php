<?php

namespace Database\Factories;

use App\Enums\PickupMethod;
use App\Models\Good;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Good>
 */
class GoodFactory extends Factory
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
            'item_description' => fake()->sentence(6),
            'receiver_email' => fake()->safeEmail(),
            'weight_kg' => fake()->randomFloat(2, 0.5, 20),
            'offered_reward' => fake()->randomFloat(2, 5, 200),
            'currency_type' => fake()->randomElement(['USD', 'PKR', 'SAR', 'BRL', 'GBP']),
            'pickup_preference' => fake()->randomElement(PickupMethod::cases()),
            'terms_accepted_at' => now(),
        ];
    }
}
