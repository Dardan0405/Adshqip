<?php

namespace Database\Seeders;

use App\Models\StatDaily;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdvertiserPaymentHistorySeeder extends Seeder
{
    private const NOTE = 'advertiser_payment_history_seeder';

    public function run(): void
    {
        $this->command->info('Starting Advertiser Payment History Seeder...');

        $advertisers = $this->ensureAdvertisers();
        $advertiserIds = $advertisers->pluck('id');

        Transaction::where('admin_note', self::NOTE)->delete();
        StatDaily::whereIn('advertiser_id', $advertiserIds)
            ->whereNull('campaign_id')
            ->whereNull('ad_id')
            ->whereBetween('date', [now()->subMonths(5)->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->delete();

        foreach ($advertisers as $advertiserIndex => $advertiser) {
            $this->seedAdvertiserHistory($advertiser, $advertiserIndex);
        }

        $this->command->info('Advertiser Payment History Seeder completed successfully.');
    }

    private function ensureAdvertisers()
    {
        return collect([
            ['email' => 'payment.history.advertiser1@adshqip.com', 'first_name' => 'Dion', 'last_name' => 'Morina'],
            ['email' => 'payment.history.advertiser2@adshqip.com', 'first_name' => 'Lira', 'last_name' => 'Hoxha'],
            ['email' => 'payment.history.advertiser3@adshqip.com', 'first_name' => 'Rron', 'last_name' => 'Kelmendi'],
        ])->map(function ($data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'password_hash' => Hash::make('password123'),
                    'role' => 'advertiser',
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
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'currency' => 'EUR',
                ]
            );

            return $user->fresh('userProfile');
        });
    }

    private function seedAdvertiserHistory(User $advertiser, int $advertiserIndex): void
    {
        $runningBalance = 0;

        for ($monthOffset = 0; $monthOffset < 5; $monthOffset++) {
            $month = Carbon::now()->subMonths($monthOffset)->startOfMonth();
            $depositAmount = 650 + ($advertiserIndex * 275) + ($monthOffset * 180);
            $depositDate = $month->copy()->addDays(2 + $advertiserIndex)->setTime(10, 30);

            Transaction::create([
                'user_id' => $advertiser->id,
                'type' => 'deposit',
                'amount' => $depositAmount,
                'currency' => 'EUR',
                'balance_before' => $runningBalance,
                'balance_after' => $runningBalance + $depositAmount,
                'payment_gateway' => ['stripe', 'paypal', 'manual'][$advertiserIndex % 3],
                'gateway_txn_id' => 'PAYHIST-' . $advertiser->id . '-' . $month->format('Ym'),
                'gateway_status' => 'confirmed',
                'description' => 'Seeded monthly advertiser deposit for ' . $month->format('F Y'),
                'admin_note' => self::NOTE,
                'status' => 'completed',
                'completed_at' => $depositDate,
                'created_at' => $depositDate,
                'updated_at' => $depositDate,
            ]);

            $runningBalance += $depositAmount;

            foreach ([5, 12, 19, 26] as $dayIndex => $day) {
                $date = $month->copy()->addDays($day - 1);

                if ($date->isFuture()) {
                    continue;
                }

                $spend = 42 + ($advertiserIndex * 18) + ($monthOffset * 9) + ($dayIndex * 7);
                $runningBalance -= $spend;

                StatDaily::create([
                    'date' => $date->toDateString(),
                    'advertiser_id' => $advertiser->id,
                    'impressions' => 2500 + ($dayIndex * 450) + ($advertiserIndex * 300),
                    'clicks' => 95 + ($dayIndex * 13),
                    'conversions' => 6 + $dayIndex,
                    'revenue' => $spend,
                    'created_at' => $date->copy()->setTime(18, 0),
                ]);
            }
        }
    }
}
