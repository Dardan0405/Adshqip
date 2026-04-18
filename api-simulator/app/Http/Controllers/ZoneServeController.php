<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Campaign;
use App\Models\PlatformSetting;
use App\Models\StatDaily;
use App\Models\Zone;
use App\Support\AdDeliveryOptions;
use App\Support\PlatformMemcache;
use Illuminate\Http\Request;

class ZoneServeController extends Controller
{
    /**
     * Serve zone JS — the public endpoint that publisher embed code calls.
     * Path: /d/{token}.js  (token = base36-encoded zone ID)
     */
    public function serve(Request $request, string $token)
    {
        $deliveryOptions = app(AdDeliveryOptions::class);

        $zoneId = $this->decodeToken($token);
        if (!$zoneId) {
            return $this->emptyJs();
        }

        if (PlatformSetting::getReviveMemcacheEnabled()) {
            $js = app(PlatformMemcache::class)->remember(
                'revive_zone_serve',
                [
                    'zone_id' => $zoneId,
                    'query' => $request->query(),
                    'user_agent' => (string) $request->userAgent(),
                ],
                60,
                function () use ($deliveryOptions, $request, $zoneId) {
                    $zone = Zone::with(['site', 'directCampaignLinks.campaign'])
                        ->where('is_deleted', false)
                        ->where('status', 'active')
                        ->find($zoneId);
                    if (!$zone) {
                        return $this->emptyJs()->getContent();
                    }

                    if (! $this->passesZoneTargeting($zone, $request)) {
                        return $this->emptyJs()->getContent();
                    }

                    if (! $deliveryOptions->mobileAdsEnabled() && $deliveryOptions->isMobileOrTabletRequest($request)) {
                        return $this->emptyJs()->getContent();
                    }

                    $adHtml = $this->buildAdHtml($zone);

                    return $this->buildLoaderJs($zone, $adHtml);
                },
                true
            );

            return response($js, 200)
                ->header('Content-Type', 'application/javascript; charset=utf-8')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Access-Control-Allow-Origin', '*');
        }

        $zone = Zone::with(['site', 'directCampaignLinks.campaign'])
            ->where('is_deleted', false)
            ->where('status', 'active')
            ->find($zoneId);
        if (!$zone) {
            return $this->emptyJs();
        }

        // ── Server-side targeting enforcement ──
        if (! $this->passesZoneTargeting($zone, $request)) {
            return $this->emptyJs();
        }

        if (! $deliveryOptions->mobileAdsEnabled() && $deliveryOptions->isMobileOrTabletRequest($request)) {
            return $this->emptyJs();
        }

        // Build the ad HTML that the JS will inject
        $adHtml = $this->buildAdHtml($zone);

        // Build JS that injects the ad into the zone container
        $js = $this->buildLoaderJs($zone, $adHtml);

        return response($js, 200)
            ->header('Content-Type', 'application/javascript; charset=utf-8')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Access-Control-Allow-Origin', '*');
    }

    /**
     * Encode zone ID to an obfuscated token (base36 with offset).
     */
    public static function encodeToken(int $zoneId): string
    {
        return base_convert($zoneId + 1000000, 10, 36);
    }

    /**
     * Decode token back to zone ID.
     */
    private function decodeToken(string $token): ?int
    {
        $token = preg_replace('/\.js$/', '', $token);
        $decoded = (int) base_convert($token, 36, 10) - 1000000;
        return $decoded > 0 ? $decoded : null;
    }

    private function passesZoneTargeting(Zone $zone, Request $request): bool
    {
        $visitorDevice = $this->detectDeviceType($request);
        if ($zone->target_devices && is_array($zone->target_devices) && count($zone->target_devices) > 0) {
            $targetDevices = array_map('strtolower', $zone->target_devices);
            if (! in_array($visitorDevice, $targetDevices, true)) {
                return false;
            }
        }

        if ($zone->target_countries && is_array($zone->target_countries) && count($zone->target_countries) > 0) {
            $visitorCountry = $this->detectCountry($request);
            if ($visitorCountry) {
                $targetCountries = array_map('strtoupper', $zone->target_countries);
                if (! in_array(strtoupper($visitorCountry), $targetCountries, true)) {
                    return false;
                }
            }
        }

        if ($request->filled('age')) {
            $age = (int) $request->query('age');
            if ($zone->target_age_min !== null && $age < (int) $zone->target_age_min) {
                return false;
            }
            if ($zone->target_age_max !== null && $age > (int) $zone->target_age_max) {
                return false;
            }
        }

        $gender = strtolower(trim((string) $request->query('gender', '')));
        if ($zone->target_gender && $zone->target_gender !== 'both' && $gender !== '') {
            if ($gender !== strtolower((string) $zone->target_gender)) {
                return false;
            }
        }

        $color = strtolower(trim((string) $request->query('color', '')));
        if ($zone->target_color && $color !== '') {
            if ($color !== strtolower(trim((string) $zone->target_color))) {
                return false;
            }
        }

        if ($request->filled('height')) {
            $height = (int) $request->query('height');
            if ($zone->target_height_min !== null && $height < (int) $zone->target_height_min) {
                return false;
            }
            if ($zone->target_height_max !== null && $height > (int) $zone->target_height_max) {
                return false;
            }
        }

        if ($request->filled('weight')) {
            $weight = (int) $request->query('weight');
            if ($zone->target_weight_min !== null && $weight < (int) $zone->target_weight_min) {
                return false;
            }
            if ($zone->target_weight_max !== null && $weight > (int) $zone->target_weight_max) {
                return false;
            }
        }

        if ($zone->frequency_views && $zone->frequency_views > 0) {
            $sessionKey = 'zone_frequency_views_' . $zone->id;
            $views = (int) $request->session()->get($sessionKey, 0);
            if ($views >= (int) $zone->frequency_views) {
                return false;
            }
            $request->session()->put($sessionKey, $views + 1);
        }

        return true;
    }

    private function detectDeviceType(Request $request): string
    {
        $override = strtolower((string) $request->query('device', ''));
        if (in_array($override, ['desktop', 'mobile', 'tablet'], true)) {
            return $override;
        }

        $ua = strtolower($request->userAgent() ?? '');
        if (str_contains($ua, 'ipad') || str_contains($ua, 'tablet')) {
            return 'tablet';
        }
        if (str_contains($ua, 'mobile') || str_contains($ua, 'android')) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function detectCountry(Request $request): ?string
    {
        $override = strtoupper((string) $request->query('country', ''));
        if (preg_match('/^[A-Z]{2}$/', $override)) {
            return $override;
        }

        $headerCountry = strtoupper((string) ($request->header('CF-IPCountry') ?? $request->header('X-Country-Code') ?? ''));
        if (preg_match('/^[A-Z]{2}$/', $headerCountry)) {
            return $headerCountry;
        }

        return null;
    }

    /**
     * Build the ad HTML content for this zone (placeholder/demo).
     */
    private function buildAdHtml(Zone $zone): string
    {
        $format = $zone->format_key ?? 'display_web';
        $size = $zone->size_key ?? '';

        // Parse dimensions for display ads
        $width = '100%';
        $height = '250px';
        if (preg_match('/^(\d+)x(\d+)$/', $size, $m)) {
            $width = $m[1] . 'px';
            $height = $m[2] . 'px';
        }

        $zoneId = $zone->id;
        $campaign = Campaign::where('zone_id', $zone->id)
            ->where('is_deleted', false)
            ->where('status', 'active')
            ->latest('id')
            ->first();

        if ($campaign) {
            $campaignHtml = $this->buildRegularCampaignHtml($campaign, $zone, $width, $height);
            if ($campaignHtml !== null) {
                return $campaignHtml;
            }
        }

        $linkedCampaign = $zone->directCampaignLinks
            ->where('is_active', true)
            ->sortByDesc('priority')
            ->first()?->campaign;

        if ($linkedCampaign && ! $linkedCampaign->is_deleted) {
            return $this->buildDirectCampaignFrame($zone, $linkedCampaign->id, $width, $height);
        }

        // For special formats, return format-specific HTML
        if ($format === 'special_web') {
            return match ($size) {
                'popunder' => '<script>console.log("[AdShqip] Popunder zone ' . $zoneId . ' loaded");</script>',
                'direct_link' => '<div style="display:none;" data-aq-direct="' . $zoneId . '"></div>',
                'in_page_push' => '<div class="aq-push" data-zone="' . $zoneId . '" style="position:fixed;bottom:20px;right:20px;z-index:999999;background:#fff;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,.15);padding:16px;max-width:320px;font-family:system-ui,sans-serif;"><div style="font-weight:600;font-size:14px;color:#1a1a1a;">Ad Zone ' . $zoneId . '</div><div style="font-size:12px;color:#666;margin-top:4px;">In-Page Push Notification</div></div>',
                'social_bar' => '<div style="position:fixed;bottom:0;left:0;right:0;z-index:999999;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:10px 16px;text-align:center;font-family:system-ui,sans-serif;color:#fff;font-size:13px;">AdShqip Social Bar &mdash; Zone ' . $zoneId . '</div>',
                default => '<div style="background:#f8f9fa;border:1px solid #e9ecef;border-radius:8px;padding:20px;text-align:center;font-family:system-ui,sans-serif;"><div style="font-size:13px;color:#6c757d;">Ad Zone ' . $zoneId . '</div></div>',
            };
        }

        if ($format === 'display_video') {
            return '<div style="width:' . $width . ';max-width:640px;aspect-ratio:16/9;background:#000;border-radius:8px;display:flex;align-items:center;justify-content:center;font-family:system-ui,sans-serif;color:#fff;font-size:14px;margin:0 auto;">Video Ad &mdash; Zone ' . $zoneId . '</div>';
        }

        // Default display_web
        return '<div style="width:' . $width . ';height:' . $height . ';background:linear-gradient(135deg,#f5f7fa 0%,#c3cfe2 100%);border:1px solid #e2e8f0;border-radius:8px;display:flex;align-items:center;justify-content:center;font-family:system-ui,sans-serif;overflow:hidden;"><div style="text-align:center;"><div style="font-size:11px;color:#94a3b8;letter-spacing:0.5px;">ADVERTISEMENT</div><div style="font-size:13px;color:#64748b;margin-top:4px;">AdShqip &mdash; ' . htmlspecialchars($size ?: 'auto') . '</div></div></div>';
    }

    private function buildRegularCampaignHtml(Campaign $campaign, Zone $zone, string $width, string $height): ?string
    {
        $ad = $this->selectCampaignAd($campaign);

        if (! $ad) {
            return null;
        }

        if (! PlatformSetting::getVideoAdsEnabled() && $ad->ad_type === 'video') {
            return null;
        }

        $query = array_filter(request()->query(), fn ($value) => $value !== null && $value !== '');
        $query['zone_id'] = $zone->id;
        $src = route('ad.serve', $ad->id) . '?' . http_build_query($query);

        $frameStyles = [
            'width:' . $width,
            'border:0',
            'display:block',
            'margin:0 auto',
            'background:transparent',
            'overflow:hidden',
        ];

        if ($zone->format_key === 'display_video') {
            $frameStyles[] = 'max-width:640px';
            $frameStyles[] = 'aspect-ratio:16/9';
            $frameStyles[] = 'height:auto';
            $frameStyles[] = 'min-height:360px';
        } elseif (preg_match('/^\d+px$/', $height)) {
            $frameStyles[] = 'height:' . $height;
        } else {
            $frameStyles[] = 'min-height:250px';
            $frameStyles[] = 'height:auto';
        }

        if ($zone->format_key === 'special_web' && in_array($zone->size_key, ['native', 'text', 'in_page_push', 'social_bar'], true)) {
            $frameStyles[] = 'min-height:220px';
        }

        return '<iframe src="' . e($src) . '" title="AdShqip Campaign Ad" loading="lazy" scrolling="no" allow="autoplay; fullscreen" style="' . e(implode(';', $frameStyles)) . '"></iframe>';
    }

    private function selectCampaignAd(Campaign $campaign): ?Ad
    {
        $ads = Ad::where('campaign_id', $campaign->id)
            ->where('is_deleted', false)
            ->where('status', 'active')
            ->get();

        if ($ads->isEmpty()) {
            return null;
        }

        $minimumImpressions = PlatformSetting::getCreativeMinimumImpressionsPerDay();
        $todayImpressions = StatDaily::query()
            ->whereDate('date', today())
            ->whereIn('ad_id', $ads->pluck('id'))
            ->selectRaw('ad_id, SUM(impressions) as total_impressions')
            ->groupBy('ad_id')
            ->pluck('total_impressions', 'ad_id');

        if ($minimumImpressions > 0) {
            $underServed = $ads
                ->filter(fn (Ad $ad) => (int) ($todayImpressions[$ad->id] ?? 0) < $minimumImpressions)
                ->sort(function (Ad $left, Ad $right) use ($todayImpressions) {
                    $leftImpressions = (int) ($todayImpressions[$left->id] ?? 0);
                    $rightImpressions = (int) ($todayImpressions[$right->id] ?? 0);

                    if ($leftImpressions !== $rightImpressions) {
                        return $leftImpressions <=> $rightImpressions;
                    }

                    if (($left->weight ?? 0) !== ($right->weight ?? 0)) {
                        return ($right->weight ?? 0) <=> ($left->weight ?? 0);
                    }

                    return $right->id <=> $left->id;
                })
                ->values();

            if ($underServed->isNotEmpty()) {
                return $underServed->first();
            }
        }

        return $this->pickWeightedAd($ads);
    }

    private function pickWeightedAd($ads): ?Ad
    {
        $pool = $ads->values();
        $totalWeight = $pool->sum(fn (Ad $ad) => max(1, (int) ($ad->weight ?? 1)));

        if ($totalWeight <= 0) {
            return $pool->sortByDesc('id')->first();
        }

        $draw = random_int(1, $totalWeight);
        $running = 0;

        foreach ($pool as $ad) {
            $running += max(1, (int) ($ad->weight ?? 1));
            if ($draw <= $running) {
                return $ad;
            }
        }

        return $pool->sortByDesc('id')->first();
    }

    private function buildDirectCampaignFrame(Zone $zone, int $campaignId, string $width, string $height): string
    {
        $src = route('direct.serve', $campaignId) . '?zone_id=' . $zone->id;

        $frameStyles = [
            'width:' . $width,
            'border:0',
            'display:block',
            'margin:0 auto',
            'background:transparent',
            'overflow:hidden',
        ];

        if ($zone->format_key === 'display_video') {
            $frameStyles[] = 'max-width:640px';
            $frameStyles[] = 'aspect-ratio:16/9';
            $frameStyles[] = 'height:auto';
            $frameStyles[] = 'min-height:360px';
        } elseif (preg_match('/^\d+px$/', $height)) {
            $frameStyles[] = 'height:' . $height;
        } else {
            $frameStyles[] = 'min-height:250px';
            $frameStyles[] = 'height:auto';
        }

        if ($zone->format_key === 'special_web' && in_array($zone->size_key, ['native', 'text', 'in_page_push', 'social_bar'], true)) {
            $frameStyles[] = 'min-height:220px';
        }

        return '<iframe src="' . e($src) . '" title="AdShqip Direct Campaign" loading="lazy" scrolling="no" allow="autoplay; fullscreen" style="' . e(implode(';', $frameStyles)) . '"></iframe>';
    }

    /**
     * Build the JS loader that injects the ad into the page.
     */
    private function buildLoaderJs(Zone $zone, string $adHtml): string
    {
        $zoneId = $zone->id;
        $escapedHtml = addslashes(str_replace(["\n", "\r"], '', $adHtml));
        $autoloadEnabled = app(AdDeliveryOptions::class)->bannerAutoloadEnabled();

        $autoReloadJs = '';
        if ($autoloadEnabled && $zone->auto_reload && $zone->reload_time && $zone->reload_time > 0) {
            $autoReloadJs = "setInterval(function(){var c=document.getElementById('adshqip-zone-{$zoneId}');if(c){c.innerHTML=h;}},{$zone->reload_time}*1000);";
        }

        $autoloadJs = $autoloadEnabled ? "loadZone();\n    " : '';

        return <<<JS
(function(){
    var h='{$escapedHtml}';
    function loadZone(){
        var c=document.getElementById('adshqip-zone-{$zoneId}');
        if(!c){
            c=document.querySelector('[data-zone-id="{$zoneId}"]');
        }
        if(c){
            c.innerHTML=h;
            c.setAttribute('data-loaded','1');
        }
    }
    window.adshqipLoadZone{$zoneId}=loadZone;
    {$autoloadJs}
    {$autoReloadJs}
})();
JS;
    }

    /**
     * Return empty JS (no ad to show).
     */
    private function emptyJs()
    {
        return response('/* no ad */', 200)
            ->header('Content-Type', 'application/javascript; charset=utf-8')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Access-Control-Allow-Origin', '*');
    }
}
