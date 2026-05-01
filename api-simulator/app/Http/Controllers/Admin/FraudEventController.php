<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FraudEvent;
use App\Models\PublisherFraudRecord;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FraudEventController extends Controller
{
    public function index(Request $request)
    {
        $events = $this->filteredQuery($request)
            ->with(['ad:id,name', 'zone:id,name,site_id', 'zone.site:id,name,publisher_id'])
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        $base = $this->filteredQuery($request);
        $stats = [
            'total' => (clone $base)->count(),
            'blocked' => (clone $base)->where('blocked', true)->count(),
            'flagged' => (clone $base)->where('blocked', false)->count(),
            'critical' => (clone $base)->where('severity', 'critical')->count(),
        ];

        return view('admin.fraud-events.index', [
            'events' => $events,
            'stats' => $stats,
            'eventTypes' => $this->eventTypes(),
            'reasons' => $this->reasons(),
            'severities' => $this->severities(),
            'publisherRecords' => PublisherFraudRecord::with('publisher:id,email')
                ->whereNull('resolved_at')
                ->latest('created_at')
                ->limit(10)
                ->get(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        return response()->streamDownload(function () use ($request) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'created_at', 'event_type', 'reason', 'severity', 'blocked', 'ad_id', 'zone_id', 'viewer_id', 'ip_address', 'user_agent']);

            $this->filteredQuery($request)->latest('created_at')->chunk(500, function ($events) use ($handle) {
                foreach ($events as $event) {
                    fputcsv($handle, [
                        $event->id,
                        $event->created_at?->toDateTimeString(),
                        $event->event_type,
                        $event->fraud_reason,
                        $event->severity,
                        $event->blocked ? 'yes' : 'no',
                        $event->ad_id,
                        $event->zone_id,
                        $event->viewer_id,
                        $event->ip_address,
                        $event->user_agent,
                    ]);
                }
            });

            fclose($handle);
        }, 'fraud-events-' . now()->format('Y-m-d-His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function destroy(FraudEvent $fraudEvent)
    {
        $fraudEvent->delete();

        return redirect()->route('admin.fraud-events')->with('success', 'Fraud event deleted successfully.');
    }

    public function clear(Request $request)
    {
        $validated = $request->validate([
            'older_than_days' => ['required', 'integer', 'min:1', 'max:3650'],
        ]);

        $deleted = FraudEvent::where('created_at', '<', now()->subDays((int) $validated['older_than_days']))->delete();

        return redirect()->route('admin.fraud-events')->with('success', number_format($deleted) . ' old fraud event(s) deleted.');
    }

    public function resolvePublisherRecord(PublisherFraudRecord $publisherFraudRecord)
    {
        $publisherFraudRecord->update(['resolved_at' => now(), 'action_taken' => 'none']);

        return redirect()->route('admin.fraud-events')->with('success', 'Publisher fraud record resolved.');
    }

    private function filteredQuery(Request $request)
    {
        $search = trim((string) $request->input('search'));

        return FraudEvent::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('viewer_id', 'like', '%' . $search . '%')
                        ->orWhere('fingerprint_id', 'like', '%' . $search . '%')
                        ->orWhere('ip_address', 'like', '%' . $search . '%')
                        ->orWhere('user_agent', 'like', '%' . $search . '%');
                });
            })
            ->when($request->filled('event_type'), fn ($query) => $query->where('event_type', $request->input('event_type')))
            ->when($request->filled('fraud_reason'), fn ($query) => $query->where('fraud_reason', $request->input('fraud_reason')))
            ->when($request->filled('severity'), fn ($query) => $query->where('severity', $request->input('severity')))
            ->when($request->filled('blocked'), fn ($query) => $query->where('blocked', $request->input('blocked') === '1'))
            ->when($request->filled('ad_id'), fn ($query) => $query->where('ad_id', (int) $request->input('ad_id')))
            ->when($request->filled('zone_id'), fn ($query) => $query->where('zone_id', (int) $request->input('zone_id')))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->input('date_to')));
    }

    private function eventTypes(): array
    {
        return ['click' => 'Click', 'impression' => 'Impression'];
    }

    private function reasons(): array
    {
        return [
            'duplicate' => 'Duplicate',
            'bot' => 'Bot',
            'datacenter_ip' => 'Datacenter IP',
            'click_flood' => 'Click Flood',
            'impression_stacking' => 'Impression Stacking',
            'geo_mismatch' => 'Geo Mismatch',
            'other' => 'Other',
        ];
    }

    private function severities(): array
    {
        return ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'];
    }
}
