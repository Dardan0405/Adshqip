<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Transaction;
use App\Models\StatDaily;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting Payment History Seeder...');

        // Get all advertisers
        $advertisers = User::where('role', 'advertiser')->get();

        if ($advertisers->isEmpty()) {
            $this->command->warn('No advertisers found. Please seed users first.');
            return;
        }

        $this->command->info("Found {$advertisers->count()} advertisers.");

        // Common deposit amounts
        $depositAmounts = [100, 500, 1000, 2500, 5000];

        // Generate transactions for the last 6 months
        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        foreach ($advertisers as $advertiser) {
            $this->command->info("Generating transactions for advertiser: {$advertiser->email}");

            // Get or create user profile
            $profile = UserProfile::firstOrCreate(
                ['user_id' => $advertiser->id],
                [
                    'currency' => 'EUR',
                    'balance' => 0,
                ]
            );

            $currentBalance = $profile->balance ?? 0;

            // Determine deposit pattern (consistent, irregular, or none)
            $depositPattern = rand(1, 3);
            // 1 = consistent (every month), 2 = irregular (skip some months), 3 = rare (very few deposits)

            $currentMonth = $startDate->copy();

            while ($currentMonth->lte($endDate)) {
                // Decide if this advertiser deposits this month based on pattern
                $shouldDeposit = false;

                if ($depositPattern === 1) {
                    // Consistent depositor
                    $shouldDeposit = true;
                } elseif ($depositPattern === 2) {
                    // Irregular depositor (60% chance)
                    $shouldDeposit = rand(1, 100) <= 60;
                } else {
                    // Rare depositor (20% chance)
                    $shouldDeposit = rand(1, 100) <= 20;
                }

                if ($shouldDeposit) {
                    // Generate 1-3 deposits for this month
                    $numDeposits = rand(1, 3);

                    for ($i = 0; $i < $numDeposits; $i++) {
                        // Random day in the month
                        $depositDay = rand(1, $currentMonth->daysInMonth);
                        $depositDate = $currentMonth->copy()->day($depositDay)->hour(rand(9, 18))->minute(rand(0, 59));

                        // Random deposit amount
                        $amount = $depositAmounts[array_rand($depositAmounts)];

                        // Create transaction
                        $balanceBefore = $currentBalance;
                        $currentBalance += $amount;

                        Transaction::create([
                            'user_id' => $advertiser->id,
                            'type' => 'deposit',
                            'amount' => $amount,
                            'currency' => 'EUR',
                            'balance_before' => $balanceBefore,
                            'balance_after' => $currentBalance,
                            'payment_method_id' => null,
                            'payment_gateway' => ['stripe', 'paypal', 'manual'][rand(0, 2)],
                            'gateway_txn_id' => 'TXN_' . strtoupper(uniqid()),
                            'gateway_status' => 'confirmed',
                            'description' => 'Account deposit via ' . ['Stripe', 'PayPal', 'Bank Transfer'][rand(0, 2)],
                            'status' => 'completed',
                            'completed_at' => $depositDate,
                            'created_at' => $depositDate,
                            'updated_at' => $depositDate,
                        ]);

                        $this->command->info("  - Created deposit: €{$amount} on {$depositDate->format('Y-m-d')}");
                    }
                }

                // Generate some spend data for this month (if advertiser has campaigns)
                // Create 5-20 daily records with random spend
                if ($currentBalance > 0) {
                    $daysInMonth = $currentMonth->daysInMonth;
                    $numDays = rand(5, min(20, $daysInMonth));

                    for ($day = 1; $day <= $numDays; $day++) {
                        $date = $currentMonth->copy()->day($day);

                        // Random daily spend (€5 to €150)
                        $dailySpend = rand(5, 150);

                        // Only create spend if they have balance
                        if ($currentBalance >= $dailySpend) {
                            // Create stat record
                            StatDaily::updateOrCreate(
                                [
                                    'date' => $date->format('Y-m-d'),
                                    'advertiser_id' => $advertiser->id,
                                    'campaign_id' => null, // Generic spend not tied to specific campaign
                                ],
                                [
                                    'revenue' => $dailySpend,
                                    'impressions' => rand(1000, 5000),
                                    'clicks' => rand(50, 200),
                                    'conversions' => rand(0, 10),
                                    'ecpm' => rand(5, 20),
                                    'ctr' => rand(1, 5),
                                ]
                            );

                            $currentBalance -= $dailySpend;
                        }
                    }
                }

                // Move to next month
                $currentMonth->addMonth();
            }

            // Update the user profile balance
            $profile->update(['balance' => max(0, $currentBalance)]);
            $this->command->info("  Final balance: €{$currentBalance}");
        }

        // ══════════════════════════════════════════════════════════════
        // Generate Publisher Earnings
        // ══════════════════════════════════════════════════════════════
        $this->command->info('');
        $this->command->info('Generating publisher earnings data...');

        $publishers = User::where('role', 'publisher')->get();

        if ($publishers->isEmpty()) {
            $this->command->warn('No publishers found. Skipping publisher earnings.');
        } else {
            $this->command->info("Found {$publishers->count()} publishers.");

            foreach ($publishers as $publisher) {
                $this->command->info("Generating earnings for publisher: {$publisher->email}");

                $currentMonth = $startDate->copy();

                while ($currentMonth->lte($endDate)) {
                    // Publishers earn revenue from ads displayed on their sites
                    // Generate 10-25 daily records with random earnings
                    $daysInMonth = $currentMonth->daysInMonth;
                    $numDays = rand(10, min(25, $daysInMonth));

                    for ($day = 1; $day <= $numDays; $day++) {
                        $date = $currentMonth->copy()->day($day);

                        // Random daily earnings (€10 to €200)
                        $dailyEarnings = rand(10, 200);

                        // Create stat record for publisher
                        StatDaily::updateOrCreate(
                            [
                                'date' => $date->format('Y-m-d'),
                                'publisher_id' => $publisher->id,
                                'site_id' => null, // Generic earnings not tied to specific site
                            ],
                            [
                                'revenue' => $dailyEarnings,
                                'impressions' => rand(2000, 10000),
                                'clicks' => rand(100, 500),
                                'conversions' => rand(5, 30),
                                'ecpm' => rand(3, 15),
                                'ctr' => rand(2, 8),
                            ]
                        );
                    }

                    // Move to next month
                    $currentMonth->addMonth();
                }

                $this->command->info("  ✓ Generated earnings data for {$publisher->email}");
            }
        }

        $this->command->info('');
        $this->command->info('✓ Payment History Seeder completed successfully!');
        $this->command->info('Generated advertiser transactions and publisher earnings for the last 6 months.');
    }
}
