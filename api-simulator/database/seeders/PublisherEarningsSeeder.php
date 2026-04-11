<?php

namespace Database\Seeders;

use App\Models\PublisherEarning;
use App\Models\User;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PublisherEarningsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting Publisher Earnings Seeder...');

        $publishers = $this->ensurePublishers();

        PublisherEarning::whereIn('publisher_id', $publishers->pluck('id'))
            ->where('site_id', 0)
            ->delete();

        foreach ($publishers as $publisherIndex => $publisher) {
            $months = collect(range(0, 5))->map(fn ($offset) => Carbon::now()->subMonths(5 - $offset)->startOfMonth());

            foreach ($months as $monthIndex => $month) {
                $daysToCreate = 6 + (($publisherIndex + $monthIndex) % 4);

                for ($dayIndex = 0; $dayIndex < $daysToCreate; $dayIndex++) {
                    $date = $month->copy()->day(min(2 + ($dayIndex * 4), $month->daysInMonth));
                    $earnings = 35 + ($publisherIndex * 12) + ($monthIndex * 9) + ($dayIndex * 4.5);

                    PublisherEarning::create([
                        'date' => $date->toDateString(),
                        'site_id' => 0,
                        'publisher_id' => $publisher->id,
                        'impressions' => 1500 + ($dayIndex * 230),
                        'clicks' => 55 + ($dayIndex * 6),
                        'conversions' => 4 + ($dayIndex % 3),
                        'revenue' => round($earnings * 1.35, 4),
                        'publisher_earnings' => round($earnings, 4),
                        'ecpm' => 2.75,
                        'ctr' => 1.85,
                        'fill_rate' => 91.50,
                        'created_at' => $date->copy()->setTime(12, 0),
                    ]);
                }
            }
        }

        $this->command->info('Publisher Earnings Seeder completed successfully.');
    }

    protected function ensurePublishers()
    {
        $seedPublishers = collect([
            ['email' => 'earnings.publisher1@adshqip.com', 'first_name' => 'Mira', 'last_name' => 'Hoxha'],
            ['email' => 'earnings.publisher2@adshqip.com', 'first_name' => 'Dion', 'last_name' => 'Kelmendi'],
        ]);

        return $seedPublishers->map(function ($publisherData) {
            $user = User::updateOrCreate(
                ['email' => $publisherData['email']],
                [
                    'password_hash' => Hash::make('password123'),
                    'role' => 'publisher',
                    'status' => 'active',
                    'preferred_language' => 'en',
                    'theme_preference' => 'system',
                    'timezone' => 'Europe/Tirane',
                    'is_deleted' => false,
                ]
            );

            UserProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => $publisherData['first_name'],
                    'last_name' => $publisherData['last_name'],
                    'currency' => 'EUR',
                    'balance' => 0,
                ]
            );

            return $user;
        });
    }
}
