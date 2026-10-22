<?php

namespace Database\Seeders;

use App\Models\Good;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone_number' => '+10000000000',
        ]);

        $travelers = User::factory(5)->create();
        $senders = User::factory(5)->create();

        Trip::factory(10)->recycle($travelers)->create();
        Good::factory(10)->recycle($senders)->create();

        Trip::factory(2)->for($testUser)->create();
        Good::factory(2)->for($testUser)->create();
    }
}
