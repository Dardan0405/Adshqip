<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DirectCampaign;
use App\Models\User;
use Illuminate\Http\Request;

class DirectCampaignRequestApprovalController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'integer'],
            'campaign_type' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $query = $this->buildBaseQuery($request);

        $totalCampaigns = (clone $query)->count();
        $approvedCount = (clone $query)->where('aq_direct_campaigns.status', 'active')->count();
        $pendingCount = (clone $query)->where('aq_direct_campaigns.status', 'pending_review')->count();
        $rejectedCount = (clone $query)->where('aq_direct_campaigns.status', 'rejected')->count();
        $totalBudget = (float) ((clone $query)->selectRaw('COALESCE(SUM(' . $this->budgetExpression() . '), 0) as aggregate')->value('aggregate') ?? 0);

        $campaigns = $query
            ->with(['advertiser.userProfile'])
            ->orderByDesc('aq_direct_campaigns.created_at')
            ->select('aq_direct_campaigns.*')
            ->selectRaw($this->budgetExpression() . ' as approval_budget')
            ->paginate(20)
            ->withQueryString();

        $users = User::where('role', 'advertiser')
            ->where('is_deleted', false)
            ->with('userProfile')
            ->orderBy('email')
            ->get();

        $campaignTypes = $this->campaignTypeLabels();

        $statuses = [
            'pending_review' => 'Pending Review',
            'active' => 'Approved',
            'rejected' => 'Rejected',
        ];

        return view('admin.direct-campaign-request-approvals.index', compact(
            'campaigns',
            'users',
            'campaignTypes',
            'statuses',
            'totalCampaigns',
            'approvedCount',
            'pendingCount',
            'rejectedCount',
            'totalBudget'
        ));
    }

    public function approve($id)
    {
        $campaign = DirectCampaign::where('is_deleted', false)
            ->where('status', 'pending_review')
            ->findOrFail($id);

        $campaign->update([
            'status' => 'active',
            'admin_approved' => true,
            'rejection_reason' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Direct campaign approved successfully.',
        ]);
    }

    public function reject($id)
    {
        $campaign = DirectCampaign::where('is_deleted', false)
            ->where('status', 'pending_review')
            ->findOrFail($id);

        $campaign->update([
            'status' => 'rejected',
            'admin_approved' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Direct campaign rejected successfully.',
        ]);
    }

    protected function buildBaseQuery(Request $request)
    {
        $query = DirectCampaign::query()
            ->join('aq_users', 'aq_direct_campaigns.advertiser_id', '=', 'aq_users.id')
            ->where('aq_direct_campaigns.is_deleted', false)
            ->where('aq_users.is_deleted', false)
            ->where('aq_users.role', 'advertiser')
            ->whereIn('aq_direct_campaigns.status', ['pending_review', 'active', 'rejected']);

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($inner) use ($search) {
                $inner
                    ->when(is_numeric($search), fn ($q) => $q->orWhere('aq_direct_campaigns.id', (int) $search))
                    ->orWhere('aq_direct_campaigns.name', 'like', '%' . $search . '%')
                    ->orWhere('aq_direct_campaigns.brand_name', 'like', '%' . $search . '%')
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

        if ($userId = $request->get('user_id')) {
            $query->where('aq_direct_campaigns.advertiser_id', $userId);
        }

        if ($campaignType = $request->get('campaign_type')) {
            $query->where('aq_direct_campaigns.pricing_model', $campaignType);
        }

        if ($status = $request->get('status')) {
            $query->where('aq_direct_campaigns.status', $status);
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
            'flat_rate' => 'Flat Rate',
        ];
    }

    protected function budgetExpression(): string
    {
        return 'COALESCE(aq_direct_campaigns.total_budget, aq_direct_campaigns.daily_budget, aq_direct_campaigns.bid_amount, 0)';
    }
}
