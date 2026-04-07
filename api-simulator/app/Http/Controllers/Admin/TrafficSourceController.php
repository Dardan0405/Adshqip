<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\DirectCampaign;
use App\Models\TrafficSource;
use App\Models\TrafficSourceLookup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TrafficSourceController extends Controller
{
    /**
     * Parse a "type:id" campaign selection value.
     */
    protected function normalizeCampaignSelection(?string $rawCampaign): array
    {
        $rawCampaign = trim((string) $rawCampaign);

        if ($rawCampaign === '') {
            return [null, null];
        }

        if (! str_contains($rawCampaign, ':')) {
            return [(int) $rawCampaign, 'network'];
        }

        [$type, $id] = explode(':', $rawCampaign, 2);
        $type = strtolower(trim($type));
        $id = (int) trim($id);

        if (! in_array($type, ['network', 'direct'], true) || $id <= 0) {
            return [null, null];
        }

        return [$id, $type];
    }

    /**
     * Get all campaigns (admin sees all).
     */
    protected function getAllCampaignOptions()
    {
        $networkCampaigns = Campaign::where('is_deleted', false)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn(Campaign $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'type' => 'network',
                'label' => $c->name,
            ]);

        $directCampaigns = DirectCampaign::where('is_deleted', false)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn(DirectCampaign $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'type' => 'direct',
                'label' => $c->name . ' (Direct)',
            ]);

        return $networkCampaigns
            ->concat($directCampaigns)
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    /**
     * Display a listing of traffic sources.
     */
    public function index(Request $request)
    {
        $query = TrafficSource::with(['source', 'campaign', 'directCampaign']);

        // Search filter
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('source', function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('campaign', function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('directCampaign', function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Status filter
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Source filter
        if ($sourceId = $request->get('source')) {
            $query->where('traffic_source_id', $sourceId);
        }

        $trafficSources = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistics
        $totalSources = TrafficSource::count();
        $activeSources = TrafficSource::where('status', 'active')->count();
        $pausedSources = TrafficSource::where('status', 'paused')->count();
        $avgBidRate = TrafficSource::avg('bid_rate') ?? 0;

        // All campaigns for the add modal
        $campaigns = $this->getAllCampaignOptions();

        // Source lookup options for dropdown
        $sourceLookups = TrafficSourceLookup::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.traffic-sources.index', compact(
            'trafficSources',
            'totalSources',
            'activeSources',
            'pausedSources',
            'avgBidRate',
            'campaigns',
            'sourceLookups'
        ));
    }

    /**
     * Store a newly created traffic source.
     */
    public function store(Request $request)
    {
        [$campaignId, $campaignType] = $this->normalizeCampaignSelection($request->input('campaign_id'));

        $validator = Validator::make($request->all(), [
            'traffic_source_id' => 'required|exists:aq_traffic_sources,id',
            'bid_rate' => 'required|numeric|min:0',
            'status' => 'required|in:active,paused',
        ]);

        $validator->after(function ($validator) use ($campaignId, $campaignType, $request) {
            if (! $campaignId) {
                $validator->errors()->add('campaign_id', 'Please select a campaign.');
                return;
            }

            // Verify campaign exists
            $exists = $campaignType === 'direct'
                ? DirectCampaign::where('id', $campaignId)->where('is_deleted', false)->exists()
                : Campaign::where('id', $campaignId)->where('is_deleted', false)->exists();

            if (! $exists) {
                $validator->errors()->add('campaign_id', 'The selected campaign does not exist.');
                return;
            }

            // Check for duplicate campaign_id + traffic_source_id
            $duplicate = TrafficSource::where('campaign_id', $campaignId)
                ->where('campaign_type', $campaignType)
                ->where('traffic_source_id', $request->input('traffic_source_id'))
                ->exists();

            if ($duplicate) {
                $validator->errors()->add('traffic_source_id', 'This traffic source already exists for the selected campaign.');
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        TrafficSource::create([
            'traffic_source_id' => $request->traffic_source_id,
            'bid_rate' => $request->bid_rate,
            'status' => $request->status,
            'campaign_id' => $campaignId,
            'campaign_type' => $campaignType,
        ]);

        return redirect()->route('admin.traffic-sources')->with('success', 'Traffic source added successfully.');
    }

    /**
     * Remove the specified traffic source.
     */
    public function destroy($id)
    {
        $trafficSource = TrafficSource::findOrFail($id);
        $trafficSource->delete();

        return response()->json(['success' => true, 'message' => 'Traffic source deleted successfully.']);
    }

    /**
     * Export traffic sources to CSV.
     */
    public function export(Request $request)
    {
        $query = TrafficSource::with(['source', 'campaign', 'directCampaign']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('source', function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('campaign', function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                });
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($sourceId = $request->get('source')) {
            $query->where('traffic_source_id', $sourceId);
        }

        $trafficSources = $query->orderBy('created_at', 'desc')->get();

        $filename = 'traffic_sources_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($trafficSources) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['ID', 'Source', 'Bid Rate', 'Status', 'Campaign', 'Campaign Type', 'Created At']);

            foreach ($trafficSources as $ts) {
                $campaignName = 'N/A';
                if ($ts->campaign_id) {
                    if ($ts->campaign_type === 'direct' && $ts->directCampaign) {
                        $campaignName = $ts->directCampaign->name . ' (Direct)';
                    } elseif ($ts->campaign) {
                        $campaignName = $ts->campaign->name;
                    }
                }

                fputcsv($file, [
                    $ts->id,
                    $ts->source->name ?? 'Unknown',
                    '$' . number_format($ts->bid_rate, 2),
                    ucfirst($ts->status),
                    $campaignName,
                    $ts->campaign_type ?? 'N/A',
                    $ts->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
