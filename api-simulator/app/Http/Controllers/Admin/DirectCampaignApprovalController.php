<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DirectCampaign;
use App\Models\User;
use Illuminate\Http\Request;

class DirectCampaignApprovalController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'integer'],
            'payment_type' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $query = $this->buildBaseQuery($request);

        $totalAmount = $this->sumApprovalAmount($query);
        $approvedCount = (clone $query)->where('aq_direct_campaigns.status', 'active')->count();
        $pendingCount = (clone $query)->where('aq_direct_campaigns.status', 'pending_review')->count();
        $thisMonthAmount = $this->sumApprovalAmount(
            (clone $query)->whereBetween('aq_direct_campaigns.created_at', [now()->startOfMonth(), now()->endOfMonth()])
        );

        $campaigns = $query
            ->with(['advertiser.userProfile'])
            ->orderByDesc('aq_direct_campaigns.created_at')
            ->select('aq_direct_campaigns.*')
            ->selectRaw($this->amountExpression() . ' as approval_amount')
            ->paginate(20)
            ->withQueryString();

        $users = User::where('role', 'advertiser')
            ->where('is_deleted', false)
            ->with('userProfile')
            ->orderBy('email')
            ->get();

        $paymentTypes = $this->paymentTypeLabels();

        $statuses = [
            'pending_review' => 'Pending Review',
            'active' => 'Approved',
            'rejected' => 'Rejected',
        ];

        return view('admin.direct-campaign-approvals.index', compact(
            'campaigns',
            'users',
            'paymentTypes',
            'statuses',
            'totalAmount',
            'approvedCount',
            'pendingCount',
            'thisMonthAmount'
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

    public function export(Request $request)
    {
        $paymentTypes = $this->paymentTypeLabels();

        $campaigns = $this->buildBaseQuery($request)
            ->with(['advertiser.userProfile'])
            ->orderByDesc('aq_direct_campaigns.created_at')
            ->select('aq_direct_campaigns.*')
            ->selectRaw($this->amountExpression() . ' as approval_amount')
            ->get();

        $filename = 'direct_campaign_approvals_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($campaigns, $paymentTypes) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Paid Date', 'Name', 'Email', 'Amount', 'Payment Type', 'Status']);

            foreach ($campaigns as $campaign) {
                $name = trim(($campaign->advertiser->userProfile->first_name ?? '') . ' ' . ($campaign->advertiser->userProfile->last_name ?? ''))
                    ?: ($campaign->brand_name ?: $campaign->name);

                fputcsv($file, [
                    $campaign->id,
                    optional($campaign->created_at)->format('Y-m-d H:i:s') ?: '',
                    $name,
                    $campaign->advertiser->email ?? 'N/A',
                    number_format((float) $campaign->approval_amount, 2, '.', ''),
                    $paymentTypes[$campaign->pricing_model] ?? strtoupper((string) $campaign->pricing_model),
                    ucwords(str_replace('_', ' ', $campaign->status)),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
                                    ->orWhere('aq_user_profiles.last_name', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        if ($userId = $request->get('user_id')) {
            $query->where('aq_direct_campaigns.advertiser_id', $userId);
        }

        if ($paymentType = $request->get('payment_type')) {
            $query->where('aq_direct_campaigns.pricing_model', $paymentType);
        }

        if ($status = $request->get('status')) {
            $query->where('aq_direct_campaigns.status', $status);
        }

        if ($startDate = $request->get('start_date')) {
            $query->whereDate('aq_direct_campaigns.created_at', '>=', $startDate);
        }

        if ($endDate = $request->get('end_date')) {
            $query->whereDate('aq_direct_campaigns.created_at', '<=', $endDate);
        }

        return $query;
    }

    protected function paymentTypeLabels(): array
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

    protected function amountExpression(): string
    {
        return 'COALESCE(aq_direct_campaigns.total_budget, aq_direct_campaigns.daily_budget, aq_direct_campaigns.bid_amount, 0)';
    }

    protected function sumApprovalAmount($query): float
    {
        return (float) ((clone $query)
            ->selectRaw('COALESCE(SUM(' . $this->amountExpression() . '), 0) as aggregate')
            ->value('aggregate') ?? 0);
    }
}
