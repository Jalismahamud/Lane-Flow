<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'role' => 'admin',
                'password' => Hash::make('12345678'),
                'avatar' => null,
                'is_otp_verified' => true,
            ],
            [
                'name' => 'User',
                'email' => 'user@gmail.com',
                'role' => 'user',
                'password' => Hash::make('12345678'),
                'avatar' => null,
                'is_otp_verified' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'password' => $userData['password'],
                    'name' => $userData['name'],
                    'role' => $userData['role'],
                    'avatar' => $userData['avatar'],
                    'is_otp_verified' => $userData['is_otp_verified'],
                ]
            );
        }
    }
}
