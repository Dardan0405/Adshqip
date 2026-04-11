<?php

namespace Database\Seeders;

use App\Models\AdvertiserDeposit;
use App\Models\SavedPaymentMethod;
use App\Models\User;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdvertiserDepositSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting Advertiser Deposit Seeder...');

        $advertisers = $this->ensureAdvertisers();

        $this->cleanupExistingSeedData();

        $paymentMethods = $this->createPaymentMethods($advertisers);
        $this->createDeposits($advertisers, $paymentMethods);

        $this->command->info('Advertiser Deposit Seeder completed successfully.');
    }

    protected function ensureAdvertisers()
    {
        $advertisers = collect([
            [
                'email' => 'deposit.advertiser1@adshqip.com',
                'first_name' => 'Ardit',
                'last_name' => 'Krasniqi',
            ],
            [
                'email' => 'deposit.advertiser2@adshqip.com',
                'first_name' => 'Elira',
                'last_name' => 'Berisha',
            ],
            [
                'email' => 'deposit.advertiser3@adshqip.com',
                'first_name' => 'Leon',
                'last_name' => 'Gashi',
            ],
        ]);

        return $advertisers->map(function ($advertiserData) {
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
        });
    }

    protected function cleanupExistingSeedData(): void
    {
        AdvertiserDeposit::where('admin_note', 'advertiser_deposit_seeder')->delete();
        SavedPaymentMethod::where('label', 'like', 'Seeder %')->delete();
    }

    protected function createPaymentMethods($advertisers)
    {
        $definitions = [
            ['label' => 'Seeder Stripe Card', 'type' => 'credit_card', 'gateway' => 'stripe'],
            ['label' => 'Seeder PayPal Wallet', 'type' => 'paypal', 'gateway' => 'paypal'],
            ['label' => 'Seeder Bank Transfer', 'type' => 'wire_transfer', 'gateway' => 'manual'],
        ];

        return $advertisers->values()->map(function ($advertiser, $index) use ($definitions) {
            $definition = $definitions[$index % count($definitions)];

            return SavedPaymentMethod::create([
                'user_id' => $advertiser->id,
                'label' => $definition['label'] . ' #' . $advertiser->id,
                'type' => $definition['type'],
                'gateway' => $definition['gateway'],
                'gateway_customer_id' => 'SEED-CUST-' . $advertiser->id,
                'gateway_payment_method_id' => 'SEED-PM-' . $advertiser->id,
                'card_brand' => $definition['type'] === 'credit_card' ? 'visa' : null,
                'card_last4' => $definition['type'] === 'credit_card' ? '4242' : null,
                'card_exp_month' => $definition['type'] === 'credit_card' ? 12 : null,
                'card_exp_year' => $definition['type'] === 'credit_card' ? 2030 : null,
                'billing_country' => 'XK',
                'is_default' => true,
                'is_active' => true,
            ]);
        })->keyBy('user_id');
    }

    protected function createDeposits($advertisers, $paymentMethods): void
    {
        $rows = [
            ['user_index' => 0, 'amount' => 500.00, 'gateway' => 'stripe', 'status' => 'completed', 'days_ago' => 120],
            ['user_index' => 1, 'amount' => 1200.00, 'gateway' => 'paypal', 'status' => 'completed', 'days_ago' => 97],
            ['user_index' => 2, 'amount' => 850.00, 'gateway' => 'wire_transfer', 'status' => 'completed', 'days_ago' => 76],
            ['user_index' => 0, 'amount' => 2400.00, 'gateway' => 'stripe', 'status' => 'completed', 'days_ago' => 55],
            ['user_index' => 1, 'amount' => 300.00, 'gateway' => 'paypal', 'status' => 'pending', 'days_ago' => 30],
            ['user_index' => 2, 'amount' => 1500.00, 'gateway' => 'wire_transfer', 'status' => 'completed', 'days_ago' => 22],
            ['user_index' => 0, 'amount' => 950.00, 'gateway' => 'stripe', 'status' => 'failed', 'days_ago' => 14],
            ['user_index' => 1, 'amount' => 1750.00, 'gateway' => 'paypal', 'status' => 'completed', 'days_ago' => 9],
            ['user_index' => 2, 'amount' => 640.00, 'gateway' => 'wire_transfer', 'status' => 'reversed', 'days_ago' => 4],
            ['user_index' => 0, 'amount' => 2100.00, 'gateway' => 'stripe', 'status' => 'completed', 'days_ago' => 1],
        ];

        $balances = [];

        foreach ($rows as $index => $row) {
            $advertiser = $advertisers[$row['user_index']];
            $paymentMethod = $paymentMethods->get($advertiser->id);
            $timestamp = Carbon::now()->subDays($row['days_ago'])->setTime(10 + ($index % 6), 20);

            $previousBalance = $balances[$advertiser->id] ?? 0;
            $isCredited = in_array($row['status'], ['completed', 'pending'], true);
            $balanceAfter = $isCredited ? $previousBalance + $row['amount'] : $previousBalance;

            AdvertiserDeposit::create([
                'user_id' => $advertiser->id,
                'type' => 'deposit',
                'amount' => $row['amount'],
                'currency' => 'EUR',
                'balance_before' => $previousBalance,
                'balance_after' => $balanceAfter,
                'payment_method_id' => $paymentMethod?->id,
                'payment_gateway' => $row['gateway'] === 'wire_transfer' ? 'manual' : $row['gateway'],
                'gateway_txn_id' => 'ADDEP-' . $advertiser->id . '-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'gateway_status' => match ($row['status']) {
                    'completed' => 'confirmed',
                    'pending' => 'pending',
                    'failed' => 'failed',
                    'reversed' => 'refunded',
                    default => null,
                },
                'description' => 'Seeder advertiser deposit via ' . str_replace('_', ' ', ucfirst($row['gateway'])),
                'admin_note' => 'advertiser_deposit_seeder',
                'status' => $row['status'],
                'completed_at' => $row['status'] === 'completed' ? $timestamp : null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            $balances[$advertiser->id] = $balanceAfter;
        }
    }
}
