<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\StatDaily;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PublisherPaymentHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting Publisher Payment History Seeder...');

        // Get all publishers or create sample ones if none exist
        $publishers = User::where('role', 'publisher')->get();

        if ($publishers->isEmpty()) {
            $this->command->warn('No publishers found. Creating sample publishers...');
            $publishers = $this->createSamplePublishers();
        }

        $this->command->info("Found/Created {$publishers->count()} publishers.");

        // Generate earnings for the last 6 months
        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        foreach ($publishers as $publisher) {
            $this->command->info("Generating earnings for publisher: {$publisher->email}");

            // Get or create user profile
            $profile = UserProfile::firstOrCreate(
                ['user_id' => $publisher->id],
                [
                    'first_name' => $this->generateFirstName(),
                    'last_name' => $this->generateLastName(),
                    'currency' => 'EUR',
                    'balance' => 0,
                    'payment_method' => ['paypal', 'wire_transfer', 'payoneer'][rand(0, 2)],
                ]
            );

            $currentMonth = $startDate->copy();
            $totalEarnings = 0;

            while ($currentMonth->lte($endDate)) {
                // Publishers earn revenue from ads displayed on their sites
                // Determine earning pattern (consistent, irregular, or sporadic)
                $earningPattern = rand(1, 3);
                // 1 = consistent (20-25 days), 2 = irregular (10-20 days), 3 = sporadic (5-15 days)

                $daysInMonth = $currentMonth->daysInMonth;

                if ($earningPattern === 1) {
                    $numDays = rand(20, min(25, $daysInMonth));
                } elseif ($earningPattern === 2) {
                    $numDays = rand(10, min(20, $daysInMonth));
                } else {
                    $numDays = rand(5, min(15, $daysInMonth));
                }

                $monthEarnings = 0;

                for ($i = 0; $i < $numDays; $i++) {
                    $day = rand(1, $daysInMonth);
                    $date = $currentMonth->copy()->day($day)->hour(rand(0, 23))->minute(rand(0, 59));

                    // Random daily earnings based on publisher tier
                    // Small publishers: €5-€50, Medium: €50-€150, Large: €150-€400
                    $tier = rand(1, 3);
                    if ($tier === 1) {
                        $dailyEarnings = rand(5, 50);
                    } elseif ($tier === 2) {
                        $dailyEarnings = rand(50, 150);
                    } else {
                        $dailyEarnings = rand(150, 400);
                    }

                    // Create stat record for publisher
                    StatDaily::updateOrCreate(
                        [
                            'date' => $date->format('Y-m-d'),
                            'publisher_id' => $publisher->id,
                            'site_id' => null, // Generic earnings not tied to specific site
                        ],
                        [
                            'revenue' => $dailyEarnings,
                            'impressions' => rand(1000, 20000),
                            'clicks' => rand(50, 1000),
                            'conversions' => rand(1, 50),
                            'ecpm' => number_format(($dailyEarnings / rand(1000, 20000)) * 1000, 2),
                            'ctr' => number_format((rand(50, 1000) / rand(1000, 20000)) * 100, 2),
                            'fill_rate' => rand(70, 100),
                        ]
                    );

                    $monthEarnings += $dailyEarnings;
                }

                $totalEarnings += $monthEarnings;
                $this->command->info("  - {$currentMonth->format('F Y')}: €" . number_format($monthEarnings, 2) . " ({$numDays} days)");

                // Move to next month
                $currentMonth->addMonth();
            }

            $this->command->info("  ✓ Total earnings: €" . number_format($totalEarnings, 2));
        }

        $this->command->info('');
        $this->command->info('✓ Publisher Payment History Seeder completed successfully!');
        $this->command->info('Generated publisher earnings for the last 6 months.');
    }

    /**
     * Create sample publisher users
     */
    private function createSamplePublishers()
    {
        $this->command->info('Creating 5 sample publishers...');

        $publishers = collect();
        $publisherEmails = [
            'publisher1@adshqip.com',
            'publisher2@adshqip.com',
            'publisher3@adshqip.com',
            'publisher4@adshqip.com',
            'publisher5@adshqip.com',
        ];

        foreach ($publisherEmails as $email) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'password_hash' => Hash::make('password'),
                    'role' => 'publisher',
                    'status' => 'active',
                    'preferred_language' => 'en',
                    'timezone' => 'Europe/Tirane',
                    'two_factor_enabled' => false,
                    'last_login_at' => Carbon::now()->subDays(rand(1, 30)),
                ]
            );

            // Create user profile
            UserProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => $this->generateFirstName(),
                    'last_name' => $this->generateLastName(),
                    'currency' => 'EUR',
                    'balance' => rand(100, 5000),
                    'payment_method' => ['paypal', 'wire_transfer', 'payoneer'][rand(0, 2)],
                ]
            );

            $publishers->push($user);
            $this->command->info("  ✓ Created publisher: {$email}");
        }

        return $publishers;
    }

    /**
     * Generate random first names
     */
    private function generateFirstName()
    {
        $names = ['John', 'Jane', 'Michael', 'Sarah', 'David', 'Emily', 'Robert', 'Lisa', 'James', 'Maria',
                  'Alex', 'Emma', 'Daniel', 'Olivia', 'William', 'Sophia', 'Joseph', 'Isabella', 'Thomas', 'Mia'];
        return $names[array_rand($names)];
    }

    /**
     * Generate random last names
     */
    private function generateLastName()
    {
        $names = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez',
                  'Anderson', 'Taylor', 'Thomas', 'Moore', 'Jackson', 'White', 'Harris', 'Martin', 'Thompson', 'Wilson'];
        return $names[array_rand($names)];
    }
}
