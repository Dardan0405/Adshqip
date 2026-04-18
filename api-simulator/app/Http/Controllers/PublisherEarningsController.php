<?php

namespace App\Http\Controllers;

use App\Models\PublisherEarning;
use App\Support\PublisherPaymentManager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class PublisherEarningsController extends Controller
{
    public function index(Request $request)
    {
        app(PublisherPaymentManager::class)->syncAutoInvoices();

        $request->validate([
            'start_month' => ['nullable', 'date_format:Y-m'],
            'end_month' => ['nullable', 'date_format:Y-m'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $publisherId = Auth::id();

        $dailyRows = PublisherEarning::query()
            ->forPublisher($publisherId)
            ->betweenMonths($request->get('start_month'), $request->get('end_month'))
            ->where('aq_stats_daily.publisher_earnings', '>', 0)
            ->orderByDesc('aq_stats_daily.date')
            ->get([
                'aq_stats_daily.id',
                'aq_stats_daily.date',
                'aq_stats_daily.publisher_earnings',
            ]);

        $grouped = $dailyRows
            ->groupBy(fn ($row) => $row->date->format('Y-m'))
            ->map(function ($items, $month) {
                $dailyBreakdown = $items
                    ->sortByDesc('date')
                    ->values()
                    ->map(fn ($item) => (object) [
                        'date' => $item->date->format('Y-m-d'),
                        'date_formatted' => $item->date->format('M d, Y'),
                        'revenue' => (float) $item->publisher_earnings,
                    ]);

                return (object) [
                    'month' => $month,
                    'month_formatted' => Carbon::createFromFormat('Y-m-d', $month . '-01')->format('F Y'),
                    'revenue' => (float) $items->sum('publisher_earnings'),
                    'daily_breakdown' => $dailyBreakdown,
                ];
            })
            ->sortByDesc('month')
            ->values();

        $perPage = 12;
        $currentPage = (int) $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $pageItems = $grouped->slice($offset, $perPage)->values();

        $earnings = $pageItems->map(function ($item, $index) use ($offset) {
            $item->row_id = $offset + $index + 1;
            return $item;
        });

        $rows = new LengthAwarePaginator(
            $earnings,
            $grouped->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $totalRevenue = $grouped->sum('revenue');
        $currentMonthRevenue = optional($grouped->firstWhere('month', now()->format('Y-m')))->revenue ?? 0;
        $activeMonths = $grouped->count();

        return view('publisher.earnings.index', compact(
            'rows',
            'totalRevenue',
            'currentMonthRevenue',
            'activeMonths'
        ));
    }
}
