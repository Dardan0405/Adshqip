<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralConversion;
use App\Models\ReferralLink;
use App\Models\ReferralPayout;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReferralCodeController extends Controller
{
    public function index(Request $request)
    {
        $linksQuery = ReferralLink::query()
            ->with(['referrer.userProfile'])
            ->withCount('conversions');

        if ($search = trim((string) $request->input('search'))) {
            $linksQuery->where(function ($query) use ($search) {
                $query->where('code', 'like', '%' . $search . '%')
                    ->orWhere('campaign_name', 'like', '%' . $search . '%')
                    ->orWhereHas('referrer', fn ($userQuery) => $userQuery->where('email', 'like', '%' . $search . '%'));
            });
        }

        if ($status = $request->input('status')) {
            $linksQuery->where('status', $status);
        }

        if ($targetRole = $request->input('target_role')) {
            $linksQuery->where('target_role', $targetRole);
        }

        if ($referrerId = $request->input('referrer_id')) {
            $linksQuery->where('referrer_id', $referrerId);
        }

        $links = $linksQuery->latest('created_at')->paginate(15)->withQueryString();

        $summary = [
            'links' => ReferralLink::where('is_deleted', false)->count(),
            'signups' => ReferralLink::sum('total_signups'),
            'qualified' => ReferralConversion::where('is_qualified', true)->count(),
            'earned' => (float) ReferralLink::sum('total_earned'),
            'payouts' => (float) ReferralPayout::sum('amount'),
        ];

        $users = User::query()
            ->where('is_deleted', false)
            ->orderBy('email')
            ->get(['id', 'email', 'role', 'referral_code']);

        return view('admin.referral-codes.index', compact('links', 'summary', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'referrer_id' => ['required', 'exists:aq_users,id'],
            'target_role' => ['required', 'in:advertiser,publisher,any'],
            'campaign_name' => ['nullable', 'string', 'max:255'],
            'landing_url' => ['nullable', 'url', 'max:500'],
            'commission_type' => ['required', 'in:percentage,flat'],
            'commission_rate' => ['required', 'numeric', 'min:0'],
            'commission_duration_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'max_commission_per_referral' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,paused,expired,revoked'],
        ]);

        $referrer = User::findOrFail($validated['referrer_id']);

        if (blank($referrer->referral_code)) {
            $referrer->referral_code = $this->uniqueCode();
            $referrer->save();
        }

        $code = $referrer->referral_code;

        if (ReferralLink::where('code', $code)->exists()) {
            $code = $this->uniqueCode();
        }

        ReferralLink::create([
            'referrer_id' => $validated['referrer_id'],
            'code' => $code,
            'target_role' => $validated['target_role'],
            'campaign_name' => $validated['campaign_name'] ?: null,
            'landing_url' => $validated['landing_url'] ?: null,
            'commission_type' => $validated['commission_type'],
            'commission_rate' => $validated['commission_rate'],
            'commission_duration_days' => $validated['commission_duration_days'] ?: 365,
            'max_commission_per_referral' => $validated['max_commission_per_referral'] ?: null,
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.referral-codes')
            ->with('success', 'Referral code created successfully.');
    }

    public function updateStatus(Request $request, ReferralLink $referralLink)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,paused,expired,revoked'],
        ]);

        $referralLink->update(['status' => $validated['status']]);

        return redirect()
            ->route('admin.referral-codes')
            ->with('success', 'Referral code status updated successfully.');
    }

    private function uniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (
            User::where('referral_code', $code)->exists()
            || ReferralLink::where('code', $code)->exists()
        );

        return $code;
    }
}
