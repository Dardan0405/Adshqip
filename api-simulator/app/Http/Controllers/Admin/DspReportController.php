<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdExchange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DspReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);

        $summary = $this->buildSummary($filters);
        $dspData = $this->buildDspData($filters);

        // Get filter options
        $exchanges = AdExchange::orderBy('name')->pluck('name', 'id')->toArray();

        return view('admin.dsp-reports.index', [
            'summary' => $summary,
            'dspData' => $dspData,
            'exchanges' => $exchanges,
            'defaults' => [
                'start_date' => now()->subDays(30)->toDateString(),
                'end_date' => now()->toDateString(),
            ],
        ]);
    }

    public function export(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $dspData = $this->buildDspData($filters);

        $filename = 'dsp_report_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($dspData) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Ad-Exchange Name', 'Type', 'Bid Requests', 'Bid Responses', 'Won Responses', 'Floor Price', 'Won Price', 'Win Rate (%)', 'Status']);

            foreach ($dspData as $row) {
                fputcsv($file, [
                    $row->name,
                    $row->type,
                    $row->bid_requests,
                    $row->bid_responses,
                    $row->won_responses,
                    number_format($row->avg_floor_price, 4),
                    number_format($row->avg_won_price, 4),
                    number_format($row->win_rate, 2),
                    ucfirst($row->status),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'exchange_id' => ['nullable', 'integer'],
            'type' => ['nullable', 'in:DSP,SSP,ad_network'],
            'status' => ['nullable', 'in:active,inactive,testing'],
        ]);
    }

    private function buildSummary(array $filters): array
    {
        $startDate = $filters['start_date'] ?? now()->subDays(30)->toDateString();
        $endDate = $filters['end_date'] ?? now()->toDateString();

        // Total bid requests
        $requestsQuery = DB::table('aq_rtb_bid_requests')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if (!empty($filters['exchange_id'])) {
            $requestsQuery->where('exchange_id', $filters['exchange_id']);
        }

        $totalRequests = $requestsQuery->count();

        // Total bid responses
        $responsesQuery = DB::table('aq_rtb_bid_responses')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if (!empty($filters['exchange_id'])) {
            $responsesQuery->where('exchange_id', $filters['exchange_id']);
        }

        $totalResponses = (clone $responsesQuery)->count();
        $wonResponses = (clone $responsesQuery)->where('win', true)->count();

        // Averages
        $avgFloorPrice = DB::table('aq_rtb_bid_requests')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->avg('bid_floor') ?? 0;

        $avgWonPrice = DB::table('aq_rtb_bid_responses')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('win', true)
            ->avg('win_price') ?? 0;

        // Active exchanges
        $activeExchanges = AdExchange::where('status', 'active')->count();

        return [
            'total_requests' => $totalRequests,
            'total_responses' => $totalResponses,
            'won_responses' => $wonResponses,
            'response_rate' => $totalRequests > 0 ? round(($totalResponses / $totalRequests) * 100, 2) : 0,
            'win_rate' => $totalResponses > 0 ? round(($wonResponses / $totalResponses) * 100, 2) : 0,
            'avg_floor_price' => round($avgFloorPrice, 4),
            'avg_won_price' => round($avgWonPrice, 4),
            'active_exchanges' => $activeExchanges,
        ];
    }

    private function buildDspData(array $filters)
    {
        $startDate = $filters['start_date'] ?? now()->subDays(30)->toDateString();
        $endDate = $filters['end_date'] ?? now()->toDateString();

        $query = DB::table('aq_ad_exchanges as ex')
            ->leftJoin(DB::raw("(
                SELECT exchange_id,
                       COUNT(*) as bid_requests,
                       AVG(bid_floor) as avg_floor_price
                FROM aq_rtb_bid_requests
                WHERE created_at BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'
                GROUP BY exchange_id
            ) as req"), 'ex.id', '=', 'req.exchange_id')
            ->leftJoin(DB::raw("(
                SELECT exchange_id,
                       COUNT(*) as bid_responses,
                       SUM(CASE WHEN win = 1 THEN 1 ELSE 0 END) as won_responses,
                       AVG(CASE WHEN win = 1 THEN win_price ELSE NULL END) as avg_won_price
                FROM aq_rtb_bid_responses
                WHERE created_at BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'
                GROUP BY exchange_id
            ) as resp"), 'ex.id', '=', 'resp.exchange_id')
            ->select([
                'ex.id',
                'ex.name',
                'ex.type',
                'ex.endpoint_url',
                'ex.auction_currency',
                'ex.status',
                DB::raw('COALESCE(req.bid_requests, 0) as bid_requests'),
                DB::raw('COALESCE(req.avg_floor_price, 0) as avg_floor_price'),
                DB::raw('COALESCE(resp.bid_responses, 0) as bid_responses'),
                DB::raw('COALESCE(resp.won_responses, 0) as won_responses'),
                DB::raw('COALESCE(resp.avg_won_price, 0) as avg_won_price'),
                DB::raw('CASE WHEN COALESCE(resp.bid_responses, 0) > 0 THEN ROUND((COALESCE(resp.won_responses, 0) / COALESCE(resp.bid_responses, 0)) * 100, 2) ELSE 0 END as win_rate'),
            ]);

        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('ex.name', 'like', "%{$search}%")
                  ->orWhere('ex.endpoint_url', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['exchange_id'])) {
            $query->where('ex.id', $filters['exchange_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('ex.type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('ex.status', $filters['status']);
        }

        return $query->orderByDesc('bid_requests')->get();
    }
}
