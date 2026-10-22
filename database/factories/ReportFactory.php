<?php

namespace Database\Factories;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reporter_id' => User::factory(),
            'reported_user_id' => User::factory(),
            'booking_id' => null,
            'reason' => $this->faker->randomElement(['no_show', 'inappropriate_item', 'scam_or_fraud', 'unsafe_behavior', 'other']),
            'details' => $this->faker->sentence(),
            'status' => ReportStatus::Pending,
        ];
    }

    public function reviewed(): static
    {
        return $this->state(fn () => ['status' => ReportStatus::Reviewed, 'reviewed_at' => now()]);
    }

    public function dismissed(): static
    {
        return $this->state(fn () => ['status' => ReportStatus::Dismissed, 'reviewed_at' => now()]);
    }
}
