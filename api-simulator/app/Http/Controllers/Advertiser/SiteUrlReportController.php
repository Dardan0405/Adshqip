<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SiteUrlReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $reports = $this->buildRows($filters);
        $summary = $this->buildSummary($filters);

        return view('advertiser.site-url-reports.index', [
            'reports' => $reports,
            'summary' => $summary,
            'defaults' => [
                'start_date' => now()->subDays(30)->toDateString(),
                'end_date' => now()->toDateString(),
            ],
        ]);
    }

    public function export(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $rows = $this->buildRows($filters, false);
        $filename = 'site_url_report_' . now()->format('Y-m-d_His') . '.csv';

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Site URL', 'Domain', 'Referrer', 'Impressions', 'Clicks', 'Conversions', 'CTR']);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->site_url,
                    $row->domain,
                    $row->referrer,
                    (int) $row->impressions,
                    (int) $row->clicks,
                    (int) $row->conversions,
                    number_format((float) $row->ctr, 2, '.', ''),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
    }

    private function dateBounds(array $filters): array
    {
        return [
            ($filters['start_date'] ?? now()->subDays(30)->toDateString()) . ' 00:00:00',
            ($filters['end_date'] ?? now()->toDateString()) . ' 23:59:59',
        ];
    }

    private function baseQuery(array $filters)
    {
        [$startDate, $endDate] = $this->dateBounds($filters);

        $query = DB::table('aq_url_ad_reports as reports')
            ->join('aq_ads as ads', 'reports.ad_id', '=', 'ads.id')
            ->join('aq_campaigns as campaigns', 'reports.campaign_id', '=', 'campaigns.id')
            ->leftJoin('aq_zones as zones', 'reports.zone_id', '=', 'zones.id')
            ->leftJoin('aq_sites as sites', 'zones.site_id', '=', 'sites.id')
            ->where('campaigns.advertiser_id', auth()->id())
            ->whereBetween('reports.created_at', [$startDate, $endDate])
            ->whereNotNull('reports.zone_id');

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($inner) use ($search) {
                $inner->where('sites.domain', 'like', '%' . $search . '%')
                    ->orWhere('sites.name', 'like', '%' . $search . '%')
                    ->orWhere('reports.referrer_url', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }

    private function buildSummary(array $filters): array
    {
        $summary = $this->baseQuery($filters)
            ->selectRaw('
                SUM(CASE WHEN reports.event_type IN ("serve", "view") THEN 1 ELSE 0 END) as impressions,
                SUM(CASE WHEN reports.event_type = "click" THEN 1 ELSE 0 END) as clicks,
                SUM(CASE WHEN reports.event_type = "conversion" THEN 1 ELSE 0 END) as conversions,
                COUNT(DISTINCT sites.id) as sites
            ')
            ->first();

        $impressions = (int) ($summary->impressions ?? 0);
        $clicks = (int) ($summary->clicks ?? 0);

        return [
            'sites' => (int) ($summary->sites ?? 0),
            'impressions' => $impressions,
            'clicks' => $clicks,
            'conversions' => (int) ($summary->conversions ?? 0),
            'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0,
        ];
    }

    private function buildRows(array $filters, bool $paginate = true)
    {
        $query = $this->baseQuery($filters)
            ->selectRaw('
                COALESCE(sites.name, "Unknown Site") as site_name,
                COALESCE(sites.domain, "Unknown") as domain,
                COALESCE(NULLIF(reports.referrer_url, ""), "Direct") as referrer,
                SUM(CASE WHEN reports.event_type IN ("serve", "view") THEN 1 ELSE 0 END) as impressions,
                SUM(CASE WHEN reports.event_type = "click" THEN 1 ELSE 0 END) as clicks,
                SUM(CASE WHEN reports.event_type = "conversion" THEN 1 ELSE 0 END) as conversions,
                CASE
                    WHEN SUM(CASE WHEN reports.event_type IN ("serve", "view") THEN 1 ELSE 0 END) > 0
                    THEN ROUND((SUM(CASE WHEN reports.event_type = "click" THEN 1 ELSE 0 END) / SUM(CASE WHEN reports.event_type IN ("serve", "view") THEN 1 ELSE 0 END)) * 100, 2)
                    ELSE 0
                END as ctr
            ')
            ->groupBy('sites.name', 'sites.domain', 'reports.referrer_url')
            ->orderByDesc('impressions')
            ->orderByDesc('clicks');

        if (!$paginate) {
            return $this->decorateRows($query->get());
        }

        $rows = $query->paginate(20)->withQueryString();
        $rows->getCollection()->transform(fn ($row) => $this->decorateRow($row));

        return $rows;
    }

    private function decorateRows($rows)
    {
        return $rows->map(fn ($row) => $this->decorateRow($row));
    }

    private function decorateRow($row)
    {
        $domain = (string) $row->domain;
        $row->site_url = $domain !== 'Unknown' && !Str::startsWith($domain, ['http://', 'https://'])
            ? 'https://' . $domain
            : $domain;

        return $row;
    }
}
