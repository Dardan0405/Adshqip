<?php

namespace App\Support;

use App\Models\Invoice;
use App\Models\Payout;
use App\Models\PlatformSetting;
use App\Models\PublisherEarning;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PublisherPaymentManager
{
    public function syncAutoInvoices(): void
    {
        if (! PlatformSetting::getPublisherAutoInvoiceEnabled()) {
            return;
        }

        $minimumInvoiceAmount = PlatformSetting::getPublisherMinimumAmountForInvoiceGeneration();

        $publishers = User::where('role', 'publisher')
            ->where('is_deleted', false)
            ->get(['id']);

        foreach ($publishers as $publisher) {
            $outstandingAmount = $this->outstandingEarningsForPublisher($publisher->id);

            if ($outstandingAmount < $minimumInvoiceAmount) {
                continue;
            }

            $existingDraft = Invoice::publisherInvoices()
                ->where('user_id', $publisher->id)
                ->whereIn('status', ['draft', 'sent', 'overdue'])
                ->exists();

            if ($existingDraft) {
                continue;
            }

            Invoice::create([
                'user_id' => $publisher->id,
                'invoice_number' => $this->nextInvoiceNumber(),
                'type' => 'publisher_payout',
                'amount' => round($outstandingAmount, 4),
                'tax_amount' => 0,
                'total_amount' => round($outstandingAmount, 4),
                'currency' => 'EUR',
                'status' => 'draft',
                'due_date' => now()->addDays(7)->toDateString(),
                'paid_at' => null,
                'pdf_url' => null,
            ]);
        }
    }

    public function syncPendingPayouts(): void
    {
        $minimumPaymentAmount = PlatformSetting::getPublisherMinimumPaymentAmount();

        $paidInvoices = Invoice::publisherInvoices()
            ->where('status', 'paid')
            ->where('total_amount', '>=', $minimumPaymentAmount)
            ->get();

        foreach ($paidInvoices as $invoice) {
            $exists = Payout::where('user_id', $invoice->user_id)
                ->whereDate('period_start', $invoice->created_at->toDateString())
                ->whereDate('period_end', $invoice->due_date?->toDateString() ?? $invoice->created_at->toDateString())
                ->exists();

            if ($exists) {
                continue;
            }

            Payout::create([
                'user_id' => $invoice->user_id,
                'amount' => $invoice->total_amount,
                'currency' => $invoice->currency,
                'payment_method' => 'wire_transfer',
                'payment_reference' => $invoice->invoice_number,
                'status' => 'pending',
                'period_start' => $invoice->created_at->toDateString(),
                'period_end' => $invoice->due_date?->toDateString() ?? $invoice->created_at->toDateString(),
                'notes' => 'Generated from paid publisher invoice ' . $invoice->invoice_number,
                'processed_at' => null,
            ]);
        }
    }

    public function applyMinimumFloorPrice(float $requestedFloorPrice): float
    {
        if (! PlatformSetting::getPublisherFloorPriceEnabled()) {
            return round(max(0, $requestedFloorPrice), 4);
        }

        return round(max(
            max(0, $requestedFloorPrice),
            PlatformSetting::getPublisherMinimumFloorPriceAmount()
        ), 4);
    }

    private function outstandingEarningsForPublisher(int $publisherId): float
    {
        $totalEarnings = (float) PublisherEarning::forPublisher($publisherId)
            ->sum('publisher_earnings');

        $invoicedAmount = (float) Invoice::publisherInvoices()
            ->where('user_id', $publisherId)
            ->sum('total_amount');

        return max(0, round($totalEarnings - $invoicedAmount, 4));
    }

    private function nextInvoiceNumber(): string
    {
        return 'PUB-' . now()->format('YmdHis') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}
