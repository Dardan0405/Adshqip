<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StatDaily;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PublisherPaymentHistoryController extends Controller
{
    /**
     * Display a listing of publisher payment history.
     */
    public function index(Request $request)
    {
        // Get earnings by month and publisher
        $earningsQuery = StatDaily::selectRaw("
                DATE_FORMAT(aq_stats_daily.date, '%Y-%m') as month,
                aq_stats_daily.publisher_id,
                SUM(aq_stats_daily.revenue) as total_earnings
            ")
            ->join('aq_users', 'aq_stats_daily.publisher_id', '=', 'aq_users.id')
            ->where('aq_users.role', 'publisher')
            ->whereNotNull('aq_stats_daily.publisher_id')
            ->groupBy('month', 'publisher_id');

        // Get the data
        $earningsData = $earningsQuery->get();

        // Convert to collection for filtering
        $collection = collect($earningsData)->map(function ($item) {
            return (object) [
                'month' => $item->month,
                'publisher_id' => $item->publisher_id,
                'total_earnings' => $item->total_earnings ?? 0,
            ];
        });

        // Get publisher info
        $publisherIds = $collection->pluck('publisher_id')->unique();
        $publishers = User::whereIn('id', $publisherIds)
            ->with('userProfile')
            ->get()
            ->keyBy('id');

        // Apply filters
        if ($search = $request->get('search')) {
            $collection = $collection->filter(function ($item) use ($publishers, $search) {
                $publisher = $publishers->get($item->publisher_id);
                if (!$publisher) return false;

                $name = trim(($publisher->userProfile->first_name ?? '') . ' ' . ($publisher->userProfile->last_name ?? ''));
                $email = $publisher->email ?? '';

                return stripos($name, $search) !== false || stripos($email, $search) !== false;
            });
        }

        if ($publisherId = $request->get('publisher_id')) {
            $collection = $collection->filter(fn($item) => $item->publisher_id == $publisherId);
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
        $totalEarnings = $collection->sum('total_earnings');
        $activePublishers = $collection->pluck('publisher_id')->unique()->count();

        // Current month stats
        $currentMonth = date('Y-m');
        $currentMonthData = $collection->where('month', $currentMonth);
        $currentMonthEarnings = $currentMonthData->sum('total_earnings');

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

        // Attach publisher info to each payment
        foreach ($payments as $payment) {
            $publisher = $publishers->get($payment->publisher_id);
            $payment->publisher_name = $publisher
                ? trim(($publisher->userProfile->first_name ?? '') . ' ' . ($publisher->userProfile->last_name ?? ''))
                : 'Unknown';
            $payment->publisher_email = $publisher->email ?? 'N/A';

            // Format month for display
            $payment->month_formatted = Carbon::parse($payment->month . '-01')->format('F Y');
        }

        // Get all publishers for filter dropdown
        $allPublishers = User::where('role', 'publisher')
            ->with('userProfile')
            ->orderBy('email')
            ->get();

        return view('admin.publisher-payment-history.index', compact(
            'payments',
            'totalEarnings',
            'activePublishers',
            'currentMonthEarnings',
            'allPublishers'
        ));
    }

    /**
     * Export publisher payment history to CSV.
     */
    public function export(Request $request)
    {
        // Get earnings by month and publisher
        $earningsQuery = StatDaily::selectRaw("
                DATE_FORMAT(aq_stats_daily.date, '%Y-%m') as month,
                aq_stats_daily.publisher_id,
                SUM(aq_stats_daily.revenue) as total_earnings
            ")
            ->join('aq_users', 'aq_stats_daily.publisher_id', '=', 'aq_users.id')
            ->where('aq_users.role', 'publisher')
            ->whereNotNull('aq_stats_daily.publisher_id')
            ->groupBy('month', 'publisher_id');

        // Get the data
        $earningsData = $earningsQuery->get();

        // Convert to collection for filtering
        $collection = collect($earningsData)->map(function ($item) {
            return (object) [
                'month' => $item->month,
                'publisher_id' => $item->publisher_id,
                'total_earnings' => $item->total_earnings ?? 0,
            ];
        });

        // Get publisher info
        $publisherIds = $collection->pluck('publisher_id')->unique();
        $publishers = User::whereIn('id', $publisherIds)
            ->with('userProfile')
            ->get()
            ->keyBy('id');

        // Apply same filters as index
        if ($search = $request->get('search')) {
            $collection = $collection->filter(function ($item) use ($publishers, $search) {
                $publisher = $publishers->get($item->publisher_id);
                if (!$publisher) return false;

                $name = trim(($publisher->userProfile->first_name ?? '') . ' ' . ($publisher->userProfile->last_name ?? ''));
                $email = $publisher->email ?? '';

                return stripos($name, $search) !== false || stripos($email, $search) !== false;
            });
        }

        if ($publisherId = $request->get('publisher_id')) {
            $collection = $collection->filter(fn($item) => $item->publisher_id == $publisherId);
        }

        if ($startMonth = $request->get('start_month')) {
            $collection = $collection->filter(fn($item) => $item->month >= $startMonth);
        }

        if ($endMonth = $request->get('end_month')) {
            $collection = $collection->filter(fn($item) => $item->month <= $endMonth);
        }

        // Sort by month descending
        $collection = $collection->sortByDesc('month')->values();

        // Attach publisher info
        foreach ($collection as $payment) {
            $publisher = $publishers->get($payment->publisher_id);
            $payment->publisher_name = $publisher
                ? trim(($publisher->userProfile->first_name ?? '') . ' ' . ($publisher->userProfile->last_name ?? ''))
                : 'Unknown';
            $payment->publisher_email = $publisher->email ?? 'N/A';
            $payment->month_formatted = Carbon::parse($payment->month . '-01')->format('F Y');
        }

        // Generate filename
        $filename = 'publisher_payment_history_' . date('Y-m-d_His') . '.csv';

        // Set headers
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        // Stream CSV
        $callback = function () use ($collection) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, ['Invoice Period', 'Publisher Name', 'Publisher Email', 'Earnings (€)']);

            // Add data rows
            foreach ($collection as $payment) {
                fputcsv($file, [
                    $payment->month_formatted,
                    $payment->publisher_name,
                    $payment->publisher_email,
                    number_format($payment->total_earnings, 2, '.', ''),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
