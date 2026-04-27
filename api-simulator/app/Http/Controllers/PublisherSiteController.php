<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DisplayScreen;
use App\Models\Site;
use App\Models\StatDaily;
use App\Models\TelegramMiniApp;
use App\Support\PublisherNotificationManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PublisherSiteController extends Controller
{
    public function __construct(private readonly PublisherNotificationManager $notifier) {}

    private function getActiveDisplayScreens(): array
    {
        if (! Schema::hasTable('aq_display_screens')) {
            return [];
        }

        return DisplayScreen::query()
            ->where('status', 'active')
            ->orderBy('screen_name')
            ->get()
            ->map(function (DisplayScreen $screen) {
                return [
                    'id' => $screen->id,
                    'screen_name' => $screen->screen_name,
                    'value' => $screen->value,
                    'width' => $screen->width,
                    'height' => $screen->height,
                    'dimension' => $screen->width . 'x' . $screen->height,
                    'label' => $screen->screen_name . ' (' . $screen->width . 'x' . $screen->height . ')',
                ];
            })
            ->toArray();
    }

    public function index(Request $request): View
    {
        $publisherId = (int) $request->user()->id;
        $search = trim((string) $request->input('search', ''));

        $query = Site::query()
            ->with('categories')
            ->where('publisher_id', $publisherId)
            ->where('is_deleted', false);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('domain', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%');
            });
        }

        $sites = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $allSites = Site::query()
            ->where('publisher_id', $publisherId)
            ->where('is_deleted', false)
            ->orderBy('name')
            ->get(['id', 'name', 'domain']);

        $stats = StatDaily::query()
            ->whereIn('site_id', $sites->pluck('id'))
            ->selectRaw('
                site_id,
                SUM(impressions) as total_impressions,
                SUM(clicks) as total_clicks,
                SUM(COALESCE(publisher_earnings, revenue)) as total_revenue,
                CASE WHEN SUM(impressions) > 0 THEN ((SUM(clicks) * 100.0) / SUM(impressions)) ELSE 0 END as ctr,
                CASE WHEN SUM(impressions) > 0 THEN (SUM(COALESCE(publisher_earnings, revenue)) / SUM(impressions) * 1000) ELSE 0 END as ecp
            ')
            ->groupBy('site_id')
            ->get()
            ->keyBy('site_id');

        return view('publisher.sites.index', [
            'sites' => $sites,
            'allSites' => $allSites,
            'stats' => $stats,
            'categories' => Category::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'mobileApps' => TelegramMiniApp::query()
                ->where('user_id', $publisherId)
                ->where('is_deleted', false)
                ->where('status', 'active')
                ->orderBy('app_name')
                ->get(['id', 'app_name', 'app_url']),
            'adFormats' => [
                'display_web' => [
                    'label' => 'Display Web',
                    'sizes' => [
                        '300x250' => '300x250 (Medium Rectangle)',
                        '728x90' => '728x90 (Leaderboard)',
                        '160x600' => '160x600 (Wide Skyscraper)',
                        '300x600' => '300x600 (Half Page)',
                        '320x50' => '320x50 (Mobile Banner)',
                        '970x250' => '970x250 (Billboard)',
                    ],
                ],
                'special_web' => [
                    'label' => 'Special Web',
                    'sizes' => [
                        'text' => 'Text Ad',
                        'native' => 'Native Ad',
                        'interstitial' => 'Interstitial',
                        'popunder' => 'Popunder',
                        'direct_link' => 'Direct Link',
                        'in_page_push' => 'In-Page Push',
                        'social_bar' => 'Social Bar',
                    ],
                ],
                'display_video' => [
                    'label' => 'Display Video',
                    'sizes' => [
                        'instream' => 'In-Stream Video',
                        'outstream' => 'Out-Stream Video',
                        'rewarded' => 'Rewarded Video',
                    ],
                ],
            ],
            'displayScreens' => $this->getActiveDisplayScreens(),
            'search' => $search,
            'balance' => 17491.53,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:255'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:aq_categories,id'],
        ]);

        $site = Site::query()->create([
            'publisher_id' => $request->user()->id,
            'name' => $validated['name'],
            'domain' => $this->normalizeDomain($validated['url']),
            'category' => $this->categorySummary($validated['category_ids'] ?? []),
            'language' => 'sq',
            'status' => 'pending_review',
            'is_deleted' => false,
        ]);

        $site->categories()->sync($validated['category_ids'] ?? []);

        $this->notifier->deliver(
            $request->user()->fresh('profile'),
            'site_add',
            'Website Added',
            'Your website "' . $site->name . '" has been submitted and is pending review.',
            route('publisher.sites')
        );

        return redirect()
            ->route('publisher.sites')
            ->with('success', 'Website created successfully.');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $site = $this->findPublisherSite($request, $id)->load('categories:id,name');

        $stats = StatDaily::query()
            ->where('site_id', $site->id)
            ->selectRaw('
                SUM(impressions) as total_impressions,
                SUM(clicks) as total_clicks,
                SUM(COALESCE(publisher_earnings, revenue)) as total_revenue,
                CASE WHEN SUM(impressions) > 0 THEN ((SUM(clicks) * 100.0) / SUM(impressions)) ELSE 0 END as ctr,
                CASE WHEN SUM(impressions) > 0 THEN (SUM(COALESCE(publisher_earnings, revenue)) / SUM(impressions) * 1000) ELSE 0 END as ecp
            ')
            ->first();

        return response()->json([
            'id' => $site->id,
            'name' => $site->name,
            'domain' => $site->domain,
            'url' => $this->siteUrl($site->domain),
            'category' => $site->category,
            'category_ids' => $site->categories->pluck('id')->values()->all(),
            'category_names' => $site->categories->pluck('name')->values()->all(),
            'status' => $site->status,
            'impressions' => (int) ($stats->total_impressions ?? 0),
            'clicks' => (int) ($stats->total_clicks ?? 0),
            'ctr' => round((float) ($stats->ctr ?? 0), 2),
            'ecp' => round((float) ($stats->ecp ?? 0), 2),
            'revenue' => round((float) ($stats->total_revenue ?? 0), 2),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $site = $this->findPublisherSite($request, $id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:255'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:aq_categories,id'],
        ]);

        $site->update([
            'name' => $validated['name'],
            'domain' => $this->normalizeDomain($validated['url']),
            'category' => $this->categorySummary($validated['category_ids'] ?? []),
        ]);

        $site->categories()->sync($validated['category_ids'] ?? []);

        $this->notifier->deliver(
            $request->user()->fresh('profile'),
            'site_edit',
            'Website Updated',
            'Your website "' . $site->name . '" has been updated successfully.',
            route('publisher.sites')
        );

        return redirect()
            ->route('publisher.sites')
            ->with('success', 'Website updated successfully.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $site = $this->findPublisherSite($request, $id);
        $name = $site->name;
        $site->update(['is_deleted' => true]);

        $this->notifier->deliver(
            $request->user()->fresh('profile'),
            'site_delete',
            'Website Deleted',
            'Your website "' . $name . '" has been deleted.',
            route('publisher.sites')
        );

        return response()->json([
            'success' => true,
            'message' => 'Website deleted successfully.',
        ]);
    }

    private function findPublisherSite(Request $request, int $id): Site
    {
        return Site::query()
            ->where('publisher_id', $request->user()->id)
            ->where('is_deleted', false)
            ->findOrFail($id);
    }

    private function categorySummary(array $categoryIds): ?string
    {
        if ($categoryIds === []) {
            return null;
        }

        $names = Category::query()
            ->whereIn('id', $categoryIds)
            ->orderBy('name')
            ->pluck('name')
            ->all();

        return $names === [] ? null : implode(', ', $names);
    }

    private function normalizeDomain(string $value): string
    {
        $candidate = trim($value);

        if ($candidate === '') {
            return $candidate;
        }

        $normalized = preg_match('#^https?://#i', $candidate) ? $candidate : 'https://' . $candidate;
        $host = parse_url($normalized, PHP_URL_HOST);

        return $host ?: preg_replace('#^https?://#i', '', $candidate);
    }

    private function siteUrl(string $domain): string
    {
        return preg_match('#^https?://#i', $domain) ? $domain : 'https://' . $domain;
    }
}
