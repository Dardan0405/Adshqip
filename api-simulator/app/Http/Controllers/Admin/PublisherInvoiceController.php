<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\User;
use App\Support\PublisherPaymentManager;
use Illuminate\Http\Request;

class PublisherInvoiceController extends Controller
{
    public function index(Request $request)
    {
        app(PublisherPaymentManager::class)->syncAutoInvoices();

        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'publisher_id' => ['nullable', 'integer'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $query = $this->buildBaseQuery($request);

        $totalInvoices = (clone $query)->count();
        $totalAmount = (clone $query)->sum('aq_invoices.total_amount');
        $paidCount = (clone $query)->where('aq_invoices.status', 'paid')->count();
        $thisMonthCount = (clone $query)
            ->whereBetween('aq_invoices.paid_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        $invoices = $query
            ->with('user.userProfile')
            ->orderByDesc('aq_invoices.created_at')
            ->select('aq_invoices.*')
            ->paginate(20)
            ->withQueryString();

        $allPublishers = User::where('role', 'publisher')
            ->where('is_deleted', false)
            ->with('userProfile')
            ->orderBy('email')
            ->get();

        return view('admin.publisher-invoices.index', compact(
            'invoices',
            'totalInvoices',
            'totalAmount',
            'paidCount',
            'thisMonthCount',
            'allPublishers'
        ));
    }

    public function show($id)
    {
        $invoice = Invoice::with('user.userProfile')
            ->publisherInvoices()
            ->whereHas('user', fn($query) => $query->where('role', 'publisher'))
            ->findOrFail($id);

        if (request()->expectsJson()) {
            $name = trim(($invoice->user->userProfile->first_name ?? '') . ' ' . ($invoice->user->userProfile->last_name ?? '')) ?: 'Unknown';

            return response()->json([
                'id' => $invoice->id,
                'name' => $name,
                'email' => $invoice->user->email,
                'invoice_id' => $invoice->invoice_number,
                'invoice_date' => $invoice->created_at?->format('M d, Y H:i'),
                'amount' => number_format((float) $invoice->amount, 2),
                'tax_amount' => number_format((float) $invoice->tax_amount, 2),
                'total_amount' => number_format((float) $invoice->total_amount, 2),
                'status' => ucfirst($invoice->status),
                'due_date' => $invoice->due_date?->format('M d, Y'),
                'paid_at' => $invoice->paid_at?->format('M d, Y H:i'),
                'currency' => $invoice->currency,
            ]);
        }

        return view('admin.publisher-invoices.show', compact('invoice'));
    }

    public function approve($id)
    {
        $invoice = Invoice::publisherInvoices()
            ->whereHas('user', fn($query) => $query->where('role', 'publisher'))
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->findOrFail($id);

        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Publisher invoice approved and marked as paid.',
        ]);
    }

    public function download($id)
    {
        $invoice = Invoice::with('user.userProfile')
            ->publisherInvoices()
            ->whereHas('user', fn($query) => $query->where('role', 'publisher'))
            ->findOrFail($id);

        if ($invoice->pdf_url) {
            return redirect($invoice->pdf_url);
        }

        $content = $this->generateInvoiceContent($invoice);
        $filename = "invoice_{$invoice->invoice_number}.txt";

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function export(Request $request)
    {
        app(PublisherPaymentManager::class)->syncAutoInvoices();

        $invoices = $this->buildBaseQuery($request)
            ->with('user.userProfile')
            ->orderByDesc('aq_invoices.created_at')
            ->select('aq_invoices.*')
            ->get();

        $filename = 'publisher_invoices_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($invoices) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Email', 'Invoice ID', 'Invoice Date', 'Invoice Amount', 'Invoice Status']);

            foreach ($invoices as $invoice) {
                fputcsv($file, [
                    $invoice->id,
                    $invoice->user->email ?? 'N/A',
                    $invoice->invoice_number,
                    $invoice->created_at?->format('Y-m-d H:i:s') ?: '',
                    number_format((float) $invoice->total_amount, 2, '.', ''),
                    ucfirst($invoice->status),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function buildBaseQuery(Request $request)
    {
        $query = Invoice::query()
            ->join('aq_users', 'aq_invoices.user_id', '=', 'aq_users.id')
            ->where('aq_users.is_deleted', false)
            ->where('aq_users.role', 'publisher')
            ->where('aq_invoices.type', 'publisher_payout');

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($inner) use ($search) {
                $inner
                    ->when(is_numeric($search), fn($q) => $q->orWhere('aq_invoices.id', (int) $search))
                    ->orWhere('aq_invoices.invoice_number', 'like', '%' . $search . '%')
                    ->orWhere('aq_users.email', 'like', '%' . $search . '%');
            });
        }

        if ($publisherId = $request->get('publisher_id')) {
            $query->where('aq_invoices.user_id', $publisherId);
        }

        if ($startDate = $request->get('start_date')) {
            $query->whereDate('aq_invoices.created_at', '>=', $startDate);
        }

        if ($endDate = $request->get('end_date')) {
            $query->whereDate('aq_invoices.created_at', '<=', $endDate);
        }

        return $query;
    }

    private function generateInvoiceContent($invoice)
    {
        $publisherName = $invoice->user->userProfile
            ? trim($invoice->user->userProfile->first_name . ' ' . $invoice->user->userProfile->last_name)
            : $invoice->user->email;

        return "
PUBLISHER INVOICE

Invoice Number: {$invoice->invoice_number}
Invoice Date: {$invoice->created_at->format('Y-m-d')}
Due Date: {$invoice->due_date?->format('Y-m-d')}

Bill To:
{$publisherName}
{$invoice->user->email}

Description: Publisher Payout
Amount: EUR " . number_format($invoice->amount, 2) . "
Tax: EUR " . number_format($invoice->tax_amount, 2) . "
TOTAL: EUR " . number_format($invoice->total_amount, 2) . "

Status: " . strtoupper($invoice->status) . "
" . ($invoice->paid_at ? "Paid At: {$invoice->paid_at->format('Y-m-d H:i:s')}" : '') . "
";
    }
}
