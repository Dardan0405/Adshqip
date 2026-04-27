<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Category;
use App\Models\DisplayScreen;
use App\Models\PlatformSetting;
use App\Models\Site;
use App\Models\TelegramMiniApp;
use App\Models\Zone;
use App\Http\Controllers\ZoneServeController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PublisherAdMarketController extends Controller
{
    /** Statuses visible on the AdMarket (exclude drafts and rejected). */
    private const VISIBLE_STATUSES = ['active', 'paused', 'pending_review'];

    public function index(Request $request): View
    {
        $publisherId = (int) $request->user()->id;

        $allSites = Site::query()
            ->where('publisher_id', $publisherId)
            ->where('is_deleted', false)
            ->orderBy('name')
            ->get(['id', 'name', 'domain']);

        $mobileApps = TelegramMiniApp::query()
            ->where('user_id', $publisherId)
            ->where('is_deleted', false)
            ->orderBy('app_name')
            ->get(['id', 'app_name', 'app_url']);

        $base = Campaign::query()->where('is_deleted', false)->whereIn('status', self::VISIBLE_STATUSES);

        $countries = (clone $base)
            ->whereNotNull('targeting_geo')
            ->pluck('targeting_geo')
            ->flatten()
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        $campaignTypes = (clone $base)
            ->whereNotNull('campaign_type')
            ->distinct()
            ->orderBy('campaign_type')
            ->pluck('campaign_type')
            ->filter()
            ->values()
            ->all();

        // Get display screen sizes from database
        $displayScreenSizes = DisplayScreen::query()
            ->where('status', 'active')
            ->orderBy('width')
            ->orderBy('height')
            ->get()
            ->mapWithKeys(fn (DisplayScreen $screen) => [
                $screen->width . 'x' . $screen->height => $screen->screen_name . ' (' . $screen->width . 'x' . $screen->height . ')'
            ])
            ->all();

        // Ad formats structure (same as Admin)
        $adFormats = [
            'display_web' => [
                'label' => 'Display Web',
                'sizes' => $displayScreenSizes,
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
        ];

        $categories = Category::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('publisher.admarket.index', [
            'allSites'      => $allSites,
            'mobileApps'    => $mobileApps,
            'countries'     => $countries,
            'campaignTypes' => $campaignTypes,
            'adFormats'     => $adFormats,
            'categories'    => $categories,
            'balance'       => 17491.53,
        ]);
    }

    public function campaigns(Request $request): JsonResponse
    {
        $publisherId = (int) $request->user()->id;
        $filter   = $request->input('filter', 'all');
        $country  = $request->input('country');
        $category = $request->input('category');
        $size     = $request->input('size');
        $page     = max(1, (int) $request->input('page', 1));

        // Guard: table may not exist if migration hasn't been run yet
        $favoriteCampaignIds = $this->getFavoriteIds($publisherId);

        $query = Campaign::query()
            ->with([
                // No limit() here — that applies globally across all parents in Eloquent eager loads
                'ads' => function ($q) {
                    $q->where('is_deleted', false)->orderByDesc('id')->with(['primaryCreative']);
                },
                'categories:id,name',
            ])
            ->where('is_deleted', false)
            ->whereIn('status', self::VISIBLE_STATUSES);

        if ($filter === 'favorite') {
            $query->whereIn('id', $favoriteCampaignIds ?: [0]);
        }

        if ($country) {
            $query->whereJsonContains('targeting_geo', $country);
        }

        if ($size) {
            // Check if it's a dimension (e.g. "300x250") or a format type (e.g. "text", "native")
            $dimensions = explode('x', $size);
            $specialFormats = ['text', 'native', 'interstitial', 'popunder', 'direct_link', 'in_page_push', 'social_bar'];
            $videoFormats = ['instream', 'outstream', 'rewarded'];

            if (count($dimensions) === 2 && is_numeric($dimensions[0]) && is_numeric($dimensions[1])) {
                // Filter by creative dimensions (Display Web)
                $width = (int) $dimensions[0];
                $height = (int) $dimensions[1];
                $query->whereHas('ads.creatives', function ($q) use ($width, $height) {
                    $q->where('width', $width)->where('height', $height);
                });
            } elseif (in_array($size, $specialFormats)) {
                // Filter by special web ad type
                $adType = match ($size) {
                    'text', 'social_bar' => 'text',
                    'native' => 'native',
                    default => 'rich_media',
                };
                $query->whereHas('ads', function ($q) use ($adType) {
                    $q->where('is_deleted', false)->where('ad_type', $adType);
                });
            } elseif (in_array($size, $videoFormats)) {
                // Filter by video ad type (Display Video)
                $query->whereHas('ads', function ($q) {
                    $q->where('is_deleted', false)->where('ad_type', 'video');
                });
            }
        }

        if ($category) {
            $query->whereHas('categories', function ($q) use ($category) {
                $q->where('aq_categories.id', (int) $category);
            });
        }

        $campaigns = $query->orderByDesc('bid_amount')->paginate(24, ['*'], 'page', $page);

        $data = $campaigns->map(function (Campaign $campaign) use ($favoriteCampaignIds) {
            // Pick only the first ad per campaign in PHP (eager-load limit is unreliable)
            $ad       = $campaign->ads->first();
            $creative = $ad?->primaryCreative;

            $imageUrl = null;
            if ($creative?->thumbnail_path) {
                // Support both storage/ and uploads/ paths
                $thumbPath = ltrim($creative->thumbnail_path, '/');
                $imageUrl = str_starts_with($thumbPath, 'uploads/')
                    ? asset($thumbPath)
                    : asset('storage/' . $thumbPath);
            } elseif ($creative?->file_path) {
                // Support both storage/ and uploads/ paths
                $filePath = ltrim($creative->file_path, '/');
                $imageUrl = str_starts_with($filePath, 'uploads/')
                    ? asset($filePath)
                    : asset('storage/' . $filePath);
            } elseif ($ad?->brand_logo_url) {
                $imageUrl = $ad->brand_logo_url;
            }

            return [
                'id'            => $campaign->id,
                'name'          => $campaign->name,
                'campaign_type' => $campaign->campaign_type,
                'bid_amount'    => (float) ($campaign->bid_amount ?? 0),
                'targeting_geo' => $campaign->targeting_geo ?? [],
                'ad_formats'    => $campaign->ad_formats ?? [],
                'categories'    => $campaign->categories->pluck('name')->all(),
                'image_url'     => $imageUrl,
                'headline'      => $ad?->headline,
                'brand_name'    => $ad?->brand_name,
                'is_favorite'   => in_array($campaign->id, $favoriteCampaignIds, true),
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $campaigns->currentPage(),
                'last_page'    => $campaigns->lastPage(),
                'total'        => $campaigns->total(),
            ],
            'favorites' => $this->favoritesPayload($publisherId),
        ]);
    }

    public function toggleFavorite(Request $request, int $id): JsonResponse
    {
        $publisherId = (int) $request->user()->id;

        $campaign = Campaign::query()
            ->where('is_deleted', false)
            ->whereIn('status', self::VISIBLE_STATUSES)
            ->findOrFail($id);

        if (! Schema::hasTable('publisher_admarket_favorites')) {
            return response()->json(['success' => false, 'message' => 'Favorites table not migrated yet.'], 503);
        }

        $exists = DB::table('publisher_admarket_favorites')
            ->where('publisher_id', $publisherId)
            ->where('campaign_id', $campaign->id)
            ->exists();

        if ($exists) {
            DB::table('publisher_admarket_favorites')
                ->where('publisher_id', $publisherId)
                ->where('campaign_id', $campaign->id)
                ->delete();
            $favorited = false;
        } else {
            DB::table('publisher_admarket_favorites')->insertOrIgnore([
                'publisher_id' => $publisherId,
                'campaign_id'  => $campaign->id,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
            $favorited = true;
        }

        return response()->json([
            'success'       => true,
            'favorited'     => $favorited,
            'campaign_id'   => $campaign->id,
            'campaign_name' => $campaign->name,
            'bid_amount'    => (float) ($campaign->bid_amount ?? 0),
            'favorites'     => $this->favoritesPayload($publisherId),
        ]);
    }

    private function getFavoriteIds(int $publisherId): array
    {
        if (! Schema::hasTable('publisher_admarket_favorites')) {
            return [];
        }

        return DB::table('publisher_admarket_favorites')
            ->where('publisher_id', $publisherId)
            ->pluck('campaign_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    private function favoritesPayload(int $publisherId): array
    {
        if (! Schema::hasTable('publisher_admarket_favorites')) {
            return [];
        }

        return DB::table('publisher_admarket_favorites as f')
            ->join('aq_campaigns as c', 'c.id', '=', 'f.campaign_id')
            ->where('f.publisher_id', $publisherId)
            ->where('c.is_deleted', false)
            ->select('c.id', 'c.name', 'c.bid_amount', 'c.campaign_type')
            ->orderByDesc('f.created_at')
            ->get()
            ->map(fn ($row) => [
                'id'            => (int) $row->id,
                'name'          => $row->name,
                'bid_amount'    => (float) ($row->bid_amount ?? 0),
                'campaign_type' => $row->campaign_type,
            ])
            ->all();
    }

    /**
     * Get campaign details for the detail modal.
     */
    public function getCampaignDetail(Request $request, int $id): JsonResponse
    {
        $campaign = Campaign::query()
            ->with([
                'ads' => function ($q) {
                    $q->where('is_deleted', false)->with('primaryCreative');
                },
                'categories:id,name',
            ])
            ->where('is_deleted', false)
            ->whereIn('status', self::VISIBLE_STATUSES)
            ->findOrFail($id);

        $ad = $campaign->ads->first();
        $creative = $ad?->primaryCreative;

        $imageUrl = null;
        if ($creative?->thumbnail_path) {
            $thumbPath = ltrim($creative->thumbnail_path, '/');
            $imageUrl = str_starts_with($thumbPath, 'uploads/')
                ? asset($thumbPath)
                : asset('storage/' . $thumbPath);
        } elseif ($creative?->file_path) {
            $filePath = ltrim($creative->file_path, '/');
            $imageUrl = str_starts_with($filePath, 'uploads/')
                ? asset($filePath)
                : asset('storage/' . $filePath);
        }

        return response()->json([
            'success' => true,
            'campaign' => [
                'id'            => $campaign->id,
                'name'          => $campaign->name,
                'campaign_type' => $campaign->campaign_type,
                'bid_amount'    => (float) ($campaign->bid_amount ?? 0),
                'targeting_geo' => $campaign->targeting_geo ?? [],
                'ad_formats'    => $campaign->ad_formats ?? [],
                'categories'    => $campaign->categories->pluck('name')->all(),
                'image_url'     => $imageUrl,
                'headline'      => $ad?->headline,
                'brand_name'    => $ad?->brand_name,
                'body_text'     => $ad?->body_text,
                'destination_url' => $ad?->destination_url,
                'ad_type'       => $ad?->ad_type,
                'creative_size' => $creative ? ($creative->width . 'x' . $creative->height) : null,
            ],
        ]);
    }

    /**
     * Get publisher's zones/adblocks for tag generation.
     */
    public function getPublisherZones(Request $request): JsonResponse
    {
        $publisherId = (int) $request->user()->id;

        $zones = Zone::query()
            ->with(['site:id,name,domain', 'mobileApp:id,app_name'])
            ->where('is_deleted', false)
            ->where('status', 'active')
            ->where(function ($builder) use ($publisherId) {
                $builder->whereHas('site', function ($siteQuery) use ($publisherId) {
                    $siteQuery->where('publisher_id', $publisherId)->where('is_deleted', false);
                })->orWhereHas('mobileApp', function ($appQuery) use ($publisherId) {
                    $appQuery->where('user_id', $publisherId)->where('is_deleted', false);
                });
            })
            ->orderBy('name')
            ->get()
            ->map(fn (Zone $zone) => [
                'id'        => $zone->id,
                'name'      => $zone->name,
                'site_name' => $zone->site?->name ?? $zone->mobileApp?->app_name ?? 'Unknown',
                'format'    => $zone->format_key,
                'size'      => $zone->size_key,
            ]);

        return response()->json(['zones' => $zones]);
    }

    /**
     * Generate tag code for a campaign + zone combination.
     */
    public function generateCampaignTag(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'campaign_id' => 'required|integer',
            'zone_id'     => 'required|integer',
        ]);

        $publisherId = (int) $request->user()->id;

        // Verify campaign exists and is visible
        $campaign = Campaign::query()
            ->where('is_deleted', false)
            ->whereIn('status', self::VISIBLE_STATUSES)
            ->findOrFail($validated['campaign_id']);

        // Verify zone belongs to publisher
        $zone = Zone::query()
            ->with(['site', 'mobileApp'])
            ->where('is_deleted', false)
            ->where(function ($query) use ($publisherId) {
                $query->whereHas('site', function ($siteQuery) use ($publisherId) {
                    $siteQuery->where('publisher_id', $publisherId)->where('is_deleted', false);
                })->orWhereHas('mobileApp', function ($appQuery) use ($publisherId) {
                    $appQuery->where('user_id', $publisherId)->where('is_deleted', false);
                });
            })
            ->findOrFail($validated['zone_id']);

        $codes = $this->generateCampaignInvocationCodes($campaign, $zone);

        return response()->json([
            'success' => true,
            'campaign_name' => $campaign->name,
            'zone_name' => $zone->name,
            'codes' => $codes,
        ]);
    }

    /**
     * Generate ad rotator code for multiple campaigns.
     */
    public function generateRotator(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'campaign_ids' => 'required|array|min:1',
            'campaign_ids.*' => 'integer',
            'zone_id' => 'required|integer',
        ]);

        $publisherId = (int) $request->user()->id;

        // Verify all campaigns exist and are visible
        $campaigns = Campaign::query()
            ->where('is_deleted', false)
            ->whereIn('status', self::VISIBLE_STATUSES)
            ->whereIn('id', $validated['campaign_ids'])
            ->get();

        if ($campaigns->count() !== count($validated['campaign_ids'])) {
            return response()->json(['success' => false, 'message' => 'Some campaigns are not available.'], 422);
        }

        // Verify zone belongs to publisher
        $zone = Zone::query()
            ->with(['site', 'mobileApp'])
            ->where('is_deleted', false)
            ->where(function ($query) use ($publisherId) {
                $query->whereHas('site', function ($siteQuery) use ($publisherId) {
                    $siteQuery->where('publisher_id', $publisherId)->where('is_deleted', false);
                })->orWhereHas('mobileApp', function ($appQuery) use ($publisherId) {
                    $appQuery->where('user_id', $publisherId)->where('is_deleted', false);
                });
            })
            ->findOrFail($validated['zone_id']);

        $rotatorCode = $this->generateRotatorCode($campaigns, $zone);

        return response()->json([
            'success' => true,
            'campaign_count' => $campaigns->count(),
            'zone_name' => $zone->name,
            'rotator_code' => $rotatorCode,
        ]);
    }

    /**
     * Generate invocation codes for a specific campaign + zone.
     */
    private function generateCampaignInvocationCodes(Campaign $campaign, Zone $zone): array
    {
        $token = ZoneServeController::encodeToken($zone->id);
        $serveDomain = PlatformSetting::getServeDomain();
        $servePath = PlatformSetting::getServePath();
        $serveUrl = "{$serveDomain}{$servePath}/{$token}.js";

        $campaignParam = '?cid=' . $campaign->id;
        $serveUrlWithCampaign = $serveUrl . $campaignParam;

        $sizeStyle = '';
        if ($zone->size_key && preg_match('/^(\d+)x(\d+)$/', $zone->size_key, $m)) {
            $sizeStyle = " style=\"width:{$m[1]}px;height:{$m[2]}px;\"";
        }

        return [
            'js' => '<div id="adshqip-zone-' . $zone->id . '" data-zone-id="' . $zone->id . '" data-campaign-id="' . $campaign->id . '"></div>' . "\n"
                . '<script async src="' . e($serveUrlWithCampaign) . '"></script>',
            'iframe' => '<iframe src="' . e($serveUrlWithCampaign) . '" loading="lazy" frameborder="0" scrolling="no"' . $sizeStyle . '></iframe>',
            'async' => "<!-- AdShqip Campaign Tag -->\n"
                . '<script async data-zone="' . $zone->id . '" data-campaign="' . $campaign->id . '" src="' . e($serveUrl) . '"></script>',
        ];
    }

    /**
     * Generate rotator code for multiple campaigns.
     */
    private function generateRotatorCode($campaigns, Zone $zone): string
    {
        $token = ZoneServeController::encodeToken($zone->id);
        $serveDomain = PlatformSetting::getServeDomain();
        $servePath = PlatformSetting::getServePath();
        $serveUrl = "{$serveDomain}{$servePath}/{$token}.js";

        $campaignIds = $campaigns->pluck('id')->implode(',');
        $campaignNames = $campaigns->pluck('name')->map(fn ($n) => '"' . addslashes($n) . '"')->implode(', ');

        $code = "<!-- AdShqip Ad Rotator - {$campaigns->count()} Campaigns -->\n";
        $code .= '<div id="adshqip-rotator-' . $zone->id . '" data-zone-id="' . $zone->id . '" data-rotator-campaigns="' . $campaignIds . '"></div>' . "\n";
        $code .= "<script>\n";
        $code .= "(function() {\n";
        $code .= "    var campaigns = [" . $campaignIds . "];\n";
        $code .= "    var campaignNames = [" . $campaignNames . "];\n";
        $code .= "    var currentIndex = Math.floor(Math.random() * campaigns.length);\n";
        $code .= "    var container = document.getElementById('adshqip-rotator-{$zone->id}');\n";
        $code .= "    container.setAttribute('data-campaign-id', campaigns[currentIndex]);\n";
        $code .= "    var script = document.createElement('script');\n";
        $code .= "    script.async = true;\n";
        $code .= "    script.src = '" . $serveUrl . "?cid=' + campaigns[currentIndex];\n";
        $code .= "    document.body.appendChild(script);\n";
        $code .= "})();\n";
        $code .= "</script>";

        return $code;
    }
}
