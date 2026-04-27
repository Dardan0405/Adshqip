<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublisherGroupReportController extends Controller
{
    private const GROUP_BY_OPTIONS = [
        'zone'    => 'Zone (Adblock)',
        'site'    => 'Site',
        'country' => 'Country',
        'device'  => 'Device Type',
        'date'    => 'Date',
    ];

    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);

        if (! Schema::hasTable('aq_stats_daily')) {
            return view('publisher.reports.groups', [
                'reports'        => $this->emptyPaginator($request),
                'summary'        => $this->emptySummary(),
                'groupByOptions' => self::GROUP_BY_OPTIONS,
                'defaults'       => $this->defaults(),
            ]);
        }

        return view('publisher.reports.groups', [
            'reports'        => $this->buildRows($filters),
            'summary'        => $this->buildSummary($filters),
            'groupByOptions' => self::GROUP_BY_OPTIONS,
            'defaults'       => $this->defaults(),
        ]);
    }

    public function export(Request $request)
    {
        $filters  = $this->validatedFilters($request);
        $groupLabel = self::GROUP_BY_OPTIONS[$filters['group_by']] ?? 'Group';
        $rows     = Schema::hasTable('aq_stats_daily')
            ? $this->buildRows($filters, paginate: false)
            : collect();
        $filename = 'publisher_group_report_' . now()->format('Y-m-d_His') . '.csv';

        $callback = function () use ($rows, $groupLabel) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [$groupLabel, 'Requests', 'Impressions', 'Clicks', 'Conversions', 'Revenue', 'CTR', 'ECPM']);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->group_label,
                    (int) $row->requests,
                    (int) $row->impressions,
                    (int) $row->clicks,
                    (int) $row->conversions,
                    number_format((float) $row->revenue, 4, '.', ''),
                    number_format((float) $row->ctr, 2, '.', '') . '%',
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
        $validated = $request->validate([
            'search'     => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
            'group_by'   => ['nullable', 'in:' . implode(',', array_keys(self::GROUP_BY_OPTIONS))],
        ]);

        $validated['group_by']   = $validated['group_by']   ?? 'zone';
        $validated['start_date'] = $validated['start_date'] ?? now()->subDays(30)->toDateString();
        $validated['end_date']   = $validated['end_date']   ?? now()->toDateString();

        return $validated;
    }

    private function baseQuery(array $filters)
    {
        $query = DB::table('aq_stats_daily as s')
            ->leftJoin('aq_zones as z', 's.zone_id', '=', 'z.id')
            ->leftJoin('aq_sites as si', 's.site_id', '=', 'si.id')
            ->where('s.publisher_id', Auth::id())
            ->whereDate('s.date', '>=', $filters['start_date'])
            ->whereDate('s.date', '<=', $filters['end_date']);

        return $query;
    }

    private function groupDefinition(string $groupBy): array
    {
        return match ($groupBy) {
            'zone'    => [
                'select' => "COALESCE(z.name, CONCAT('Zone #', s.zone_id), 'Unknown') as group_label",
                'group'  => "COALESCE(z.name, CONCAT('Zone #', s.zone_id), 'Unknown')",
            ],
            'site'    => [
                'select' => "COALESCE(si.name, si.domain, CONCAT('Site #', s.site_id), 'Unknown') as group_label",
                'group'  => "COALESCE(si.name, si.domain, CONCAT('Site #', s.site_id), 'Unknown')",
            ],
            'country' => [
                'select' => "COALESCE(s.country_code, 'Unknown') as group_label",
                'group'  => "COALESCE(s.country_code, 'Unknown')",
            ],
            'device'  => [
                'select' => "COALESCE(UPPER(s.device_type), 'Unknown') as group_label",
                'group'  => "COALESCE(UPPER(s.device_type), 'Unknown')",
            ],
            'date'    => [
                'select' => "DATE(s.date) as group_label",
                'group'  => "DATE(s.date)",
            ],
            default   => [
                'select' => "COALESCE(z.name, CONCAT('Zone #', s.zone_id), 'Unknown') as group_label",
                'group'  => "COALESCE(z.name, CONCAT('Zone #', s.zone_id), 'Unknown')",
            ],
        };
    }

    private function buildSummary(array $filters): array
    {
        $row = $this->baseQuery($filters)
            ->selectRaw("
                COUNT(*) as requests,
                COALESCE(SUM(s.impressions), 0) as impressions,
                COALESCE(SUM(s.clicks), 0) as clicks,
                COALESCE(SUM(s.conversions), 0) as conversions,
                COALESCE(SUM(s.publisher_earnings), 0) as revenue,
                CASE WHEN COALESCE(SUM(s.impressions), 0) > 0
                     THEN ROUND((COALESCE(SUM(s.clicks), 0) / SUM(s.impressions)) * 100, 2) ELSE 0 END as ctr,
                CASE WHEN COALESCE(SUM(s.impressions), 0) > 0
                     THEN ROUND((COALESCE(SUM(s.publisher_earnings), 0) / SUM(s.impressions)) * 1000, 2) ELSE 0 END as ecpm
            ")
            ->first();

        return [
            'requests'    => (int)   ($row->requests    ?? 0),
            'impressions' => (int)   ($row->impressions ?? 0),
            'clicks'      => (int)   ($row->clicks      ?? 0),
            'conversions' => (int)   ($row->conversions ?? 0),
            'revenue'     => (float) ($row->revenue     ?? 0),
            'ctr'         => (float) ($row->ctr         ?? 0),
            'ecpm'        => (float) ($row->ecpm        ?? 0),
        ];
    }

    private function buildRows(array $filters, bool $paginate = true)
    {
        $def   = $this->groupDefinition($filters['group_by']);
        $query = $this->baseQuery($filters)
            ->selectRaw("
                {$def['select']},
                COUNT(*) as requests,
                COALESCE(SUM(s.impressions), 0) as impressions,
                COALESCE(SUM(s.clicks), 0) as clicks,
                COALESCE(SUM(s.conversions), 0) as conversions,
                COALESCE(SUM(s.publisher_earnings), 0) as revenue,
                CASE WHEN COALESCE(SUM(s.impressions), 0) > 0
                     THEN ROUND((COALESCE(SUM(s.clicks), 0) / SUM(s.impressions)) * 100, 2) ELSE 0 END as ctr,
                CASE WHEN COALESCE(SUM(s.impressions), 0) > 0
                     THEN ROUND((COALESCE(SUM(s.publisher_earnings), 0) / SUM(s.impressions)) * 1000, 2) ELSE 0 END as ecpm
            ")
            ->groupBy(DB::raw($def['group']))
            ->orderByDesc('impressions');

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->havingRaw("{$def['group']} LIKE ?", ['%' . $search . '%']);
        }

        if (! $paginate) {
            return $query->get();
        }

        return $query->paginate(20)->withQueryString();
    }

    private function emptySummary(): array
    {
        return [
            'requests' => 0, 'impressions' => 0, 'clicks' => 0,
            'conversions' => 0, 'revenue' => 0.0, 'ctr' => 0.0, 'ecpm' => 0.0,
        ];
    }

    private function emptyPaginator(Request $request)
    {
        return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20, 1, [
            'path' => $request->url(),
        ]);
    }

    private function defaults(): array
    {
        return [
            'start_date' => now()->subDays(30)->toDateString(),
            'end_date'   => now()->toDateString(),
        ];
    }
}
