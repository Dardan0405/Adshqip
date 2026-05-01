<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceHistoryController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $query = $this->baseQuery($request, $filters);

        $summaryQuery = clone $query;
        $totalInvoices = (clone $summaryQuery)->count();
        $totalAmount = (float) (clone $summaryQuery)->sum('total_amount');
        $paidCount = (clone $summaryQuery)->where('status', 'paid')->count();
        $openCount = (clone $summaryQuery)->whereIn('status', ['draft', 'sent', 'overdue'])->count();

        $invoices = $query
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $missingDepositInvoices = Transaction::query()
            ->where('user_id', $request->user()->id)
            ->where('type', 'deposit')
            ->where('status', 'completed')
            ->whereNull('invoice_id')
            ->count();

        return view('advertiser.invoices.index', [
            'invoices' => $invoices,
            'statuses' => $this->statuses(),
            'filters' => [
                'search' => $filters['search'] ?? '',
                'status' => $filters['status'] ?? '',
                'start_date' => $filters['start_date'] ?? '',
                'end_date' => $filters['end_date'] ?? '',
            ],
            'summary' => [
                'total_invoices' => $totalInvoices,
                'total_amount' => $totalAmount,
                'paid_count' => $paidCount,
                'open_count' => $openCount,
                'missing_deposit_invoices' => $missingDepositInvoices,
            ],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $invoices = $this->baseQuery($request, $filters)
            ->orderByDesc('created_at')
            ->get();

        $filename = 'advertiser_invoice_history_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($invoices) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Invoice Number', 'Invoice Date', 'Due Date', 'Amount', 'Tax', 'Total', 'Currency', 'Status', 'Paid At']);

            foreach ($invoices as $invoice) {
                fputcsv($handle, [
                    $invoice->id,
                    $invoice->invoice_number,
                    $invoice->created_at?->format('Y-m-d H:i:s'),
                    $invoice->due_date?->format('Y-m-d'),
                    number_format((float) $invoice->amount, 2, '.', ''),
                    number_format((float) $invoice->tax_amount, 2, '.', ''),
                    number_format((float) $invoice->total_amount, 2, '.', ''),
                    $invoice->currency,
                    ucfirst($invoice->status),
                    $invoice->paid_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function download(Request $request, int $id)
    {
        $invoice = Invoice::query()
            ->where('user_id', $request->user()->id)
            ->where('type', 'advertiser_charge')
            ->findOrFail($id);

        if ($invoice->pdf_url) {
            return redirect($invoice->pdf_url);
        }

        return response($this->invoiceContent($invoice, $request), 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="invoice_' . $invoice->invoice_number . '.txt"',
        ]);
    }

    public function generateMissing(Request $request)
    {
        $transactions = Transaction::query()
            ->where('user_id', $request->user()->id)
            ->where('type', 'deposit')
            ->where('status', 'completed')
            ->whereNull('invoice_id')
            ->orderBy('completed_at')
            ->get();

        $created = 0;

        DB::transaction(function () use ($transactions, &$created, $request) {
            foreach ($transactions as $transaction) {
                $invoiceNumber = $this->invoiceNumber($transaction);
                $invoice = Invoice::firstOrCreate([
                    'invoice_number' => $invoiceNumber,
                ], [
                    'user_id' => $request->user()->id,
                    'type' => 'advertiser_charge',
                    'amount' => $transaction->amount,
                    'tax_amount' => 0,
                    'total_amount' => $transaction->amount,
                    'currency' => $transaction->currency ?: 'EUR',
                    'status' => 'paid',
                    'due_date' => optional($transaction->completed_at ?? $transaction->created_at)->toDateString(),
                    'paid_at' => $transaction->completed_at ?? $transaction->created_at,
                ]);

                $transaction->update(['invoice_id' => $invoice->id]);
                if ($invoice->wasRecentlyCreated) {
                    $created++;
                }
            }
        });

        return redirect()
            ->route('advertiser.payments.invoices')
            ->with('success', number_format($created) . ' invoice(s) generated from completed deposits.');
    }

    private function baseQuery(Request $request, array $filters)
    {
        return Invoice::query()
            ->where('user_id', $request->user()->id)
            ->where('type', 'advertiser_charge')
            ->when(!empty($filters['search']), function ($query) use ($filters) {
                $search = trim((string) $filters['search']);
                $query->where(function ($inner) use ($search) {
                    $inner->where('invoice_number', 'like', '%' . $search . '%')
                        ->when(is_numeric($search), fn ($q) => $q->orWhere('id', (int) $search));
                });
            })
            ->when(!empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(!empty($filters['start_date']), fn ($query) => $query->whereDate('created_at', '>=', $filters['start_date']))
            ->when(!empty($filters['end_date']), fn ($query) => $query->whereDate('created_at', '<=', $filters['end_date']));
    }

    private function invoiceNumber(Transaction $transaction): string
    {
        $date = optional($transaction->completed_at ?? $transaction->created_at)->format('Ymd') ?: now()->format('Ymd');

        return 'ADV-' . $date . '-' . str_pad((string) $transaction->id, 6, '0', STR_PAD_LEFT);
    }

    private function invoiceContent(Invoice $invoice, Request $request): string
    {
        $user = $request->user();
        $profile = $user->profile ?? $user->userProfile;
        $name = trim(($profile->first_name ?? '') . ' ' . ($profile->last_name ?? '')) ?: $user->email;
        $company = $profile->company_name ?? null;

        return "ADVERTISER INVOICE\n\n"
            . "Invoice Number: {$invoice->invoice_number}\n"
            . 'Invoice Date: ' . $invoice->created_at?->format('Y-m-d') . "\n"
            . 'Due Date: ' . ($invoice->due_date?->format('Y-m-d') ?: '-') . "\n\n"
            . "Bill To:\n"
            . ($company ? "{$company}\n" : '')
            . "{$name}\n"
            . "{$user->email}\n\n"
            . "Description: Advertiser account charge\n"
            . 'Amount: ' . $invoice->currency . ' ' . number_format((float) $invoice->amount, 2) . "\n"
            . 'Tax: ' . $invoice->currency . ' ' . number_format((float) $invoice->tax_amount, 2) . "\n"
            . 'TOTAL: ' . $invoice->currency . ' ' . number_format((float) $invoice->total_amount, 2) . "\n\n"
            . 'Status: ' . strtoupper((string) $invoice->status) . "\n"
            . ($invoice->paid_at ? 'Paid At: ' . $invoice->paid_at->format('Y-m-d H:i:s') . "\n" : '');
    }

    private function statuses(): array
    {
        return [
            'draft' => 'Draft',
            'sent' => 'Sent',
            'paid' => 'Paid',
            'overdue' => 'Overdue',
            'cancelled' => 'Cancelled',
        ];
    }
}
