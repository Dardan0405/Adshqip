<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DirectCampaign;
use App\Models\RequestReport;
use App\Models\Site;
use App\Models\User;
use App\Models\Zone;
use App\Models\PlatformSetting;
use App\Support\PlatformMemcache;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RequestReportController extends Controller
{
    private const DEFAULT_METRICS = ['requests', 'impressions', 'clicks', 'conversions', 'spend', 'revenue', 'profit', 'ctr', 'ecpm'];
    private const DEFAULT_DIMENSIONS = ['date', 'advertiser', 'campaign'];

    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);

        [$reports, $summary, $tableColumns, $selectedMetrics, $selectedDimensions, $displayBy] = $this->buildReport($filters);

        $savedRequests = RequestReport::query()
            ->where('is_deleted', false)
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'saved_page')
            ->withQueryString();

        return view('admin.request-reports.index', [
            'reports' => $reports,
            'summary' => $summary,
            'tableColumns' => $tableColumns,
            'savedRequests' => $savedRequests,
            'selectedMetrics' => $selectedMetrics,
            'selectedDimensions' => $selectedDimensions,
            'selectedDisplayBy' => $displayBy,
            'availableMetrics' => $this->metricDefinitions(),
            'availableDimensions' => $this->dimensionDefinitions(),
            'availableDisplayBy' => $this->displayByOptions(),
            'availableFrequencies' => $this->frequencyOptions(),
            'availableReportTypes' => $this->reportTypeOptions(),
            'availableFormats' => $this->formatOptions(),
            'availableEnvironments' => $this->environmentOptions(),
            'advertisers' => User::where('role', 'advertiser')->where('is_deleted', false)->with('userProfile')->orderBy('email')->get(),
            'campaigns' => DirectCampaign::where('is_deleted', false)->orderBy('name')->get(['id', 'name']),
            'publishers' => User::where('role', 'publisher')->where('is_deleted', false)->with('userProfile')->orderBy('email')->get(),
            'sites' => Site::where('is_deleted', false)->orderBy('name')->get(['id', 'name']),
            'adblocks' => Zone::where('is_deleted', false)->orderBy('name')->get(['id', 'name', 'format_key', 'size_key']),
            'countries' => $this->campaignCountries(),
            'adSizes' => $this->campaignAdSizes(),
            'revenueTypes' => $this->revenueTypes(),
        ]);
    }

    public function store(Request $request)
    {
        $payload = $this->validatedFilters($request, true);

        if (($payload['frequency'] ?? 'one') === 'one') {
            return redirect()
                ->route('admin.reports.requests', $request->except('_token'))
                ->with('warning', 'This was not saved because Frequency is set to One. Choose Daily, Weekly, or Monthly to save it into Saved Requests.');
        }

        RequestReport::create([
            'user_id' => $request->user()->id,
            'email' => $request->user()->email,
            'name' => $payload['name'] ?: 'Request Report',
            'frequency' => $payload['frequency'],
            'report_type' => $payload['report_type'],
            'file_format' => $payload['file_format'],
            'display_by' => $payload['display_by'],
            'environment' => $payload['environment'],
            'metrics' => $payload['metrics'],
            'dimensions' => $payload['dimensions'],
            'filters' => $this->extractFilterPayload($payload),
            'status' => 'active',
        ]);

        app(PlatformMemcache::class)->bumpVersion('adshqip_request_reports');

        return redirect()
            ->route('admin.reports.requests', $request->except('_token'))
            ->with('success', 'Recurring request report saved successfully.');
    }

    public function export(Request $request)
    {
        $filters = $this->validatedFilters($request);
        [$rows, , $tableColumns] = $this->buildReport($filters, false);

        $format = $filters['file_format'] ?? 'csv';
        $extension = $format === 'xls' ? 'xls' : 'csv';
        $separator = $format === 'xls' ? "\t" : ',';
        $contentType = $format === 'xls'
            ? 'application/vnd.ms-excel'
            : 'text/csv';

        $filename = 'request_report_' . now()->format('Y-m-d_His') . '.' . $extension;

        return response()->streamDownload(function () use ($rows, $tableColumns, $separator) {
            $file = fopen('php://output', 'w');

            if ($separator === "\t") {
                fwrite($file, implode($separator, array_column($tableColumns, 'label')) . PHP_EOL);
                foreach ($rows as $row) {
                    $values = [];
                    foreach ($tableColumns as $column) {
                        $values[] = $this->formatValueForExport($row->{$column['key']} ?? null, $column);
                    }
                    fwrite($file, implode($separator, $values) . PHP_EOL);
                }
                fclose($file);
                return;
            }

            fputcsv($file, array_column($tableColumns, 'label'));
            foreach ($rows as $row) {
                $csvRow = [];
                foreach ($tableColumns as $column) {
                    $csvRow[] = $this->formatValueForExport($row->{$column['key']} ?? null, $column);
                }
                fputcsv($file, $csvRow);
            }
            fclose($file);
        }, $filename, ['Content-Type' => $contentType]);
    }

    public function download(RequestReport $requestReport)
    {
        abort_unless(! $requestReport->is_deleted, 404);

        $filters = array_merge($requestReport->filters ?? [], [
            'metrics' => $requestReport->metrics ?? self::DEFAULT_METRICS,
            'dimensions' => $requestReport->dimensions ?? self::DEFAULT_DIMENSIONS,
            'display_by' => $requestReport->display_by,
            'environment' => $requestReport->environment,
            'file_format' => $requestReport->file_format,
        ]);

        $requestReport->update(['last_run_at' => now()]);

        return $this->export(new Request($filters));
    }

    public function updateStatus(Request $request, RequestReport $requestReport)
    {
        $data = $request->validate([
            'status' => ['required', 'in:active,paused'],
        ]);

        $requestReport->update(['status' => $data['status']]);
        app(PlatformMemcache::class)->bumpVersion('adshqip_request_reports');

        return redirect()->back()->with('success', 'Request status updated.');
    }

    public function destroy(RequestReport $requestReport)
    {
        $requestReport->update(['is_deleted' => true]);
        app(PlatformMemcache::class)->bumpVersion('adshqip_request_reports');

        return redirect()->back()->with('success', 'Request deleted successfully.');
    }

    private function buildReport(array $filters, bool $paginated = true): array
    {
        $selectedMetrics = $this->normalizeSelections($filters['metrics'] ?? [], array_keys($this->metricDefinitions()), self::DEFAULT_METRICS);
        $selectedDimensions = $this->normalizeSelections($filters['dimensions'] ?? [], array_keys($this->dimensionDefinitions()), self::DEFAULT_DIMENSIONS);
        $displayBy = $filters['display_by'] ?? 'cumulative';

        $collection = $this->baseRows($filters);
        $summary = $this->buildSummary($collection);
        $grouped = $this->groupRows($collection, $selectedDimensions, $selectedMetrics, $displayBy);
        $tableColumns = $this->tableColumns($selectedDimensions, $selectedMetrics, $displayBy);

        if (! $paginated) {
            return [$grouped, $summary, $tableColumns, $selectedMetrics, $selectedDimensions, $displayBy];
        }

        $perPage = 20;
        $currentPage = request()->integer('page', 1);
        $paged = new LengthAwarePaginator(
            $grouped->slice(($currentPage - 1) * $perPage, $perPage)->values(),
            $grouped->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return [$paged, $summary, $tableColumns, $selectedMetrics, $selectedDimensions, $displayBy];
    }

    private function baseRows(array $filters): Collection
    {
        $builder = function () use ($filters) {
            $query = DB::table('aq_direct_campaign_stats as stats')
                ->leftJoin('aq_direct_campaigns as campaigns', 'stats.campaign_id', '=', 'campaigns.id')
                ->leftJoin('aq_users as advertisers', 'campaigns.advertiser_id', '=', 'advertisers.id')
                ->leftJoin('aq_user_profiles as advertiser_profiles', 'advertisers.id', '=', 'advertiser_profiles.user_id')
                ->leftJoin('aq_zones as zones', 'stats.zone_id', '=', 'zones.id')
                ->leftJoin('aq_sites as sites', 'zones.site_id', '=', 'sites.id')
                ->leftJoin('aq_users as publishers', 'sites.publisher_id', '=', 'publishers.id')
                ->leftJoin('aq_user_profiles as publisher_profiles', 'publishers.id', '=', 'publisher_profiles.user_id')
                ->where('campaigns.is_deleted', false);

            if (! empty($filters['search'])) {
                $search = trim((string) $filters['search']);
                $query->where(function ($inner) use ($search) {
                    $inner
                        ->orWhere('campaigns.name', 'like', '%' . $search . '%')
                        ->orWhere('campaigns.brand_name', 'like', '%' . $search . '%')
                        ->orWhere('advertisers.email', 'like', '%' . $search . '%')
                        ->orWhere('publishers.email', 'like', '%' . $search . '%')
                        ->orWhere('sites.name', 'like', '%' . $search . '%')
                        ->orWhere('zones.name', 'like', '%' . $search . '%');
                });
            }

            if ($environment = $filters['environment'] ?? 'all') {
                if ($environment !== 'all') {
                    $query->where('stats.device_type', $environment);
                }
            }

            foreach ([
                'advertiser_ids' => 'campaigns.advertiser_id',
                'campaign_ids' => 'campaigns.id',
                'publisher_ids' => 'sites.publisher_id',
                'site_ids' => 'sites.id',
                'adblock_ids' => 'zones.id',
                'country_codes' => 'stats.country_code',
                'ad_sizes' => 'zones.size_key',
                'revenue_types' => 'campaigns.pricing_model',
            ] as $filterKey => $column) {
                if (! empty($filters[$filterKey])) {
                    $query->whereIn($column, $filters[$filterKey]);
                }
            }

            $query->whereBetween('stats.date', [
                $filters['date_from'] ?? now()->subDays(30)->toDateString(),
                $filters['date_to'] ?? now()->toDateString(),
            ]);

            return $query->selectRaw("
                stats.date,
                campaigns.id as campaign_id,
                campaigns.name as campaign_name,
                campaigns.pricing_model,
                campaigns.marketing_objective,
                advertisers.id as advertiser_id,
                COALESCE(NULLIF(TRIM(CONCAT(COALESCE(advertiser_profiles.first_name, ''), ' ', COALESCE(advertiser_profiles.last_name, ''))), ''), advertiser_profiles.company_name, advertisers.email, CONCAT('Advertiser #', advertisers.id)) as advertiser_name,
                advertisers.email as advertiser_email,
                publishers.id as publisher_id,
                COALESCE(NULLIF(TRIM(CONCAT(COALESCE(publisher_profiles.first_name, ''), ' ', COALESCE(publisher_profiles.last_name, ''))), ''), publisher_profiles.company_name, publishers.email, CONCAT('Publisher #', publishers.id)) as publisher_name,
                publishers.email as publisher_email,
                sites.id as site_id,
                sites.name as site_name,
                sites.domain as site_domain,
                zones.id as adblock_id,
                zones.name as adblock_name,
                zones.size_key as ad_size,
                stats.country_code,
                stats.device_type,
                stats.impressions,
                stats.clicks,
                stats.conversions,
                stats.revenue,
                stats.publisher_earnings,
                stats.fill_rate,
                stats.adblock_detected
            ")
                ->get()
                ->map(fn ($row) => (array) $row)
                ->all();
        };

        $rows = app(PlatformMemcache::class)->remember(
            'adshqip_request_reports',
            $filters,
            300,
            $builder,
            PlatformSetting::getAdshqipMemcacheEnabled()
        );

        return collect($rows)->map(fn ($row) => (object) $row);
    }

    private function buildSummary(Collection $rows): array
    {
        $impressions = (float) $rows->sum('impressions');
        $clicks = (float) $rows->sum('clicks');
        $revenue = (float) $rows->sum('revenue');
        $publisherEarnings = (float) $rows->sum('publisher_earnings');

        return [
            'requests' => $rows->count(),
            'impressions' => $impressions,
            'clicks' => $clicks,
            'conversions' => (float) $rows->sum('conversions'),
            'spend' => $revenue,
            'revenue' => $publisherEarnings,
            'profit' => $revenue - $publisherEarnings,
            'ctr' => $impressions > 0 ? ($clicks / $impressions) * 100 : 0,
            'ecpm' => $impressions > 0 ? ($revenue / $impressions) * 1000 : 0,
        ];
    }

    private function groupRows(Collection $rows, array $dimensions, array $metrics, string $displayBy): Collection
    {
        return $rows
            ->groupBy(function ($row) use ($dimensions, $displayBy) {
                $keys = [];
                foreach ($dimensions as $dimension) {
                    $keys[] = $dimension . ':' . $this->dimensionValue($row, $dimension, $displayBy);
                }
                return implode('|', $keys);
            })
            ->map(function (Collection $group) use ($dimensions, $metrics, $displayBy) {
                $first = $group->first();
                $row = [];

                foreach ($dimensions as $dimension) {
                    $key = $this->dimensionDefinitions()[$dimension]['key'];
                    $row[$key] = $this->dimensionValue($first, $dimension, $displayBy);
                }

                foreach ($metrics as $metric) {
                    $row[$metric] = $this->metricValue($group, $metric);
                }

                return (object) $row;
            })
            ->sortByDesc(function ($row) {
                return $row->report_period ?? $row->campaign_name ?? $row->advertiser_name ?? '';
            })
            ->values();
    }

    private function dimensionValue(object $row, string $dimension, string $displayBy): string
    {
        return match ($dimension) {
            'date' => match ($displayBy) {
                'year' => \Carbon\Carbon::parse($row->date)->format('Y'),
                'month' => \Carbon\Carbon::parse($row->date)->format('Y-m'),
                'hour' => \Carbon\Carbon::parse($row->date)->format('Y-m-d'),
                default => 'Cumulative',
            },
            'advertiser' => $row->advertiser_name ?? 'N/A',
            'campaign' => $row->campaign_name ?? 'N/A',
            'publisher' => $row->publisher_name ?? 'N/A',
            'site' => $row->site_name ?? 'N/A',
            'adblock' => $row->adblock_name ?? 'N/A',
            'country' => $row->country_code ?? 'N/A',
            'adsize' => $row->ad_size ?? 'N/A',
            'type' => strtoupper((string) ($row->pricing_model ?? 'n/a')),
            default => 'N/A',
        };
    }

    private function metricValue(Collection $group, string $metric): float|int
    {
        $impressions = (float) $group->sum('impressions');
        $clicks = (float) $group->sum('clicks');
        $revenue = (float) $group->sum('revenue');
        $publisherEarnings = (float) $group->sum('publisher_earnings');

        return match ($metric) {
            'requests' => $group->count(),
            'impressions' => $impressions,
            'clicks' => $clicks,
            'conversions' => (float) $group->sum('conversions'),
            'spend' => $revenue,
            'revenue' => $publisherEarnings,
            'profit' => $revenue - $publisherEarnings,
            'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0,
            'ecpm' => $impressions > 0 ? round(($revenue / $impressions) * 1000, 2) : 0,
            'eppm' => $impressions > 0 ? round(($publisherEarnings / $impressions) * 1000, 2) : 0,
            'fill_rate' => round((float) $group->avg('fill_rate'), 2),
            default => 0,
        };
    }

    private function tableColumns(array $dimensions, array $metrics, string $displayBy): array
    {
        $columns = [];

        foreach ($dimensions as $dimension) {
            $definition = $this->dimensionDefinitions()[$dimension];
            $columns[] = [
                'key' => $definition['key'],
                'label' => $dimension === 'date' && $displayBy !== 'cumulative' ? ucfirst($displayBy) : $definition['label'],
                'type' => 'text',
            ];
        }

        foreach ($metrics as $metric) {
            $definition = $this->metricDefinitions()[$metric];
            $columns[] = [
                'key' => $metric,
                'label' => $definition['label'],
                'type' => $definition['type'],
            ];
        }

        return $columns;
    }

    private function formatValueForExport(mixed $value, array $column): string
    {
        if ($value === null) {
            return '';
        }

        return match ($column['type']) {
            'currency', 'percent' => number_format((float) $value, 2, '.', ''),
            'number' => number_format((float) $value, 0, '.', ''),
            default => (string) $value,
        };
    }

    private function validatedFilters(Request $request, bool $includeName = false): array
    {
        $rules = [
            'search' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'environment' => ['nullable', 'in:' . implode(',', array_keys($this->environmentOptions()))],
            'frequency' => ['nullable', 'in:' . implode(',', array_keys($this->frequencyOptions()))],
            'report_type' => ['nullable', 'in:' . implode(',', array_keys($this->reportTypeOptions()))],
            'file_format' => ['nullable', 'in:' . implode(',', array_keys($this->formatOptions()))],
            'display_by' => ['nullable', 'in:' . implode(',', array_keys($this->displayByOptions()))],
            'metrics' => ['nullable', 'array'],
            'metrics.*' => ['string'],
            'dimensions' => ['nullable', 'array'],
            'dimensions.*' => ['string'],
            'advertiser_ids' => ['nullable', 'array'],
            'advertiser_ids.*' => ['integer'],
            'campaign_ids' => ['nullable', 'array'],
            'campaign_ids.*' => ['integer'],
            'publisher_ids' => ['nullable', 'array'],
            'publisher_ids.*' => ['integer'],
            'site_ids' => ['nullable', 'array'],
            'site_ids.*' => ['integer'],
            'adblock_ids' => ['nullable', 'array'],
            'adblock_ids.*' => ['integer'],
            'country_codes' => ['nullable', 'array'],
            'country_codes.*' => ['string', 'size:2'],
            'ad_sizes' => ['nullable', 'array'],
            'ad_sizes.*' => ['string'],
            'revenue_types' => ['nullable', 'array'],
            'revenue_types.*' => ['string'],
        ];

        if ($includeName) {
            $rules['name'] = ['nullable', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);
        $validated['metrics'] = $validated['metrics'] ?? self::DEFAULT_METRICS;
        $validated['dimensions'] = $validated['dimensions'] ?? self::DEFAULT_DIMENSIONS;
        $validated['display_by'] = $validated['display_by'] ?? 'month';
        $validated['environment'] = $validated['environment'] ?? 'all';
        $validated['frequency'] = $validated['frequency'] ?? 'one';
        $validated['report_type'] = $validated['report_type'] ?? 'summarized';
        $validated['file_format'] = $validated['file_format'] ?? 'csv';

        return $validated;
    }

    private function extractFilterPayload(array $filters): array
    {
        return collect($filters)
            ->only(['search', 'date_from', 'date_to', 'advertiser_ids', 'campaign_ids', 'publisher_ids', 'site_ids', 'adblock_ids', 'country_codes', 'ad_sizes', 'revenue_types'])
            ->toArray();
    }

    private function normalizeSelections(array $values, array $allowed, array $fallback): array
    {
        $selected = array_values(array_intersect($values, $allowed));
        return $selected !== [] ? $selected : $fallback;
    }

    private function metricDefinitions(): array
    {
        return [
            'requests' => ['label' => 'Requests', 'type' => 'number'],
            'impressions' => ['label' => 'Impressions', 'type' => 'number'],
            'clicks' => ['label' => 'Clicks', 'type' => 'number'],
            'conversions' => ['label' => 'Conversions', 'type' => 'number'],
            'spend' => ['label' => 'Spend', 'type' => 'currency'],
            'revenue' => ['label' => 'Revenue', 'type' => 'currency'],
            'profit' => ['label' => 'Profit', 'type' => 'currency'],
            'ctr' => ['label' => 'CTR', 'type' => 'percent'],
            'ecpm' => ['label' => 'ECPM', 'type' => 'currency'],
            'eppm' => ['label' => 'EPPM', 'type' => 'currency'],
            'fill_rate' => ['label' => 'Fill Rate', 'type' => 'percent'],
        ];
    }

    private function dimensionDefinitions(): array
    {
        return [
            'date' => ['key' => 'report_period', 'label' => 'Date'],
            'advertiser' => ['key' => 'advertiser_name', 'label' => 'Advertiser'],
            'campaign' => ['key' => 'campaign_name', 'label' => 'Campaign'],
            'publisher' => ['key' => 'publisher_name', 'label' => 'Publisher'],
            'site' => ['key' => 'site_name', 'label' => 'Site'],
            'adblock' => ['key' => 'adblock_name', 'label' => 'AdBlock'],
            'country' => ['key' => 'country_code', 'label' => 'Country'],
            'adsize' => ['key' => 'ad_size', 'label' => 'Ad Size'],
            'type' => ['key' => 'revenue_type_name', 'label' => 'Revenue Type'],
        ];
    }

    private function displayByOptions(): array
    {
        return [
            'cumulative' => 'Cumulative',
            'hour' => 'Hour',
            'month' => 'Month',
            'year' => 'Year',
        ];
    }

    private function frequencyOptions(): array
    {
        return [
            'one' => 'One',
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
        ];
    }

    private function reportTypeOptions(): array
    {
        return [
            'summarized' => 'Summarized',
            'detailed' => 'Detailed',
        ];
    }

    private function formatOptions(): array
    {
        return [
            'csv' => 'CSV',
            'xls' => 'XLS',
        ];
    }

    private function environmentOptions(): array
    {
        return [
            'all' => 'All',
            'desktop' => 'Display',
            'mobile' => 'Mobile',
            'tablet' => 'Tablet',
        ];
    }

    private function revenueTypes(): array
    {
        return [
            'cpm' => 'CPM',
            'cpc' => 'CPC',
            'cpa' => 'CPA',
            'cpv' => 'CPV',
            'cpv_ctw' => 'CPV Click-to-Watch',
            'flat_rate' => 'Flat Rate',
        ];
    }

    private function campaignCountries(): array
    {
        return [
            ['code' => 'AL', 'name' => 'Albania'],
            ['code' => 'BA', 'name' => 'Bosnia and Herzegovina'],
            ['code' => 'BG', 'name' => 'Bulgaria'],
            ['code' => 'HR', 'name' => 'Croatia'],
            ['code' => 'GR', 'name' => 'Greece'],
            ['code' => 'XK', 'name' => 'Kosovo'],
            ['code' => 'ME', 'name' => 'Montenegro'],
            ['code' => 'MK', 'name' => 'North Macedonia'],
            ['code' => 'RO', 'name' => 'Romania'],
            ['code' => 'RS', 'name' => 'Serbia'],
            ['code' => 'SI', 'name' => 'Slovenia'],
            ['code' => 'TR', 'name' => 'Turkey'],
        ];
    }

    private function campaignAdSizes(): array
    {
        return [
            'display_web' => [
                'label' => 'Display Web',
                'sizes' => [
                    '300x250' => '300x250 (Medium Rectangle)',
                    '728x90' => '728x90 (Leaderboard)',
                    '160x600' => '160x600 (Wide Skyscraper)',
                    '300x600' => '300x600 (Half Page)',
                    '320x50' => '320x50 (Mobile Banner)',
                    '970x250' => '970x250 (Billboard)',
                ],
            ],
            'special_web' => [
                'label' => 'Special Web',
                'sizes' => [
                    'text' => 'Text Ad',
                    'native' => 'Native Ad',
                    'interstitial' => 'Interstitial',
                    'popunder' => 'Popunder',
                    'direct_link' => 'Direct Link',
                    'in_page_push' => 'In-Page Push',
                    'social_bar' => 'Social Bar',
                ],
            ],
            'display_video' => [
                'label' => 'Display Video',
                'sizes' => [
                    'instream' => 'In-Stream Video',
                    'outstream' => 'Out-Stream Video',
                    'rewarded' => 'Rewarded Video',
                ],
            ],
        ];
    }
}
