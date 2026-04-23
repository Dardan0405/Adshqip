<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\User;
use App\Support\AdminEventNotifier;
use App\Support\AdvertiserNotificationManager;
use Illuminate\Http\Request;

class CampaignApprovalController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'advertiser_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'string', 'max:50'],
            'campaign_type' => ['nullable', 'string', 'max:50'],
        ]);

        $query = $this->buildBaseQuery($request);

        $totalCampaigns = (clone $query)->count();
        $approvedCount = (clone $query)->where('aq_campaigns.status', 'active')->count();
        $pendingCount = (clone $query)->where('aq_campaigns.status', 'pending_review')->count();
        $rejectedCount = (clone $query)->where('aq_campaigns.status', 'rejected')->count();
        $totalBudget = (float) ((clone $query)->sum('aq_campaigns.total_budget') ?? 0);

        $campaigns = $query
            ->with(['advertiser.userProfile'])
            ->orderByDesc('aq_campaigns.created_at')
            ->select('aq_campaigns.*')
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

        $campaignTypes = $this->campaignTypeLabels();

        return view('admin.campaign-approvals.index', compact(
            'campaigns',
            'advertisers',
            'statuses',
            'campaignTypes',
            'totalCampaigns',
            'approvedCount',
            'pendingCount',
            'rejectedCount',
            'totalBudget'
        ));
    }

    public function approve(Request $request, $id)
    {
        $campaign = Campaign::where('is_deleted', false)
            ->where('status', 'pending_review')
            ->findOrFail($id);

        $campaign->update([
            'status' => 'active',
            'admin_approved' => true,
        ]);

        app(AdminEventNotifier::class)->notifyAdmins(
            'Campaign Approved',
            'Campaign "' . $campaign->name . '" was approved by admin.',
            'success',
            route('admin.campaigns.show', $campaign->id),
        );

        $campaign->loadMissing('advertiser.profile');

        if ($campaign->advertiser) {
            app(AdvertiserNotificationManager::class)->deliver(
                $campaign->advertiser,
                'campaign_approved',
                'Campaign Approved',
                'Your campaign "' . $campaign->name . '" has been approved.',
                route('advertiser.campaigns.show', $campaign->id),
                $request->user()?->id
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Campaign approved successfully.',
        ]);
    }

    public function reject(Request $request, $id)
    {
        $campaign = Campaign::where('is_deleted', false)
            ->where('status', 'pending_review')
            ->findOrFail($id);

        $campaign->update([
            'status' => 'rejected',
            'admin_approved' => false,
        ]);

        app(AdminEventNotifier::class)->notifyAdmins(
            'Campaign Rejected',
            'Campaign "' . $campaign->name . '" was rejected by admin.',
            'warning',
            route('admin.campaigns.show', $campaign->id),
        );

        $campaign->loadMissing('advertiser.profile');

        if ($campaign->advertiser) {
            app(AdvertiserNotificationManager::class)->deliver(
                $campaign->advertiser,
                'campaign_rejected',
                'Campaign Rejected',
                'Your campaign "' . $campaign->name . '" has been rejected.',
                route('advertiser.campaigns.show', $campaign->id),
                $request->user()?->id
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Campaign rejected successfully.',
        ]);
    }

    protected function buildBaseQuery(Request $request)
    {
        $query = Campaign::query()
            ->join('aq_users', 'aq_campaigns.advertiser_id', '=', 'aq_users.id')
            ->where('aq_campaigns.is_deleted', false)
            ->where('aq_users.is_deleted', false)
            ->where('aq_users.role', 'advertiser')
            ->whereIn('aq_campaigns.status', ['pending_review', 'active', 'rejected']);

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($inner) use ($search) {
                $inner
                    ->when(is_numeric($search), fn ($q) => $q->orWhere('aq_campaigns.id', (int) $search))
                    ->orWhere('aq_campaigns.name', 'like', '%' . $search . '%')
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
            $query->where('aq_campaigns.status', $status);
        }

        if ($campaignType = $request->get('campaign_type')) {
            $query->where('aq_campaigns.campaign_type', $campaignType);
        }

        return $query;
    }

    protected function campaignTypeLabels(): array
    {
        return [
            'cpm' => 'CPM',
            'cpc' => 'CPC',
            'cpa' => 'CPA',
            'cpv' => 'CPV',
            'cpv_ctw' => 'CPV Click-to-Watch',
        ];
    }
}
