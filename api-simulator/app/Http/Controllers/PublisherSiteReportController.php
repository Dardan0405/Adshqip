<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PublisherSiteReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $reports = $this->buildRows($filters);
        $summary = $this->buildSummary($filters);

        return view('publisher.reports.sites', [
            'reports' => $reports,
            'summary' => $summary,
            'defaults' => [
                'start_date' => now()->subDays(30)->toDateString(),
                'end_date'   => now()->toDateString(),
            ],
        ]);
    }

    public function export(Request $request)
    {
        $filters  = $this->validatedFilters($request);
        $rows     = $this->buildRows($filters, paginate: false);
        $filename = 'publisher_site_report_' . now()->format('Y-m-d_His') . '.csv';

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Site', 'Site URL', 'Impressions', 'Clicks', 'Earnings', 'ECPM']);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->site_name,
                    $row->site_url,
                    (int) $row->impressions,
                    (int) $row->clicks,
                    number_format((float) $row->earnings, 4, '.', ''),
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

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'search'     => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
    }

    private function baseQuery(array $filters)
    {
        $startDate = $filters['start_date'] ?? now()->subDays(30)->toDateString();
        $endDate   = $filters['end_date']   ?? now()->toDateString();

        $query = DB::table('aq_stats_daily as stats')
            ->leftJoin('aq_sites as sites', 'stats.site_id', '=', 'sites.id')
            ->where('stats.publisher_id', Auth::id())
            ->whereBetween('stats.date', [$startDate, $endDate]);

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('sites.name', 'like', '%' . $search . '%')
                  ->orWhere('sites.domain', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }

    private function buildSummary(array $filters): array
    {
        $row = $this->baseQuery($filters)
            ->selectRaw('
                COUNT(DISTINCT stats.site_id) as total_sites,
                COALESCE(SUM(stats.impressions), 0) as impressions,
                COALESCE(SUM(stats.clicks), 0) as clicks,
                COALESCE(SUM(stats.publisher_earnings), 0) as earnings,
                CASE
                    WHEN SUM(stats.impressions) > 0
                    THEN ROUND((SUM(stats.publisher_earnings) / SUM(stats.impressions)) * 1000, 2)
                    ELSE 0
                END as ecpm
            ')
            ->first();

        return [
            'total_sites' => (int) ($row->total_sites ?? 0),
            'impressions' => (int) ($row->impressions ?? 0),
            'clicks'      => (int) ($row->clicks ?? 0),
            'earnings'    => (float) ($row->earnings ?? 0),
            'ecpm'        => (float) ($row->ecpm ?? 0),
        ];
    }

    private function buildRows(array $filters, bool $paginate = true)
    {
        $query = $this->baseQuery($filters)
            ->selectRaw("
                COALESCE(sites.name, 'Unknown Site') as site_name,
                COALESCE(sites.domain, '') as domain,
                COALESCE(SUM(stats.impressions), 0) as impressions,
                COALESCE(SUM(stats.clicks), 0) as clicks,
                COALESCE(SUM(stats.publisher_earnings), 0) as earnings,
                CASE
                    WHEN SUM(stats.impressions) > 0
                    THEN ROUND((SUM(stats.publisher_earnings) / SUM(stats.impressions)) * 1000, 2)
                    ELSE 0
                END as ecpm
            ")
            ->groupBy('sites.id', 'sites.name', 'sites.domain')
            ->orderByDesc('impressions');

        if (! $paginate) {
            return $query->get()->map(fn ($r) => $this->decorateRow($r));
        }

        $rows = $query->paginate(20)->withQueryString();
        $rows->getCollection()->transform(fn ($r) => $this->decorateRow($r));

        return $rows;
    }

    private function decorateRow(object $row): object
    {
        $domain = (string) $row->domain;
        $row->site_url = $domain !== '' && ! Str::startsWith($domain, ['http://', 'https://'])
            ? 'https://' . $domain
            : ($domain ?: '#');

        return $row;
    }
}
