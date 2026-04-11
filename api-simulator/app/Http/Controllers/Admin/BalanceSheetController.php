<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class BalanceSheetController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'start_month' => ['nullable', 'date_format:Y-m'],
            'end_month' => ['nullable', 'date_format:Y-m'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $balanceSheet = $this->buildBalanceSheetCollection(
            $request->string('start_month')->toString(),
            $request->string('end_month')->toString()
        );

        $totalDeposits = $balanceSheet->sum('advertiser_deposits');
        $totalPayments = $balanceSheet->sum('publisher_payments');
        $netBalance = $totalDeposits - $totalPayments;

        $currentMonth = date('Y-m');
        $currentMonthRow = $balanceSheet->firstWhere('month', $currentMonth);
        $currentMonthDeposits = $currentMonthRow->advertiser_deposits ?? 0;
        $currentMonthPayments = $currentMonthRow->publisher_payments ?? 0;

        $perPage = 20;
        $currentPage = (int) $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedData = $balanceSheet->slice($offset, $perPage)->values();

        $rows = new LengthAwarePaginator(
            $paginatedData,
            $balanceSheet->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.balance-sheet.index', compact(
            'rows',
            'totalDeposits',
            'totalPayments',
            'netBalance',
            'currentMonthDeposits',
            'currentMonthPayments'
        ));
    }

    public function export(Request $request)
    {
        $request->validate([
            'start_month' => ['nullable', 'date_format:Y-m'],
            'end_month' => ['nullable', 'date_format:Y-m'],
        ]);

        $balanceSheet = $this->buildBalanceSheetCollection(
            $request->string('start_month')->toString(),
            $request->string('end_month')->toString()
        );

        $filename = 'balance_sheet_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($balanceSheet) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Month', 'Advertiser Deposits (EUR)', 'Publisher Payments (EUR)']);

            foreach ($balanceSheet as $row) {
                fputcsv($file, [
                    $row->month_formatted,
                    number_format($row->advertiser_deposits, 2, '.', ''),
                    number_format($row->publisher_payments, 2, '.', ''),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function buildBalanceSheetCollection(?string $startMonth = null, ?string $endMonth = null)
    {
        $deposits = Transaction::query()
            ->completedAdvertiserDeposits()
            ->whereNotNull('aq_transactions.completed_at')
            ->selectRaw("
                DATE_FORMAT(aq_transactions.completed_at, '%Y-%m') as month,
                SUM(aq_transactions.amount) as total_deposits
            ")
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $payments = Invoice::query()
            ->paidPublisherPayouts()
            ->whereNotNull('aq_invoices.paid_at')
            ->selectRaw("
                DATE_FORMAT(aq_invoices.paid_at, '%Y-%m') as month,
                SUM(aq_invoices.total_amount) as total_payments
            ")
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $allMonths = collect($deposits->keys())
            ->merge($payments->keys())
            ->unique()
            ->sort()
            ->reverse()
            ->values();

        if ($startMonth) {
            $allMonths = $allMonths->filter(fn ($month) => $month >= $startMonth)->values();
        }

        if ($endMonth) {
            $allMonths = $allMonths->filter(fn ($month) => $month <= $endMonth)->values();
        }

        return $allMonths->map(function ($month) use ($deposits, $payments) {
            $depositsAmount = (float) ($deposits->get($month)->total_deposits ?? 0);
            $paymentsAmount = (float) ($payments->get($month)->total_payments ?? 0);

            return (object) [
                'month' => $month,
                'month_formatted' => Carbon::createFromFormat('Y-m-d', $month . '-01')->format('F Y'),
                'advertiser_deposits' => $depositsAmount,
                'publisher_payments' => $paymentsAmount,
                'net_balance' => $depositsAmount - $paymentsAmount,
            ];
        })->values();
    }
}
