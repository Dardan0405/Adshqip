<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\StatDaily;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentHistoryController extends Controller
{
    /**
     * Display a listing of payment history.
     */
    public function index(Request $request)
    {
        $transactionDate = 'COALESCE(aq_transactions.completed_at, aq_transactions.updated_at, aq_transactions.created_at)';
        $transactionMonth = $this->monthExpression($transactionDate);
        $statMonth = $this->monthExpression('aq_stats_daily.date');

        // Get deposits by month and advertiser
        $depositsQuery = Transaction::selectRaw("
                {$transactionMonth} as month,
                aq_transactions.user_id as advertiser_id,
                SUM(aq_transactions.amount) as total_deposits,
                SUM(CASE WHEN aq_transactions.payment_gateway = 'paypal' THEN aq_transactions.amount ELSE 0 END) as paypal_deposits,
                SUM(CASE WHEN aq_transactions.payment_gateway IN ('bitcoin', 'coinbase', 'crypto') THEN aq_transactions.amount ELSE 0 END) as bitcoin_deposits,
                SUM(CASE WHEN aq_transactions.payment_gateway IN ('wire_transfer', 'manual') THEN aq_transactions.amount ELSE 0 END) as bank_wire_deposits,
                SUM(CASE WHEN aq_transactions.payment_gateway = 'stripe' THEN aq_transactions.amount ELSE 0 END) as stripe_deposits,
                SUM(CASE WHEN aq_transactions.payment_gateway = 'authorize' THEN aq_transactions.amount ELSE 0 END) as authorize_deposits
            ")
            ->join('aq_users', 'aq_transactions.user_id', '=', 'aq_users.id')
            ->where('aq_users.role', 'advertiser')
            ->where('aq_transactions.type', 'deposit')
            ->where('aq_transactions.status', 'completed')
            ->groupBy('month', 'advertiser_id');

        // Get spend by month and advertiser
        $spendQuery = StatDaily::selectRaw("
                {$statMonth} as month,
                aq_stats_daily.advertiser_id,
                SUM(aq_stats_daily.revenue) as total_spend
            ")
            ->join('aq_users', 'aq_stats_daily.advertiser_id', '=', 'aq_users.id')
            ->where('aq_users.role', 'advertiser')
            ->whereNotNull('aq_stats_daily.advertiser_id')
            ->groupBy('month', 'advertiser_id');

        // Combine deposits and spend data
        $depositsData = $depositsQuery->get()->keyBy(fn($item) => $item->month . '_' . $item->advertiser_id);
        $spendData = $spendQuery->get()->keyBy(fn($item) => $item->month . '_' . $item->advertiser_id);

        // Get all unique month-advertiser combinations
        $allKeys = collect($depositsData->keys())->merge($spendData->keys())->unique();

        // Merge the data
        $paymentData = [];
        foreach ($allKeys as $key) {
            list($month, $advertiserId) = explode('_', $key);

            $paymentData[] = (object) [
                'month' => $month,
                'advertiser_id' => $advertiserId,
                'total_deposits' => $depositsData->get($key)->total_deposits ?? 0,
                'paypal_deposits' => $depositsData->get($key)->paypal_deposits ?? 0,
                'bitcoin_deposits' => $depositsData->get($key)->bitcoin_deposits ?? 0,
                'bank_wire_deposits' => $depositsData->get($key)->bank_wire_deposits ?? 0,
                'stripe_deposits' => $depositsData->get($key)->stripe_deposits ?? 0,
                'authorize_deposits' => $depositsData->get($key)->authorize_deposits ?? 0,
                'total_spend' => $spendData->get($key)->total_spend ?? 0,
            ];
        }

        // Convert to collection for filtering
        $collection = collect($paymentData);

        // Get advertiser info
        $advertiserIds = $collection->pluck('advertiser_id')->unique();
        $advertisers = User::whereIn('id', $advertiserIds)
            ->with('userProfile')
            ->get()
            ->keyBy('id');

        // Apply filters
        if ($search = trim((string) $request->get('search'))) {
            $collection = $collection->filter(function ($item) use ($advertisers, $search) {
                $advertiser = $advertisers->get($item->advertiser_id);
                if (!$advertiser) return false;

                $name = trim(($advertiser->userProfile->first_name ?? '') . ' ' . ($advertiser->userProfile->last_name ?? ''));
                $email = $advertiser->email ?? '';

                return stripos($name, $search) !== false || stripos($email, $search) !== false;
            });
        }

        if ($advertiserId = $request->get('advertiser_id')) {
            $collection = $collection->filter(fn($item) => $item->advertiser_id == $advertiserId);
        }

        if ($startMonth = $request->get('start_month')) {
            $collection = $collection->filter(fn($item) => $item->month >= $startMonth);
        }

        if ($endMonth = $request->get('end_month')) {
            $collection = $collection->filter(fn($item) => $item->month <= $endMonth);
        }

        // Sort by month descending
        $collection = $collection->sortByDesc('month')->values();

        // Calculate summary stats
        $totalDeposits = $collection->sum('total_deposits');
        $totalSpend = $collection->sum('total_spend');
        $activeAdvertisers = $collection->pluck('advertiser_id')->unique()->count();

        // Current month stats
        $currentMonth = date('Y-m');
        $currentMonthData = $collection->where('month', $currentMonth);
        $currentMonthDeposits = $currentMonthData->sum('total_deposits');
        $currentMonthSpend = $currentMonthData->sum('total_spend');

        // Paginate manually
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedData = $collection->slice($offset, $perPage)->values();

        // Create paginator
        $payments = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedData,
            $collection->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Attach advertiser info to each payment
        foreach ($payments as $payment) {
            $advertiser = $advertisers->get($payment->advertiser_id);
            $payment->advertiser_name = $advertiser
                ? trim(($advertiser->userProfile->first_name ?? '') . ' ' . ($advertiser->userProfile->last_name ?? ''))
                : 'Unknown';
            $payment->advertiser_email = $advertiser->email ?? 'N/A';

            // Format month for display
            $payment->month_formatted = Carbon::parse($payment->month . '-01')->format('F Y');
        }

        // Get all advertisers for filter dropdown
        $allAdvertisers = User::where('role', 'advertiser')
            ->with('userProfile')
            ->orderBy('email')
            ->get();

        return view('admin.payment-history.index', compact(
            'payments',
            'totalDeposits',
            'totalSpend',
            'activeAdvertisers',
            'currentMonthDeposits',
            'currentMonthSpend',
            'allAdvertisers'
        ));
    }

    /**
     * Export payment history to CSV.
     */
    public function export(Request $request)
    {
        $transactionDate = 'COALESCE(aq_transactions.completed_at, aq_transactions.updated_at, aq_transactions.created_at)';
        $transactionMonth = $this->monthExpression($transactionDate);
        $statMonth = $this->monthExpression('aq_stats_daily.date');

        // Get deposits by month and advertiser
        $depositsQuery = Transaction::selectRaw("
                {$transactionMonth} as month,
                aq_transactions.user_id as advertiser_id,
                SUM(aq_transactions.amount) as total_deposits,
                SUM(CASE WHEN aq_transactions.payment_gateway = 'paypal' THEN aq_transactions.amount ELSE 0 END) as paypal_deposits,
                SUM(CASE WHEN aq_transactions.payment_gateway IN ('bitcoin', 'coinbase', 'crypto') THEN aq_transactions.amount ELSE 0 END) as bitcoin_deposits,
                SUM(CASE WHEN aq_transactions.payment_gateway IN ('wire_transfer', 'manual') THEN aq_transactions.amount ELSE 0 END) as bank_wire_deposits,
                SUM(CASE WHEN aq_transactions.payment_gateway = 'stripe' THEN aq_transactions.amount ELSE 0 END) as stripe_deposits,
                SUM(CASE WHEN aq_transactions.payment_gateway = 'authorize' THEN aq_transactions.amount ELSE 0 END) as authorize_deposits
            ")
            ->join('aq_users', 'aq_transactions.user_id', '=', 'aq_users.id')
            ->where('aq_users.role', 'advertiser')
            ->where('aq_transactions.type', 'deposit')
            ->where('aq_transactions.status', 'completed')
            ->groupBy('month', 'advertiser_id');

        // Get spend by month and advertiser
        $spendQuery = StatDaily::selectRaw("
                {$statMonth} as month,
                aq_stats_daily.advertiser_id,
                SUM(aq_stats_daily.revenue) as total_spend
            ")
            ->join('aq_users', 'aq_stats_daily.advertiser_id', '=', 'aq_users.id')
            ->where('aq_users.role', 'advertiser')
            ->whereNotNull('aq_stats_daily.advertiser_id')
            ->groupBy('month', 'advertiser_id');

        // Combine deposits and spend data
        $depositsData = $depositsQuery->get()->keyBy(fn($item) => $item->month . '_' . $item->advertiser_id);
        $spendData = $spendQuery->get()->keyBy(fn($item) => $item->month . '_' . $item->advertiser_id);

        // Get all unique month-advertiser combinations
        $allKeys = collect($depositsData->keys())->merge($spendData->keys())->unique();

        // Merge the data
        $paymentData = [];
        foreach ($allKeys as $key) {
            list($month, $advertiserId) = explode('_', $key);

            $paymentData[] = (object) [
                'month' => $month,
                'advertiser_id' => $advertiserId,
                'total_deposits' => $depositsData->get($key)->total_deposits ?? 0,
                'paypal_deposits' => $depositsData->get($key)->paypal_deposits ?? 0,
                'bitcoin_deposits' => $depositsData->get($key)->bitcoin_deposits ?? 0,
                'bank_wire_deposits' => $depositsData->get($key)->bank_wire_deposits ?? 0,
                'stripe_deposits' => $depositsData->get($key)->stripe_deposits ?? 0,
                'authorize_deposits' => $depositsData->get($key)->authorize_deposits ?? 0,
                'total_spend' => $spendData->get($key)->total_spend ?? 0,
            ];
        }

        // Convert to collection for filtering
        $collection = collect($paymentData);

        // Get advertiser info
        $advertiserIds = $collection->pluck('advertiser_id')->unique();
        $advertisers = User::whereIn('id', $advertiserIds)
            ->with('userProfile')
            ->get()
            ->keyBy('id');

        // Apply same filters as index
        if ($search = trim((string) $request->get('search'))) {
            $collection = $collection->filter(function ($item) use ($advertisers, $search) {
                $advertiser = $advertisers->get($item->advertiser_id);
                if (!$advertiser) return false;

                $name = trim(($advertiser->userProfile->first_name ?? '') . ' ' . ($advertiser->userProfile->last_name ?? ''));
                $email = $advertiser->email ?? '';

                return stripos($name, $search) !== false || stripos($email, $search) !== false;
            });
        }

        if ($advertiserId = $request->get('advertiser_id')) {
            $collection = $collection->filter(fn($item) => $item->advertiser_id == $advertiserId);
        }

        if ($startMonth = $request->get('start_month')) {
            $collection = $collection->filter(fn($item) => $item->month >= $startMonth);
        }

        if ($endMonth = $request->get('end_month')) {
            $collection = $collection->filter(fn($item) => $item->month <= $endMonth);
        }

        // Sort by month descending
        $collection = $collection->sortByDesc('month')->values();

        // Attach advertiser info
        foreach ($collection as $payment) {
            $advertiser = $advertisers->get($payment->advertiser_id);
            $payment->advertiser_name = $advertiser
                ? trim(($advertiser->userProfile->first_name ?? '') . ' ' . ($advertiser->userProfile->last_name ?? ''))
                : 'Unknown';
            $payment->advertiser_email = $advertiser->email ?? 'N/A';
            $payment->month_formatted = Carbon::parse($payment->month . '-01')->format('F Y');
        }

        // Generate filename
        $filename = 'payment_history_' . date('Y-m-d_His') . '.csv';

        // Set headers
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        // Stream CSV
        $currencyCode = app(\App\Support\AdminCurrency::class)->code();

        $callback = function () use ($collection, $currencyCode) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, ['Deposit Month', 'Advertiser Name', 'Advertiser Email', "Total Deposits ({$currencyCode})", "PayPal ({$currencyCode})", "Bitcoin ({$currencyCode})", "Bank Wire ({$currencyCode})", "Stripe ({$currencyCode})", "Authorize.net ({$currencyCode})", "Spend Amount ({$currencyCode})"]);

            // Add data rows
            foreach ($collection as $payment) {
                fputcsv($file, [
                    $payment->month_formatted,
                    $payment->advertiser_name,
                    $payment->advertiser_email,
                    number_format($payment->total_deposits, 2, '.', ''),
                    number_format($payment->paypal_deposits, 2, '.', ''),
                    number_format($payment->bitcoin_deposits, 2, '.', ''),
                    number_format($payment->bank_wire_deposits, 2, '.', ''),
                    number_format($payment->stripe_deposits, 2, '.', ''),
                    number_format($payment->authorize_deposits, 2, '.', ''),
                    number_format($payment->total_spend, 2, '.', ''),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function monthExpression(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', {$column})"
            : "DATE_FORMAT({$column}, '%Y-%m')";
    }
}
