<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'email' => 'admin@adshqip.com',
                'password_hash' => Hash::make('password123'),
                'role' => 'admin',
                'status' => 'active',
                'preferred_language' => 'en',
                'theme_preference' => 'system',
                'timezone' => 'Europe/Tirane',
            ],
            [
                'email' => 'advertiser@adshqip.com',
                'password_hash' => Hash::make('password123'),
                'role' => 'advertiser',
                'status' => 'active',
                'preferred_language' => 'en',
                'theme_preference' => 'system',
                'timezone' => 'Europe/Tirane',
            ],
            [
                'email' => 'publisher@adshqip.com',
                'password_hash' => Hash::make('password123'),
                'role' => 'publisher',
                'status' => 'active',
                'preferred_language' => 'sq',
                'theme_preference' => 'dark',
                'timezone' => 'Europe/Tirane',
            ],
            [
                'email' => 'manager@adshqip.com',
                'password_hash' => Hash::make('password123'),
                'role' => 'manager',
                'status' => 'active',
                'preferred_language' => 'en',
                'theme_preference' => 'light',
                'timezone' => 'Europe/Tirane',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
