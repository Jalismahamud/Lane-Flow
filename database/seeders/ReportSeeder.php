<?php
// database/seeders/ReportSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Report;
use App\Models\User;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please seed users first.');
            return;
        }

        $types = [
            Report::TYPE_BLOCKED_LANE,
            Report::TYPE_EMERGENCY_CLEAR,
            Report::TYPE_TEMP_SHIFT
        ];

        foreach (range(1, 50) as $index) {
            Report::create([
                'user_id' => $users->random()->id,
                'latitude' => fake()->latitude(23.7, 23.9),
                'longitude' => fake()->longitude(90.3, 90.5),
                'type' => fake()->randomElement($types),
                'lane' => fake()->randomElement(['Left Lane', 'Middle Lane', 'Right Lane']),
                'description' => fake()->sentence(),
                'status' => Report::STATUS_BLOCKED,
                'expires_at' => now()->addMinutes(rand(30, 180)),
                'blocked_report_count' => rand(1, 10),
                'clear_report_count' => rand(0, 4),
            ]);
        }

        $this->command->info('Reports seeded successfully!');
    }
}
