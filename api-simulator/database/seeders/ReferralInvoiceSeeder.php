<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\ReferralPayout;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ReferralInvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting Referral Invoice Seeder...');

        // Get all users who could be referrers (any role)
        $referrers = User::all();

        if ($referrers->isEmpty()) {
            $this->command->warn('No users found. Creating sample referrers...');
            $referrers = $this->createSampleReferrers();
        }

        // Pick a subset of users as active referrers (3-5 users)
        $activeReferrers = $referrers->random(min(5, $referrers->count()));

        $this->command->info("Using {$activeReferrers->count()} users as active referrers.");

        // Generate referral payouts for the last 6 months
        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $paymentMethods = ['balance_credit', 'paypal', 'wire_transfer', 'crypto', 'payoneer'];
        $statuses = ['completed', 'pending', 'processing', 'failed', 'cancelled'];
        $statusWeights = [50, 25, 15, 5, 5];

        $totalPayouts = 0;

        foreach ($activeReferrers as $referrer) {
            // Ensure user profile exists
            UserProfile::firstOrCreate(
                ['user_id' => $referrer->id],
                [
                    'first_name' => $this->generateFirstName(),
                    'last_name' => $this->generateLastName(),
                    'currency' => 'EUR',
                    'balance' => rand(100, 5000),
                ]
            );

            $this->command->info("Generating referral invoices for: {$referrer->email}");

            $currentMonth = $startDate->copy();

            while ($currentMonth->lte($endDate)) {
                // 70% chance of having a payout each month
                if (rand(1, 100) <= 70) {
                    $periodStart = $currentMonth->copy()->startOfMonth();
                    $periodEnd = $currentMonth->copy()->endOfMonth();

                    // Random number of conversions (1-15)
                    $conversions = rand(1, 15);

                    // Random earning per referral (€5-€50 per conversion)
                    $earningPerReferral = rand(5, 50);
                    $totalAmount = $conversions * $earningPerReferral;

                    // Random status
                    $status = $this->weightedRandom($statuses, $statusWeights);

                    // Random payment method
                    $paymentMethod = $paymentMethods[array_rand($paymentMethods)];

                    // Invoice date (a few days after period end, or random in month)
                    $invoiceDate = $periodEnd->copy()->addDays(rand(1, 5));
                    if ($invoiceDate->isFuture()) {
                        $invoiceDate = Carbon::now()->subDays(rand(1, 5));
                    }

                    // Processed date if completed
                    $processedAt = null;
                    if ($status === 'completed') {
                        $processedAt = $invoiceDate->copy()->addDays(rand(1, 10));
                        if ($processedAt->isFuture()) {
                            $processedAt = Carbon::now();
                        }
                    }

                    // Payment reference for completed payments
                    $paymentReference = null;
                    if (in_array($status, ['completed', 'processing'])) {
                        $paymentReference = 'PAY-' . strtoupper(substr(md5(uniqid()), 0, 12));
                    }

                    ReferralPayout::create([
                        'referrer_id' => $referrer->id,
                        'amount' => $totalAmount,
                        'currency' => 'EUR',
                        'payment_method' => $paymentMethod,
                        'payment_reference' => $paymentReference,
                        'period_start' => $periodStart,
                        'period_end' => $periodEnd,
                        'conversions_count' => $conversions,
                        'status' => $status,
                        'processed_at' => $processedAt,
                        'notes' => $status === 'failed' ? 'Payment processing failed. Retry scheduled.' : null,
                    ]);

                    $totalPayouts++;
                    $this->command->info("  - {$periodStart->format('F Y')}: €" . number_format($totalAmount, 2) . " ({$conversions} referrals, {$status})");
                }

                // Move to next month
                $currentMonth->addMonth();
            }
        }

        // Summary
        $totalAmount = ReferralPayout::sum('amount');
        $completedAmount = ReferralPayout::where('status', 'completed')->sum('amount');
        $pendingCount = ReferralPayout::where('status', 'pending')->count();

        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('✓ Referral Invoice Seeder completed!');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info("Total Invoices:    {$totalPayouts}");
        $this->command->info("Total Amount:      €" . number_format($totalAmount, 2));
        $this->command->info("Completed Amount:  €" . number_format($completedAmount, 2));
        $this->command->info("Pending:           {$pendingCount}");
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }

    /**
     * Create sample referrer users.
     */
    private function createSampleReferrers()
    {
        $referrers = collect();
        $emails = [
            'referrer1@adshqip.com',
            'referrer2@adshqip.com',
            'referrer3@adshqip.com',
        ];

        foreach ($emails as $email) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'password_hash' => Hash::make('password'),
                    'role' => 'advertiser',
                    'status' => 'active',
                    'preferred_language' => 'en',
                    'timezone' => 'Europe/Tirane',
                    'two_factor_enabled' => false,
                    'last_login_at' => Carbon::now()->subDays(rand(1, 30)),
                ]
            );

            UserProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => $this->generateFirstName(),
                    'last_name' => $this->generateLastName(),
                    'currency' => 'EUR',
                    'balance' => rand(100, 5000),
                ]
            );

            $referrers->push($user);
        }

        return $referrers;
    }

    /**
     * Select a random value based on weights.
     */
    private function weightedRandom(array $values, array $weights)
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);

        $currentWeight = 0;
        foreach ($values as $index => $value) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) {
                return $value;
            }
        }

        return $values[0];
    }

    private function generateFirstName()
    {
        $names = ['John', 'Jane', 'Michael', 'Sarah', 'David', 'Emily', 'Robert', 'Lisa', 'James', 'Maria'];
        return $names[array_rand($names)];
    }

    private function generateLastName()
    {
        $names = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez'];
        return $names[array_rand($names)];
    }
}
