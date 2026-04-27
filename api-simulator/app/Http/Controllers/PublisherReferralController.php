<?php

namespace App\Http\Controllers;

use App\Models\ReferralConversion;
use App\Models\ReferralLink;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublisherReferralController extends Controller
{
    public function index(): View
    {
        $publisher = auth()->user();
        $advertiserLink = $this->resolveAdvertiserReferralLink($publisher);
        $publisherLink  = $this->resolvePublisherReferralLink($publisher);

        $conversions = ReferralConversion::query()
            ->with(['referredUser.userProfile'])
            ->where('referrer_id', $publisher->id)
            ->where('referred_role', 'advertiser')
            ->orderByDesc('created_at')
            ->paginate(15);

        $earnings = $this->advertiserEarnings($conversions->getCollection());

        $rows = $conversions->getCollection()->map(function (ReferralConversion $conversion) use ($earnings) {
            $referredUser = $conversion->referredUser;
            $profile = $referredUser?->userProfile;
            $name = trim((string) ($profile?->first_name . ' ' . $profile?->last_name));

            return (object) [
                'name' => $name !== '' ? $name : ($referredUser?->email ?? 'Unknown Advertiser'),
                'email' => $referredUser?->email ?? '-',
                'registration_date' => optional($referredUser?->created_at ?? $conversion->created_at),
                'earning' => (float) ($earnings[$conversion->referred_user_id] ?? 0),
                'referral_income' => (float) $conversion->commission_earned,
            ];
        });

        $conversions->setCollection($rows);

        return view('publisher.referrals.index', [
            'referralLink'          => $this->buildReferralUrl($advertiserLink),
            'publisherReferralLink' => $this->buildPublisherReferralUrl($publisherLink),
            'rows'                  => $conversions,
            'totalReferralIncome'   => (float) ReferralConversion::query()
                ->where('referrer_id', $publisher->id)
                ->where('referred_role', 'advertiser')
                ->sum('commission_earned'),
            'totalAdvertiserEarnings' => array_sum($earnings->all()),
            'totalReferrals'        => ReferralConversion::query()
                ->where('referrer_id', $publisher->id)
                ->where('referred_role', 'advertiser')
                ->count(),
            'totalPublisherReferrals' => ReferralConversion::query()
                ->where('referrer_id', $publisher->id)
                ->where('referred_role', 'publisher')
                ->count(),
        ]);
    }

    private function resolvePublisherReferralLink(User $publisher): ReferralLink
    {
        if (blank($publisher->referral_code)) {
            $publisher->update(['referral_code' => $this->uniqueCode()]);
            $publisher->refresh();
        }

        $link = ReferralLink::query()
            ->where('referrer_id', $publisher->id)
            ->where('target_role', 'publisher')
            ->where('is_deleted', false)
            ->orderByDesc('created_at')
            ->first();

        if ($link) {
            return $link;
        }

        $code = $this->uniqueCode();

        return ReferralLink::query()->create([
            'referrer_id'             => $publisher->id,
            'code'                    => $code,
            'target_role'             => 'publisher',
            'campaign_name'           => 'Publisher Publisher Referral',
            'landing_url'             => route('register', ['role' => 'publisher', 'ref' => $code]),
            'commission_type'         => 'percentage',
            'commission_rate'         => 5,
            'commission_duration_days' => 365,
            'status'                  => 'active',
            'is_deleted'              => false,
        ]);
    }

    private function resolveAdvertiserReferralLink(User $publisher): ReferralLink
    {
        if (blank($publisher->referral_code)) {
            $publisher->update(['referral_code' => $this->uniqueCode()]);
            $publisher->refresh();
        }

        $link = ReferralLink::query()
            ->where('referrer_id', $publisher->id)
            ->where('target_role', 'advertiser')
            ->where('is_deleted', false)
            ->orderByDesc('created_at')
            ->first();

        if ($link) {
            return $link;
        }

        return ReferralLink::query()->create([
            'referrer_id' => $publisher->id,
            'code' => $publisher->referral_code,
            'target_role' => 'advertiser',
            'campaign_name' => 'Publisher Advertiser Referral',
            'landing_url' => route('register', ['role' => 'advertiser', 'ref' => $publisher->referral_code]),
            'commission_type' => 'percentage',
            'commission_rate' => 5,
            'commission_duration_days' => 365,
            'status' => 'active',
            'is_deleted' => false,
        ]);
    }

    private function buildReferralUrl(ReferralLink $referralLink): string
    {
        return route('register', [
            'role' => 'advertiser',
            'ref' => $referralLink->code,
        ]);
    }

    private function buildPublisherReferralUrl(ReferralLink $referralLink): string
    {
        return route('register', [
            'role' => 'publisher',
            'ref' => $referralLink->code,
        ]);
    }

    private function advertiserEarnings(Collection $conversions): Collection
    {
        $ids = $conversions->pluck('referred_user_id')->filter()->unique()->values();

        if ($ids->isEmpty() || ! Schema::hasTable('aq_transactions')) {
            return collect();
        }

        return Transaction::query()
            ->selectRaw('user_id, SUM(amount) as total_amount')
            ->whereIn('user_id', $ids)
            ->where('type', 'ad_spend')
            ->where('status', 'completed')
            ->groupBy('user_id')
            ->pluck('total_amount', 'user_id')
            ->map(fn ($value) => (float) $value);
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
