<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'advertiser_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'string', 'max:50'],
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

        $allAdvertisers = User::query()
            ->where('role', 'advertiser')
            ->where('is_deleted', false)
            ->with('userProfile')
            ->orderBy('email')
            ->get();

        $statuses = [
            'draft' => 'Draft',
            'sent' => 'Sent',
            'paid' => 'Paid',
            'overdue' => 'Overdue',
            'cancelled' => 'Cancelled',
        ];

        return view('admin.invoices.index', compact(
            'invoices',
            'totalInvoices',
            'totalAmount',
            'paidCount',
            'thisMonthCount',
            'allAdvertisers',
            'statuses'
        ));
    }

    public function export(Request $request)
    {
        $invoices = $this->buildBaseQuery($request)
            ->with('user.userProfile')
            ->orderByDesc('aq_invoices.created_at')
            ->select('aq_invoices.*')
            ->get();

        $filename = 'advertiser_invoices_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($invoices) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Email', 'Invoice Number', 'Invoice Date', 'Total Amount', 'Status']);

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

    public function download($id)
    {
        $invoice = Invoice::with('user.userProfile')
            ->advertiserInvoices()
            ->whereHas('user', fn ($query) => $query->where('role', 'advertiser'))
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

    protected function buildBaseQuery(Request $request)
    {
        $query = Invoice::query()
            ->join('aq_users', 'aq_invoices.user_id', '=', 'aq_users.id')
            ->where('aq_users.is_deleted', false)
            ->where('aq_users.role', 'advertiser')
            ->where('aq_invoices.type', 'advertiser_charge');

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($inner) use ($search) {
                $inner
                    ->when(is_numeric($search), fn ($q) => $q->orWhere('aq_invoices.id', (int) $search))
                    ->orWhere('aq_invoices.invoice_number', 'like', '%' . $search . '%')
                    ->orWhere('aq_users.email', 'like', '%' . $search . '%');
            });
        }

        if ($advertiserId = $request->get('advertiser_id')) {
            $query->where('aq_invoices.user_id', $advertiserId);
        }

        if ($status = $request->get('status')) {
            $query->where('aq_invoices.status', $status);
        }

        if ($startDate = $request->get('start_date')) {
            $query->whereDate('aq_invoices.created_at', '>=', $startDate);
        }

        if ($endDate = $request->get('end_date')) {
            $query->whereDate('aq_invoices.created_at', '<=', $endDate);
        }

        return $query;
    }

    private function generateInvoiceContent(Invoice $invoice): string
    {
        $advertiserName = $invoice->user->userProfile
            ? trim(($invoice->user->userProfile->first_name ?? '') . ' ' . ($invoice->user->userProfile->last_name ?? ''))
            : $invoice->user->email;

        return "
ADVERTISER INVOICE

Invoice Number: {$invoice->invoice_number}
Invoice Date: {$invoice->created_at->format('Y-m-d')}
Due Date: {$invoice->due_date?->format('Y-m-d')}

Bill To:
{$advertiserName}
{$invoice->user->email}

Description: Advertiser Charge
Amount: EUR " . number_format((float) $invoice->amount, 2) . "
Tax: EUR " . number_format((float) $invoice->tax_amount, 2) . "
TOTAL: EUR " . number_format((float) $invoice->total_amount, 2) . "

Status: " . strtoupper((string) $invoice->status) . "
" . ($invoice->paid_at ? "Paid At: {$invoice->paid_at->format('Y-m-d H:i:s')}" : '') . "
";
    }
}
