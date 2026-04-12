<?php

namespace Database\Seeders;

use App\Models\Payout;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdvertiserPaymentApprovalSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting Advertiser Payment Approval Seeder...');

        $advertisers = $this->ensureAdvertisers();

        Payout::where('notes', 'like', 'advertiser_payment_approval_seeder%')->delete();

        $rows = [
            ['user_index' => 0, 'amount' => 450.00, 'payment_method' => 'paypal', 'status' => 'pending', 'days_ago' => 18, 'period_start' => now()->subMonths(2)->startOfMonth(), 'period_end' => now()->subMonths(2)->endOfMonth()],
            ['user_index' => 1, 'amount' => 780.00, 'payment_method' => 'wire_transfer', 'status' => 'pending', 'days_ago' => 15, 'period_start' => now()->subMonths(2)->startOfMonth(), 'period_end' => now()->subMonths(2)->endOfMonth()],
            ['user_index' => 2, 'amount' => 1200.00, 'payment_method' => 'crypto', 'status' => 'completed', 'days_ago' => 12, 'period_start' => now()->subMonths(1)->startOfMonth(), 'period_end' => now()->subMonths(1)->endOfMonth()],
            ['user_index' => 0, 'amount' => 635.50, 'payment_method' => 'payoneer', 'status' => 'cancelled', 'days_ago' => 10, 'period_start' => now()->subMonths(1)->startOfMonth(), 'period_end' => now()->subMonths(1)->endOfMonth()],
            ['user_index' => 1, 'amount' => 990.00, 'payment_method' => 'paypal', 'status' => 'pending', 'days_ago' => 8, 'period_start' => now()->subMonth()->startOfMonth(), 'period_end' => now()->subMonth()->endOfMonth()],
            ['user_index' => 2, 'amount' => 1540.25, 'payment_method' => 'wire_transfer', 'status' => 'completed', 'days_ago' => 6, 'period_start' => now()->startOfMonth(), 'period_end' => now()->copy()->startOfMonth()->addDays(9)],
            ['user_index' => 3, 'amount' => 410.75, 'payment_method' => 'crypto', 'status' => 'pending', 'days_ago' => 5, 'period_start' => now()->startOfMonth(), 'period_end' => now()->copy()->startOfMonth()->addDays(14)],
            ['user_index' => 3, 'amount' => 870.00, 'payment_method' => 'payoneer', 'status' => 'processing', 'days_ago' => 3, 'period_start' => now()->startOfMonth(), 'period_end' => now()->copy()->startOfMonth()->addDays(18)],
            ['user_index' => 2, 'amount' => 512.30, 'payment_method' => 'paypal', 'status' => 'pending', 'days_ago' => 2, 'period_start' => now()->startOfMonth(), 'period_end' => now()->copy()->startOfMonth()->addDays(21)],
        ];

        foreach ($rows as $index => $row) {
            $createdAt = now()->subDays($row['days_ago'])->setTime(9 + ($index % 6), 15);
            $processedAt = in_array($row['status'], ['completed', 'cancelled', 'failed'], true)
                ? $createdAt->copy()->addDay()
                : null;

            Payout::create([
                'user_id' => $advertisers[$row['user_index']]->id,
                'amount' => $row['amount'],
                'currency' => 'EUR',
                'payment_method' => $row['payment_method'],
                'payment_reference' => in_array($row['status'], ['completed', 'processing'], true)
                    ? 'ADV-PAY-' . strtoupper(str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT))
                    : null,
                'status' => $row['status'],
                'period_start' => $row['period_start']->toDateString(),
                'period_end' => $row['period_end']->toDateString(),
                'notes' => 'advertiser_payment_approval_seeder record #' . ($index + 1),
                'processed_at' => $processedAt,
                'created_at' => $createdAt,
                'updated_at' => $processedAt ?? $createdAt,
            ]);
        }

        $this->command->info('Advertiser Payment Approval Seeder completed successfully.');
    }

    protected function ensureAdvertisers()
    {
        $advertisers = collect([
            ['email' => 'approval.advertiser1@adshqip.com', 'first_name' => 'Dion', 'last_name' => 'Kelmendi'],
            ['email' => 'approval.advertiser2@adshqip.com', 'first_name' => 'Arta', 'last_name' => 'Shala'],
            ['email' => 'approval.advertiser3@adshqip.com', 'first_name' => 'Blend', 'last_name' => 'Hoxha'],
            ['email' => 'approval.advertiser4@adshqip.com', 'first_name' => 'Lira', 'last_name' => 'Matoshi'],
        ]);

        return $advertisers->map(function (array $advertiserData) {
            $user = User::updateOrCreate(
                ['email' => $advertiserData['email']],
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
                    'first_name' => $advertiserData['first_name'],
                    'last_name' => $advertiserData['last_name'],
                    'currency' => 'EUR',
                    'balance' => 0,
                ]
            );

            return $user->fresh('userProfile');
        })->values();
    }
}
