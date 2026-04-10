<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PublisherInvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting Publisher Invoice Seeder...');

        // Get all publishers
        $publishers = User::where('role', 'publisher')->get();

        if ($publishers->isEmpty()) {
            $this->command->warn('No publishers found. Please seed publishers first.');
            return;
        }

        $this->command->info("Found {$publishers->count()} publishers.");

        // Generate invoices for the last 6 months
        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $invoiceCounter = 1000; // Starting invoice number
        $statuses = ['paid', 'sent', 'overdue', 'draft'];
        $statusWeights = [60, 25, 10, 5]; // 60% paid, 25% sent, 10% overdue, 5% draft

        foreach ($publishers as $publisher) {
            $this->command->info("Generating invoices for publisher: {$publisher->email}");

            $currentMonth = $startDate->copy();
            $invoicesCreated = 0;

            while ($currentMonth->lte($endDate)) {
                // Not all publishers get invoices every month (50% chance)
                if (rand(1, 100) <= 50) {
                    $invoiceCounter++;

                    // Random invoice amount between €100 - €5000
                    $amount = rand(100, 5000);

                    // Calculate tax (20% VAT)
                    $taxAmount = $amount * 0.20;
                    $totalAmount = $amount + $taxAmount;

                    // Generate invoice date (random day in the month)
                    $invoiceDate = $currentMonth->copy()->day(rand(1, $currentMonth->daysInMonth));

                    // Weighted random status selection
                    $status = $this->weightedRandom($statuses, $statusWeights);

                    // Due date is 30 days after invoice date
                    $dueDate = $invoiceDate->copy()->addDays(30);

                    // Paid date if status is 'paid'
                    $paidAt = null;
                    if ($status === 'paid') {
                        $paidAt = $invoiceDate->copy()->addDays(rand(1, 25));
                    }

                    // If overdue, make sure the due date is in the past
                    if ($status === 'overdue') {
                        $dueDate = Carbon::now()->subDays(rand(1, 30));
                        $invoiceDate = $dueDate->copy()->subDays(30);
                    }

                    // Create invoice
                    Invoice::create([
                        'user_id' => $publisher->id,
                        'invoice_number' => 'INV-' . str_pad($invoiceCounter, 6, '0', STR_PAD_LEFT),
                        'type' => 'publisher_payout',
                        'amount' => $amount,
                        'tax_amount' => $taxAmount,
                        'total_amount' => $totalAmount,
                        'currency' => 'EUR',
                        'status' => $status,
                        'due_date' => $dueDate,
                        'paid_at' => $paidAt,
                        'pdf_url' => null, // PDF generation would happen here
                        'created_at' => $invoiceDate,
                    ]);

                    $invoicesCreated++;
                    $this->command->info("  - Created invoice INV-" . str_pad($invoiceCounter, 6, '0', STR_PAD_LEFT) . " for {$currentMonth->format('F Y')}: €" . number_format($totalAmount, 2) . " ({$status})");
                }

                // Move to next month
                $currentMonth->addMonth();
            }

            $this->command->info("  ✓ Total invoices created: {$invoicesCreated}");
        }

        // Summary stats
        $totalInvoices = Invoice::where('type', 'publisher_payout')->count();
        $totalAmount = Invoice::where('type', 'publisher_payout')->sum('total_amount');
        $paidCount = Invoice::where('type', 'publisher_payout')->where('status', 'paid')->count();
        $overdueCount = Invoice::where('type', 'publisher_payout')->where('status', 'overdue')->count();

        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('✓ Publisher Invoice Seeder completed!');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info("Total Invoices:  {$totalInvoices}");
        $this->command->info("Total Amount:    €" . number_format($totalAmount, 2));
        $this->command->info("Paid:            {$paidCount}");
        $this->command->info("Overdue:         {$overdueCount}");
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
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
}
