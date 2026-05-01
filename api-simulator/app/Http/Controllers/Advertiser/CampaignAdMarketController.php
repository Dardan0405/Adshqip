<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;

class CampaignAdMarketController extends Controller
{
    public function index(Request $request)
    {
        $campaigns = Campaign::query()
            ->withCount('ads')
            ->where('advertiser_id', auth()->id())
            ->where('is_deleted', false)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));
                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('market_status'), function ($query) use ($request) {
                match ($request->input('market_status')) {
                    'listed' => $query->where('admarket_enabled', true)->where('admarket_status', 'listed'),
                    'unlisted' => $query->where(function ($nested) {
                        $nested->where('admarket_enabled', false)->orWhere('admarket_status', 'unlisted');
                    }),
                    'suspended' => $query->where('admarket_status', 'suspended'),
                    default => null,
                };
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => Campaign::where('advertiser_id', auth()->id())->where('is_deleted', false)->count(),
            'listed' => Campaign::where('advertiser_id', auth()->id())->where('is_deleted', false)->where('admarket_enabled', true)->where('admarket_status', 'listed')->count(),
            'eligible' => Campaign::where('advertiser_id', auth()->id())->where('is_deleted', false)->whereIn('status', ['active', 'paused', 'pending_review'])->whereHas('ads', fn ($query) => $query->where('is_deleted', false))->count(),
            'suspended' => Campaign::where('advertiser_id', auth()->id())->where('is_deleted', false)->where('admarket_status', 'suspended')->count(),
        ];

        return view('advertiser.campaign-admarket.index', compact('campaigns', 'summary'));
    }

    public function publish(Campaign $campaign)
    {
        $this->authorizeCampaign($campaign);

        if (! $this->isEligible($campaign)) {
            return redirect()
                ->route('advertiser.campaign-admarket')
                ->withErrors(['campaign' => 'Campaign must be active, paused, or pending review and have at least one creative before it can be listed.']);
        }

        $campaign->update([
            'admarket_enabled' => true,
            'admarket_status' => 'listed',
            'admarket_notes' => null,
            'admarket_published_at' => $campaign->admarket_published_at ?: now(),
        ]);

        return redirect()->route('advertiser.campaign-admarket')->with('success', 'Campaign listed on AdMarket.');
    }

    public function unpublish(Campaign $campaign)
    {
        $this->authorizeCampaign($campaign);

        $campaign->update([
            'admarket_enabled' => false,
            'admarket_status' => 'unlisted',
        ]);

        return redirect()->route('advertiser.campaign-admarket')->with('success', 'Campaign removed from AdMarket.');
    }

    public function updateSettings(Request $request, Campaign $campaign)
    {
        $this->authorizeCampaign($campaign);

        $validated = $request->validate([
            'distribution_mode' => ['required', 'in:all_networks,selected_networks,msn_exclusive'],
            'msn_enabled' => ['nullable', 'boolean'],
            'msn_exclusive' => ['nullable', 'boolean'],
            'msn_bid_adjustment' => ['nullable', 'numeric', 'min:-100', 'max:500'],
            'admarket_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $campaign->update([
            'distribution_mode' => $validated['distribution_mode'],
            'msn_enabled' => (bool) ($validated['msn_enabled'] ?? false),
            'msn_exclusive' => $validated['distribution_mode'] === 'msn_exclusive' || (bool) ($validated['msn_exclusive'] ?? false),
            'msn_bid_adjustment' => $validated['msn_bid_adjustment'] ?? null,
            'admarket_notes' => $validated['admarket_notes'] ?? null,
        ]);

        return redirect()->route('advertiser.campaign-admarket')->with('success', 'AdMarket settings updated.');
    }

    private function authorizeCampaign(Campaign $campaign): void
    {
        abort_unless($campaign->advertiser_id === auth()->id() && ! $campaign->is_deleted, 404);
    }

    private function isEligible(Campaign $campaign): bool
    {
        return in_array($campaign->status, ['active', 'paused', 'pending_review'], true)
            && $campaign->ads()->where('is_deleted', false)->exists();
    }
}
