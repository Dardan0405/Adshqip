<?php

namespace App\Http\Controllers;

use App\Models\StatDaily;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublisherOverviewReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = $this->baseQuery($request)
            ->orderByDesc('date')
            ->paginate(20)
            ->withQueryString();

        $summary = $this->summaryQuery($request)->first();

        return view('publisher.reports.overview', [
            'reports'  => $reports,
            'summary'  => (object) [
                'total_impressions' => (int) ($summary->total_impressions ?? 0),
                'total_clicks'      => (int) ($summary->total_clicks ?? 0),
                'total_earnings'    => (float) ($summary->total_earnings ?? 0),
                'ecpm'              => (float) ($summary->ecpm ?? 0),
            ],
        ]);
    }

    public function export(Request $request)
    {
        $reports  = $this->baseQuery($request)->orderByDesc('date')->get();
        $filename = 'publisher_overview_report_' . now()->format('Y-m-d_His') . '.csv';

        $callback = function () use ($reports) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Impressions', 'Clicks', 'Earnings', 'ECPM']);

            foreach ($reports as $row) {
                fputcsv($file, [
                    Carbon::parse($row->date)->format('Y-m-d'),
                    (int) $row->total_impressions,
                    (int) $row->total_clicks,
                    number_format((float) $row->total_earnings, 4, '.', ''),
                    number_format((float) $row->ecpm, 2, '.', ''),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
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
                SUM(publisher_earnings) as total_earnings,
                CASE
                    WHEN SUM(impressions) > 0
                    THEN ROUND((SUM(publisher_earnings) / SUM(impressions)) * 1000, 2)
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
                SUM(publisher_earnings) as total_earnings,
                CASE
                    WHEN SUM(impressions) > 0
                    THEN ROUND((SUM(publisher_earnings) / SUM(impressions)) * 1000, 2)
                    ELSE 0
                END as ecpm
            ');
    }

    private function filteredStats(Request $request)
    {
        $query = StatDaily::query()->where('publisher_id', Auth::id());

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->input('end_date'));
        }

        return $query;
    }
}
