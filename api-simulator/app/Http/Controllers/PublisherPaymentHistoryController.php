<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payout;
use App\Models\StatDaily;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublisherPaymentHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        $earningsRaw = StatDaily::where('publisher_id', $userId)
            ->selectRaw('YEAR(date) as year, MONTH(date) as month, SUM(publisher_earnings) as total_earnings')
            ->groupByRaw('YEAR(date), MONTH(date)')
            ->get()
            ->keyBy(fn($r) => $r->year . '-' . str_pad((string) $r->month, 2, '0', STR_PAD_LEFT));

        $invoicesRaw = Invoice::where('user_id', $userId)
            ->where('type', 'publisher_payout')
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(total_amount) as total_invoiced')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->get()
            ->keyBy(fn($r) => $r->year . '-' . str_pad((string) $r->month, 2, '0', STR_PAD_LEFT));

        $allKeys = collect($earningsRaw->keys())
            ->merge($invoicesRaw->keys())
            ->unique()
            ->sort()
            ->values();

        $runningBalance = 0.0;
        $rows = $allKeys->map(function ($key) use ($earningsRaw, $invoicesRaw, &$runningBalance) {
            [$year, $month] = explode('-', $key);
            $earned         = (float) ($earningsRaw[$key]->total_earnings ?? 0);
            $invoiced       = (float) ($invoicesRaw[$key]->total_invoiced ?? 0);
            $runningBalance += $earned - $invoiced;

            return [
                'year'           => (int) $year,
                'month'          => (int) $month,
                'earnings'       => $earned,
                'invoice_amount' => $invoiced,
                'balance'        => $runningBalance,
            ];
        })->reverse()->values();

        return view('publisher.payment-history.index', [
            'rows' => $rows,
            'user' => $request->user()->load('profile'),
        ]);
    }

    public function show(Request $request, int $year, int $month): View
    {
        $userId = $request->user()->id;

        $invoices = Invoice::where('user_id', $userId)
            ->where('type', 'publisher_payout')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('created_at')
            ->get();

        $rows = $invoices->map(function ($invoice) use ($userId) {
            $payout   = Payout::where('user_id', $userId)
                ->where('payment_reference', $invoice->invoice_number)
                ->first();

            $earnings = 0.0;
            if ($payout?->period_start && $payout?->period_end) {
                $earnings = (float) StatDaily::where('publisher_id', $userId)
                    ->whereBetween('date', [
                        $payout->period_start->toDateString(),
                        $payout->period_end->toDateString(),
                    ])
                    ->sum('publisher_earnings');
            }

            return [
                'id'             => $invoice->id,
                'payout_id'      => $payout?->id,
                'date'           => $invoice->created_at,
                'earnings'       => $earnings,
                'invoice_amount' => (float) $invoice->total_amount,
                'invoice_number' => $invoice->invoice_number,
                'status'         => $invoice->status,
                'status_color'   => $invoice->status_color,
            ];
        });

        return view('publisher.payment-history.show', [
            'year'          => $year,
            'month'         => $month,
            'rows'          => $rows,
            'totalEarnings' => $rows->sum('earnings'),
            'totalInvoiced' => $rows->sum('invoice_amount'),
            'user'          => $request->user()->load('profile'),
        ]);
    }
}
