<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PublisherInvoiceController extends Controller
{
    /**
     * Display a listing of publisher invoices.
     */
    public function index(Request $request)
    {
        $query = Invoice::with('user.userProfile')
            ->publisherInvoices()
            ->whereHas('user', function($q) {
                $q->where('role', 'publisher');
            });

        // Apply filters
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($publisherId = $request->get('publisher_id')) {
            $query->where('user_id', $publisherId);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($startDate = $request->get('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate = $request->get('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Get paginated results
        $invoices = $query->orderBy('created_at', 'desc')->paginate(20);

        // Calculate summary stats
        $totalInvoices = Invoice::publisherInvoices()
            ->whereHas('user', fn($q) => $q->where('role', 'publisher'))
            ->count();

        $totalAmount = Invoice::publisherInvoices()
            ->whereHas('user', fn($q) => $q->where('role', 'publisher'))
            ->sum('total_amount');

        $paidCount = Invoice::publisherInvoices()
            ->whereHas('user', fn($q) => $q->where('role', 'publisher'))
            ->where('status', 'paid')
            ->count();

        $overdueCount = Invoice::publisherInvoices()
            ->whereHas('user', fn($q) => $q->where('role', 'publisher'))
            ->where('status', 'overdue')
            ->count();

        // Get all publishers for filter dropdown
        $allPublishers = User::where('role', 'publisher')
            ->with('userProfile')
            ->orderBy('email')
            ->get();

        return view('admin.publisher-invoices.index', compact(
            'invoices',
            'totalInvoices',
            'totalAmount',
            'paidCount',
            'overdueCount',
            'allPublishers'
        ));
    }

    /**
     * Display the specified invoice.
     */
    public function show($id)
    {
        $invoice = Invoice::with('user.userProfile')->findOrFail($id);

        return view('admin.publisher-invoices.show', compact('invoice'));
    }

    /**
     * Download invoice as PDF.
     */
    public function download($id)
    {
        $invoice = Invoice::with('user.userProfile')->findOrFail($id);

        // If PDF already generated, redirect to it
        if ($invoice->pdf_url) {
            return redirect($invoice->pdf_url);
        }

        // Generate PDF (placeholder - implement PDF generation library)
        // For now, return a simple text file
        $content = $this->generateInvoiceContent($invoice);

        $filename = "invoice_{$invoice->invoice_number}.txt";

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export invoices to CSV.
     */
    public function export(Request $request)
    {
        $query = Invoice::with('user.userProfile')
            ->publisherInvoices()
            ->whereHas('user', function($q) {
                $q->where('role', 'publisher');
            });

        // Apply same filters as index
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($publisherId = $request->get('publisher_id')) {
            $query->where('user_id', $publisherId);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($startDate = $request->get('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate = $request->get('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $invoices = $query->orderBy('created_at', 'desc')->get();

        // Generate filename
        $filename = 'publisher_invoices_' . date('Y-m-d_His') . '.csv';

        // Set headers
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        // Stream CSV
        $callback = function () use ($invoices) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, ['ID', 'Invoice Number', 'Publisher Name', 'Invoice Date', 'Amount (€)', 'Tax (€)', 'Total (€)', 'Status', 'Due Date', 'Paid At']);

            // Add data rows
            foreach ($invoices as $invoice) {
                $publisherName = $invoice->user->userProfile
                    ? trim($invoice->user->userProfile->first_name . ' ' . $invoice->user->userProfile->last_name)
                    : $invoice->user->email;

                fputcsv($file, [
                    $invoice->id,
                    $invoice->invoice_number,
                    $publisherName,
                    $invoice->created_at->format('Y-m-d'),
                    number_format($invoice->amount, 2, '.', ''),
                    number_format($invoice->tax_amount, 2, '.', ''),
                    number_format($invoice->total_amount, 2, '.', ''),
                    ucfirst($invoice->status),
                    $invoice->due_date ? $invoice->due_date->format('Y-m-d') : 'N/A',
                    $invoice->paid_at ? $invoice->paid_at->format('Y-m-d H:i:s') : 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate invoice content (placeholder for PDF generation).
     */
    private function generateInvoiceContent($invoice)
    {
        $publisherName = $invoice->user->userProfile
            ? trim($invoice->user->userProfile->first_name . ' ' . $invoice->user->userProfile->last_name)
            : $invoice->user->email;

        return "
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            PUBLISHER INVOICE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Invoice Number: {$invoice->invoice_number}
Invoice Date:   {$invoice->created_at->format('Y-m-d')}
Due Date:       {$invoice->due_date->format('Y-m-d')}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
BILL TO:
{$publisherName}
{$invoice->user->email}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Description:    Publisher Payout
Amount:         €" . number_format($invoice->amount, 2) . "
Tax:            €" . number_format($invoice->tax_amount, 2) . "
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
TOTAL:          €" . number_format($invoice->total_amount, 2) . "
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Status: " . strtoupper($invoice->status) . "
" . ($invoice->paid_at ? "Paid At: {$invoice->paid_at->format('Y-m-d H:i:s')}" : '') . "

Thank you for your business!
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        ";
    }
}
