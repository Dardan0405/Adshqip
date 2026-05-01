<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdServingLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdServingLogController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->filteredQuery($request)
            ->with([
                'campaign:id,name',
                'ad:id,name',
                'directCampaign:id,name',
                'directCreative:id,variant_label,headline',
                'zone:id,name',
                'site:id,name',
                'publisher:id,email',
                'advertiser:id,email',
            ])
            ->latest();

        $logs = $query->paginate(25)->withQueryString();

        $baseStatsQuery = $this->filteredQuery($request);
        $stats = [
            'total' => (clone $baseStatsQuery)->count(),
            'served' => (clone $baseStatsQuery)->where('status', 'served')->count(),
            'blocked' => (clone $baseStatsQuery)->where('status', 'blocked')->count(),
            'revenue' => (float) (clone $baseStatsQuery)->sum('revenue'),
        ];

        return view('admin.ad-serving-logs.index', [
            'logs' => $logs,
            'stats' => $stats,
            'deliveryTypes' => $this->deliveryTypes(),
            'eventTypes' => $this->eventTypes(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $fileName = 'ad-serving-logs-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($request) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'id',
                'created_at',
                'delivery_type',
                'event_type',
                'status',
                'campaign_id',
                'ad_id',
                'direct_campaign_id',
                'direct_creative_id',
                'zone_id',
                'site_id',
                'publisher_id',
                'advertiser_id',
                'country_code',
                'device_type',
                'pricing_model',
                'bid_amount',
                'revenue',
                'ip_address',
                'referer',
                'request_url',
                'destination_url',
            ]);

            $this->filteredQuery($request)
                ->latest()
                ->chunk(500, function ($logs) use ($handle) {
                    foreach ($logs as $log) {
                        fputcsv($handle, [
                            $log->id,
                            $log->created_at?->toDateTimeString(),
                            $log->delivery_type,
                            $log->event_type,
                            $log->status,
                            $log->campaign_id,
                            $log->ad_id,
                            $log->direct_campaign_id,
                            $log->direct_creative_id,
                            $log->zone_id,
                            $log->site_id,
                            $log->publisher_id,
                            $log->advertiser_id,
                            $log->country_code,
                            $log->device_type,
                            $log->pricing_model,
                            $log->bid_amount,
                            $log->revenue,
                            $log->ip_address,
                            $log->referer,
                            $log->request_url,
                            $log->destination_url,
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    public function destroy(AdServingLog $adServingLog)
    {
        $adServingLog->delete();

        return redirect()->route('admin.ad-serving-logs')->with('success', 'Ad serving log deleted successfully.');
    }

    public function clear(Request $request)
    {
        $validated = $request->validate([
            'older_than_days' => ['required', 'integer', 'min:1', 'max:3650'],
        ]);

        $deleted = AdServingLog::where('created_at', '<', now()->subDays((int) $validated['older_than_days']))->delete();

        return redirect()
            ->route('admin.ad-serving-logs')
            ->with('success', number_format($deleted) . ' old ad serving log(s) deleted.');
    }

    private function filteredQuery(Request $request)
    {
        $search = trim((string) $request->input('search'));

        return AdServingLog::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('request_id', 'like', '%' . $search . '%')
                        ->orWhere('viewer_id', 'like', '%' . $search . '%')
                        ->orWhere('click_id', 'like', '%' . $search . '%')
                        ->orWhere('ip_address', 'like', '%' . $search . '%')
                        ->orWhere('request_url', 'like', '%' . $search . '%')
                        ->orWhere('destination_url', 'like', '%' . $search . '%');
                });
            })
            ->when(array_key_exists((string) $request->input('delivery_type'), $this->deliveryTypes()), fn ($query) => $query->where('delivery_type', $request->input('delivery_type')))
            ->when(array_key_exists((string) $request->input('event_type'), $this->eventTypes()), fn ($query) => $query->where('event_type', $request->input('event_type')))
            ->when(array_key_exists((string) $request->input('status'), $this->statuses()), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('campaign_id'), fn ($query) => $query->where('campaign_id', (int) $request->input('campaign_id')))
            ->when($request->filled('direct_campaign_id'), fn ($query) => $query->where('direct_campaign_id', (int) $request->input('direct_campaign_id')))
            ->when($request->filled('zone_id'), fn ($query) => $query->where('zone_id', (int) $request->input('zone_id')))
            ->when($request->filled('publisher_id'), fn ($query) => $query->where('publisher_id', (int) $request->input('publisher_id')))
            ->when($request->filled('advertiser_id'), fn ($query) => $query->where('advertiser_id', (int) $request->input('advertiser_id')))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->input('date_to')));
    }

    private function deliveryTypes(): array
    {
        return [
            'network' => 'Network Campaign',
            'direct' => 'Direct Campaign',
            'zone' => 'Zone Loader',
        ];
    }

    private function eventTypes(): array
    {
        return [
            'zone_request' => 'Zone Request',
            'impression' => 'Impression',
            'view' => 'View',
            'click' => 'Click',
            'conversion' => 'Conversion',
            'adblock' => 'Adblock',
            'video_event' => 'Video Event',
            'postback' => 'Postback',
        ];
    }

    private function statuses(): array
    {
        return [
            'served' => 'Served',
            'tracked' => 'Tracked',
            'blocked' => 'Blocked',
            'error' => 'Error',
        ];
    }
}
