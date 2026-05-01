<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\AdServingLog;
use App\Models\PixelTracker;
use App\Models\UrlAdReport;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $events = $this->events($request, $filters);
        $stats = $this->stats($events);
        $paginatedEvents = $this->paginate($events, $request);

        return view('advertiser.tracking.event-log', [
            'events' => $paginatedEvents,
            'stats' => $stats,
            'eventTypes' => $this->eventTypes(),
            'sources' => $this->sources(),
            'defaults' => [
                'date_from' => now()->subDays(7)->toDateString(),
                'date_to' => now()->toDateString(),
            ],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);
        $events = $this->events($request, $filters)->take(5000);
        $fileName = 'advertiser-event-log-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($events) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'time',
                'source',
                'event_type',
                'status',
                'campaign',
                'tracker',
                'device',
                'country',
                'ip_address',
                'request_url',
                'destination_url',
            ]);

            foreach ($events as $event) {
                fputcsv($handle, [
                    $event['created_at']?->toDateTimeString(),
                    $event['source_label'],
                    $event['event_label'],
                    $event['status'],
                    $event['campaign_name'],
                    $event['tracker_name'],
                    $event['device_type'],
                    $event['country_code'],
                    $event['ip_address'],
                    $event['request_url'],
                    $event['destination_url'],
                ]);
            }

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    private function filters(Request $request): array
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'in:ad_serving,url_report,pixel'],
            'event_type' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:30'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $filters['date_from'] ??= now()->subDays(7)->toDateString();
        $filters['date_to'] ??= now()->toDateString();

        return $filters;
    }

    private function events(Request $request, array $filters): Collection
    {
        $events = collect();
        $source = $filters['source'] ?? null;

        if (!$source || $source === 'ad_serving') {
            $events = $events->merge($this->adServingEvents($request, $filters));
        }

        if (!$source || $source === 'url_report') {
            $events = $events->merge($this->urlReportEvents($request, $filters));
        }

        if (!$source || $source === 'pixel') {
            $events = $events->merge($this->pixelEvents($request, $filters));
        }

        return $events
            ->filter()
            ->sortByDesc('created_at')
            ->values();
    }

    private function adServingEvents(Request $request, array $filters): Collection
    {
        if (!Schema::hasTable('aq_ad_serving_logs')) {
            return collect();
        }

        return AdServingLog::query()
            ->with(['campaign:id,name', 'directCampaign:id,name'])
            ->where('advertiser_id', $request->user()->id)
            ->when($filters['event_type'] ?? null, fn ($query, $value) => $query->where('event_type', $value))
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->when($filters['date_from'] ?? null, fn ($query, $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn ($query, $value) => $query->whereDate('created_at', '<=', $value))
            ->when(trim((string) ($filters['search'] ?? '')) !== '', function ($query) use ($filters) {
                $search = trim((string) $filters['search']);
                $query->where(function ($inner) use ($search) {
                    $inner->where('request_id', 'like', "%{$search}%")
                        ->orWhere('viewer_id', 'like', "%{$search}%")
                        ->orWhere('click_id', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhere('request_url', 'like', "%{$search}%")
                        ->orWhere('destination_url', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->limit(1000)
            ->get()
            ->map(fn (AdServingLog $log) => [
                'id' => 'ad_serving-' . $log->id,
                'source' => 'ad_serving',
                'source_label' => 'Ad Serving',
                'event_type' => $log->event_type,
                'event_label' => $this->eventTypes()[$log->event_type] ?? Str::headline($log->event_type),
                'status' => $log->status,
                'status_class' => $this->statusClass($log->status),
                'campaign_name' => $log->campaign?->name ?? $log->directCampaign?->name ?? '-',
                'tracker_name' => '-',
                'device_type' => $log->device_type ?: '-',
                'country_code' => $log->country_code ?: '-',
                'ip_address' => $log->ip_address ?: '-',
                'request_url' => $log->request_url,
                'destination_url' => $log->destination_url,
                'meta' => array_filter([
                    'request_id' => $log->request_id,
                    'viewer_id' => $log->viewer_id,
                    'click_id' => $log->click_id,
                    'delivery' => $log->delivery_type,
                    'revenue' => (float) $log->revenue,
                ], fn ($value) => $value !== null && $value !== ''),
                'created_at' => $log->created_at,
            ]);
    }

    private function urlReportEvents(Request $request, array $filters): Collection
    {
        if (!Schema::hasTable('aq_url_ad_reports')) {
            return collect();
        }

        $query = UrlAdReport::query()
            ->from('aq_url_ad_reports as reports')
            ->leftJoin('aq_campaigns as campaigns', 'reports.campaign_id', '=', 'campaigns.id')
            ->leftJoin('aq_direct_campaigns as direct_campaigns', 'reports.direct_campaign_id', '=', 'direct_campaigns.id')
            ->where(function ($inner) use ($request) {
                $inner->where('campaigns.advertiser_id', $request->user()->id)
                    ->orWhere('direct_campaigns.advertiser_id', $request->user()->id);
            })
            ->when($filters['event_type'] ?? null, fn ($query, $value) => $query->where('reports.event_type', $value))
            ->when($filters['date_from'] ?? null, fn ($query, $value) => $query->whereDate('reports.created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn ($query, $value) => $query->whereDate('reports.created_at', '<=', $value))
            ->when(trim((string) ($filters['search'] ?? '')) !== '', function ($query) use ($filters) {
                $search = trim((string) $filters['search']);
                $query->where(function ($inner) use ($search) {
                    $inner->where('reports.request_url', 'like', "%{$search}%")
                        ->orWhere('reports.tracking_url', 'like', "%{$search}%")
                        ->orWhere('reports.destination_url', 'like', "%{$search}%")
                        ->orWhere('reports.ip_address', 'like', "%{$search}%")
                        ->orWhere('campaigns.name', 'like', "%{$search}%")
                        ->orWhere('direct_campaigns.name', 'like', "%{$search}%");
                });
            });

        if (($filters['status'] ?? null) && $filters['status'] !== 'tracked') {
            return collect();
        }

        return $query
            ->select([
                'reports.*',
                DB::raw('COALESCE(campaigns.name, direct_campaigns.name) as campaign_name'),
            ])
            ->orderByDesc('reports.created_at')
            ->limit(1000)
            ->get()
            ->map(fn ($report) => [
                'id' => 'url_report-' . $report->id,
                'source' => 'url_report',
                'source_label' => 'URL Report',
                'event_type' => $report->event_type,
                'event_label' => $this->eventTypes()[$report->event_type] ?? Str::headline($report->event_type),
                'status' => 'tracked',
                'status_class' => $this->statusClass('tracked'),
                'campaign_name' => $report->campaign_name ?: '-',
                'tracker_name' => '-',
                'device_type' => $report->device_type ?: '-',
                'country_code' => '-',
                'ip_address' => $report->ip_address ?: '-',
                'request_url' => $report->request_url ?: $report->tracking_url,
                'destination_url' => $report->destination_url,
                'meta' => array_filter([
                    'ad_id' => $report->ad_id,
                    'zone_id' => $report->zone_id,
                    'direct_creative_id' => $report->direct_creative_id,
                    'url_hidden' => $report->url_hidden ? 'yes' : null,
                    'url_encoded' => $report->url_encoded ? 'yes' : null,
                ], fn ($value) => $value !== null && $value !== ''),
                'created_at' => $report->created_at,
            ]);
    }

    private function pixelEvents(Request $request, array $filters): Collection
    {
        if (!Schema::hasTable('aq_pixel_trackers')) {
            return collect();
        }

        if (($filters['event_type'] ?? null) && !in_array($filters['event_type'], ['pixel_fire', 'conversion', 'postback'], true)) {
            return collect();
        }

        if (($filters['status'] ?? null) && !in_array($filters['status'], ['tracked', 'active'], true)) {
            return collect();
        }

        return PixelTracker::query()
            ->where('advertiser_id', $request->user()->id)
            ->where('is_deleted', false)
            ->where('fire_count', '>', 0)
            ->whereNotNull('last_fired_at')
            ->when($filters['date_from'] ?? null, fn ($query, $value) => $query->whereDate('last_fired_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn ($query, $value) => $query->whereDate('last_fired_at', '<=', $value))
            ->when(trim((string) ($filters['search'] ?? '')) !== '', function ($query) use ($filters) {
                $search = trim((string) $filters['search']);
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('pixel_code', 'like', "%{$search}%")
                        ->orWhere('pixel_goal', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('last_fired_at')
            ->limit(500)
            ->get()
            ->map(fn (PixelTracker $tracker) => [
                'id' => 'pixel-' . $tracker->id,
                'source' => 'pixel',
                'source_label' => 'Pixel Tracker',
                'event_type' => 'pixel_fire',
                'event_label' => 'Pixel Fire',
                'status' => $tracker->status === 'active' ? 'active' : 'tracked',
                'status_class' => $this->statusClass($tracker->status === 'active' ? 'tracked' : $tracker->status),
                'campaign_name' => '-',
                'tracker_name' => $tracker->name,
                'device_type' => '-',
                'country_code' => '-',
                'ip_address' => '-',
                'request_url' => $tracker->tracking_url,
                'destination_url' => null,
                'meta' => array_filter([
                    'pixel_code' => $tracker->pixel_code,
                    'goal' => $tracker->pixel_goal,
                    'category' => $tracker->category,
                    'fire_count' => $tracker->fire_count,
                ], fn ($value) => $value !== null && $value !== ''),
                'created_at' => $tracker->last_fired_at,
            ]);
    }

    private function stats(Collection $events): array
    {
        return [
            'total' => $events->count(),
            'conversions' => $events->where('event_type', 'conversion')->count(),
            'clicks' => $events->where('event_type', 'click')->count(),
            'pixel_fires' => $events->where('event_type', 'pixel_fire')->count(),
        ];
    }

    private function paginate(Collection $events, Request $request): LengthAwarePaginator
    {
        $perPage = 25;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $items = $events->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $events->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    private function eventTypes(): array
    {
        return [
            'zone_request' => 'Zone Request',
            'impression' => 'Impression',
            'serve' => 'Serve',
            'view' => 'View',
            'click' => 'Click',
            'conversion' => 'Conversion',
            'adblock' => 'Adblock',
            'video_event' => 'Video Event',
            'postback' => 'Postback',
            'pixel_fire' => 'Pixel Fire',
        ];
    }

    private function sources(): array
    {
        return [
            'ad_serving' => 'Ad Serving',
            'url_report' => 'URL Report',
            'pixel' => 'Pixel Tracker',
        ];
    }

    private function statusClass(?string $status): string
    {
        return match ($status) {
            'served', 'tracked', 'active' => 'bg-emerald-50 text-emerald-700',
            'blocked', 'paused' => 'bg-amber-50 text-amber-700',
            'error' => 'bg-red-50 text-red-700',
            default => 'bg-gray-100 text-gray-600',
        };
    }
}
