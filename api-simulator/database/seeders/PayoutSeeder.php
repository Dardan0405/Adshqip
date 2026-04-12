<?php

namespace Database\Seeders;

use App\Models\Payout;
use App\Models\User;
use Illuminate\Database\Seeder;

class PayoutSeeder extends Seeder
{
    public function run(): void
    {
        $publishers = User::where('role', 'publisher')
            ->where('is_deleted', false)
            ->pluck('id')
            ->toArray();

        if (empty($publishers)) {
            $this->command->warn('No publishers found. Skipping payout seeding.');
            return;
        }

        $paymentMethods = ['paypal', 'wire_transfer', 'crypto', 'payoneer'];
        $statuses = ['pending', 'processing', 'completed', 'completed', 'completed', 'failed', 'cancelled'];

        $count = 0;

        for ($i = 0; $i < 15; $i++) {
            $status = $statuses[array_rand($statuses)];
            $createdAt = now()->subDays(rand(1, 120));
            $processedAt = in_array($status, ['completed', 'failed', 'cancelled'])
                ? $createdAt->copy()->addDays(rand(1, 5))
                : null;

            $periodEnd = $createdAt->copy()->startOfMonth()->subDay();
            $periodStart = $periodEnd->copy()->startOfMonth();

            Payout::updateOrCreate(
                [
                    'user_id' => $publishers[array_rand($publishers)],
                    'period_start' => $periodStart->format('Y-m-d'),
                    'period_end' => $periodEnd->format('Y-m-d'),
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                ],
                [
                    'amount' => round(rand(5000, 250000) / 100, 4),
                    'currency' => 'EUR',
                    'payment_reference' => $status === 'completed' ? 'PAY-' . strtoupper(substr(md5(rand()), 0, 12)) : null,
                    'status' => $status,
                    'notes' => $status === 'pending' ? 'Awaiting admin review' : ($status === 'failed' ? 'Payment processor declined' : null),
                    'processed_at' => $processedAt,
                    'created_at' => $createdAt,
                    'updated_at' => $processedAt ?? $createdAt,
                ]
            );

            $count++;
        }

        $this->command->info("Seeded {$count} payout records.");
    }
}
