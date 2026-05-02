<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Support\PublisherPaymentManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PublisherInvoiceHistoryController extends Controller
{
    public function __construct(private readonly PublisherPaymentManager $paymentManager) {}

    public function index(Request $request): View
    {
        $this->paymentManager->syncAutoInvoices();

        $filters = $this->filters($request);
        $query = $this->baseQuery($request->user()->id);

        if ($filters['search'] !== '') {
            $search = $filters['search'];

            $query->where(function ($builder) use ($search) {
                $builder->where('aq_invoices.invoice_number', 'like', '%' . $search . '%')
                    ->orWhere('aq_invoices.id', is_numeric($search) ? (int) $search : 0)
                    ->orWhere('aq_users.email', 'like', '%' . $search . '%');
            });
        }

        if ($filters['status'] !== null) {
            $query->where('aq_invoices.status', $filters['status']);
        }

        if ($filters['start_date'] !== null) {
            $query->whereDate('aq_invoices.created_at', '>=', $filters['start_date']);
        }

        if ($filters['end_date'] !== null) {
            $query->whereDate('aq_invoices.created_at', '<=', $filters['end_date']);
        }

        $summary = $this->summary((clone $query));

        $invoices = $query
            ->with('user.userProfile')
            ->orderByDesc('aq_invoices.created_at')
            ->select('aq_invoices.*')
            ->paginate(20)
            ->withQueryString();

        return view('publisher.invoices.index', [
            'invoices' => $invoices,
            'summary' => $summary,
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, int $id): View|Response
    {
        $invoice = Invoice::with('user.userProfile')
            ->whereIn('type', ['publisher_payout', 'subscription_charge'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        if ($request->expectsJson()) {
            $name = trim(($invoice->user->userProfile->first_name ?? '') . ' ' . ($invoice->user->userProfile->last_name ?? '')) ?: $invoice->user->email;

            return response()->json([
                'id' => $invoice->id,
                'name' => $name,
                'email' => $invoice->user->email,
                'invoice_number' => $invoice->invoice_number,
                'created_at' => $invoice->created_at?->format('M d, Y H:i'),
                'due_date' => $invoice->due_date?->format('M d, Y'),
                'amount' => number_format((float) $invoice->amount, 2),
                'tax_amount' => number_format((float) $invoice->tax_amount, 2),
                'total_amount' => number_format((float) $invoice->total_amount, 2),
                'status' => ucfirst($invoice->status),
                'paid_at' => $invoice->paid_at?->format('M d, Y H:i'),
                'currency' => $invoice->currency,
            ]);
        }

        return view('publisher.invoices.show', [
            'invoice' => $invoice,
        ]);
    }

    public function export(Request $request): Response
    {
        $this->paymentManager->syncAutoInvoices();

        $filters = $this->filters($request);
        $query = $this->baseQuery($request->user()->id);
        $this->applyFilters($query, $filters);

        $invoices = $query
            ->with('user.userProfile')
            ->orderByDesc('aq_invoices.created_at')
            ->select('aq_invoices.*')
            ->get();

        $filename = 'publisher_invoices_' . now()->format('Y-m-d_His') . '.csv';

        return response()->stream(function () use ($invoices) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Invoice Number', 'Invoice Date', 'Due Date', 'Amount', 'Tax', 'Total', 'Currency', 'Status', 'Paid At']);

            foreach ($invoices as $invoice) {
                fputcsv($file, [
                    $invoice->id,
                    $invoice->invoice_number,
                    $invoice->created_at?->format('Y-m-d H:i:s') ?: '',
                    $invoice->due_date?->format('Y-m-d') ?: '',
                    number_format((float) $invoice->amount, 2, '.', ''),
                    number_format((float) $invoice->tax_amount, 2, '.', ''),
                    number_format((float) $invoice->total_amount, 2, '.', ''),
                    $invoice->currency,
                    ucfirst($invoice->status),
                    $invoice->paid_at?->format('Y-m-d H:i:s') ?: '',
                ]);
            }

            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function download(Request $request, int $id): Response
    {
        $invoice = Invoice::with('user.userProfile')
            ->whereIn('type', ['publisher_payout', 'subscription_charge'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        if ($invoice->pdf_url) {
            return redirect()->away($invoice->pdf_url);
        }

        $content = $this->invoiceContent($invoice);
        $filename = 'invoice_' . $invoice->invoice_number . '.txt';

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function filters(Request $request): array
    {
        return [
            'search' => trim((string) $request->query('search', '')),
            'status' => $request->query('status') && $request->query('status') !== 'all'
                ? (string) $request->query('status')
                : null,
            'start_date' => $request->query('start_date') ?: null,
            'end_date' => $request->query('end_date') ?: null,
        ];
    }

    private function baseQuery(int $userId)
    {
        return Invoice::query()
            ->join('aq_users', 'aq_invoices.user_id', '=', 'aq_users.id')
            ->where('aq_users.id', $userId)
            ->where('aq_users.role', 'publisher')
            ->whereIn('aq_invoices.type', ['publisher_payout', 'subscription_charge']);
    }

    private function applyFilters($query, array $filters): void
    {
        if ($filters['search'] !== '') {
            $search = $filters['search'];

            $query->where(function ($builder) use ($search) {
                $builder->where('aq_invoices.invoice_number', 'like', '%' . $search . '%')
                    ->orWhere('aq_invoices.id', is_numeric($search) ? (int) $search : 0)
                    ->orWhere('aq_users.email', 'like', '%' . $search . '%');
            });
        }

        if ($filters['status'] !== null) {
            $query->where('aq_invoices.status', $filters['status']);
        }

        if ($filters['start_date'] !== null) {
            $query->whereDate('aq_invoices.created_at', '>=', $filters['start_date']);
        }

        if ($filters['end_date'] !== null) {
            $query->whereDate('aq_invoices.created_at', '<=', $filters['end_date']);
        }
    }

    private function summary($query): array
    {
        return [
            'total_invoices' => (clone $query)->count(),
            'total_amount' => (float) (clone $query)->sum('aq_invoices.total_amount'),
            'paid_count' => (clone $query)->where('aq_invoices.status', 'paid')->count(),
            'draft_count' => (clone $query)->where('aq_invoices.status', 'draft')->count(),
            'sent_count' => (clone $query)->where('aq_invoices.status', 'sent')->count(),
            'overdue_count' => (clone $query)->where('aq_invoices.status', 'overdue')->count(),
        ];
    }

    private function invoiceContent(Invoice $invoice): string
    {
        $publisherName = $invoice->user->userProfile
            ? trim(($invoice->user->userProfile->first_name ?? '') . ' ' . ($invoice->user->userProfile->last_name ?? ''))
            : $invoice->user->email;

        return "PUBLISHER INVOICE\n\n"
            . "Invoice Number: {$invoice->invoice_number}\n"
            . 'Invoice Date: ' . $invoice->created_at?->format('Y-m-d') . "\n"
            . 'Due Date: ' . ($invoice->due_date?->format('Y-m-d') ?? 'N/A') . "\n\n"
            . "Bill To:\n{$publisherName}\n{$invoice->user->email}\n\n"
            . 'Description: ' . ($invoice->type === 'subscription_charge' ? 'Subscription Plan Charge' : 'Publisher Payout') . "\n"
            . 'Amount: ' . $invoice->currency . ' ' . number_format((float) $invoice->amount, 2) . "\n"
            . 'Tax: ' . $invoice->currency . ' ' . number_format((float) $invoice->tax_amount, 2) . "\n"
            . 'TOTAL: ' . $invoice->currency . ' ' . number_format((float) $invoice->total_amount, 2) . "\n\n"
            . 'Status: ' . strtoupper($invoice->status) . "\n"
            . ($invoice->paid_at ? 'Paid At: ' . $invoice->paid_at->format('Y-m-d H:i:s') . "\n" : '');
    }
}
