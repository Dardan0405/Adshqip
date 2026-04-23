<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\StatDaily;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OverviewReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = $this->baseQuery($request)
            ->orderByDesc('date')
            ->paginate(20)
            ->withQueryString();

        $summary = $this->summaryQuery($request)->first();

        return view('advertiser.reports.overview', [
            'reports' => $reports,
            'summary' => (object) [
                'total_impressions' => (int) ($summary->total_impressions ?? 0),
                'total_clicks' => (int) ($summary->total_clicks ?? 0),
                'total_conversions' => (int) ($summary->total_conversions ?? 0),
                'total_spend' => (float) ($summary->total_spend ?? 0),
                'ctr' => (float) ($summary->ctr ?? 0),
                'ecpm' => (float) ($summary->ecpm ?? 0),
            ],
        ]);
    }

    public function export(Request $request)
    {
        $reports = $this->baseQuery($request)
            ->orderByDesc('date')
            ->get();

        $filename = 'advertiser_overview_report_' . now()->format('Y-m-d_His') . '.csv';

        $callback = function () use ($reports) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Impressions', 'Clicks', 'Conversions', 'Spend', 'CTR', 'ECPM']);

            foreach ($reports as $row) {
                fputcsv($file, [
                    Carbon::parse($row->date)->format('Y-m-d'),
                    (int) $row->total_impressions,
                    (int) $row->total_clicks,
                    (int) $row->total_conversions,
                    number_format((float) $row->total_spend, 2, '.', ''),
                    number_format((float) $row->ctr, 2, '.', ''),
                    number_format((float) $row->ecpm, 2, '.', ''),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function baseQuery(Request $request)
    {
        return $this->filteredStats($request)
            ->selectRaw('
                date,
                SUM(impressions) as total_impressions,
                SUM(clicks) as total_clicks,
                SUM(conversions) as total_conversions,
                SUM(revenue) as total_spend,
                CASE
                    WHEN SUM(impressions) > 0
                    THEN ROUND((SUM(clicks) / SUM(impressions)) * 100, 2)
                    ELSE 0
                END as ctr,
                CASE
                    WHEN SUM(impressions) > 0
                    THEN ROUND((SUM(revenue) / SUM(impressions)) * 1000, 2)
                    ELSE 0
                END as ecpm
            ')
            ->groupBy('date');
    }

    private function summaryQuery(Request $request)
    {
        return $this->filteredStats($request)
            ->selectRaw('
                SUM(impressions) as total_impressions,
                SUM(clicks) as total_clicks,
                SUM(conversions) as total_conversions,
                SUM(revenue) as total_spend,
                CASE
                    WHEN SUM(impressions) > 0
                    THEN ROUND((SUM(clicks) / SUM(impressions)) * 100, 2)
                    ELSE 0
                END as ctr,
                CASE
                    WHEN SUM(impressions) > 0
                    THEN ROUND((SUM(revenue) / SUM(impressions)) * 1000, 2)
                    ELSE 0
                END as ecpm
            ');
    }

    private function filteredStats(Request $request)
    {
        $query = StatDaily::query()
            ->where('advertiser_id', auth()->id());

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->input('end_date'));
        }

        return $query;
    }
}
