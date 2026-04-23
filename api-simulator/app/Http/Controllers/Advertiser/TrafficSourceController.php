<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\DirectCampaign;
use App\Models\TrafficSource;
use App\Models\TrafficSourceLookup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class TrafficSourceController extends Controller
{
    public function index(Request $request)
    {
        $advertiserId = $request->user()->id;

        $relations = Schema::hasTable('aq_direct_campaigns') ? ['source', 'campaign', 'directCampaign'] : ['source', 'campaign'];
        $query = $this->scopedQuery($advertiserId)->with($relations);

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('campaign', fn ($campaign) => $campaign->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('directCampaign', fn ($campaign) => $campaign->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $trafficSources = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $statsQuery = $this->scopedQuery($advertiserId);

        return view('advertiser.network.traffic-sources.index', [
            'trafficSources' => $trafficSources,
            'campaigns' => $this->campaignOptions($advertiserId),
            'sourceLookups' => TrafficSourceLookup::where('status', 'active')->orderBy('name')->get(),
            'totalSources' => (clone $statsQuery)->count(),
            'activeSources' => (clone $statsQuery)->where('status', 'active')->count(),
            'pausedSources' => (clone $statsQuery)->where('status', 'paused')->count(),
            'avgBidRate' => (clone $statsQuery)->avg('bid_rate') ?? 0,
        ]);
    }

    public function store(Request $request)
    {
        [$campaignId, $campaignType] = $this->normalizeCampaignSelection($request->input('campaign_id'));
        $advertiserId = $request->user()->id;

        $validator = Validator::make($request->all(), [
            'traffic_source_id' => ['required', 'exists:aq_traffic_sources,id'],
            'bid_rate' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,paused'],
        ]);

        $validator->after(function ($validator) use ($advertiserId, $campaignId, $campaignType, $request) {
            if (! $campaignId || ! $this->campaignExistsForAdvertiser($advertiserId, $campaignId, $campaignType)) {
                $validator->errors()->add('campaign_id', 'The selected campaign is invalid.');
                return;
            }

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

        return redirect()->route('advertiser.network.traffic-sources')->with('success', 'Traffic source added successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $this->scopedQuery($request->user()->id)->findOrFail($id)->delete();

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => 'Traffic source deleted successfully.'])
            : redirect()->route('advertiser.network.traffic-sources')->with('success', 'Traffic source deleted successfully.');
    }

    private function scopedQuery(int $advertiserId)
    {
        return TrafficSource::query()
            ->where(function ($query) use ($advertiserId) {
                $query->whereHas('campaign', fn ($campaign) => $campaign->where('advertiser_id', $advertiserId))
                    ->when(Schema::hasTable('aq_direct_campaigns'), fn ($q) => $q->orWhereHas('directCampaign', fn ($campaign) => $campaign->where('advertiser_id', $advertiserId)));
            });
    }

    private function normalizeCampaignSelection(?string $rawCampaign): array
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

        return in_array($type, ['network', 'direct'], true) && $id > 0 ? [$id, $type] : [null, null];
    }

    private function campaignExistsForAdvertiser(int $advertiserId, ?int $campaignId, ?string $campaignType): bool
    {
        if (! $campaignId || ! $campaignType) {
            return false;
        }

        if ($campaignType === 'direct' && ! Schema::hasTable('aq_direct_campaigns')) {
            return false;
        }

        $model = $campaignType === 'direct' ? DirectCampaign::query() : Campaign::query();

        return $model->where('id', $campaignId)
            ->where('advertiser_id', $advertiserId)
            ->where('is_deleted', false)
            ->exists();
    }

    private function campaignOptions(int $advertiserId)
    {
        $networkCampaigns = Campaign::where('advertiser_id', $advertiserId)
            ->where('is_deleted', false)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Campaign $campaign) => ['id' => $campaign->id, 'type' => 'network', 'label' => $campaign->name]);

        $directCampaigns = Schema::hasTable('aq_direct_campaigns')
            ? DirectCampaign::where('advertiser_id', $advertiserId)
                ->where('is_deleted', false)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (DirectCampaign $campaign) => ['id' => $campaign->id, 'type' => 'direct', 'label' => $campaign->name . ' (Direct)'])
            : collect();

        return $networkCampaigns->concat($directCampaigns)->sortBy('label')->values();
    }
}
