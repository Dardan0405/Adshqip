<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BalanceSheetSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting Balance Sheet Seeder...');

        $advertisers = $this->ensureAdvertisers();
        $publishers = $this->ensurePublishers();

        $this->cleanupExistingSeedData();

        $months = collect(range(0, 5))
            ->map(fn ($offset) => Carbon::now()->subMonths(5 - $offset)->startOfMonth());

        $depositBlueprints = [
            [1250, 750],
            [2500],
            [1800, 900, 600],
            [3000, 1200],
            [2250],
            [3500, 1500],
        ];

        $payoutBlueprints = [
            [680, 420],
            [1200],
            [950, 510],
            [1450],
            [1100, 600],
            [1750],
        ];

        foreach ($months as $monthIndex => $month) {
            $this->seedAdvertiserDeposits($advertisers, $month, $depositBlueprints[$monthIndex]);
            $this->seedPublisherPayouts($publishers, $month, $payoutBlueprints[$monthIndex], $monthIndex);
        }

        $this->command->info('Balance Sheet Seeder completed successfully.');
    }

    protected function ensureAdvertisers()
    {
        $advertisers = User::where('role', 'advertiser')->orderBy('id')->take(3)->get();

        if ($advertisers->count() >= 3) {
            return $advertisers;
        }

        $emails = [
            'balancesheet-advertiser1@adshqip.com',
            'balancesheet-advertiser2@adshqip.com',
            'balancesheet-advertiser3@adshqip.com',
        ];

        foreach ($emails as $email) {
            User::firstOrCreate(
                ['email' => $email],
                [
                    'password_hash' => Hash::make('password123'),
                    'role' => 'advertiser',
                    'status' => 'active',
                    'preferred_language' => 'en',
                    'theme_preference' => 'system',
                    'timezone' => 'Europe/Tirane',
                ]
            );
        }

        return User::where('role', 'advertiser')->orderBy('id')->take(3)->get();
    }

    protected function ensurePublishers()
    {
        $publishers = User::where('role', 'publisher')->orderBy('id')->take(3)->get();

        if ($publishers->count() >= 3) {
            return $publishers;
        }

        $emails = [
            'balancesheet-publisher1@adshqip.com',
            'balancesheet-publisher2@adshqip.com',
            'balancesheet-publisher3@adshqip.com',
        ];

        foreach ($emails as $email) {
            User::firstOrCreate(
                ['email' => $email],
                [
                    'password_hash' => Hash::make('password123'),
                    'role' => 'publisher',
                    'status' => 'active',
                    'preferred_language' => 'en',
                    'theme_preference' => 'system',
                    'timezone' => 'Europe/Tirane',
                ]
            );
        }

        return User::where('role', 'publisher')->orderBy('id')->take(3)->get();
    }

    protected function cleanupExistingSeedData(): void
    {
        Transaction::where('admin_note', 'balance_sheet_seeder')->delete();
        Invoice::where('invoice_number', 'like', 'BS-%')->delete();
    }

    protected function seedAdvertiserDeposits($advertisers, Carbon $month, array $amounts): void
    {
        foreach ($amounts as $index => $amount) {
            $advertiser = $advertisers[$index % $advertisers->count()];
            $completedAt = $month->copy()->day(min(5 + ($index * 7), $month->daysInMonth))->setTime(10 + $index, 15);

            $previousBalance = (float) Transaction::where('user_id', $advertiser->id)
                ->orderByDesc('completed_at')
                ->orderByDesc('id')
                ->value('balance_after');

            $balanceBefore = $previousBalance ?: 0;
            $balanceAfter = $balanceBefore + $amount;

            Transaction::create([
                'user_id' => $advertiser->id,
                'type' => 'deposit',
                'amount' => $amount,
                'currency' => 'EUR',
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'payment_method_id' => null,
                'payment_gateway' => 'manual',
                'gateway_txn_id' => 'BS-TXN-' . $month->format('Ym') . '-' . $advertiser->id . '-' . $index,
                'gateway_status' => 'confirmed',
                'description' => 'Balance sheet sample deposit for ' . $month->format('F Y'),
                'admin_note' => 'balance_sheet_seeder',
                'status' => 'completed',
                'completed_at' => $completedAt,
                'created_at' => $completedAt,
                'updated_at' => $completedAt,
            ]);
        }
    }

    protected function seedPublisherPayouts($publishers, Carbon $month, array $amounts, int $monthIndex): void
    {
        foreach ($amounts as $index => $amount) {
            $publisher = $publishers[$index % $publishers->count()];
            $createdAt = $month->copy()->day(min(18 + ($index * 4), $month->daysInMonth))->setTime(14, 30);
            $paidAt = $createdAt->copy()->addDays(3);

            $invoiceNumber = 'BS-' . $month->format('Ym') . '-' . $publisher->id . '-' . ($monthIndex + 1) . ($index + 1);

            Invoice::create([
                'user_id' => $publisher->id,
                'invoice_number' => $invoiceNumber,
                'type' => 'publisher_payout',
                'amount' => $amount,
                'tax_amount' => 0,
                'total_amount' => $amount,
                'currency' => 'EUR',
                'status' => 'paid',
                'due_date' => $createdAt->copy()->addDays(14)->toDateString(),
                'paid_at' => $paidAt,
                'pdf_url' => null,
                'created_at' => $createdAt,
            ]);
        }
    }
}
