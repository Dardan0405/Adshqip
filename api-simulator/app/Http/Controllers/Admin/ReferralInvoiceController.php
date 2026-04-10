<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralPayout;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReferralInvoiceController extends Controller
{
    /**
     * Display a listing of referral invoices.
     */
    public function index(Request $request)
    {
        $query = ReferralPayout::with('referrer.userProfile');

        // Apply filters
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('referrer', function ($userQuery) use ($search) {
                      $userQuery->where('email', 'like', "%{$search}%")
                          ->orWhereHas('userProfile', function ($profileQuery) use ($search) {
                              $profileQuery->where('first_name', 'like', "%{$search}%")
                                  ->orWhere('last_name', 'like', "%{$search}%");
                          });
                  });
            });
        }

        if ($referrerId = $request->get('referrer_id')) {
            $query->where('referrer_id', $referrerId);
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
        $totalInvoices = ReferralPayout::count();
        $totalAmount = ReferralPayout::sum('amount');
        $totalReferrals = ReferralPayout::sum('conversions_count');
        $pendingCount = ReferralPayout::where('status', 'pending')->count();

        // Get all referrers for filter dropdown
        $allReferrers = User::whereHas('referralPayouts')
            ->with('userProfile')
            ->orderBy('email')
            ->get();

        return view('admin.referral-invoices.index', compact(
            'invoices',
            'totalInvoices',
            'totalAmount',
            'totalReferrals',
            'pendingCount',
            'allReferrers'
        ));
    }

    /**
     * Update the status of a referral invoice.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,failed,cancelled',
        ]);

        $payout = ReferralPayout::findOrFail($id);
        $payout->status = $request->status;

        if ($request->status === 'completed') {
            $payout->processed_at = Carbon::now();
        }

        $payout->save();

        return redirect()->back()->with('success', "Invoice {$payout->invoiceNumber} status updated to {$request->status}.");
    }

    /**
     * Download referral invoice.
     */
    public function download($id)
    {
        $payout = ReferralPayout::with('referrer.userProfile')->findOrFail($id);

        $referrerName = $payout->referrer->userProfile
            ? trim($payout->referrer->userProfile->first_name . ' ' . $payout->referrer->userProfile->last_name)
            : $payout->referrer->email;

        $content = "
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            REFERRAL INVOICE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Invoice Number: {$payout->invoiceNumber}
Invoice Date:   {$payout->created_at->format('Y-m-d')}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
BILL TO:
{$referrerName}
{$payout->referrer->email}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Period:           {$payout->period_start->format('M d, Y')} - {$payout->period_end->format('M d, Y')}
Total Referrals:  {$payout->conversions_count}
Payment Method:   " . ucfirst(str_replace('_', ' ', $payout->payment_method)) . "

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
TOTAL EARNING:    {$payout->formattedAmount}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Status: " . strtoupper($payout->status) . "
" . ($payout->processed_at ? "Processed At: {$payout->processed_at->format('Y-m-d H:i:s')}" : '') . "
" . ($payout->notes ? "\nNotes: {$payout->notes}" : '') . "

Thank you for your referrals!
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        ";

        $filename = "referral_invoice_{$payout->invoiceNumber}.txt";

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export referral invoices to CSV.
     */
    public function export(Request $request)
    {
        $query = ReferralPayout::with('referrer.userProfile');

        // Apply same filters as index
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('referrer', function ($userQuery) use ($search) {
                      $userQuery->where('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($referrerId = $request->get('referrer_id')) {
            $query->where('referrer_id', $referrerId);
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

        $filename = 'referral_invoices_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($invoices) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['ID', 'Name', 'Invoice ID', 'Invoice Date', 'Invoice Amount (€)', 'Referrals', 'Referral Earning (€)', 'Status']);

            foreach ($invoices as $payout) {
                $referrerName = $payout->referrer->userProfile
                    ? trim($payout->referrer->userProfile->first_name . ' ' . $payout->referrer->userProfile->last_name)
                    : $payout->referrer->email;

                fputcsv($file, [
                    $payout->id,
                    $referrerName,
                    $payout->invoiceNumber,
                    $payout->created_at->format('Y-m-d'),
                    number_format($payout->amount, 2, '.', ''),
                    $payout->conversions_count,
                    number_format($payout->amount, 2, '.', ''),
                    ucfirst($payout->status),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
