<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CountryWiseBidding;
use App\Models\DirectCampaign;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CountryWiseBiddingController extends Controller
{
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

    protected function getCampaignOptionsForAdvertiser(int $advertiserId)
    {
        $networkCampaigns = Campaign::where('advertiser_id', $advertiserId)
            ->where('is_deleted', false)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Campaign $campaign) => [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'type' => 'network',
                'label' => $campaign->name,
            ]);

        $directCampaigns = DirectCampaign::where('advertiser_id', $advertiserId)
            ->where('is_deleted', false)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (DirectCampaign $campaign) => [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'type' => 'direct',
                'label' => $campaign->name . ' (Direct)',
            ]);

        return $networkCampaigns
            ->concat($directCampaigns)
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    protected function campaignExistsForAdvertiser(int $advertiserId, ?int $campaignId, ?string $campaignType): bool
    {
        if (! $campaignId || ! $campaignType) {
            return true;
        }

        if ($campaignType === 'direct') {
            return DirectCampaign::where('id', $campaignId)
                ->where('advertiser_id', $advertiserId)
                ->where('is_deleted', false)
                ->exists();
        }

        return Campaign::where('id', $campaignId)
            ->where('advertiser_id', $advertiserId)
            ->where('is_deleted', false)
            ->exists();
    }

    /**
     * Display a listing of country-wise bidding rules
     */
    public function index(Request $request)
    {
        $query = CountryWiseBidding::with(['advertiser.profile', 'campaign', 'directCampaign']);

        // Search filter
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('advertiser', function ($subQ) use ($search) {
                    $subQ->where('email', 'like', "%{$search}%")
                         ->orWhereHas('profile', function ($profQ) use ($search) {
                             $profQ->where('first_name', 'like', "%{$search}%")
                                   ->orWhere('last_name', 'like', "%{$search}%")
                                   ->orWhere('company_name', 'like', "%{$search}%");
                         });
                })
                ->orWhereHas('campaign', function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('directCampaign', function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                })
                ->orWhere('country_code', 'like', "%{$search}%");
            });
        }

        // Type filter
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        // Advertiser filter
        if ($advertiserId = $request->get('advertiser_id')) {
            $query->where('advertiser_id', $advertiserId);
        }

        // Country filter
        if ($country = $request->get('country')) {
            $query->where('country_code', $country);
        }

        $biddings = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get all advertisers for filter dropdown
        $advertisers = User::where('role', 'advertiser')
            ->where('is_deleted', false)
            ->with('profile')
            ->orderBy('email')
            ->get();

        // Statistics
        $totalBiddings = CountryWiseBidding::count();
        $totalAdvertisers = CountryWiseBidding::distinct('advertiser_id')->count();
        $avgBidValue = CountryWiseBidding::avg('bid_value') ?? 0;
        $cpcCount = CountryWiseBidding::where('type', 'CPC')->count();
        $cpmCount = CountryWiseBidding::where('type', 'CPM')->count();
        $cpaCount = CountryWiseBidding::where('type', 'CPA')->count();

        return view('admin.country-wise-bidding.index', compact(
            'biddings',
            'advertisers',
            'totalBiddings',
            'totalAdvertisers',
            'avgBidValue',
            'cpcCount',
            'cpmCount',
            'cpaCount'
        ));
    }

    /**
     * Store a newly created bidding rule
     */
    public function store(Request $request)
    {
        [$campaignId, $campaignType] = $this->normalizeCampaignSelection($request->input('campaign_id'));

        $validator = Validator::make($request->all(), [
            'advertiser_id' => 'required|exists:aq_users,id',
            'type' => 'required|in:CPC,CPM,CPA,CPV',
            'country_code' => 'required|string|size:2',
            'bid_value' => 'required|numeric|min:0',
        ]);

        $validator->after(function ($validator) use ($request, $campaignId, $campaignType) {
            if (! $this->campaignExistsForAdvertiser((int) $request->advertiser_id, $campaignId, $campaignType)) {
                $validator->errors()->add('campaign_id', 'The selected campaign is invalid for this advertiser.');
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        CountryWiseBidding::create([
            'advertiser_id' => $request->advertiser_id,
            'campaign_id' => $campaignId,
            'campaign_type' => $campaignId ? $campaignType : null,
            'type' => $request->type,
            'country_code' => strtoupper($request->country_code),
            'bid_value' => $request->bid_value,
        ]);

        return redirect()->route('admin.country-wise-bidding')->with('success', 'Country-wise bidding rule created successfully.');
    }

    /**
     * Show the specified bidding rule (for edit modal)
     */
    public function show($id)
    {
        $bidding = CountryWiseBidding::with(['advertiser.profile', 'campaign', 'directCampaign'])->findOrFail($id);

        // Get campaigns for the advertiser
        $campaigns = $this->getCampaignOptionsForAdvertiser($bidding->advertiser_id);

        return response()->json([
            'id' => $bidding->id,
            'advertiser_id' => $bidding->advertiser_id,
            'campaign_id' => $bidding->campaign_id,
            'campaign_type' => $bidding->campaign_type ?? 'network',
            'type' => $bidding->type,
            'country_code' => $bidding->country_code,
            'bid_value' => $bidding->bid_value,
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Update the specified bidding rule
     */
    public function update(Request $request, $id)
    {
        $bidding = CountryWiseBidding::findOrFail($id);
        [$campaignId, $campaignType] = $this->normalizeCampaignSelection($request->input('campaign_id'));

        $validator = Validator::make($request->all(), [
            'advertiser_id' => 'required|exists:aq_users,id',
            'type' => 'required|in:CPC,CPM,CPA,CPV',
            'country_code' => 'required|string|size:2',
            'bid_value' => 'required|numeric|min:0',
        ]);

        $validator->after(function ($validator) use ($request, $campaignId, $campaignType) {
            if (! $this->campaignExistsForAdvertiser((int) $request->advertiser_id, $campaignId, $campaignType)) {
                $validator->errors()->add('campaign_id', 'The selected campaign is invalid for this advertiser.');
            }
        });

        if ($validator->fails()) {
            if (! $request->expectsJson()) {
                return back()->withErrors($validator)->withInput();
            }

            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $bidding->update([
            'advertiser_id' => $request->advertiser_id,
            'campaign_id' => $campaignId,
            'campaign_type' => $campaignId ? $campaignType : null,
            'type' => $request->type,
            'country_code' => strtoupper($request->country_code),
            'bid_value' => $request->bid_value,
        ]);

        if (! $request->expectsJson()) {
            return redirect()->route('admin.country-wise-bidding');
        }

        return response()->json(['success' => true, 'message' => 'Bidding rule updated successfully.']);
    }

    /**
     * Remove the specified bidding rule
     */
    public function destroy($id)
    {
        $bidding = CountryWiseBidding::findOrFail($id);
        $bidding->delete();

        return response()->json(['success' => true, 'message' => 'Bidding rule deleted successfully.']);
    }

    /**
     * Get campaigns for a specific advertiser (AJAX)
     */
    public function getCampaigns($advertiserId)
    {
        return response()->json($this->getCampaignOptionsForAdvertiser((int) $advertiserId));
    }

    /**
     * Export bidding rules to CSV
     */
    public function export(Request $request)
    {
        $query = CountryWiseBidding::with(['advertiser.profile', 'campaign', 'directCampaign']);

        // Apply same filters as index
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('advertiser', function ($subQ) use ($search) {
                    $subQ->where('email', 'like', "%{$search}%")
                         ->orWhereHas('profile', function ($profQ) use ($search) {
                             $profQ->where('first_name', 'like', "%{$search}%")
                                   ->orWhere('last_name', 'like', "%{$search}%")
                                   ->orWhere('company_name', 'like', "%{$search}%");
                         });
                })
                ->orWhereHas('campaign', function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('directCampaign', function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                })
                ->orWhere('country_code', 'like', "%{$search}%");
            });
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($advertiserId = $request->get('advertiser_id')) {
            $query->where('advertiser_id', $advertiserId);
        }

        if ($country = $request->get('country')) {
            $query->where('country_code', $country);
        }

        $biddings = $query->orderBy('created_at', 'desc')->get();

        $filename = 'country_wise_bidding_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($biddings) {
            $file = fopen('php://output', 'w');

            // CSV Header
            fputcsv($file, ['ID', 'Advertiser', 'Email', 'Company', 'Campaign', 'Type', 'Country Code', 'Bid Value', 'Created At']);

            // CSV Rows
            foreach ($biddings as $bidding) {
                $advertiserName = trim(($bidding->advertiser->profile->first_name ?? '') . ' ' . ($bidding->advertiser->profile->last_name ?? ''));
                if (!$advertiserName) {
                    $advertiserName = $bidding->advertiser->email;
                }

                fputcsv($file, [
                    $bidding->id,
                    $advertiserName,
                    $bidding->advertiser->email,
                    $bidding->advertiser->profile->company_name ?? '',
                    $bidding->selected_campaign?->name ?? 'All Campaigns',
                    $bidding->type,
                    $bidding->country_code,
                    '$' . number_format($bidding->bid_value, 2),
                    $bidding->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
