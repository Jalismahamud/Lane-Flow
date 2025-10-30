<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Fixed admin + test user
        $fixedUsers = [
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'role' => 'admin',
                'password' => Hash::make('12345678'),
            ],
            [
                'name' => 'Demo User',
                'email' => 'user@gmail.com',
                'role' => 'user',
                'password' => Hash::make('12345678'),
            ],
        ];

        foreach ($fixedUsers as $user) {
            User::updateOrCreate(['email' => $user['email']], $user);
        }

        for ($i = 0; $i < 200; $i++) {
            $name = $faker->name;
            $createdAt = $faker->dateTimeBetween('-3 months', 'now');

            User::create([
                'name' => $name,
                'email' => $faker->unique()->safeEmail,
                'role' => 'user',
                'password' => Hash::make('12345678'),
                'avatar' => 'default/default-avatar.png',
                'is_otp_verified' => true,
                'created_at' => $createdAt,
                'updated_at' => now(),
            ]);
        }
    }
}
