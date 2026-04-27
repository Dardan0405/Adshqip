<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ZoneServeController;
use App\Models\DisplayScreen;
use App\Models\PlatformSetting;
use App\Models\Site;
use App\Models\StatDaily;
use App\Models\TelegramMiniApp;
use App\Models\Zone;
use App\Support\PublisherNotificationManager;
use App\Support\PublisherPaymentManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PublisherAdBlockController extends Controller
{
    public function __construct(private readonly PublisherNotificationManager $notifier) {}

    public function index(Request $request): View
    {
        $publisherId = (int) $request->user()->id;
        $search = trim((string) $request->input('search', ''));

        $query = Zone::query()
            ->with([
                'site:id,publisher_id,name,domain,is_deleted',
                'mobileApp:id,user_id,app_name,app_url,is_deleted',
            ])
            ->where('is_deleted', false)
            ->where(function ($builder) use ($publisherId) {
                $builder->whereHas('site', function ($siteQuery) use ($publisherId) {
                    $siteQuery->where('publisher_id', $publisherId)->where('is_deleted', false);
                })->orWhereHas('mobileApp', function ($appQuery) use ($publisherId) {
                    $appQuery->where('user_id', $publisherId)->where('is_deleted', false);
                });
            });

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('format_key', 'like', '%' . $search . '%')
                    ->orWhere('size_key', 'like', '%' . $search . '%')
                    ->orWhereHas('site', function ($siteQuery) use ($search) {
                        $siteQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('domain', 'like', '%' . $search . '%');
                    })->orWhereHas('mobileApp', function ($appQuery) use ($search) {
                        $appQuery->where('app_name', 'like', '%' . $search . '%')
                            ->orWhere('app_url', 'like', '%' . $search . '%');
                    });
            });
        }

        $zones = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $stats = StatDaily::query()
            ->whereIn('zone_id', $zones->pluck('id'))
            ->selectRaw('
                zone_id,
                SUM(impressions) as total_impressions,
                SUM(clicks) as total_clicks,
                SUM(COALESCE(publisher_earnings, revenue)) as total_revenue,
                CASE WHEN SUM(impressions) > 0 THEN ((SUM(clicks) * 100.0) / SUM(impressions)) ELSE 0 END as ctr,
                CASE WHEN SUM(impressions) > 0 THEN (SUM(COALESCE(publisher_earnings, revenue)) / SUM(impressions) * 1000) ELSE 0 END as ecpm
            ')
            ->groupBy('zone_id')
            ->get()
            ->keyBy('zone_id');

        return view('publisher.adblocks.index', [
            'zones' => $zones,
            'stats' => $stats,
            'allSites' => Site::query()
                ->where('publisher_id', $publisherId)
                ->where('is_deleted', false)
                ->orderBy('name')
                ->get(['id', 'name', 'domain']),
            'mobileApps' => TelegramMiniApp::query()
                ->where('user_id', $publisherId)
                ->where('is_deleted', false)
                ->orderBy('app_name')
                ->get(['id', 'app_name', 'app_url']),
            'adFormats' => $this->adFormats(),
            'displayScreens' => $this->getActiveDisplayScreens(),
            'search' => $search,
            'balance' => 17491.53,
        ]);
    }

    protected function buildServeUrl(Zone $zone): string
    {
        $token = ZoneServeController::encodeToken($zone->id);
        $serveDomain = PlatformSetting::getServeDomain();
        $servePath = PlatformSetting::getServePath();

        return "{$serveDomain}{$servePath}/{$token}.js";
    }

    protected function buildSimpleEmbedCode(Zone $zone): string
    {
        return '<div ' . implode(' ', $this->zoneContainerAttributes($zone, true)) . '></div>' . "\n"
            . '<script async src="' . $this->buildServeUrl($zone) . '"></script>';
    }

    protected function generateInvocationCodes(Zone $zone): array
    {
        $serveUrl = $this->buildServeUrl($zone);
        $sizeStyle = '';

        if ($zone->size_key && preg_match('/^(\d+)x(\d+)$/', $zone->size_key, $m)) {
            $sizeStyle = " style=\"width:{$m[1]}px;height:{$m[2]}px;\"";
        }

        $videoBase = '<div class="adshqip-video-player" data-zone-id="' . $zone->id . '" data-player="%s"></div>' . "\n"
            . '<script async src="' . e($serveUrl) . '"></script>';

        return [
            'js' => $this->buildSimpleEmbedCode($zone),
            'iframe' => '<iframe src="' . e($serveUrl) . '" loading="lazy" frameborder="0" scrolling="no"' . $sizeStyle . '></iframe>',
            'inline' => '<div class="adshqip-inline-video" data-zone-id="' . $zone->id . '"></div>' . "\n" . '<script async src="' . e($serveUrl) . '"></script>',
            'real' => sprintf($videoBase, 'real'),
            'small' => sprintf($videoBase, 'small'),
            'box' => sprintf($videoBase, 'box'),
            'head' => sprintf($videoBase, 'head'),
            'overlay' => '<div class="adshqip-overlay" data-zone-id="' . $zone->id . '"></div>' . "\n" . '<script async src="' . e($serveUrl) . '"></script>',
            'curl' => "<?php\n\$ch = curl_init('" . addslashes($serveUrl) . "');\ncurl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);\necho curl_exec(\$ch);\ncurl_close(\$ch);\n",
        ];
    }

    protected function generateAdCodeForZone(Zone $zone): string
    {
        $zoneId = $zone->id;
        $token = ZoneServeController::encodeToken($zoneId);
        $serveDomain = PlatformSetting::getServeDomain();
        $servePath = PlatformSetting::getServePath();

        $scriptUrl = "{$serveDomain}{$servePath}/{$token}.js";
        $prefix = $zone->sponsored_prefix ? '<div class="adshqip-sponsored-prefix">' . e($zone->sponsored_prefix) . '</div>' . "\n" : '';
        $customCss = $zone->custom_css ? "<style>\n" . $zone->custom_css . "\n</style>\n" : '';
        $template = $zone->html_template ? "<template id=\"adshqip-template-{$zoneId}\">\n" . $zone->html_template . "\n</template>\n" : '';
        $settingsScript = $this->buildTargetingHelperScript($zone);

        $code = $customCss . $template . "<div " . implode(' ', $this->zoneContainerAttributes($zone, false)) . "></div>\n";

        if ($prefix !== '') {
            $code = $prefix . $code;
        }

        if ($settingsScript !== '') {
            $code .= $settingsScript . "\n";
        }

        $code .= "<script>\n";
        $code .= "(function() {\n";
        $code .= "    var d = document, s = d.createElement('script');\n";
        $code .= "    s.async = true;\n";
        $code .= "    s.src = '{$scriptUrl}';\n";
        $code .= "    (d.head || d.body).appendChild(s);\n";
        $code .= "})();\n";
        $code .= "</script>";

        return $code;
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $zone = $this->findPublisherZone($request, $id);

        return response()->json([
            'id' => $zone->id,
            'choose_type' => $zone->choose_type,
            'site_id' => $zone->site_id,
            'mobile_app_id' => $zone->mobile_app_id,
            'name' => $zone->name,
            'format_key' => $zone->format_key,
            'size_key' => $zone->size_key,
            'zone_type' => $zone->zone_type,
            'placement' => $zone->placement,
            'floor_price' => (float) ($zone->floor_price ?? 0),
            'passback' => $zone->passback,
            'image_width' => $zone->image_width,
            'image_height' => $zone->image_height,
            'html_template' => $zone->html_template,
            'custom_css' => $zone->custom_css,
            'bg_color' => $zone->bg_color ?: '#ffffff',
            'sponsored_prefix' => $zone->sponsored_prefix,
            'css_path' => $zone->css_path,
            'inline_video' => (bool) $zone->inline_video,
            'status' => $zone->status,
            'target_age_min' => $zone->target_age_min,
            'target_age_max' => $zone->target_age_max,
            'target_gender' => $zone->target_gender,
            'target_color' => $zone->target_color,
            'target_height_min' => $zone->target_height_min,
            'target_height_max' => $zone->target_height_max,
            'target_weight_min' => $zone->target_weight_min,
            'target_weight_max' => $zone->target_weight_max,
            'frequency_views' => $zone->frequency_views,
            'auto_reload' => (bool) $zone->auto_reload,
            'reload_time' => $zone->reload_time,
            'content_width_px' => $zone->content_width_px,
            'content_height_px' => $zone->content_height_px,
            'top_offset_px' => $zone->top_offset_px,
            'right_offset_px' => $zone->right_offset_px,
            'z_index_value' => $zone->z_index_value,
            'is_fixed' => (bool) $zone->is_fixed,
            'hide_side' => $zone->hide_side,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateZonePayload($request);
        $userId    = (int) $request->user()->id;
        $zone      = Zone::query()->create($this->zoneAttributes($validated, $userId));
        $this->refreshZoneCode($zone);

        $this->notifier->deliver(
            $request->user()->fresh('profile'),
            'adblock_add_update',
            'Adblock Created',
            'Your adblock "' . $zone->name . '" has been created successfully.',
            route('publisher.adblocks')
        );

        return response()->json([
            'success' => true,
            'zone_id' => $zone->id,
            'ad_code' => $zone->ad_code,
            'codes'   => $this->generateInvocationCodes($zone),
            'message' => 'AdBlock created successfully.',
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $userId    = (int) $request->user()->id;
        $zone      = $this->findPublisherZone($request, $id);
        $validated = $this->validateZonePayload($request);

        $zone->update($this->zoneAttributes($validated, $userId));
        $this->refreshZoneCode($zone);

        $this->notifier->deliver(
            $request->user()->fresh('profile'),
            'adblock_add_update',
            'Adblock Updated',
            'Your adblock "' . $zone->name . '" settings have been updated.',
            route('publisher.adblocks')
        );

        if ($request->expectsJson()) {
            return response()->json($this->tagPayload($zone) + [
                'success' => true,
                'message' => 'Tag settings updated successfully.',
            ]);
        }

        return redirect()->route('publisher.adblocks')->with('success', 'Adblock updated successfully.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $userId = (int) $request->user()->id;
        $zone   = $this->findPublisherZone($request, $id);
        $name   = $zone->name;
        $zone->update(['is_deleted' => true]);

        $this->notifier->deliver(
            $request->user()->fresh('profile'),
            'adblock_delete',
            'Adblock Deleted',
            'The adblock "' . $name . '" has been removed from your account.',
            route('publisher.adblocks')
        );

        return response()->json([
            'success' => true,
            'message' => 'Adblock deleted successfully.',
        ]);
    }

    public function getTag(Request $request, int $id): JsonResponse
    {
        $zone = $this->findPublisherZone($request, $id);

        return response()->json($this->tagPayload($zone));
    }

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

    private function adFormats(): array
    {
        return [
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
    }

    private function validateZonePayload(Request $request): array
    {
        return $request->validate([
            'choose_type' => 'required|in:web,app',
            'site_id' => 'nullable|integer',
            'mobile_app_id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'format_id' => 'required|string|max:50',
            'size_id' => 'nullable|string|max:50',
            'zone_type' => 'required|string|max:50',
            'placement' => 'required|in:header,sidebar,content,footer,overlay,interstitial,push,popup',
            'floor_price' => 'nullable|numeric|min:0',
            'passback' => 'nullable|string',
            'image_width' => 'nullable|integer|min:1',
            'image_height' => 'nullable|integer|min:1',
            'html_template' => 'nullable|string',
            'custom_css' => 'nullable|string',
            'bg_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'sponsored_prefix' => 'nullable|string|max:255',
            'css_path' => 'nullable|string|max:255',
            'inline_video' => 'nullable|boolean',
            'status' => 'nullable|in:active,paused,archived',
            'target_age_min' => 'nullable|integer|min:0|max:120',
            'target_age_max' => 'nullable|integer|min:0|max:120',
            'target_gender' => 'nullable|in:male,female,both',
            'target_color' => 'nullable|string|max:50',
            'target_height_min' => 'nullable|integer|min:0|max:300',
            'target_height_max' => 'nullable|integer|min:0|max:300',
            'target_weight_min' => 'nullable|integer|min:0|max:500',
            'target_weight_max' => 'nullable|integer|min:0|max:500',
            'frequency_views' => 'nullable|integer|min:1',
            'auto_reload' => 'nullable|boolean',
            'reload_time' => 'nullable|integer|min:1',
            'content_width_px' => 'nullable|integer|min:1|max:4000',
            'content_height_px' => 'nullable|integer|min:1|max:4000',
            'top_offset_px' => 'nullable|integer|min:0|max:4000',
            'right_offset_px' => 'nullable|integer|min:0|max:4000',
            'z_index_value' => 'nullable|integer|min:0|max:2147483647',
            'is_fixed' => 'nullable|boolean',
            'hide_side' => 'nullable|in:none,left,right',
        ]);
    }

    private function zoneAttributes(array $validated, int $publisherId): array
    {
        $siteId = null;
        if ($validated['choose_type'] === 'web') {
            $siteId = Site::query()
                ->where('publisher_id', $publisherId)
                ->where('is_deleted', false)
                ->whereKey($validated['site_id'])
                ->value('id');

            abort_if(! $siteId, 422, 'Selected site is not available.');
        }

        $mobileAppId = null;
        if ($validated['choose_type'] === 'app') {
            $mobileAppId = TelegramMiniApp::query()
                ->where('user_id', $publisherId)
                ->where('is_deleted', false)
                ->whereKey($validated['mobile_app_id'])
                ->value('id');

            abort_if(! $mobileAppId, 422, 'Selected mobile app is not available.');
        }

        $publisherPaymentManager = app(PublisherPaymentManager::class);

        return [
            'site_id' => $siteId,
            'choose_type' => $validated['choose_type'],
            'mobile_app_id' => $mobileAppId,
            'name' => $validated['name'],
            'format_id' => null,
            'format_key' => $validated['format_id'],
            'size_id' => null,
            'size_key' => $validated['size_id'] ?? null,
            'zone_type' => $validated['zone_type'],
            'placement' => $validated['placement'],
            'floor_price' => $publisherPaymentManager->applyMinimumFloorPrice((float) ($validated['floor_price'] ?? 0)),
            'passback' => $validated['passback'] ?? null,
            'image_width' => $validated['image_width'] ?? null,
            'image_height' => $validated['image_height'] ?? null,
            'html_template' => $validated['html_template'] ?? null,
            'custom_css' => $validated['custom_css'] ?? null,
            'bg_color' => $validated['bg_color'] ?? null,
            'sponsored_prefix' => $validated['sponsored_prefix'] ?? null,
            'css_path' => $validated['css_path'] ?? null,
            'inline_video' => (bool) ($validated['inline_video'] ?? false),
            'status' => $validated['status'] ?? 'active',
            'target_age_min' => $validated['target_age_min'] ?? null,
            'target_age_max' => $validated['target_age_max'] ?? null,
            'target_gender' => $validated['target_gender'] ?? null,
            'target_color' => $validated['target_color'] ?? null,
            'target_height_min' => $validated['target_height_min'] ?? null,
            'target_height_max' => $validated['target_height_max'] ?? null,
            'target_weight_min' => $validated['target_weight_min'] ?? null,
            'target_weight_max' => $validated['target_weight_max'] ?? null,
            'frequency_views' => $validated['frequency_views'] ?? null,
            'auto_reload' => (bool) ($validated['auto_reload'] ?? false),
            'reload_time' => $validated['reload_time'] ?? null,
            'content_width_px' => $validated['content_width_px'] ?? null,
            'content_height_px' => $validated['content_height_px'] ?? null,
            'top_offset_px' => $validated['top_offset_px'] ?? null,
            'right_offset_px' => $validated['right_offset_px'] ?? null,
            'z_index_value' => $validated['z_index_value'] ?? null,
            'is_fixed' => (bool) ($validated['is_fixed'] ?? false),
            'hide_side' => $validated['hide_side'] ?? 'none',
            'is_deleted' => false,
        ];
    }

    private function refreshZoneCode(Zone $zone): void
    {
        $zone->loadMissing('site', 'mobileApp');
        $zone->update(['ad_code' => $this->generateAdCodeForZone($zone)]);
        $zone->refresh();
    }

    private function findPublisherZone(Request $request, int $id): Zone
    {
        $publisherId = (int) $request->user()->id;

        return Zone::query()
            ->with(['site', 'mobileApp'])
            ->where('is_deleted', false)
            ->where(function ($query) use ($publisherId) {
                $query->whereHas('site', function ($siteQuery) use ($publisherId) {
                    $siteQuery->where('publisher_id', $publisherId)->where('is_deleted', false);
                })->orWhereHas('mobileApp', function ($appQuery) use ($publisherId) {
                    $appQuery->where('user_id', $publisherId)->where('is_deleted', false);
                });
            })
            ->findOrFail($id);
    }

    private function zoneContainerAttributes(Zone $zone, bool $compact = false): array
    {
        $attrs = [];
        $attrs[] = 'id="adshqip-zone-' . $zone->id . '"';
        $attrs[] = 'data-zone-id="' . $zone->id . '"';

        if ($zone->format_key) {
            $attrs[] = 'data-format="' . htmlspecialchars((string) $zone->format_key) . '"';
        }
        if ($zone->size_key) {
            $attrs[] = 'data-size="' . htmlspecialchars((string) $zone->size_key) . '"';
        }

        foreach ($this->zoneTargetingDataset($zone) as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $attrs[] = 'data-' . $key . '="' . htmlspecialchars((string) $value) . '"';
        }

        $style = $this->zoneContainerStyle($zone, $compact);
        if ($style !== '') {
            $attrs[] = 'style="' . htmlspecialchars($style) . '"';
        }

        return $attrs;
    }

    private function zoneContainerStyle(Zone $zone, bool $compact): string
    {
        $styles = [];

        if ($zone->size_key && preg_match('/^(\d+)x(\d+)$/', (string) $zone->size_key, $m)) {
            $styles[] = 'min-width:' . $m[1] . 'px';
            $styles[] = 'min-height:' . $m[2] . 'px';
        }
        if ($zone->content_width_px) {
            $styles[] = 'width:' . (int) $zone->content_width_px . 'px';
        }
        if ($zone->content_height_px) {
            $styles[] = 'height:' . (int) $zone->content_height_px . 'px';
        }
        if ($zone->bg_color) {
            $styles[] = 'background:' . trim((string) $zone->bg_color);
        }
        if (! $compact && $zone->is_fixed) {
            $styles[] = 'position:fixed';
        }
        if (! $compact && $zone->top_offset_px !== null) {
            $styles[] = 'top:' . (int) $zone->top_offset_px . 'px';
        }
        if (! $compact && $zone->right_offset_px !== null) {
            $styles[] = 'right:' . (int) $zone->right_offset_px . 'px';
        }
        if (! $compact && $zone->z_index_value !== null) {
            $styles[] = 'z-index:' . (int) $zone->z_index_value;
        }
        if (! $compact && in_array($zone->hide_side, ['left', 'right'], true)) {
            $styles[] = $zone->hide_side . ':-9999px';
        }

        return implode(';', $styles);
    }

    private function zoneTargetingDataset(Zone $zone): array
    {
        return [
            'target-age-min' => $zone->target_age_min,
            'target-age-max' => $zone->target_age_max,
            'target-gender' => $zone->target_gender,
            'target-color' => $zone->target_color,
            'target-height-min' => $zone->target_height_min,
            'target-height-max' => $zone->target_height_max,
            'target-weight-min' => $zone->target_weight_min,
            'target-weight-max' => $zone->target_weight_max,
            'frequency-views' => $zone->frequency_views,
            'auto-reload' => $zone->auto_reload ? '1' : '0',
            'reload-time' => $zone->reload_time,
            'content-width-px' => $zone->content_width_px,
            'content-height-px' => $zone->content_height_px,
            'top-offset-px' => $zone->top_offset_px,
            'right-offset-px' => $zone->right_offset_px,
            'z-index-value' => $zone->z_index_value,
            'is-fixed' => $zone->is_fixed ? '1' : '0',
            'hide-side' => $zone->hide_side,
        ];
    }

    private function buildTargetingHelperScript(Zone $zone): string
    {
        $settings = [
            'ageMin' => $zone->target_age_min,
            'ageMax' => $zone->target_age_max,
            'gender' => $zone->target_gender,
            'color' => $zone->target_color,
            'heightMin' => $zone->target_height_min,
            'heightMax' => $zone->target_height_max,
            'weightMin' => $zone->target_weight_min,
            'weightMax' => $zone->target_weight_max,
            'frequencyViews' => $zone->frequency_views,
            'autoReload' => (bool) $zone->auto_reload,
            'reloadTime' => $zone->reload_time,
            'contentWidthPx' => $zone->content_width_px,
            'contentHeightPx' => $zone->content_height_px,
            'topOffsetPx' => $zone->top_offset_px,
            'rightOffsetPx' => $zone->right_offset_px,
            'zIndexValue' => $zone->z_index_value,
            'isFixed' => (bool) $zone->is_fixed,
            'hideSide' => $zone->hide_side,
        ];

        return '<script>window.adshqipZoneSettings=window.adshqipZoneSettings||{};window.adshqipZoneSettings[' . $zone->id . ']=' . json_encode($settings, JSON_UNESCAPED_SLASHES) . ';</script>';
    }

    private function tagPayload(Zone $zone): array
    {
        return [
            'id' => $zone->id,
            'name' => $zone->name,
            'ad_code' => $zone->ad_code,
            'full_ad_code' => $zone->ad_code,
            'codes' => $this->generateInvocationCodes($zone),
            'editable' => [
                'choose_type' => $zone->choose_type,
                'site_id' => $zone->site_id,
                'mobile_app_id' => $zone->mobile_app_id,
                'name' => $zone->name,
                'format_id' => $zone->format_key,
                'size_id' => $zone->size_key,
                'zone_type' => $zone->zone_type,
                'placement' => $zone->placement,
                'floor_price' => (float) ($zone->floor_price ?? 0),
                'passback' => $zone->passback,
                'image_width' => $zone->image_width,
                'image_height' => $zone->image_height,
                'html_template' => $zone->html_template,
                'custom_css' => $zone->custom_css,
                'bg_color' => $zone->bg_color ?: '#ffffff',
                'sponsored_prefix' => $zone->sponsored_prefix,
                'css_path' => $zone->css_path,
                'inline_video' => (bool) $zone->inline_video,
                'status' => $zone->status,
                'target_age_min' => $zone->target_age_min,
                'target_age_max' => $zone->target_age_max,
                'target_gender' => $zone->target_gender,
                'target_color' => $zone->target_color,
                'target_height_min' => $zone->target_height_min,
                'target_height_max' => $zone->target_height_max,
                'target_weight_min' => $zone->target_weight_min,
                'target_weight_max' => $zone->target_weight_max,
                'frequency_views' => $zone->frequency_views,
                'auto_reload' => (bool) $zone->auto_reload,
                'reload_time' => $zone->reload_time,
                'content_width_px' => $zone->content_width_px,
                'content_height_px' => $zone->content_height_px,
                'top_offset_px' => $zone->top_offset_px,
                'right_offset_px' => $zone->right_offset_px,
                'z_index_value' => $zone->z_index_value,
                'is_fixed' => (bool) $zone->is_fixed,
                'hide_side' => $zone->hide_side,
            ],
            'targeting' => [
                'age' => $this->formatAgeRange($zone->target_age_min, $zone->target_age_max),
                'gender' => $zone->target_gender ?: 'All',
                'color' => $zone->target_color ?: 'Any',
                'height' => $this->formatRange($zone->target_height_min, $zone->target_height_max, 'cm'),
                'weight' => $this->formatRange($zone->target_weight_min, $zone->target_weight_max, 'kg'),
                'frequency' => $zone->frequency_views
                    ? $zone->frequency_views . ' views / ' . ($zone->reload_time ?: 0) . ' sec'
                    : 'Not set',
            ],
            'settings' => [
                'autoload' => $zone->auto_reload ? 'Yes' : 'No',
                'reload_time' => $zone->reload_time ? $zone->reload_time . ' sec' : 'Not set',
                'content_width' => $zone->content_width_px ? $zone->content_width_px . ' px' : 'Auto',
                'content_height' => $zone->content_height_px ? $zone->content_height_px . ' px' : 'Auto',
                'top_offset' => $zone->top_offset_px !== null ? $zone->top_offset_px . ' px' : 'Not set',
                'right_offset' => $zone->right_offset_px !== null ? $zone->right_offset_px . ' px' : 'Not set',
                'z_index' => $zone->z_index_value !== null ? (string) $zone->z_index_value : 'Default',
                'fixed' => $zone->is_fixed ? 'Yes' : 'No',
                'hide' => ucfirst($zone->hide_side ?: 'none'),
            ],
        ];
    }

    private function formatAgeRange(?int $min, ?int $max): string
    {
        if ($min === null && $max === null) {
            return 'All ages';
        }
        if ($min === null) {
            return 'Less than ' . ($max + 1);
        }
        if ($max === null) {
            return 'More than ' . ($min - 1);
        }

        return $min . ' - ' . $max;
    }

    private function formatRange(?int $min, ?int $max, string $unit): string
    {
        if ($min === null && $max === null) {
            return 'Any';
        }
        if ($min === null) {
            return 'Up to ' . $max . ' ' . $unit;
        }
        if ($max === null) {
            return $min . '+ ' . $unit;
        }

        return $min . ' - ' . $max . ' ' . $unit;
    }
}
