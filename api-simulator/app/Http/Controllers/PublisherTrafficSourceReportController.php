<?php

namespace App\Http\Controllers;

use App\Models\AdServingLog;
use App\Models\TrafficSourceLookup;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PublisherTrafficSourceReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $zones = $this->publisherZones((int) $request->user()->id);
        $rows = $this->buildRows($filters, $zones);
        $summary = $this->summary($rows);

        return view('publisher.reports.traffic-sources', [
            'reports' => $this->paginate($rows, $request),
            'summary' => $summary,
            'zones' => $zones,
            'sourceOptions' => TrafficSourceLookup::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'defaults' => [
                'start_date' => now()->subDays(30)->toDateString(),
                'end_date' => now()->toDateString(),
            ],
        ]);
    }

    public function export(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $rows = $this->buildRows($filters, $this->publisherZones((int) $request->user()->id));
        $filename = 'publisher_traffic_sources_' . now()->format('Y-m-d_His') . '.csv';

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Traffic Source',
                'Domain',
                'AdBlock',
                'Property',
                'Delivery Type',
                'Impressions',
                'Clicks',
                'Conversions',
                'Revenue',
                'CTR',
                'ECPM',
                'Last Activity',
            ]);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->traffic_source,
                    $row->domain,
                    $row->zone_name,
                    $row->property_name,
                    $row->delivery_type,
                    (int) $row->impressions,
                    (int) $row->clicks,
                    (int) $row->conversions,
                    number_format((float) $row->revenue, 4, '.', ''),
                    number_format((float) $row->ctr, 2, '.', ''),
                    number_format((float) $row->ecpm, 4, '.', ''),
                    $row->last_activity,
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
            'zone_id' => ['nullable', 'integer'],
            'source_id' => ['nullable', 'integer', 'exists:aq_traffic_sources,id'],
            'delivery_type' => ['nullable', 'in:network,direct'],
        ]);
    }

    private function buildRows(array $filters, Collection $zones): Collection
    {
        $zoneIds = $zones->pluck('id')->map(fn ($id) => (int) $id)->all();

        if ($zoneIds === []) {
            return collect();
        }

        $sources = TrafficSourceLookup::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $startDate = ($filters['start_date'] ?? now()->subDays(30)->toDateString()) . ' 00:00:00';
        $endDate = ($filters['end_date'] ?? now()->toDateString()) . ' 23:59:59';
        $selectedSource = ! empty($filters['source_id']) ? (int) $filters['source_id'] : null;
        $selectedZone = ! empty($filters['zone_id']) ? (int) $filters['zone_id'] : null;
        $search = strtolower(trim((string) ($filters['search'] ?? '')));

        $query = AdServingLog::query()
            ->with(['zone.site:id,name,domain', 'zone.mobileApp:id,app_name'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('event_type', ['impression', 'serve', 'click', 'conversion'])
            ->where(function ($builder) use ($zoneIds) {
                $builder->where('publisher_id', auth()->id())
                    ->orWhereIn('zone_id', $zoneIds);
            });

        if ($selectedZone !== null) {
            $query->where('zone_id', $selectedZone);
        }

        if (! empty($filters['delivery_type'])) {
            $query->where('delivery_type', $filters['delivery_type']);
        }

        $groups = [];

        foreach ($query->orderByDesc('created_at')->get() as $log) {
            $source = $this->classifySource((string) ($log->referer ?? ''), $sources);

            if ($selectedSource !== null && $source['id'] !== $selectedSource) {
                continue;
            }

            $zone = $log->zone;
            $zoneName = $zone?->name ?? ($log->zone_id ? 'AdBlock #' . $log->zone_id : 'Unknown AdBlock');
            $propertyName = $zone?->site?->name ?? $zone?->mobileApp?->app_name ?? 'Unknown Property';

            if ($search !== '' && ! str_contains(strtolower($source['name'] . ' ' . $source['domain'] . ' ' . $zoneName . ' ' . $propertyName), $search)) {
                continue;
            }

            $key = implode('|', [$source['id'] ?? 0, $source['domain'], $log->zone_id ?? 0, $log->delivery_type ?? 'network']);

            if (! isset($groups[$key])) {
                $groups[$key] = (object) [
                    'traffic_source_id' => $source['id'],
                    'traffic_source' => $source['name'],
                    'domain' => $source['domain'],
                    'zone_name' => $zoneName,
                    'property_name' => $propertyName,
                    'delivery_type' => ucfirst((string) ($log->delivery_type ?? 'network')),
                    'impressions' => 0,
                    'clicks' => 0,
                    'conversions' => 0,
                    'revenue' => 0.0,
                    'last_activity' => optional($log->created_at)->format('Y-m-d H:i:s'),
                ];
            }

            if (in_array($log->event_type, ['impression', 'serve'], true)) {
                $groups[$key]->impressions++;
            } elseif ($log->event_type === 'click') {
                $groups[$key]->clicks++;
            } elseif ($log->event_type === 'conversion') {
                $groups[$key]->conversions++;
            }

            $publisherRevenue = (float) ($log->publisher_earnings > 0 ? $log->publisher_earnings : $log->revenue);
            $groups[$key]->revenue += $publisherRevenue;

            if ($log->created_at && $log->created_at->format('Y-m-d H:i:s') > $groups[$key]->last_activity) {
                $groups[$key]->last_activity = $log->created_at->format('Y-m-d H:i:s');
            }
        }

        return collect($groups)
            ->map(function ($row) {
                $row->ctr = $row->impressions > 0 ? round(($row->clicks / $row->impressions) * 100, 2) : 0.0;
                $row->ecpm = $row->impressions > 0 ? round(($row->revenue / $row->impressions) * 1000, 4) : 0.0;

                return $row;
            })
            ->sortByDesc(fn ($row) => [$row->revenue, $row->impressions, $row->clicks])
            ->values();
    }

    private function classifySource(string $referer, Collection $sources): array
    {
        $domain = $this->domainFromReferer($referer);

        if ($referer === '' || $domain === 'Direct') {
            return ['id' => null, 'name' => 'Direct / Unknown', 'domain' => 'Direct'];
        }

        $haystack = strtolower($referer . ' ' . $domain);

        foreach ($sources as $source) {
            $tokens = array_filter([
                strtolower((string) $source->slug),
                strtolower((string) $source->name),
                strtolower(str_replace(' ', '', (string) $source->name)),
            ]);

            foreach ($tokens as $token) {
                if ($token !== '' && str_contains($haystack, $token)) {
                    return ['id' => (int) $source->id, 'name' => $source->name, 'domain' => $domain];
                }
            }
        }

        return ['id' => null, 'name' => 'Other Referrals', 'domain' => $domain];
    }

    private function domainFromReferer(string $referer): string
    {
        $referer = trim($referer);

        if ($referer === '') {
            return 'Direct';
        }

        $host = parse_url($referer, PHP_URL_HOST);

        if (! $host && ! str_contains($referer, '://')) {
            $host = parse_url('https://' . ltrim($referer, '/'), PHP_URL_HOST);
        }

        return $host ? strtolower(preg_replace('/^www\./', '', $host)) : 'Other';
    }

    private function summary(Collection $rows): array
    {
        $impressions = (int) $rows->sum('impressions');
        $clicks = (int) $rows->sum('clicks');
        $revenue = (float) $rows->sum('revenue');

        return [
            'sources' => $rows->pluck('traffic_source')->unique()->count(),
            'impressions' => $impressions,
            'clicks' => $clicks,
            'conversions' => (int) $rows->sum('conversions'),
            'revenue' => $revenue,
            'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0.0,
            'ecpm' => $impressions > 0 ? round(($revenue / $impressions) * 1000, 4) : 0.0,
        ];
    }

    private function paginate(Collection $rows, Request $request): LengthAwarePaginator
    {
        $perPage = 20;
        $page = max(1, (int) $request->query('page', 1));

        return new LengthAwarePaginator(
            $rows->slice(($page - 1) * $perPage, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    private function publisherZones(int $publisherId): Collection
    {
        return Zone::query()
            ->with(['site:id,name,domain,publisher_id,is_deleted', 'mobileApp:id,app_name,user_id,is_deleted'])
            ->where('is_deleted', false)
            ->where(function ($builder) use ($publisherId) {
                $builder->whereHas('site', function ($site) use ($publisherId) {
                    $site->where('publisher_id', $publisherId)->where('is_deleted', false);
                })->orWhereHas('mobileApp', function ($app) use ($publisherId) {
                    $app->where('user_id', $publisherId)->where('is_deleted', false);
                });
            })
            ->orderBy('name')
            ->get();
    }
}
