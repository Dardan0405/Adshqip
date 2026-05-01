<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\ReferralConversion;
use App\Models\ReferralLink;
use App\Models\ReferralPayout;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReferralProgramController extends Controller
{
    public function index(Request $request)
    {
        $advertiser = $request->user();
        $advertiserLink = $this->resolveDefaultLink($advertiser, 'advertiser');
        $publisherLink = $this->resolveDefaultLink($advertiser, 'publisher');

        $links = ReferralLink::query()
            ->withCount('conversions')
            ->where('referrer_id', $advertiser->id)
            ->where('is_deleted', false)
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->get()
            ->map(function (ReferralLink $link) {
                $link->share_url = $this->buildReferralUrl($link);
                return $link;
            });

        $conversionsQuery = ReferralConversion::query()
            ->with(['link', 'referredUser.userProfile'])
            ->where('referrer_id', $advertiser->id);

        if ($targetRole = $request->query('target_role')) {
            if (in_array($targetRole, ['advertiser', 'publisher'], true)) {
                $conversionsQuery->where('referred_role', $targetRole);
            }
        }

        $conversions = $conversionsQuery
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $payouts = ReferralPayout::query()
            ->where('referrer_id', $advertiser->id)
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('advertiser.referrals.index', [
            'advertiserLink' => $this->buildReferralUrl($advertiserLink),
            'publisherLink' => $this->buildReferralUrl($publisherLink),
            'links' => $links,
            'conversions' => $conversions,
            'payouts' => $payouts,
            'summary' => $this->summary($advertiser->id),
            'filters' => [
                'target_role' => $request->query('target_role', ''),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'target_role' => ['required', 'in:advertiser,publisher,any'],
            'campaign_name' => ['required', 'string', 'max:255'],
            'utm_source' => ['nullable', 'string', 'max:100'],
            'utm_medium' => ['nullable', 'string', 'max:100'],
            'utm_campaign' => ['nullable', 'string', 'max:100'],
        ]);

        ReferralLink::create([
            'referrer_id' => $request->user()->id,
            'code' => $this->uniqueCode(),
            'target_role' => $data['target_role'],
            'campaign_name' => $data['campaign_name'],
            'utm_source' => $data['utm_source'] ?: null,
            'utm_medium' => $data['utm_medium'] ?: null,
            'utm_campaign' => $data['utm_campaign'] ?: null,
            'commission_type' => 'percentage',
            'commission_rate' => 5,
            'commission_duration_days' => 365,
            'status' => 'active',
            'is_deleted' => false,
        ]);

        return redirect()
            ->route('advertiser.referrals')
            ->with('success', 'Referral campaign link created.');
    }

    public function updateStatus(Request $request, ReferralLink $referralLink)
    {
        $this->authorizeOwnedLink($request, $referralLink);

        $data = $request->validate([
            'status' => ['required', 'in:active,paused'],
        ]);

        $referralLink->update(['status' => $data['status']]);

        return redirect()
            ->route('advertiser.referrals')
            ->with('success', 'Referral link status updated.');
    }

    public function destroy(Request $request, ReferralLink $referralLink)
    {
        $this->authorizeOwnedLink($request, $referralLink);

        $referralLink->update([
            'status' => 'revoked',
            'is_deleted' => true,
        ]);

        return redirect()
            ->route('advertiser.referrals')
            ->with('success', 'Referral link archived.');
    }

    public function export(Request $request): StreamedResponse
    {
        $conversions = ReferralConversion::query()
            ->with(['link', 'referredUser.userProfile'])
            ->where('referrer_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        $filename = 'advertiser_referrals_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($conversions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Campaign', 'Code', 'Referred Role', 'Name', 'Email', 'Status', 'Qualified', 'Commission', 'Currency']);

            foreach ($conversions as $conversion) {
                $profile = $conversion->referredUser?->userProfile;
                $name = trim((string) (($profile?->first_name ?? '') . ' ' . ($profile?->last_name ?? '')));

                fputcsv($handle, [
                    $conversion->created_at?->format('Y-m-d H:i:s'),
                    $conversion->link?->campaign_name ?: '-',
                    $conversion->link?->code ?: '-',
                    $conversion->referred_role,
                    $name !== '' ? $name : '-',
                    $conversion->referredUser?->email ?: '-',
                    $conversion->status,
                    $conversion->is_qualified ? 'yes' : 'no',
                    number_format((float) $conversion->commission_earned, 4, '.', ''),
                    $conversion->commission_currency,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function resolveDefaultLink(User $advertiser, string $targetRole): ReferralLink
    {
        if (blank($advertiser->referral_code)) {
            $advertiser->update(['referral_code' => $this->uniqueCode()]);
            $advertiser->refresh();
        }

        $link = ReferralLink::query()
            ->where('referrer_id', $advertiser->id)
            ->where('target_role', $targetRole)
            ->where('is_deleted', false)
            ->orderBy('id')
            ->first();

        if ($link) {
            return $link;
        }

        $code = $targetRole === 'advertiser' && ! ReferralLink::where('code', $advertiser->referral_code)->exists()
            ? $advertiser->referral_code
            : $this->uniqueCode();

        return ReferralLink::create([
            'referrer_id' => $advertiser->id,
            'code' => $code,
            'target_role' => $targetRole,
            'campaign_name' => 'Advertiser ' . ucfirst($targetRole) . ' Referral',
            'commission_type' => 'percentage',
            'commission_rate' => 5,
            'commission_duration_days' => 365,
            'status' => 'active',
            'is_deleted' => false,
        ]);
    }

    private function buildReferralUrl(ReferralLink $link): string
    {
        return route('referrals.redirect', $link->code);
    }

    private function summary(int $advertiserId): array
    {
        return [
            'links' => ReferralLink::query()
                ->where('referrer_id', $advertiserId)
                ->where('is_deleted', false)
                ->count(),
            'signups' => ReferralConversion::query()
                ->where('referrer_id', $advertiserId)
                ->count(),
            'qualified' => ReferralConversion::query()
                ->where('referrer_id', $advertiserId)
                ->where('is_qualified', true)
                ->count(),
            'earned' => (float) ReferralConversion::query()
                ->where('referrer_id', $advertiserId)
                ->sum('commission_earned'),
            'pending_payouts' => (float) ReferralPayout::query()
                ->where('referrer_id', $advertiserId)
                ->whereIn('status', ['pending', 'processing'])
                ->sum('amount'),
            'paid_payouts' => (float) ReferralPayout::query()
                ->where('referrer_id', $advertiserId)
                ->where('status', 'completed')
                ->sum('amount'),
        ];
    }

    private function authorizeOwnedLink(Request $request, ReferralLink $referralLink): void
    {
        if ((int) $referralLink->referrer_id !== (int) $request->user()->id || $referralLink->is_deleted) {
            abort(404);
        }
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
