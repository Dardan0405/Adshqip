<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CountryWiseBidding;
use App\Models\DirectCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class CountryWiseBiddingController extends Controller
{
    public function index(Request $request)
    {
        $advertiserId = $request->user()->id;

        $relations = Schema::hasTable('aq_direct_campaigns') ? ['campaign', 'directCampaign'] : ['campaign'];

        $query = CountryWiseBidding::with($relations)
            ->where('advertiser_id', $advertiserId);

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('country_code', 'like', "%{$search}%")
                    ->orWhereHas('campaign', fn ($campaign) => $campaign->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('directCampaign', fn ($campaign) => $campaign->where('name', 'like', "%{$search}%"));
            });
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($country = $request->get('country')) {
            $query->where('country_code', strtoupper($country));
        }

        $biddings = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $statsQuery = CountryWiseBidding::where('advertiser_id', $advertiserId);

        return view('advertiser.network.country-wise-bidding.index', [
            'biddings' => $biddings,
            'campaigns' => $this->campaignOptions($advertiserId),
            'totalBiddings' => (clone $statsQuery)->count(),
            'avgBidValue' => (clone $statsQuery)->avg('bid_value') ?? 0,
            'cpcCount' => (clone $statsQuery)->where('type', 'CPC')->count(),
            'cpmCount' => (clone $statsQuery)->where('type', 'CPM')->count(),
        ]);
    }

    public function store(Request $request)
    {
        [$campaignId, $campaignType] = $this->normalizeCampaignSelection($request->input('campaign_id'));
        $advertiserId = $request->user()->id;

        $validator = Validator::make($request->all(), [
            'type' => ['required', 'in:CPC,CPM,CPA,CPV'],
            'country_code' => ['required', 'string', 'size:2'],
            'bid_value' => ['required', 'numeric', 'min:0'],
        ]);

        $validator->after(function ($validator) use ($advertiserId, $campaignId, $campaignType) {
            if (! $this->campaignExistsForAdvertiser($advertiserId, $campaignId, $campaignType)) {
                $validator->errors()->add('campaign_id', 'The selected campaign is invalid.');
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        CountryWiseBidding::create([
            'advertiser_id' => $advertiserId,
            'campaign_id' => $campaignId,
            'campaign_type' => $campaignId ? $campaignType : null,
            'type' => $request->type,
            'country_code' => strtoupper($request->country_code),
            'bid_value' => $request->bid_value,
        ]);

        return redirect()->route('advertiser.network.country-wise-bidding')->with('success', 'Country-wise bidding rule created successfully.');
    }

    public function show(Request $request, int $id)
    {
        $bidding = CountryWiseBidding::where('advertiser_id', $request->user()->id)->findOrFail($id);

        return response()->json([
            'id' => $bidding->id,
            'campaign_id' => $bidding->campaign_id,
            'campaign_type' => $bidding->campaign_type ?? 'network',
            'type' => $bidding->type,
            'country_code' => $bidding->country_code,
            'bid_value' => $bidding->bid_value,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $bidding = CountryWiseBidding::where('advertiser_id', $request->user()->id)->findOrFail($id);
        [$campaignId, $campaignType] = $this->normalizeCampaignSelection($request->input('campaign_id'));

        $validator = Validator::make($request->all(), [
            'type' => ['required', 'in:CPC,CPM,CPA,CPV'],
            'country_code' => ['required', 'string', 'size:2'],
            'bid_value' => ['required', 'numeric', 'min:0'],
        ]);

        $validator->after(function ($validator) use ($request, $campaignId, $campaignType) {
            if (! $this->campaignExistsForAdvertiser($request->user()->id, $campaignId, $campaignType)) {
                $validator->errors()->add('campaign_id', 'The selected campaign is invalid.');
            }
        });

        if ($validator->fails()) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $validator->errors()->first()], 422)
                : back()->withErrors($validator)->withInput();
        }

        $bidding->update([
            'campaign_id' => $campaignId,
            'campaign_type' => $campaignId ? $campaignType : null,
            'type' => $request->type,
            'country_code' => strtoupper($request->country_code),
            'bid_value' => $request->bid_value,
        ]);

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => 'Bidding rule updated successfully.'])
            : redirect()->route('advertiser.network.country-wise-bidding')->with('success', 'Bidding rule updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        CountryWiseBidding::where('advertiser_id', $request->user()->id)->findOrFail($id)->delete();

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => 'Bidding rule deleted successfully.'])
            : redirect()->route('advertiser.network.country-wise-bidding')->with('success', 'Bidding rule deleted successfully.');
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
            return true;
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
