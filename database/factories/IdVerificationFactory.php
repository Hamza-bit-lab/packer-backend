<?php

namespace Database\Factories;

use App\Enums\DocumentType;
use App\Enums\IdVerificationStatus;
use App\Models\IdVerification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IdVerification>
 */
class IdVerificationFactory extends Factory
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
            'document_type' => fake()->randomElement(DocumentType::cases()),
            'document_photo_url' => 'id-verification-photos/'.fake()->uuid().'.jpg',
            'status' => IdVerificationStatus::Pending,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => ['status' => IdVerificationStatus::Approved, 'reviewed_at' => now()]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IdVerificationStatus::Rejected,
            'rejection_reason' => 'Document photo was blurry.',
            'reviewed_at' => now(),
        ]);
    }
}
