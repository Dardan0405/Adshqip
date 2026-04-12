<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PublisherInvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting Publisher Invoice Seeder...');

        $publishers = $this->ensurePublishers();

        Invoice::where('type', 'publisher_payout')
            ->where('invoice_number', 'like', 'PUB-INV-%')
            ->delete();

        $rows = [
            ['user_index' => 0, 'amount' => 320.00, 'tax_amount' => 32.00, 'status' => 'draft', 'days_ago' => 18],
            ['user_index' => 1, 'amount' => 540.00, 'tax_amount' => 54.00, 'status' => 'sent', 'days_ago' => 15],
            ['user_index' => 2, 'amount' => 880.00, 'tax_amount' => 88.00, 'status' => 'paid', 'days_ago' => 12],
            ['user_index' => 3, 'amount' => 460.00, 'tax_amount' => 46.00, 'status' => 'overdue', 'days_ago' => 9],
            ['user_index' => 0, 'amount' => 710.00, 'tax_amount' => 71.00, 'status' => 'sent', 'days_ago' => 7],
            ['user_index' => 1, 'amount' => 930.00, 'tax_amount' => 93.00, 'status' => 'paid', 'days_ago' => 5],
            ['user_index' => 2, 'amount' => 285.00, 'tax_amount' => 28.50, 'status' => 'draft', 'days_ago' => 3],
            ['user_index' => 3, 'amount' => 620.00, 'tax_amount' => 62.00, 'status' => 'cancelled', 'days_ago' => 1],
        ];

        foreach ($rows as $index => $row) {
            $createdAt = now()->subDays($row['days_ago'])->setTime(9 + ($index % 5), 45);
            $dueDate = $createdAt->copy()->addDays(7)->toDateString();
            $paidAt = $row['status'] === 'paid' ? $createdAt->copy()->addDays(2) : null;

            Invoice::create([
                'user_id' => $publishers[$row['user_index']]->id,
                'invoice_number' => 'PUB-INV-' . now()->format('Y') . '-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'type' => 'publisher_payout',
                'amount' => $row['amount'],
                'tax_amount' => $row['tax_amount'],
                'total_amount' => $row['amount'] + $row['tax_amount'],
                'currency' => 'EUR',
                'status' => $row['status'],
                'due_date' => $dueDate,
                'paid_at' => $paidAt,
                'pdf_url' => null,
                'created_at' => $createdAt,
            ]);
        }

        $this->command->info('Publisher Invoice Seeder completed successfully.');
    }

    protected function ensurePublishers()
    {
        $publishers = collect([
            ['email' => 'invoice.publisher1@adshqip.com', 'first_name' => 'Arben', 'last_name' => 'Bajrami'],
            ['email' => 'invoice.publisher2@adshqip.com', 'first_name' => 'Mira', 'last_name' => 'Berani'],
            ['email' => 'invoice.publisher3@adshqip.com', 'first_name' => 'Luan', 'last_name' => 'Beqiri'],
            ['email' => 'invoice.publisher4@adshqip.com', 'first_name' => 'Drita', 'last_name' => 'Zeneli'],
        ]);

        return $publishers->map(function (array $publisherData) {
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

            return $user->fresh('userProfile');
        })->values();
    }
}
