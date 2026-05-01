<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Http\Request;

class ManageAdMarketCampaignController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $query = User::query()
            ->where('role', 'advertiser')
            ->where('is_deleted', false)
            ->whereHas('campaigns', function ($campaignQuery) {
                $campaignQuery->where('is_deleted', false)->where('admarket_enabled', true)->where('admarket_status', 'listed');
            })
            ->with([
                'profile',
                'campaigns' => function ($campaignQuery) {
                    $campaignQuery
                        ->where('is_deleted', false)
                        ->where('admarket_enabled', true)
                        ->where('admarket_status', 'listed')
                        ->orderByDesc('created_at');
                },
            ]);

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($inner) use ($search) {
                $inner
                    ->when(is_numeric($search), fn ($q) => $q->orWhere('id', (int) $search))
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhereHas('profile', function ($profileQuery) use ($search) {
                        $profileQuery
                            ->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('company_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('campaigns', function ($campaignQuery) use ($search) {
                        $campaignQuery
                            ->where('is_deleted', false)
                            ->where('admarket_enabled', true)
                            ->where('admarket_status', 'listed')
                            ->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $advertisers = $query
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $advertiserIds = User::query()
            ->where('role', 'advertiser')
            ->where('is_deleted', false)
            ->whereHas('campaigns', function ($campaignQuery) {
                $campaignQuery->where('is_deleted', false)->where('admarket_enabled', true)->where('admarket_status', 'listed');
            })
            ->pluck('id');

        $totalAdvertisers = $advertiserIds->count();
        $suspendedAdvertisers = User::query()
            ->whereIn('id', $advertiserIds)
            ->where('status', 'suspended')
            ->count();
        $totalCampaigns = Campaign::query()
            ->whereIn('advertiser_id', $advertiserIds)
            ->where('is_deleted', false)
            ->where('admarket_enabled', true)
            ->where('admarket_status', 'listed')
            ->count();
        $pausedCampaigns = Campaign::query()
            ->whereIn('advertiser_id', $advertiserIds)
            ->where('is_deleted', false)
            ->where('admarket_enabled', true)
            ->where('admarket_status', 'listed')
            ->where('status', 'paused')
            ->count();

        return view('admin.manage-admarket-campaigns.index', compact(
            'advertisers',
            'totalAdvertisers',
            'suspendedAdvertisers',
            'totalCampaigns',
            'pausedCampaigns'
        ));
    }

    public function disallowAdvertiser($id)
    {
        $advertiser = User::query()
            ->where('role', 'advertiser')
            ->where('is_deleted', false)
            ->whereHas('campaigns', function ($campaignQuery) {
                $campaignQuery->where('is_deleted', false)->where('admarket_enabled', true)->where('admarket_status', 'listed');
            })
            ->findOrFail($id);

        $advertiser->update([
            'status' => 'suspended',
        ]);

        Campaign::query()
            ->where('advertiser_id', $advertiser->id)
            ->where('is_deleted', false)
            ->where('admarket_enabled', true)
            ->where('admarket_status', 'listed')
            ->update([
                'status' => 'paused',
                'admarket_status' => 'suspended',
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Advertiser and all AdMarket campaigns were disallowed successfully.',
        ]);
    }

    public function disallowCampaign($id)
    {
        $campaign = Campaign::query()
            ->where('is_deleted', false)
            ->findOrFail($id);

        $campaign->update([
            'status' => 'paused',
            'admarket_status' => 'suspended',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Campaign was disallowed successfully.',
        ]);
    }
}
