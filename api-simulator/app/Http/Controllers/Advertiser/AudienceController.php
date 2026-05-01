<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\AdvertiserAudience;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AudienceController extends Controller
{
    private const TYPES = ['custom', 'retargeting', 'lookalike', 'interest'];
    private const STATUSES = ['active', 'paused', 'archived'];
    private const DEVICES = ['desktop', 'mobile', 'tablet', 'smart_tv'];
    private const COUNTRIES = [
        'AL' => 'Albania',
        'XK' => 'Kosovo',
        'MK' => 'North Macedonia',
        'ME' => 'Montenegro',
        'RS' => 'Serbia',
        'BA' => 'Bosnia and Herzegovina',
        'HR' => 'Croatia',
        'SI' => 'Slovenia',
        'GR' => 'Greece',
        'BG' => 'Bulgaria',
        'RO' => 'Romania',
        'TR' => 'Turkey',
        'US' => 'United States',
        'DE' => 'Germany',
        'CH' => 'Switzerland',
        'AT' => 'Austria',
        'IT' => 'Italy',
        'GB' => 'United Kingdom',
    ];

    public function index(Request $request)
    {
        $audiences = AdvertiserAudience::query()
            ->withCount('campaigns')
            ->with(['campaigns' => fn ($query) => $query->where('aq_campaigns.is_deleted', false)->select('aq_campaigns.id', 'aq_campaigns.name')])
            ->where('advertiser_id', auth()->id())
            ->where('is_deleted', false)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));
                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->input('type')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => AdvertiserAudience::where('advertiser_id', auth()->id())->where('is_deleted', false)->count(),
            'active' => AdvertiserAudience::where('advertiser_id', auth()->id())->where('is_deleted', false)->where('status', 'active')->count(),
            'campaigns' => Campaign::where('advertiser_id', auth()->id())->where('is_deleted', false)->where('audience_targeting_mode', '!=', 'none')->count(),
            'reach' => AdvertiserAudience::where('advertiser_id', auth()->id())->where('is_deleted', false)->sum('estimated_size'),
        ];

        $campaigns = Campaign::query()
            ->where('advertiser_id', auth()->id())
            ->where('is_deleted', false)
            ->orderBy('name')
            ->get(['id', 'name', 'status', 'audience_targeting_mode']);

        return view('advertiser.audiences.index', [
            'audiences' => $audiences,
            'campaigns' => $campaigns,
            'summary' => $summary,
            'types' => self::TYPES,
            'statuses' => self::STATUSES,
            'countries' => self::COUNTRIES,
            'devices' => self::DEVICES,
        ]);
    }

    public function store(Request $request)
    {
        $payload = $this->payload($request);
        $payload['advertiser_id'] = auth()->id();
        $payload['slug'] = $this->uniqueSlug($payload['name']);
        $payload['estimated_size'] = $this->estimateSize($payload);

        AdvertiserAudience::create($payload);

        return redirect()->route('advertiser.audiences')->with('success', 'Audience created successfully.');
    }

    public function update(Request $request, AdvertiserAudience $audience)
    {
        $this->authorizeAudience($audience);

        $payload = $this->payload($request);
        $payload['slug'] = $this->uniqueSlug($payload['name'], $audience->id);
        $payload['estimated_size'] = $this->estimateSize($payload);

        $audience->update($payload);

        return redirect()->route('advertiser.audiences')->with('success', 'Audience updated successfully.');
    }

    public function attach(Request $request)
    {
        $validated = $request->validate([
            'campaign_id' => ['required', 'integer'],
            'audience_ids' => ['required', 'array', 'min:1'],
            'audience_ids.*' => ['integer'],
            'mode' => ['required', 'in:include,exclude'],
        ]);

        $campaign = Campaign::where('advertiser_id', auth()->id())
            ->where('is_deleted', false)
            ->findOrFail($validated['campaign_id']);

        $audienceIds = AdvertiserAudience::where('advertiser_id', auth()->id())
            ->where('is_deleted', false)
            ->whereIn('id', $validated['audience_ids'])
            ->pluck('id')
            ->all();

        foreach ($audienceIds as $audienceId) {
            $campaign->audiences()->syncWithoutDetaching([
                $audienceId => ['mode' => $validated['mode']],
            ]);
        }

        $this->syncCampaignAudienceMode($campaign);

        return redirect()->route('advertiser.audiences')->with('success', 'Audience targeting attached to campaign.');
    }

    public function detach(Campaign $campaign, AdvertiserAudience $audience)
    {
        abort_unless($campaign->advertiser_id === auth()->id() && ! $campaign->is_deleted, 404);
        $this->authorizeAudience($audience);

        $campaign->audiences()->detach($audience->id);
        $this->syncCampaignAudienceMode($campaign);

        return redirect()->route('advertiser.audiences')->with('success', 'Audience detached from campaign.');
    }

    public function destroy(AdvertiserAudience $audience)
    {
        $this->authorizeAudience($audience);

        $audience->campaigns()->detach();
        $audience->update(['is_deleted' => true, 'status' => 'archived']);

        Campaign::where('advertiser_id', auth()->id())->get()->each(fn (Campaign $campaign) => $this->syncCampaignAudienceMode($campaign));

        return redirect()->route('advertiser.audiences')->with('success', 'Audience deleted successfully.');
    }

    private function payload(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'type' => ['required', 'in:' . implode(',', self::TYPES)],
            'status' => ['required', 'in:' . implode(',', self::STATUSES)],
            'description' => ['nullable', 'string', 'max:2000'],
            'countries' => ['nullable', 'array'],
            'countries.*' => ['string', 'max:3'],
            'devices' => ['nullable', 'array'],
            'devices.*' => ['string', 'in:' . implode(',', self::DEVICES)],
            'interests' => ['nullable', 'string', 'max:2000'],
            'keywords' => ['nullable', 'string', 'max:2000'],
            'min_visits' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'days_since_visit' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        return [
            'name' => $validated['name'],
            'type' => $validated['type'],
            'status' => $validated['status'],
            'source' => 'manual',
            'description' => $validated['description'] ?? null,
            'countries' => array_values($validated['countries'] ?? []),
            'devices' => array_values($validated['devices'] ?? []),
            'interests' => $this->lines($validated['interests'] ?? ''),
            'keywords' => $this->lines($validated['keywords'] ?? ''),
            'rules' => [
                'min_visits' => (int) ($validated['min_visits'] ?? 0),
                'days_since_visit' => (int) ($validated['days_since_visit'] ?? 30),
            ],
        ];
    }

    private function lines(string $value): array
    {
        return array_values(array_filter(array_unique(array_map('trim', preg_split('/[\r\n,]+/', $value)))));
    }

    private function estimateSize(array $payload): int
    {
        $score = 1000;
        $score += count($payload['countries'] ?? []) * 1200;
        $score += count($payload['devices'] ?? []) * 800;
        $score += count($payload['interests'] ?? []) * 450;
        $score += count($payload['keywords'] ?? []) * 300;

        if (($payload['type'] ?? 'custom') === 'lookalike') {
            $score *= 3;
        }

        if (($payload['type'] ?? 'custom') === 'retargeting') {
            $score = max(250, (int) ($score * 0.45));
        }

        return min(500000, max(0, (int) $score));
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'audience';
        $slug = $base;
        $counter = 2;

        while (AdvertiserAudience::where('advertiser_id', auth()->id())
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function authorizeAudience(AdvertiserAudience $audience): void
    {
        abort_unless($audience->advertiser_id === auth()->id() && ! $audience->is_deleted, 404);
    }

    private function syncCampaignAudienceMode(Campaign $campaign): void
    {
        $modes = $campaign->audiences()
            ->where('aq_advertiser_audiences.is_deleted', false)
            ->pluck('aq_campaign_audience.mode')
            ->unique()
            ->values()
            ->all();

        $mode = match (true) {
            in_array('include', $modes, true) && in_array('exclude', $modes, true) => 'both',
            in_array('include', $modes, true) => 'include',
            in_array('exclude', $modes, true) => 'exclude',
            default => 'none',
        };

        $campaign->update(['audience_targeting_mode' => $mode]);
    }
}
