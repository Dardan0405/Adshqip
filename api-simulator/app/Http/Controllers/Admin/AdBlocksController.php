<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ZoneServeController;
use App\Models\Ad;
use App\Models\Campaign;
use App\Models\DirectCampaign;
use App\Models\DirectCampaignZone;
use App\Models\PlatformSetting;
use App\Models\Zone;
use App\Models\Site;
use App\Models\StatDaily;
use App\Models\User;
use Illuminate\Http\Request;

class AdBlocksController extends Controller
{
    protected function syncDirectCampaignLink(Zone $zone, ?int $campaignId): void
    {
        DirectCampaignZone::where('zone_id', $zone->id)->delete();

        if (! $campaignId) {
            return;
        }

        DirectCampaignZone::create([
            'campaign_id' => $campaignId,
            'zone_id' => $zone->id,
            'priority' => 100,
            'is_active' => true,
        ]);
    }

    protected function buildServeUrl(Zone $zone): string
    {
        $token = ZoneServeController::encodeToken($zone->id);
        $serveDomain = PlatformSetting::getServeDomain();
        $servePath = PlatformSetting::getServePath();

        return "{$serveDomain}{$servePath}/{$token}.js";
    }

    protected function getCampaignAdblockTrackingUrl(Zone $zone): ?string
    {
        $campaign = Campaign::where('zone_id', $zone->id)
            ->where('is_deleted', false)
            ->where('status', 'active')
            ->latest('id')
            ->first();

        if (! $campaign) {
            return null;
        }

        $ad = Ad::where('campaign_id', $campaign->id)
            ->where('is_deleted', false)
            ->orderByDesc('id')
            ->first();

        if (! $ad) {
            return null;
        }

        return route('ad.adblock', $ad->id);
    }

    protected function buildSimpleEmbedCode(Zone $zone): string
    {
        $attrs = [];
        $attrs[] = 'id="adshqip-zone-' . $zone->id . '"';
        $attrs[] = 'data-zone-id="' . $zone->id . '"';

        if ($zone->format_key) {
            $attrs[] = 'data-format="' . htmlspecialchars($zone->format_key) . '"';
        }

        if ($zone->size_key) {
            $attrs[] = 'data-size="' . htmlspecialchars($zone->size_key) . '"';
        }

        if ($zone->size_key && preg_match('/^(\d+)x(\d+)$/', $zone->size_key, $m)) {
            $attrs[] = 'style="min-width:' . $m[1] . 'px;min-height:' . $m[2] . 'px;"';
        }

        $container = '<div ' . implode(' ', $attrs) . '></div>';
        $script = '<script async src="' . $this->buildServeUrl($zone) . '"></script>';

        return $container . "\n" . $script;
    }

    public function index(Request $request)
    {
        $query = Zone::with(['site'])
            ->where('is_deleted', false);

        // Filter by site
        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        // Filter by publisher
        if ($request->filled('publisher_id')) {
            $query->whereHas('site', function($q) use ($request) {
                $q->where('publisher_id', $request->publisher_id);
            });
        }

        // Filter by format
        if ($request->filled('format_id')) {
            $query->where('format_key', $request->format_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('site', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                         ->orWhere('domain', 'like', "%{$search}%");
                  });
            });
        }

        $zones = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        // Load stats for each zone
        $zoneIds = $zones->pluck('id');
        $stats = StatDaily::whereIn('zone_id', $zoneIds)
            ->selectRaw('zone_id,
                SUM(impressions) as total_impressions,
                SUM(clicks) as total_clicks,
                SUM(revenue) as total_revenue,
                SUM(publisher_earnings) as total_earnings,
                AVG(ecpm) as avg_ecpm,
                CASE WHEN SUM(impressions) > 0 THEN (SUM(clicks) / SUM(impressions)) * 100 ELSE 0 END as ctr')
            ->groupBy('zone_id')
            ->get()
            ->keyBy('zone_id');

        $sites = Site::where('is_deleted', false)
            ->where('status', 'active')
            ->with('publisher:id,email')
            ->orderBy('name')
            ->get();

        // Hardcoded ad formats (same as CampaignController)
        $adFormats = [
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
        ];

        // Format categories for filter dropdown
        $formatCategories = collect([]);
        foreach ($adFormats as $key => $format) {
            $formatCategories->push((object)[
                'format_key' => $key,
                'label' => $format['label'],
            ]);
        }

        // Full sizes list for wizard & edit modals
        $formats = collect([]);
        foreach ($adFormats as $key => $format) {
            foreach ($format['sizes'] as $sizeKey => $sizeName) {
                $formats->push((object)[
                    'name' => $sizeName,
                    'category' => $format['label'],
                    'format_key' => $key,
                    'size_key' => $sizeKey,
                ]);
            }
        }

        $publishers = User::where('role', 'publisher')
            ->where('is_deleted', false)
            ->with('profile')
            ->orderBy('email')
            ->get();

        $directCampaigns = DirectCampaign::where('is_deleted', false)
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get(['id', 'name', 'status']);

        $serveDomains = PlatformSetting::getServeDomains();
        $activeServeDomain = PlatformSetting::getServeDomain();
        $servePath = PlatformSetting::getServePath();

        return view('admin.adblocks.index', compact(
            'zones',
            'stats',
            'sites',
            'formats',
            'formatCategories',
            'publishers',
            'directCampaigns',
            'serveDomains',
            'activeServeDomain',
            'servePath'
        ));
    }

    public function updateServeSettings(Request $request)
    {
        $request->validate([
            'serve_domains' => 'nullable|string',
            'active_serve_domain' => 'required|string|max:255',
            'serve_path' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9_\/-]+$/'],
        ]);

        $domains = preg_split('/[\r\n,]+/', (string) $request->input('serve_domains', ''));
        $normalizedDomains = array_values(array_unique(array_filter(array_map(
            fn ($domain) => PlatformSetting::normalizeServeUrl($domain),
            $domains
        ))));

        $activeDomain = PlatformSetting::normalizeServeUrl($request->input('active_serve_domain'));
        if (! $activeDomain) {
            return back()->withErrors([
                'active_serve_domain' => 'Please enter a valid serve domain.',
            ])->withInput();
        }

        if (! in_array($activeDomain, $normalizedDomains, true)) {
            array_unshift($normalizedDomains, $activeDomain);
        }

        $servePath = '/' . trim($request->input('serve_path'), '/');

        PlatformSetting::setServeDomains($normalizedDomains);
        PlatformSetting::setValue('ad_serve_domain', $activeDomain, 'string', 'serving', 'Active anti-block serving domain');
        PlatformSetting::setValue('ad_serve_path', $servePath, 'string', 'serving', 'Obfuscated serving path');

        return redirect()->route('admin.adblocks')->with('success', 'Anti-block serve settings updated successfully.');
    }

    public function show($id)
    {
        $zone = Zone::with(['site.publisher.profile', 'directCampaignLinks.campaign'])->findOrFail($id);

        // Get stats for this zone
        $stats = StatDaily::where('zone_id', $id)
            ->selectRaw('SUM(impressions) as total_impressions,
                SUM(clicks) as total_clicks,
                SUM(revenue) as total_revenue,
                SUM(publisher_earnings) as total_earnings,
                AVG(ecpm) as avg_ecpm,
                CASE WHEN SUM(impressions) > 0 THEN (SUM(clicks) / SUM(impressions)) * 100 ELSE 0 END as ctr')
            ->first();

        $siteName = $zone->site ? $zone->site->name : 'Unknown';
        $formatName = $zone->format_key ? str_replace('_', ' ', ucwords($zone->format_key, '_')) : 'Unknown';
        $sizeName = $zone->size_key ? str_replace('_', ' ', ucwords($zone->size_key, '_')) : 'Auto';
        $linkedCampaign = $zone->directCampaignLinks
            ->sortByDesc('priority')
            ->first()?->campaign;

        $serveUrl = $this->buildServeUrl($zone);

        return response()->json([
            'id' => $zone->id,
            'name' => $zone->name,
            'site_id' => $zone->site_id,
            'site_name' => $siteName,
            'format_id' => $zone->format_id,
            'format_key' => $zone->format_key,
            'format_name' => $formatName,
            'size_id' => $zone->size_id,
            'size_key' => $zone->size_key,
            'size_name' => $sizeName,
            'placement' => $zone->placement,
            'floor_price' => $zone->floor_price,
            'status' => $zone->status,
            'ad_code' => $zone->ad_code,
            'serve_url' => $serveUrl,
            'preview_url' => route('admin.adblocks.preview', $zone->id),
            'impressions' => $stats->total_impressions ?? 0,
            'clicks' => $stats->total_clicks ?? 0,
            'revenue' => $stats->total_revenue ?? 0,
            'earnings' => $stats->total_earnings ?? 0,
            'ecpm' => round($stats->avg_ecpm ?? 0, 2),
            'ctr' => round($stats->ctr ?? 0, 2),
            'created_at' => $zone->created_at?->format('M d, Y'),
            'target_age_min' => $zone->target_age_min,
            'target_age_max' => $zone->target_age_max,
            'target_gender' => $zone->target_gender,
            'target_color' => $zone->target_color,
            'target_height_min' => $zone->target_height_min,
            'target_height_max' => $zone->target_height_max,
            'target_weight_min' => $zone->target_weight_min,
            'target_weight_max' => $zone->target_weight_max,
            'frequency_views' => $zone->frequency_views,
            'auto_reload' => $zone->auto_reload,
            'reload_time' => $zone->reload_time,
            'target_countries' => $zone->target_countries ?? [],
            'target_devices' => $zone->target_devices ?? [],
            'direct_campaign_id' => $linkedCampaign?->id,
            'direct_campaign_name' => $linkedCampaign?->name,
            'direct_campaign_status' => $linkedCampaign?->status,
        ]);
    }

    public function preview(Request $request, $id)
    {
        $zone = Zone::with('site')->where('is_deleted', false)->findOrFail($id);
        $previewParams = array_filter([
            'device' => $request->query('device'),
            'country' => $request->query('country'),
            'age' => $request->query('age'),
            'gender' => $request->query('gender'),
            'color' => $request->query('color'),
            'height' => $request->query('height'),
            'weight' => $request->query('weight'),
        ], fn ($value) => $value !== null && $value !== '');
        $adCode = $this->generateAdCodeForZone($zone, $previewParams);

        return view('admin.adblocks.preview', [
            'zone' => $zone,
            'serveUrl' => $this->buildServeUrl($zone),
            'adCode' => $adCode,
            'previewParams' => $previewParams,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'site_id' => 'required|exists:aq_sites,id',
            'format_id' => 'required|string|max:50',
            'size_id' => 'nullable|string|max:50',
            'placement' => 'required|in:header,sidebar,content,footer,overlay,interstitial,push',
            'floor_price' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,paused,archived',
            'target_age_min' => 'nullable|integer|min:0|max:120',
            'target_age_max' => 'nullable|integer|min:0|max:120',
            'target_gender' => 'nullable|in:male,female,both',
            'target_color' => 'nullable|string|max:50',
            'target_height_min' => 'nullable|integer|min:0',
            'target_height_max' => 'nullable|integer|min:0',
            'target_weight_min' => 'nullable|integer|min:0',
            'target_weight_max' => 'nullable|integer|min:0',
            'frequency_views' => 'nullable|integer|min:1',
            'auto_reload' => 'nullable|boolean',
            'reload_time' => 'nullable|integer|min:1',
            'direct_campaign_id' => 'nullable|exists:aq_direct_campaigns,id',
        ]);

        $zone = Zone::create([
            'name' => $request->name,
            'site_id' => $request->site_id,
            'format_id' => null,
            'format_key' => $request->format_id,
            'size_id' => null,
            'size_key' => $request->size_id,
            'placement' => $request->placement,
            'floor_price' => $request->floor_price ?? 0,
            'status' => $request->status ?? 'active',
            'target_age_min' => $request->target_age_min,
            'target_age_max' => $request->target_age_max,
            'target_gender' => $request->target_gender,
            'target_color' => $request->target_color,
            'target_height_min' => $request->target_height_min,
            'target_height_max' => $request->target_height_max,
            'target_weight_min' => $request->target_weight_min,
            'target_weight_max' => $request->target_weight_max,
            'frequency_views' => $request->frequency_views,
            'auto_reload' => $request->boolean('auto_reload'),
            'reload_time' => $request->reload_time,
        ]);

        // Generate ad code with the real zone ID and save it
        $zone->load('site');
        $adCode = $this->generateAdCodeForZone($zone);
        $zone->update(['ad_code' => $adCode]);
        $this->syncDirectCampaignLink($zone, $request->integer('direct_campaign_id') ?: null);

        return response()->json([
            'success' => true,
            'zone_id' => $zone->id,
            'ad_code' => $adCode,
            'message' => 'AdBlock created successfully.'
        ]);
    }

    public function update(Request $request, $id)
    {
        $zone = Zone::where('is_deleted', false)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'site_id' => 'required|exists:aq_sites,id',
            'format_id' => 'required|string|max:50',
            'size_id' => 'nullable|string|max:50',
            'placement' => 'required|in:header,sidebar,content,footer,overlay,interstitial,push',
            'floor_price' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,paused,archived',
            'target_age_min' => 'nullable|integer|min:0|max:120',
            'target_age_max' => 'nullable|integer|min:0|max:120',
            'target_gender' => 'nullable|in:male,female,both',
            'target_color' => 'nullable|string|max:50',
            'target_height_min' => 'nullable|integer|min:0',
            'target_height_max' => 'nullable|integer|min:0',
            'target_weight_min' => 'nullable|integer|min:0',
            'target_weight_max' => 'nullable|integer|min:0',
            'frequency_views' => 'nullable|integer|min:1',
            'auto_reload' => 'nullable|boolean',
            'reload_time' => 'nullable|integer|min:1',
            'direct_campaign_id' => 'nullable|exists:aq_direct_campaigns,id',
        ]);

        $formatId = null; // Hardcoded formats don't reference DB table to avoid FK constraint
        $sizeId = null; // Hardcoded sizes don't reference DB table to avoid FK constraint

        $zone->update([
            'name' => $request->name,
            'site_id' => $request->site_id,
            'format_id' => $formatId,
            'format_key' => $request->format_id,
            'size_id' => $sizeId,
            'size_key' => $request->size_id,
            'placement' => $request->placement,
            'floor_price' => $request->floor_price ?? $zone->floor_price,
            'status' => $request->status ?? $zone->status,
            'target_age_min' => $request->target_age_min,
            'target_age_max' => $request->target_age_max,
            'target_gender' => $request->target_gender,
            'target_color' => $request->target_color,
            'target_height_min' => $request->target_height_min,
            'target_height_max' => $request->target_height_max,
            'target_weight_min' => $request->target_weight_min,
            'target_weight_max' => $request->target_weight_max,
            'frequency_views' => $request->frequency_views,
            'auto_reload' => $request->boolean('auto_reload'),
            'reload_time' => $request->reload_time,
        ]);
        $this->syncDirectCampaignLink($zone, $request->integer('direct_campaign_id') ?: null);

        // Return JSON for API requests, redirect for form submissions
        if ($request->expectsJson() || $request->ajax() || $request->header('Content-Type') === 'application/json') {
            return response()->json([
                'success' => true,
                'zone' => $zone->fresh(),
                'message' => 'AdBlock updated successfully.'
            ]);
        }

        return redirect()->route('admin.adblocks')->with('success', 'AdBlock updated successfully.');
    }

    public function destroy($id)
    {
        $zone = Zone::where('is_deleted', false)->findOrFail($id);
        $zone->update(['is_deleted' => true]);

        return response()->json(['success' => true, 'message' => 'AdBlock deleted.']);
    }

    public function updateStatus(Request $request, $id)
    {
        $zone = Zone::where('is_deleted', false)->findOrFail($id);

        $request->validate([
            'status' => 'required|in:active,paused,archived',
        ]);

        $zone->update(['status' => $request->status]);

        return response()->json(['success' => true, 'message' => 'Status updated.']);
    }

    public function updateTargeting(Request $request, $id)
    {
        $zone = Zone::with('site')->where('is_deleted', false)->findOrFail($id);

        $request->validate([
            'target_age_min' => 'nullable|integer|min:0|max:120',
            'target_age_max' => 'nullable|integer|min:0|max:120',
            'target_gender' => 'nullable|in:male,female,both',
            'target_color' => 'nullable|string|max:50',
            'target_height_min' => 'nullable|integer|min:0',
            'target_height_max' => 'nullable|integer|min:0',
            'target_weight_min' => 'nullable|integer|min:0',
            'target_weight_max' => 'nullable|integer|min:0',
            'frequency_views' => 'nullable|integer|min:1',
            'auto_reload' => 'nullable|boolean',
            'reload_time' => 'nullable|integer|min:1',
            'target_countries' => 'nullable|array',
            'target_countries.*' => 'string|size:2',
            'target_devices' => 'nullable|array',
            'target_devices.*' => 'string|in:desktop,mobile,tablet',
        ]);

        $zone->update([
            'target_age_min' => $request->target_age_min,
            'target_age_max' => $request->target_age_max,
            'target_gender' => $request->target_gender,
            'target_color' => $request->target_color,
            'target_height_min' => $request->target_height_min,
            'target_height_max' => $request->target_height_max,
            'target_weight_min' => $request->target_weight_min,
            'target_weight_max' => $request->target_weight_max,
            'frequency_views' => $request->frequency_views,
            'auto_reload' => $request->boolean('auto_reload'),
            'reload_time' => $request->reload_time,
            'target_countries' => $request->target_countries,
            'target_devices' => $request->target_devices,
        ]);

        // Regenerate ad code with updated targeting
        $adCode = $this->generateAdCodeForZone($zone->fresh()->load('site'));
        $zone->update(['ad_code' => $adCode]);

        return response()->json([
            'success' => true,
            'message' => 'Targeting settings updated successfully.',
        ]);
    }

    public function getSizesByFormat($formatKey)
    {
        $adFormats = [
            'display_web' => [
                'label' => 'Display Web',
                'sizes' => [
                    ['id' => '300x250', 'name' => '300x250 (Medium Rectangle)', 'width' => 300, 'height' => 250],
                    ['id' => '728x90', 'name' => '728x90 (Leaderboard)', 'width' => 728, 'height' => 90],
                    ['id' => '160x600', 'name' => '160x600 (Wide Skyscraper)', 'width' => 160, 'height' => 600],
                    ['id' => '300x600', 'name' => '300x600 (Half Page)', 'width' => 300, 'height' => 600],
                    ['id' => '320x50', 'name' => '320x50 (Mobile Banner)', 'width' => 320, 'height' => 50],
                    ['id' => '970x250', 'name' => '970x250 (Billboard)', 'width' => 970, 'height' => 250],
                ],
            ],
            'special_web' => [
                'label' => 'Special Web',
                'sizes' => [
                    ['id' => 'text', 'name' => 'Text Ad', 'width' => null, 'height' => null],
                    ['id' => 'native', 'name' => 'Native Ad', 'width' => null, 'height' => null],
                    ['id' => 'interstitial', 'name' => 'Interstitial', 'width' => null, 'height' => null],
                    ['id' => 'popunder', 'name' => 'Popunder', 'width' => null, 'height' => null],
                    ['id' => 'direct_link', 'name' => 'Direct Link', 'width' => null, 'height' => null],
                    ['id' => 'in_page_push', 'name' => 'In-Page Push', 'width' => null, 'height' => null],
                    ['id' => 'social_bar', 'name' => 'Social Bar', 'width' => null, 'height' => null],
                ],
            ],
            'display_video' => [
                'label' => 'Display Video',
                'sizes' => [
                    ['id' => 'instream', 'name' => 'In-Stream Video', 'width' => null, 'height' => null],
                    ['id' => 'outstream', 'name' => 'Out-Stream Video', 'width' => null, 'height' => null],
                    ['id' => 'rewarded', 'name' => 'Rewarded Video', 'width' => null, 'height' => null],
                ],
            ],
        ];

        if (isset($adFormats[$formatKey])) {
            return response()->json($adFormats[$formatKey]['sizes']);
        }

        return response()->json([]);
    }

    public function getTag($id)
    {
        $zone = Zone::with('site')->where('is_deleted', false)->findOrFail($id);
        $adCode = $this->generateAdCodeForZone($zone);
        $simpleCode = $this->buildSimpleEmbedCode($zone);

        // Update stored code if it differs
        if ($zone->ad_code !== $adCode) {
            $zone->update(['ad_code' => $adCode]);
        }

        return response()->json([
            'ad_code' => $simpleCode,
            'full_ad_code' => $adCode,
            'name' => $zone->name,
        ]);
    }

    public function regenerateCode($id)
    {
        $zone = Zone::with('site')->where('is_deleted', false)->findOrFail($id);
        $adCode = $this->generateAdCodeForZone($zone);
        $zone->update(['ad_code' => $adCode]);

        return response()->json([
            'success' => true,
            'ad_code' => $adCode,
        ]);
    }

    private function generateAdCodeForZone(Zone $zone, array $queryOverrides = [])
    {
        $zoneId = $zone->id;
        $token = ZoneServeController::encodeToken($zoneId);
        $serveDomain = PlatformSetting::getServeDomain();
        $servePath = PlatformSetting::getServePath();
        $adblockTrackingUrl = $this->getCampaignAdblockTrackingUrl($zone);

        // Only include display-related attributes (safe for public embed)
        // Targeting is enforced server-side when the JS is requested
        $attrs = [];
        $attrs[] = 'data-zone-id="' . $zoneId . '"';
        if ($zone->format_key) $attrs[] = 'data-format="' . htmlspecialchars($zone->format_key) . '"';
        if ($zone->size_key) $attrs[] = 'data-size="' . htmlspecialchars($zone->size_key) . '"';

        $dataAttrs = implode(' ', $attrs);

        // Build size style for display formats
        $sizeStyle = '';
        if ($zone->size_key && preg_match('/^(\d+)x(\d+)$/', $zone->size_key, $m)) {
            $sizeStyle = " style=\"min-width:{$m[1]}px;min-height:{$m[2]}px;\"";
        }

        $scriptUrl = "{$serveDomain}{$servePath}/{$token}.js";
        if (! empty($queryOverrides)) {
            $scriptUrl .= '?' . http_build_query($queryOverrides);
        }
        $trackingLine = $adblockTrackingUrl
            ? "    var adblockUrl = '" . addslashes($adblockTrackingUrl . (!empty($queryOverrides) ? ('?' . http_build_query($queryOverrides)) : '')) . "';\n"
            : "    var adblockUrl = null;\n";
        $adblockFallback = <<<JS
    var adblockTracked = false;
    function trackAdblock() {
        if (!adblockUrl || adblockTracked) return;
        adblockTracked = true;
        var img = new Image();
        img.src = adblockUrl + '?_=' + Date.now();
    }
    s.onerror = trackAdblock;
    setTimeout(function() {
        var zone = d.getElementById('adshqip-zone-{$zoneId}');
        if (!zone || zone.getAttribute('data-loaded') !== '1') {
            trackAdblock();
        }
    }, 2500);
JS;

        $code = "<div id=\"adshqip-zone-{$zoneId}\" {$dataAttrs}{$sizeStyle}></div>\n";
        $code .= "<script>\n";
        $code .= "(function() {\n";
        $code .= "    var d = document, s = d.createElement('script');\n";
        $code .= "    s.async = true;\n";
        $code .= "    s.src = '{$scriptUrl}';\n";
        $code .= $trackingLine;
        $code .= $adblockFallback . "\n";
        $code .= "    (d.head || d.body).appendChild(s);\n";
        $code .= "})();\n";
        $code .= "</script>";

        return $code;
    }
}
