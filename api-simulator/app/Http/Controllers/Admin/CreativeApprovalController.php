<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\User;
use App\Support\AdvertiserNotificationManager;
use Illuminate\Http\Request;

class CreativeApprovalController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'advertiser_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $query = $this->buildBaseQuery($request);

        $totalCreatives = (clone $query)->count();
        $approvedCount = (clone $query)->where('aq_ads.status', 'active')->count();
        $pendingCount = (clone $query)->where('aq_ads.status', 'pending_review')->count();
        $rejectedCount = (clone $query)->where('aq_ads.status', 'rejected')->count();

        $ads = $query
            ->with(['campaign.advertiser.userProfile', 'primaryCreative'])
            ->orderByDesc('aq_ads.created_at')
            ->select('aq_ads.*')
            ->paginate(20)
            ->withQueryString();

        $advertisers = User::where('role', 'advertiser')
            ->where('is_deleted', false)
            ->with('userProfile')
            ->orderBy('email')
            ->get();

        $statuses = [
            'pending_review' => 'Pending Review',
            'active' => 'Approved',
            'rejected' => 'Rejected',
        ];

        return view('admin.creative-approvals.index', compact(
            'ads',
            'advertisers',
            'statuses',
            'totalCreatives',
            'approvedCount',
            'pendingCount',
            'rejectedCount'
        ));
    }

    public function approve(Request $request, $id)
    {
        $ad = Ad::where('is_deleted', false)
            ->where('status', 'pending_review')
            ->findOrFail($id);

        $ad->update([
            'status' => 'active',
            'admin_approved' => true,
        ]);

        $ad->loadMissing('campaign.advertiser.profile');

        if ($ad->campaign?->advertiser) {
            app(AdvertiserNotificationManager::class)->deliver(
                $ad->campaign->advertiser,
                'creative_approved',
                'Creative Approved',
                'Your creative "' . $ad->name . '" has been approved.',
                route('advertiser.adformats.edit', $ad->id),
                $request->user()?->id
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Creative approved successfully.',
        ]);
    }

    public function reject(Request $request, $id)
    {
        $ad = Ad::where('is_deleted', false)
            ->where('status', 'pending_review')
            ->findOrFail($id);

        $ad->update([
            'status' => 'rejected',
            'admin_approved' => false,
        ]);

        $ad->loadMissing('campaign.advertiser.profile');

        if ($ad->campaign?->advertiser) {
            app(AdvertiserNotificationManager::class)->deliver(
                $ad->campaign->advertiser,
                'creative_rejected',
                'Creative Rejected',
                'Your creative "' . $ad->name . '" has been rejected.',
                route('advertiser.adformats.edit', $ad->id),
                $request->user()?->id
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Creative rejected successfully.',
        ]);
    }

    protected function buildBaseQuery(Request $request)
    {
        $query = Ad::query()
            ->join('aq_campaigns', 'aq_ads.campaign_id', '=', 'aq_campaigns.id')
            ->join('aq_users', 'aq_campaigns.advertiser_id', '=', 'aq_users.id')
            ->where('aq_ads.is_deleted', false)
            ->where('aq_users.is_deleted', false)
            ->where('aq_users.role', 'advertiser')
            ->whereIn('aq_ads.status', ['pending_review', 'active', 'rejected']);

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($inner) use ($search) {
                $inner
                    ->when(is_numeric($search), fn ($q) => $q->orWhere('aq_ads.id', (int) $search))
                    ->orWhere('aq_ads.name', 'like', '%' . $search . '%')
                    ->orWhere('aq_users.email', 'like', '%' . $search . '%')
                    ->orWhereExists(function ($profileQuery) use ($search) {
                        $profileQuery
                            ->selectRaw('1')
                            ->from('aq_user_profiles')
                            ->whereColumn('aq_user_profiles.user_id', 'aq_users.id')
                            ->where(function ($nameQuery) use ($search) {
                                $nameQuery
                                    ->where('aq_user_profiles.first_name', 'like', '%' . $search . '%')
                                    ->orWhere('aq_user_profiles.last_name', 'like', '%' . $search . '%')
                                    ->orWhere('aq_user_profiles.company_name', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        if ($advertiserId = $request->get('advertiser_id')) {
            $query->where('aq_campaigns.advertiser_id', $advertiserId);
        }

        if ($status = $request->get('status')) {
            $query->where('aq_ads.status', $status);
        }

        return $query;
    }
}
