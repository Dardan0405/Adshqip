<?php

namespace Database\Seeders;

use App\Models\ReferralConversion;
use App\Models\ReferralLink;
use App\Models\ReferralPayout;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PublisherReferralSeeder extends Seeder
{
    public function run(): void
    {
        if (! $this->tablesExist()) {
            $this->command?->warn('Referral tables are missing. Run migrations first.');
            return;
        }

        $publishers = User::query()
            ->where('role', 'publisher')
            ->where('is_deleted', false)
            ->take(2)
            ->get();

        if ($publishers->isEmpty()) {
            $publishers = collect([
                $this->createUser('publisher.referrals1@adshqip.com', 'publisher', 'Mira', 'Publisher'),
                $this->createUser('publisher.referrals2@adshqip.com', 'publisher', 'Aron', 'Publisher'),
            ]);
        }

        foreach ($publishers as $index => $publisher) {
            if (blank($publisher->referral_code)) {
                $publisher->update(['referral_code' => strtoupper(Str::random(8))]);
            }

            $link = ReferralLink::query()->updateOrCreate(
                [
                    'referrer_id' => $publisher->id,
                    'target_role' => 'advertiser',
                    'is_deleted' => false,
                ],
                [
                    'code' => $publisher->referral_code,
                    'campaign_name' => 'Publisher Advertiser Referral ' . ($index + 1),
                    'landing_url' => route('register', ['role' => 'advertiser', 'ref' => $publisher->referral_code]),
                    'commission_type' => 'percentage',
                    'commission_rate' => 5,
                    'commission_duration_days' => 365,
                    'status' => 'active',
                ]
            );

            $advertisers = collect([
                $this->createUser(
                    'advertiser.ref.' . $publisher->id . '.1@adshqip.com',
                    'advertiser',
                    'Lena',
                    'Advertiser',
                    $publisher->id
                ),
                $this->createUser(
                    'advertiser.ref.' . $publisher->id . '.2@adshqip.com',
                    'advertiser',
                    'Dren',
                    'Advertiser',
                    $publisher->id
                ),
            ]);

            $totalQualified = 0;
            $totalEarned = 0;

            foreach ($advertisers as $advertiserIndex => $advertiser) {
                $commission = 32.50 + ($advertiserIndex * 14.75);
                $spend = 640 + ($advertiserIndex * 210);

                ReferralConversion::query()->updateOrCreate(
                    ['referred_user_id' => $advertiser->id],
                    [
                        'link_id' => $link->id,
                        'referrer_id' => $publisher->id,
                        'referred_role' => 'advertiser',
                        'click_ip' => '127.0.0.1',
                        'signup_ip' => '127.0.0.1',
                        'cookie_id' => 'publisher-ref-' . $publisher->id . '-' . $advertiserIndex,
                        'is_qualified' => true,
                        'qualified_at' => now()->subDays(10 - $advertiserIndex),
                        'qualification_threshold' => 100,
                        'commission_earned' => $commission,
                        'commission_currency' => 'EUR',
                        'commission_ends_at' => now()->addMonths(6),
                        'status' => 'qualified',
                    ]
                );

                Transaction::query()->updateOrCreate(
                    [
                        'user_id' => $advertiser->id,
                        'type' => 'ad_spend',
                        'description' => 'Referral advertiser spend seed',
                    ],
                    [
                        'amount' => $spend,
                        'currency' => 'EUR',
                        'balance_before' => 0,
                        'balance_after' => 0,
                        'status' => 'completed',
                        'completed_at' => now()->subDays(5 - $advertiserIndex),
                    ]
                );

                $totalQualified++;
                $totalEarned += $commission;
            }

            $link->update([
                'total_signups' => $advertisers->count(),
                'total_qualified' => $totalQualified,
                'total_earned' => $totalEarned,
            ]);

            ReferralPayout::query()->updateOrCreate(
                [
                    'referrer_id' => $publisher->id,
                    'period_start' => now()->startOfMonth()->toDateString(),
                    'period_end' => now()->endOfMonth()->toDateString(),
                ],
                [
                    'amount' => $totalEarned,
                    'currency' => 'EUR',
                    'payment_method' => 'balance_credit',
                    'payment_reference' => 'PUB-REF-' . $publisher->id,
                    'conversions_count' => $totalQualified,
                    'status' => 'completed',
                    'processed_at' => now(),
                    'notes' => 'Seeded publisher referral payout.',
                ]
            );
        }

        $this->command?->info('Publisher referral data seeded successfully.');
    }

    private function createUser(string $email, string $role, string $firstName, string $lastName, ?int $referredBy = null): User
    {
        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'password_hash' => Hash::make('password'),
                'role' => $role,
                'status' => 'active',
                'email_verified_at' => now(),
                'preferred_language' => 'en',
                'referral_code' => strtoupper(Str::random(8)),
                'referred_by' => $referredBy,
                'referred_at' => $referredBy ? now()->subDays(15) : null,
                'is_deleted' => false,
            ]
        );

        if (Schema::hasTable('aq_user_profiles')) {
            UserProfile::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'country_code' => 'AL',
                ]
            );
        }

        return $user;
    }

    private function tablesExist(): bool
    {
        foreach (['aq_users', 'aq_referral_links', 'aq_referral_conversions', 'aq_referral_payouts', 'aq_transactions'] as $table) {
            if (! Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }
}
