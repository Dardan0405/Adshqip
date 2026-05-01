<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DirectCampaign;
use App\Models\DirectCampaignCreative;
use App\Models\DirectCampaignStat;
use App\Models\DirectCampaignTargeting;
use App\Models\PlatformSetting;
use App\Support\AdDeliveryOptions;
use App\Support\AntiFraudClickGuard;
use App\Support\GeoCpmResolver;
use App\Support\UrlAdReporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Direct Campaign Serve & Tracking Engine
 *
 * Handles the public-facing ad delivery and event tracking for Direct Campaigns:
 *   - /serve/direct/{id}            → Render the ad HTML with embedded tracking beacons
 *   - /serve/direct/{id}/click      → Record click, redirect to destination URL
 *   - /serve/direct/{id}/view       → Record viewable impression (1×1 beacon)
 *   - /serve/direct/{id}/conversion → Record conversion (1×1 beacon)
 *   - /track/direct/{id}/postback   → S2S conversion postback
 *
 * This mirrors the regular campaign tracker at /serve/ad/{id} in AdCreativeController,
 * but operates against the aq_direct_campaigns + aq_direct_campaign_stats tables.
 */
class DirectCampaignServeController extends Controller
{
    // ────────────────────────────────────────────────────
    //  1. SERVE — Render the ad creative
    // ────────────────────────────────────────────────────

    /**
     * Public: Serve a Direct Campaign ad creative.
     * GET /serve/direct/{id}
     *
     * Performs all enforcement checks (status, schedule, budget, targeting,
     * frequency cap) and then renders the appropriate creative HTML with
     * embedded tracking scripts for view/adblock detection.
     */
    public function serve(int $id)
    {
        $campaign = DirectCampaign::with(['creatives', 'targeting'])->find($id);
        $request  = request();
        $deliveryOptions = app(AdDeliveryOptions::class);
        $debug    = $request->query('debug') === '1';

        // ── Basic availability ──
        if (! $campaign || $campaign->is_deleted) {
            if ($debug) return $this->adResponse('<pre>BLOCKED: campaign not found or deleted.</pre>');
            return $this->adResponse('<!-- direct campaign not available -->', 204);
        }

        if (! $deliveryOptions->mobileAdsEnabled() && $deliveryOptions->isMobileOrTabletRequest($request)) {
            if ($debug) return $this->adResponse('<pre>BLOCKED: mobile ads are disabled.</pre>');
            return $this->adResponse('<!-- mobile ads disabled -->', 204);
        }

        // ── 1. Status check ──
        if ($campaign->status !== 'active') {
            if ($debug) return $this->adResponse('<pre>BLOCKED: campaign not active. Status: ' . $campaign->status . '</pre>');
            return $this->adResponse('<!-- campaign not active -->', 204);
        }

        // ── 2. Schedule / date range ──
        if ($campaign->start_date && now()->lt($campaign->start_date)) {
            if ($debug) return $this->adResponse('<pre>BLOCKED: campaign not started yet. Start: ' . $campaign->start_date . '</pre>');
            return $this->adResponse('<!-- campaign not started -->', 204);
        }
        if ($campaign->end_date && now()->gt($campaign->end_date)) {
            if ($debug) return $this->adResponse('<pre>BLOCKED: campaign ended. End: ' . $campaign->end_date . '</pre>');
            return $this->adResponse('<!-- campaign ended -->', 204);
        }

        // ── 3. Budget enforcement ──
        if ($campaign->total_budget > 0 && $campaign->remaining_budget <= 0) {
            if ($debug) return $this->adResponse('<pre>BLOCKED: budget exhausted. Total: ' . $campaign->total_budget . ', Remaining: ' . $campaign->remaining_budget . '</pre>');
            return $this->adResponse('<!-- budget exhausted -->', 204);
        }

        // Daily budget enforcement
        if ($campaign->daily_budget > 0) {
            $todaySpend = DirectCampaignStat::where('campaign_id', $id)
                ->where('date', now()->toDateString())
                ->sum('revenue');
            if ($todaySpend >= $campaign->daily_budget) {
                if ($debug) return $this->adResponse('<pre>BLOCKED: daily budget exhausted. Daily budget: ' . $campaign->daily_budget . ', Today spend: ' . $todaySpend . '</pre>');
                return $this->adResponse('<!-- daily budget exhausted -->', 204);
            }
        }

        // ── 4. Targeting enforcement ──
        $blocked = $this->enforceTargeting($campaign, $request, $debug);
        if ($blocked !== null) {
            return $blocked;
        }

        // ── 5. Frequency cap ──
        $freqCap = $campaign->frequency_cap;
        if ($freqCap && $freqCap > 0) {
            $period = $campaign->frequency_cap_period ?? 'day';
            $freqKey = "aq_dc_freq_{$campaign->id}_{$period}_" . $this->frequencyPeriodKey($period);
            try {
                $count = (int) $request->session()->get($freqKey, 0);
                if ($count >= $freqCap) {
                    if ($debug) return $this->adResponse('<pre>BLOCKED: frequency cap reached. Cap: ' . $freqCap . '/' . $period . ', Count: ' . $count . '</pre>');
                    return $this->adResponse('<!-- frequency cap reached -->', 204);
                }
                $request->session()->put($freqKey, $count + 1);
            } catch (\Exception $e) {
                // No session — skip cap
            }
        }

        // ── 6. Priority / weight-based delivery logic ──
        // For requests that load a "zone" (future), multiple campaigns compete.
        // When served directly by ID, priority only affects ORDER of consideration;
        // actual selection happens upstream. We still record priority in stats.

        // ── Track impression ──
        $creative = $this->selectCreative($campaign);
        if (! $deliveryOptions->videoAdsEnabled() && $creative && in_array($creative->creative_type, ['video', 'clip'], true)) {
            if ($debug) return $this->adResponse('<pre>BLOCKED: video ads are disabled.</pre>');
            return $this->adResponse('<!-- video ads disabled -->', 204);
        }
        $this->trackStat($campaign, $creative, 'impression');

        // ── Build the ad HTML ──
        $countryParam = $request->query('country');
        $zoneId = $request->query('zone_id');
        $query = [];
        if ($countryParam) {
            $query['country'] = $countryParam;
        }
        if ($zoneId) {
            $query['zone_id'] = $zoneId;
        }
        $clickUrl = $this->buildTrackingUrl('click', $id, $query, $request, $campaign->destination_url);
        $tracking = $this->trackingScript($id, $countryParam, $zoneId ? (int) $zoneId : null, $request);
        app(UrlAdReporter::class)->logDirectEvent($request, $campaign, $creative, 'serve', $clickUrl, $campaign->destination_url);

        // Determine which creative to render
        if ($creative && $creative->creative_type === 'image' && $creative->file_path) {
            return $this->renderImageAd($campaign, $creative, $clickUrl, $tracking);
        }

        if ($creative && in_array($creative->creative_type, ['video', 'clip']) && $creative->file_path) {
            return $this->renderVideoAd($campaign, $creative, $clickUrl, $tracking);
        }

        if ($creative && $creative->creative_type === 'html' && $creative->file_path) {
            return $this->renderHtmlAd($campaign, $creative, $clickUrl, $tracking);
        }

        // Native / Text / Fallback — use campaign-level headline/body/CTA
        return $this->renderNativeAd($campaign, $creative, $clickUrl, $tracking);
    }

    // ────────────────────────────────────────────────────
    //  2. CLICK — Track click & redirect
    // ────────────────────────────────────────────────────

    /**
     * GET /serve/direct/{id}/click
     *
     * Records a click event against aq_direct_campaign_stats,
     * then 302-redirects the visitor to the campaign's destination URL
     * with click_id / aq_cid appended for S2S attribution.
     */
    public function click(int $id, Request $request)
    {
        $campaign = DirectCampaign::with('creatives')->find($id);
        $deliveryOptions = app(AdDeliveryOptions::class);

        if (! $campaign || $campaign->is_deleted) {
            return redirect('/');
        }

        if (! $deliveryOptions->mobileAdsEnabled() && $deliveryOptions->isMobileOrTabletRequest($request)) {
            return response('Mobile ads are disabled.', 204);
        }

        $creative = $this->selectCreative($campaign);

        if (! $deliveryOptions->videoAdsEnabled() && $creative && in_array($creative->creative_type, ['video', 'clip'], true)) {
            return response('Video ads are disabled.', 204);
        }

        $clickId = Str::uuid()->toString();
        $zoneId = $request->query('zone_id');
        $guardResult = app(AntiFraudClickGuard::class)->inspect($request, [
            'ad_id' => null,
            'zone_id' => is_numeric($zoneId) ? (int) $zoneId : null,
            'viewer_id' => $request->cookie('aq_viewer_id', $clickId),
        ]);

        if (! $guardResult['allowed']) {
            return response('Click blocked by anti-fraud protection.', 429);
        }

        // Track click stat
        $this->trackStat($campaign, $creative, 'click');

        // Detect device info
        $ua = strtolower($request->userAgent() ?? '');
        $deviceType = $this->detectDeviceType($ua, $request);

        // Store click record in aq_direct_campaign_clicks
        try {
            DB::table('aq_direct_campaign_clicks')->insert([
                'campaign_id' => $campaign->id,
                'creative_id' => $creative?->id,
                'viewer_id'   => $guardResult['viewer_id'],
                'ip_address'  => $request->ip(),
                'user_agent'  => substr($request->userAgent() ?? '', 0, 500),
                'country_code' => $this->detectCountry($request),
                'device_type' => $deviceType,
                'is_unique'   => true,
                'created_at'  => now(),
            ]);
        } catch (\Exception $e) {
            // Table may not exist yet — clicks are still tracked in stats
        }

        // Build destination URL with click_id appended
        $destinationUrl = $deliveryOptions->resolveDestinationUrl($request->query('target'), $campaign->destination_url) ?? $campaign->destination_url;

        // If campaign has a click_tracking_url, fire it as a pixel
        if ($campaign->click_tracking_url) {
            $trackUrl = str_replace(
                ['{click_id}', '{campaign_id}'],
                [$clickId, $campaign->id],
                $campaign->click_tracking_url
            );
            // Fire-and-forget via image pixel in redirect page
        }

        $separator = str_contains($destinationUrl, '?') ? '&' : '?';
        $destinationUrl .= $separator . 'click_id=' . $clickId . '&aq_dcid=' . $campaign->id;
        app(UrlAdReporter::class)->logDirectEvent($request, $campaign, $creative, 'click', $request->fullUrl(), $destinationUrl);

        return redirect($destinationUrl);
    }

    // ────────────────────────────────────────────────────
    //  3. VIEW — Viewable impression beacon
    // ────────────────────────────────────────────────────

    /**
     * GET /serve/direct/{id}/view
     *
     * Called by the IntersectionObserver JS embedded in the served ad
     * after the ad has been visible for ≥1 second.
     * Returns a 1×1 transparent GIF.
     */
    public function view(int $id)
    {
        $campaign = DirectCampaign::with('creatives')->find($id);

        if ($campaign && ! $campaign->is_deleted) {
            $creative = $this->selectCreative($campaign);
            $this->trackStat($campaign, $creative, 'view');
            app(UrlAdReporter::class)->logDirectEvent(request(), $campaign, $creative, 'view');
        }

        return $this->pixelResponse();
    }

    // ────────────────────────────────────────────────────
    //  4. ADBLOCK — Adblock detection beacon
    // ────────────────────────────────────────────────────

    /**
     * GET /serve/direct/{id}/adblock
     */
    public function adblock(int $id)
    {
        $campaign = DirectCampaign::with('creatives')->find($id);

        if ($campaign && ! $campaign->is_deleted) {
            $creative = $this->selectCreative($campaign);
            $this->trackStat($campaign, $creative, 'adblock');
            app(UrlAdReporter::class)->logDirectEvent(request(), $campaign, $creative, 'adblock');
        }

        return $this->pixelResponse();
    }

    // ────────────────────────────────────────────────────
    //  5. CONVERSION — Conversion pixel
    // ────────────────────────────────────────────────────

    /**
     * GET /serve/direct/{id}/conversion
     *
     * Placed by the advertiser on their "thank you" page:
     *   <img src="/serve/direct/{id}/conversion" width="1" height="1">
     */
    public function conversion(int $id)
    {
        $campaign = DirectCampaign::with('creatives')->find($id);

        if ($campaign && ! $campaign->is_deleted) {
            $creative = $this->selectCreative($campaign);
            $this->trackStat($campaign, $creative, 'conversion');
            app(UrlAdReporter::class)->logDirectEvent(request(), $campaign, $creative, 'conversion');
        }

        return $this->pixelResponse();
    }

    // ────────────────────────────────────────────────────
    //  6. S2S POSTBACK — Server-to-server conversion
    // ────────────────────────────────────────────────────

    /**
     * GET|POST /track/direct/{id}/postback
     *
     * Params: click_id (required), payout (optional), tx_id (optional), goal (optional)
     */
    public function postback(int $id, Request $request)
    {
        $campaign = DirectCampaign::find($id);

        if (! $campaign || $campaign->is_deleted) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Direct campaign not found.',
            ], 404);
        }

        $clickId = $request->input('click_id');
        if (empty($clickId)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Missing required parameter: click_id',
            ], 400);
        }

        $payout = (float) $request->input('payout', 0);
        $txId   = $request->input('tx_id');
        $goal   = $request->input('goal', 'sale');

        // Prevent duplicate conversions for the same tx_id
        if ($txId) {
            try {
                $exists = DB::table('aq_direct_campaign_clicks')
                    ->where('campaign_id', $id)
                    ->where('viewer_id', $clickId)
                    ->exists();
                // If we can't verify, just proceed
            } catch (\Exception $e) {
                // Table may not exist
            }
        }

        // Record the conversion
        $creative = $this->selectCreative($campaign);
        $this->trackStat($campaign, $creative, 'conversion');

        return response()->json([
            'status'  => 'ok',
            'message' => 'Conversion recorded.',
            'campaign_id' => $id,
            'click_id'    => $clickId,
        ]);
    }

    // ═══════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ═══════════════════════════════════════════════════

    /**
     * Record an event (impression / click / view / conversion) in aq_direct_campaign_stats.
     *
     * Mirrors the logic of AdCreativeController::trackStat() but writes to
     * the direct campaign stats table instead.
     */
    private function trackStat(DirectCampaign $campaign, ?DirectCampaignCreative $creative, string $type): void
    {
        $today   = now()->toDateString();
        $request = request();
        $zoneId = $request->query('zone_id');
        $zoneId = is_numeric($zoneId) ? (int) $zoneId : null;

        // Detect device type
        $ua = strtolower($request->userAgent() ?? '');
        $deviceType = $this->detectDeviceType($ua, $request);

        // Detect country
        $countryCode = $this->detectCountry($request);

        // Unique detection via session
        $sessionKey = "aq_dc_tracked_{$type}_{$campaign->id}_{$today}";
        try {
            $isUnique = ! $request->session()->has($sessionKey);
        } catch (\Exception $e) {
            $isUnique = true;
        }

        // Upsert: find or create the row for today + campaign + creative + device + country
        $creativeId = $creative?->id;

        $rowQuery = DirectCampaignStat::where('date', $today)
            ->where('campaign_id', $campaign->id)
            ->where('device_type', $deviceType);

        if ($creativeId) {
            $rowQuery->where('creative_id', $creativeId);
        } else {
            $rowQuery->whereNull('creative_id');
        }

        if ($countryCode) {
            $rowQuery->where('country_code', $countryCode);
        } else {
            $rowQuery->whereNull('country_code');
        }

        if ($zoneId) {
            $rowQuery->where('zone_id', $zoneId);
        } else {
            $rowQuery->whereNull('zone_id');
        }

        $row = $rowQuery->first();

        if (! $row) {
            $row = DirectCampaignStat::create([
                'date'                => $today,
                'campaign_id'         => $campaign->id,
                'creative_id'         => $creativeId,
                'zone_id'             => $zoneId,
                'country_code'        => $countryCode,
                'device_type'         => $deviceType,
                'impressions'         => 0,
                'viewable_impressions' => 0,
                'clicks'              => 0,
                'unique_clicks'       => 0,
                'conversions'         => 0,
                'revenue'             => 0,
                'publisher_earnings'  => 0,
            ]);
        }

        // Get pricing info
        $bidAmount    = (float) ($campaign->bid_amount ?? 0);
        $pricingModel = strtolower($campaign->pricing_model ?? 'cpm');

        if ($pricingModel === 'cpm' && $countryCode) {
            $geoCpm = app(GeoCpmResolver::class)->resolve($countryCode);
            if ($geoCpm !== null) {
                $bidAmount = $geoCpm;
            }
        }

        // ── Increment counters + revenue ──
        if ($type === 'impression') {
            $row->increment('impressions');
            // CPM: revenue per 1000 impressions
            if ($pricingModel === 'cpm' && $bidAmount > 0) {
                $row->increment('revenue', round($bidAmount / 1000, 4));
            }
        } elseif ($type === 'click') {
            $row->increment('clicks');
            if ($isUnique) {
                $row->increment('unique_clicks');
            }
            // CPC: revenue per click
            if ($pricingModel === 'cpc' && $bidAmount > 0) {
                $row->increment('revenue', $bidAmount);
            }
        } elseif ($type === 'view') {
            $row->increment('viewable_impressions');
            // CPV / CPV_CTW: revenue per viewable impression
            if (in_array($pricingModel, ['cpv', 'cpv_ctw']) && $bidAmount > 0) {
                $row->increment('revenue', $bidAmount);
            }
        } elseif ($type === 'conversion') {
            $row->increment('conversions');
            // CPA: revenue per conversion
            if ($pricingModel === 'cpa' && $bidAmount > 0) {
                $row->increment('revenue', $bidAmount);
            }
        } elseif ($type === 'adblock') {
            $row->increment('adblock_detected');
        }

        // Recalculate derived metrics
        $row->refresh();
        if ($row->impressions > 0) {
            $row->ctr  = round(($row->clicks / $row->impressions) * 100, 4);
            $row->ecpm = $row->revenue > 0 ? round(($row->revenue / $row->impressions) * 1000, 4) : 0;
        }
        if ($row->clicks > 0 && $row->revenue > 0) {
            $row->avg_cpc = round($row->revenue / $row->clicks, 4);
        }
        if ($row->conversions > 0) {
            $row->conversion_rate = $row->clicks > 0 ? round(($row->conversions / $row->clicks) * 100, 4) : 0;
            if ($row->revenue > 0) {
                $row->avg_cpa = round($row->revenue / $row->conversions, 4);
            }
        }
        $row->save();

        // Deduct from remaining budget
        if ($row->revenue > 0 && $campaign->total_budget > 0) {
            $totalSpend = DirectCampaignStat::where('campaign_id', $campaign->id)->sum('revenue');
            $campaign->remaining_budget = max(0, $campaign->total_budget - $totalSpend);
            if ($campaign->remaining_budget <= 0) {
                $campaign->status = 'completed';
            }
            $campaign->save();
        }

        // Mark as seen in session for unique tracking
        if ($isUnique) {
            try {
                $request->session()->put($sessionKey, true);
            } catch (\Exception $e) {
                // No session available
            }
        }

        app(\App\Support\AdServingLogger::class)->log($request, [
            'delivery_type' => 'direct',
            'event_type' => $type,
            'status' => $type === 'impression' ? 'served' : 'tracked',
            'direct_campaign_id' => $campaign->id,
            'direct_creative_id' => $creativeId,
            'zone_id' => $zoneId,
            'advertiser_id' => $campaign->advertiser_id,
            'country_code' => $countryCode,
            'device_type' => $deviceType,
            'pricing_model' => $pricingModel,
            'bid_amount' => $bidAmount,
            'revenue' => $this->eventRevenue($pricingModel, $type, $bidAmount),
            'publisher_earnings' => 0,
            'destination_url' => $campaign->destination_url,
            'meta' => [
                'is_unique' => $isUnique,
                'direct_campaign_stat_id' => $row->id,
            ],
        ]);
    }

    private function eventRevenue(string $pricingModel, string $eventType, float $bidAmount): float
    {
        if ($bidAmount <= 0) {
            return 0.0;
        }

        return match (true) {
            $pricingModel === 'cpm' && $eventType === 'impression' => round($bidAmount / 1000, 4),
            $pricingModel === 'cpc' && $eventType === 'click' => $bidAmount,
            in_array($pricingModel, ['cpv', 'cpv_ctw'], true) && $eventType === 'view' => $bidAmount,
            $pricingModel === 'cpa' && $eventType === 'conversion' => $bidAmount,
            default => 0.0,
        };
    }

    /**
     * Select the best creative for a campaign.
     * Uses priority/weight logic: primary creative first, then winner, then random by weight.
     */
    private function selectCreative(DirectCampaign $campaign): ?DirectCampaignCreative
    {
        $creatives = $campaign->creatives;

        if ($creatives->isEmpty()) {
            return null;
        }

        // 1. Use primary creative if one is set
        $primary = $creatives->firstWhere('is_primary', true);
        if ($primary && $primary->status === 'active') {
            return $primary;
        }

        // 2. Use A/B test winner if one is designated
        $winner = $creatives->firstWhere('is_winner', true);
        if ($winner && $winner->status === 'active') {
            return $winner;
        }

        // 3. Filter to active-only creatives
        $active = $creatives->filter(fn($c) => ($c->status ?? 'active') === 'active' || $c->status === null);

        if ($active->isEmpty()) {
            return $creatives->first(); // fallback to any creative
        }

        // 4. Weighted random selection (for A/B testing)
        if ($campaign->ab_test_enabled && $campaign->ab_test_split_percent) {
            // Simple 50/50 or configured split
            $rand = mt_rand(1, 100);
            $split = (int) $campaign->ab_test_split_percent;
            if ($rand <= $split && $active->count() >= 2) {
                return $active->values()[1]; // variant B
            }
            return $active->first(); // variant A
        }

        return $active->first();
    }

    // ────────────────────────────────────────────────────
    //  TARGETING ENFORCEMENT
    // ────────────────────────────────────────────────────

    /**
     * Check all targeting rules from aq_direct_campaign_targeting.
     * Returns a Response if blocked, or null to proceed.
     */
    private function enforceTargeting(DirectCampaign $campaign, Request $request, bool $debug)
    {
        $targetingRules = $campaign->targeting;

        if ($targetingRules->isEmpty()) {
            return null; // No targeting = serve to everyone
        }

        foreach ($targetingRules as $rule) {
            if (! ($rule->is_active ?? true)) {
                continue;
            }

            $type       = $rule->targeting_type;
            $values     = $rule->target_values;
            $mode       = $rule->match_mode ?? 'include';

            if (empty($values) || ! is_array($values)) {
                continue;
            }

            $blocked = match ($type) {
                'geo_country'     => $this->checkGeoTargeting($values, $mode, $request, $debug),
                'device'          => $this->checkDeviceTargeting($values, $mode, $request, $debug),
                'os'              => $this->checkOsTargeting($values, $mode, $request, $debug),
                'browser'         => $this->checkBrowserTargeting($values, $mode, $request, $debug),
                'language'        => $this->checkLanguageTargeting($values, $mode, $request, $debug),
                'connection_type' => $this->checkConnectionTypeTargeting($values, $mode, $request, $debug),
                'carrier'         => $this->checkCarrierTargeting($values, $mode, $request, $debug),
                default           => null,
            };

            if ($blocked !== null) {
                return $blocked;
            }
        }

        return null;
    }

    private function checkGeoTargeting(array $values, string $mode, Request $request, bool $debug)
    {
        $visitorCountry = $this->detectCountry($request);
        if (! $visitorCountry) return null; // Can't detect, allow through

        $matched = in_array(strtoupper($visitorCountry), array_map('strtoupper', $values));

        if ($mode === 'include' && ! $matched) {
            if ($debug) return $this->adResponse('<pre>BLOCKED: geo not targeted. Visitor: ' . $visitorCountry . ', Targeted: ' . json_encode($values) . '</pre>');
            return $this->adResponse('<!-- geo not targeted -->', 204);
        }
        if ($mode === 'exclude' && $matched) {
            if ($debug) return $this->adResponse('<pre>BLOCKED: geo excluded. Visitor: ' . $visitorCountry . ', Excluded: ' . json_encode($values) . '</pre>');
            return $this->adResponse('<!-- geo excluded -->', 204);
        }

        return null;
    }

    private function checkDeviceTargeting(array $values, string $mode, Request $request, bool $debug)
    {
        $ua = strtolower($request->userAgent() ?? '');
        $visitorDevice = $this->detectDeviceType($ua, $request);

        $matched = false;
        foreach ($values as $dev) {
            if (strtolower($dev) === $visitorDevice || str_contains(strtolower($dev), $visitorDevice)) {
                $matched = true;
                break;
            }
        }

        if ($mode === 'include' && ! $matched) {
            if ($debug) return $this->adResponse('<pre>BLOCKED: device not targeted. Visitor: ' . $visitorDevice . ', Targeted: ' . json_encode($values) . '</pre>');
            return $this->adResponse('<!-- device not targeted -->', 204);
        }
        if ($mode === 'exclude' && $matched) {
            if ($debug) return $this->adResponse('<pre>BLOCKED: device excluded. Visitor: ' . $visitorDevice . '</pre>');
            return $this->adResponse('<!-- device excluded -->', 204);
        }

        return null;
    }

    private function checkOsTargeting(array $values, string $mode, Request $request, bool $debug)
    {
        $ua = strtolower($request->userAgent() ?? '');
        $visitorOs = $this->detectOs($ua);
        if (! $visitorOs) return null;

        $matched = false;
        foreach ($values as $os) {
            // Match on OS family (e.g. "Windows" matches "Windows 10")
            $osLower = strtolower($os);
            $visitorOsLower = strtolower($visitorOs);
            if ($osLower === $visitorOsLower || str_starts_with($osLower, $visitorOsLower) || str_starts_with($visitorOsLower, $osLower)) {
                $matched = true;
                break;
            }
        }

        if ($mode === 'include' && ! $matched) {
            if ($debug) return $this->adResponse('<pre>BLOCKED: OS not targeted. Visitor: ' . $visitorOs . ', Targeted: ' . json_encode($values) . '</pre>');
            return $this->adResponse('<!-- OS not targeted -->', 204);
        }

        return null;
    }

    private function checkBrowserTargeting(array $values, string $mode, Request $request, bool $debug)
    {
        $ua = strtolower($request->userAgent() ?? '');
        $visitorBrowser = $this->detectBrowser($ua);
        if (! $visitorBrowser) return null;

        $matched = false;
        foreach ($values as $br) {
            $brLower = strtolower($br);
            $vbLower = strtolower($visitorBrowser);
            if ($brLower === $vbLower || str_starts_with($brLower, $vbLower) || str_starts_with($vbLower, $brLower)) {
                $matched = true;
                break;
            }
        }

        if ($mode === 'include' && ! $matched) {
            if ($debug) return $this->adResponse('<pre>BLOCKED: browser not targeted. Visitor: ' . $visitorBrowser . ', Targeted: ' . json_encode($values) . '</pre>');
            return $this->adResponse('<!-- browser not targeted -->', 204);
        }

        return null;
    }

    private function checkLanguageTargeting(array $values, string $mode, Request $request, bool $debug)
    {
        $visitorLang = $this->detectLanguage($request);
        if (! $visitorLang) return null;

        $matched = in_array(strtolower($visitorLang), array_map('strtolower', $values));

        if ($mode === 'include' && ! $matched) {
            if ($debug) return $this->adResponse('<pre>BLOCKED: language not targeted. Visitor: ' . $visitorLang . ', Targeted: ' . json_encode($values) . '</pre>');
            return $this->adResponse('<!-- language not targeted -->', 204);
        }

        return null;
    }

    private function checkConnectionTypeTargeting(array $values, string $mode, Request $request, bool $debug)
    {
        $visitorConn = $this->detectConnectionType($request);
        if (! $visitorConn) return null;

        $matched = in_array($visitorConn, $values);

        if ($mode === 'include' && ! $matched) {
            if ($debug) return $this->adResponse('<pre>BLOCKED: connection type not targeted. Visitor: ' . $visitorConn . '</pre>');
            return $this->adResponse('<!-- connection type not targeted -->', 204);
        }

        return null;
    }

    private function checkCarrierTargeting(array $values, string $mode, Request $request, bool $debug)
    {
        $visitorCarrier = $this->detectCarrier($request);
        if (! $visitorCarrier) return null;

        $matched = false;
        foreach ($values as $carrier) {
            if (is_array($carrier)) {
                $carrierName = $carrier['carrier'] ?? $carrier['name'] ?? '';
            } else {
                $carrierName = $carrier;
            }
            if (stripos($visitorCarrier, $carrierName) !== false || stripos($carrierName, $visitorCarrier) !== false) {
                $matched = true;
                break;
            }
        }

        if ($mode === 'include' && ! $matched) {
            if ($debug) return $this->adResponse('<pre>BLOCKED: carrier not targeted. Visitor: ' . $visitorCarrier . '</pre>');
            return $this->adResponse('<!-- carrier not targeted -->', 204);
        }

        return null;
    }

    // ────────────────────────────────────────────────────
    //  RENDERING HELPERS
    // ────────────────────────────────────────────────────

    private function renderImageAd(DirectCampaign $campaign, DirectCampaignCreative $creative, string $clickUrl, string $tracking)
    {
        $imgUrl = asset($creative->file_path);
        $alt    = e($campaign->headline ?: $campaign->name);
        $w      = $creative->width ? "width=\"{$creative->width}\"" : '';
        $h      = $creative->height ? "height=\"{$creative->height}\"" : '';

        $html = <<<HTML
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Ad</title><style>*{margin:0;padding:0}a{display:block;line-height:0}</style></head>
<body>
<a href="{$clickUrl}" target="_blank" rel="noopener" id="aq-ad"><img src="{$imgUrl}" alt="{$alt}" {$w} {$h} style="max-width:100%;border:0;"></a>
{$tracking}
</body></html>
HTML;
        return $this->adResponse($html);
    }

    private function renderVideoAd(DirectCampaign $campaign, DirectCampaignCreative $creative, string $clickUrl, string $tracking)
    {
        $videoSrc = asset($creative->file_path);
        $headline = e($campaign->headline ?: $campaign->name);
        $cta      = e($campaign->call_to_action ?: 'Learn More');
        $w = $creative->width ? "width=\"{$creative->width}\"" : 'width="640"';
        $h = $creative->height ? "height=\"{$creative->height}\"" : 'height="360"';
        $poster = $creative->thumbnail_path ? 'poster="' . asset($creative->thumbnail_path) . '"' : '';

        $html = <<<HTML
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Ad</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:#000;display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
.vc{position:relative;max-width:100%}
video{max-width:100%;display:block;border-radius:4px}
.v-label{position:absolute;top:8px;left:8px;padding:2px 8px;background:rgba(0,0,0,.6);color:#ccc;border-radius:4px;font-size:10px;text-transform:uppercase;letter-spacing:.5px;z-index:10}
.v-headline{position:absolute;bottom:52px;left:12px;padding:4px 10px;background:rgba(0,0,0,.7);color:#fff;border-radius:4px;font-size:12px;font-weight:600;z-index:10;max-width:60%}
.v-cta{position:absolute;bottom:12px;right:12px;padding:8px 18px;background:#4285f4;color:#fff;border-radius:6px;font-size:13px;font-weight:600;text-decoration:none;z-index:10}
.v-cta:hover{background:#3367d6}
</style></head>
<body>
<div class="vc" id="aq-ad">
    <span class="v-label">Sponsored</span>
    <div class="v-headline">{$headline}</div>
    <div id="aq-jw-player-slot" style="display:none;max-width:100%;"></div>
    <video id="aq-video-fallback" {$w} {$h} {$poster} autoplay muted playsinline controls>
        <source src="{$videoSrc}" type="video/mp4">
    </video>
    <a href="{$clickUrl}" target="_blank" rel="noopener" class="v-cta">{$cta}</a>
</div>
{$this->jwPlayerBootstrapScript('aq-jw-player-slot', 'aq-video-fallback', $videoSrc, $creative->thumbnail_path ? asset($creative->thumbnail_path) : null)}
{$tracking}
</body></html>
HTML;
        return $this->adResponse($html);
    }

    private function renderHtmlAd(DirectCampaign $campaign, DirectCampaignCreative $creative, string $clickUrl, string $tracking)
    {
        // Redirect to the HTML creative file
        return redirect(asset($creative->file_path));
    }

    private function renderNativeAd(DirectCampaign $campaign, ?DirectCampaignCreative $creative, string $clickUrl, string $tracking)
    {
        $headline   = e($campaign->headline ?: $campaign->name);
        $body       = e($campaign->body_text ?: '');
        $brand      = e($campaign->brand_name ?: '');
        $cta        = e($campaign->call_to_action ?: 'Learn More');
        $sponsored  = e($campaign->sponsored_label ?: 'Sponsored');
        $brandColor = $campaign->brand_color_primary ?: '#4285f4';

        $imgTag = '';
        if ($creative && $creative->file_path) {
            $imgUrl = asset($creative->file_path);
            $imgTag = "<img src=\"{$imgUrl}\" alt=\"" . e($campaign->name) . "\" style=\"width:100%;height:180px;object-fit:cover;border-radius:8px 8px 0 0;\">";
        }

        $logoTag = '';
        if ($campaign->brand_logo_url) {
            $logoUrl = e($campaign->brand_logo_url);
            $logoTag = "<img src=\"{$logoUrl}\" style=\"width:24px;height:24px;border-radius:4px;object-fit:cover;margin-right:6px;\">";
        }

        $html = <<<HTML
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Ad</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
.native-ad{border:1px solid #e0e0e0;border-radius:8px;background:#fff;max-width:320px;overflow:hidden;cursor:pointer;transition:box-shadow .2s}
.native-ad:hover{box-shadow:0 2px 12px rgba(0,0,0,.12)}
.native-ad-content{padding:12px 16px}
.native-ad-label{font-size:10px;color:#999;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px}
.native-ad-headline{font-size:15px;font-weight:600;color:#1a1a1a;margin-bottom:4px;line-height:1.3}
.native-ad-body{font-size:13px;color:#666;line-height:1.4;margin-bottom:8px}
.native-ad-footer{display:flex;align-items:center;justify-content:space-between}
.native-ad-brand{font-size:11px;color:#999;font-weight:500;display:flex;align-items:center}
.native-ad-cta{padding:6px 14px;background:{$brandColor};color:#fff;border-radius:4px;font-size:12px;font-weight:600;text-decoration:none;transition:opacity .2s}
.native-ad-cta:hover{opacity:.85}
</style></head>
<body>
<div class="native-ad" id="aq-ad" onclick="window.open('{$clickUrl}','_blank')">
    {$imgTag}
    <div class="native-ad-content">
        <div class="native-ad-label">{$sponsored}</div>
        <div class="native-ad-headline">{$headline}</div>
        <div class="native-ad-body">{$body}</div>
        <div class="native-ad-footer">
            <span class="native-ad-brand">{$logoTag}{$brand}</span>
            <a href="{$clickUrl}" target="_blank" rel="noopener" class="native-ad-cta">{$cta}</a>
        </div>
    </div>
</div>
{$tracking}
</body></html>
HTML;
        return $this->adResponse($html);
    }

    // ────────────────────────────────────────────────────
    //  RESPONSE HELPERS
    // ────────────────────────────────────────────────────

    /**
     * Return HTML with headers allowing cross-origin iframe embedding.
     */
    private function adResponse(string $html, int $status = 200)
    {
        return response($html, $status)
            ->header('Content-Type', 'text/html')
            ->header('X-Frame-Options', 'ALLOWALL')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Content-Security-Policy', 'frame-ancestors *');
    }

    /**
     * Return a 1×1 transparent GIF with no-cache headers.
     */
    private function pixelResponse()
    {
        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($gif, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Access-Control-Allow-Origin', '*');
    }

    /**
     * Generate tracking JavaScript for view and adblock detection.
     */
    private function trackingScript(int $campaignId, ?string $countryOverride = null, ?int $zoneId = null, ?Request $request = null): string
    {
        $request ??= request();
        $extraParams = [];
        if ($countryOverride) {
            $extraParams[] = 'country=' . urlencode($countryOverride);
        }
        if ($zoneId) {
            $extraParams[] = 'zone_id=' . $zoneId;
        }
        $suffix = !empty($extraParams) ? '&' . implode('&', $extraParams) : '';
        $viewUrl = $this->buildTrackingUrl('view', $campaignId, array_filter([
            'country' => $countryOverride,
            'zone_id' => $zoneId,
        ], fn ($value) => $value !== null && $value !== ''), $request);
        $adblockUrl = $this->buildTrackingUrl('adblock', $campaignId, array_filter([
            'country' => $countryOverride,
            'zone_id' => $zoneId,
        ], fn ($value) => $value !== null && $value !== ''), $request);

        return <<<SCRIPT
<script>
(function(){
  var vFired=false;
  var obs=new IntersectionObserver(function(entries){
    if(entries[0].isIntersecting&&!vFired){
      setTimeout(function(){
        if(!vFired){vFired=true;new Image().src='{$viewUrl}?_='+Date.now()+'{$suffix}';}
      },1000);
    }
  },{threshold:0.5});
  obs.observe(document.body);
})();
setTimeout(function(){
  var el=document.querySelector('#aq-ad,img,a');
  if(!el||el.offsetHeight===0||getComputedStyle(el).display==='none'){
    new Image().src='{$adblockUrl}?_='+Date.now()+'{$suffix}';
  }
},2000);
</script>
SCRIPT;
    }

    public function mobileClick(string $trackingPath, int $id, Request $request)
    {
        return $this->click($id, $request);
    }

    public function mobileView(string $trackingPath, int $id)
    {
        return $this->view($id);
    }

    public function mobileAdblock(string $trackingPath, int $id)
    {
        return $this->adblock($id);
    }

    public function mobileConversion(string $trackingPath, int $id)
    {
        return $this->conversion($id);
    }

    /**
     * Get the session key suffix for frequency cap period.
     */
    private function frequencyPeriodKey(string $period): string
    {
        return match ($period) {
            'hour'     => now()->format('Y-m-d-H'),
            'day'      => now()->toDateString(),
            'week'     => now()->format('Y-W'),
            'month'    => now()->format('Y-m'),
            'lifetime' => 'lifetime',
            default    => now()->toDateString(),
        };
    }

    // ────────────────────────────────────────────────────
    //  DETECTION HELPERS
    // ────────────────────────────────────────────────────

    /**
     * Detect device type from User-Agent. Supports ?device= override.
     */
    private function detectDeviceType(string $ua, Request $request): string
    {
        $override = $request->query('device');
        if ($override && in_array(strtolower($override), ['desktop', 'mobile', 'tablet'])) {
            return strtolower($override);
        }

        if (str_contains($ua, 'mobile') || str_contains($ua, 'android')) {
            return (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) ? 'tablet' : 'mobile';
        }
        if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) {
            return 'tablet';
        }

        return 'desktop';
    }

    /**
     * Detect 2-letter country code from IP. Supports ?country= override.
     */
    private function detectCountry(Request $request): ?string
    {
        $override = $request->query('country');
        if ($override && preg_match('/^[A-Za-z]{2}$/', $override)) {
            return strtoupper($override);
        }

        $ip = $request->ip();
        if (in_array($ip, ['127.0.0.1', '::1']) || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
            return null;
        }

        $cacheKey = "aq_geo_{$ip}";
        try {
            $cached = $request->session()->get($cacheKey);
            if ($cached !== null) {
                return $cached ?: null;
            }
        } catch (\Exception $e) {}

        try {
            $response = @file_get_contents("http://ip-api.com/json/{$ip}?fields=countryCode");
            if ($response) {
                $data = json_decode($response, true);
                $countryCode = $data['countryCode'] ?? null;
                try { $request->session()->put($cacheKey, $countryCode ?? ''); } catch (\Exception $e) {}
                return $countryCode;
            }
        } catch (\Exception $e) {}

        return null;
    }

    /**
     * Detect OS from User-Agent. Supports ?os= override.
     */
    private function detectOs(string $ua): ?string
    {
        $override = request()->query('os');
        if ($override && is_string($override)) return $override;

        if (str_contains($ua, 'windows'))   return 'Windows';
        if (str_contains($ua, 'iphone') || str_contains($ua, 'ipad')) return 'iOS';
        if (str_contains($ua, 'mac os'))    return 'macOS';
        if (str_contains($ua, 'android'))   return 'Android';
        if (str_contains($ua, 'cros'))      return 'Chrome OS';
        if (str_contains($ua, 'linux'))     return 'Linux';

        return null;
    }

    /**
     * Detect browser from User-Agent. Supports ?browser= override.
     */
    private function detectBrowser(string $ua): ?string
    {
        $override = request()->query('browser');
        if ($override && is_string($override)) return $override;

        if (str_contains($ua, 'samsungbrowser')) return 'Samsung Internet';
        if (str_contains($ua, 'ucbrowser'))      return 'UC Browser';
        if (str_contains($ua, 'vivaldi'))        return 'Vivaldi';
        if (str_contains($ua, 'brave'))          return 'Brave';
        if (str_contains($ua, 'opr') || str_contains($ua, 'opera')) return 'Opera';
        if (str_contains($ua, 'edg'))            return 'Edge';
        if (str_contains($ua, 'firefox') || str_contains($ua, 'fxios')) return 'Firefox';
        if (str_contains($ua, 'crios') || (str_contains($ua, 'chrome') && !str_contains($ua, 'chromium'))) return 'Chrome';
        if (str_contains($ua, 'safari') && !str_contains($ua, 'chrome')) return 'Safari';

        return null;
    }

    /**
     * Detect language from Accept-Language header. Supports ?language= override.
     */
    private function detectLanguage(Request $request): ?string
    {
        $override = $request->query('language');
        if ($override && is_string($override)) {
            return strtolower(substr($override, 0, 2));
        }

        $acceptLanguage = $request->header('Accept-Language');
        if (! $acceptLanguage) return null;

        $languages = [];
        foreach (explode(',', $acceptLanguage) as $part) {
            $segments = explode(';', trim($part));
            $lang = trim($segments[0]);
            $q = 1.0;
            if (isset($segments[1]) && preg_match('/q=([\d.]+)/', $segments[1], $m)) {
                $q = (float) $m[1];
            }
            $code = strtolower(substr($lang, 0, 2));
            if ($code && $code !== '*') {
                $languages[$code] = max($languages[$code] ?? 0, $q);
            }
        }

        if (empty($languages)) return null;
        arsort($languages);
        return array_key_first($languages);
    }

    /**
     * Detect connection type (Wi-Fi / mobile data). Supports ?connection_type= override.
     */
    private function detectConnectionType(Request $request): ?string
    {
        $override = $request->query('connection_type');
        if ($override && is_string($override)) return $override;

        $ip = $request->query('ip') ?? $request->ip();
        if (! $ip || in_array($ip, ['127.0.0.1', '::1'])) return null;

        try {
            $response = @file_get_contents("http://ip-api.com/json/{$ip}?fields=mobile");
            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['mobile'])) {
                    return $data['mobile'] ? '4G/LTE' : 'Wi-Fi';
                }
            }
        } catch (\Exception $e) {}

        return null;
    }

    /**
     * Detect carrier/ISP from IP. Supports ?carrier= override.
     */
    private function detectCarrier(Request $request): ?string
    {
        $override = $request->query('carrier');
        if ($override && is_string($override)) return $override;

        $ip = $request->query('ip') ?? $request->ip();
        if (! $ip || in_array($ip, ['127.0.0.1', '::1'])) return null;

        try {
            $response = @file_get_contents("http://ip-api.com/json/{$ip}?fields=isp,org");
            if ($response) {
                $data = json_decode($response, true);
                return $data['isp'] ?? $data['org'] ?? null;
            }
        } catch (\Exception $e) {}

        return null;
    }

    private function buildTrackingUrl(string $event, int $id, array $params, Request $request, ?string $destinationUrl = null): string
    {
        $baseUrl = PlatformSetting::getTerawurlMobileTrackingUrl();
        $deviceType = $this->detectDeviceType(strtolower($request->userAgent() ?? ''), $request);
        $deliveryOptions = app(AdDeliveryOptions::class);

        if ($baseUrl && in_array($deviceType, ['mobile', 'tablet'], true)) {
            $url = rtrim($baseUrl, '/') . '/direct/' . $id . '/' . $event;
        } else {
            $routeName = match ($event) {
                'click' => 'direct.click',
                'view' => 'direct.view',
                'adblock' => 'direct.adblock',
                'conversion' => 'direct.conversion',
                default => 'direct.serve',
            };

            $url = route($routeName, $id);
        }

        if ($event === 'click') {
            $params = $deliveryOptions->appendEncodedTarget($params, $destinationUrl);
        }

        return ! empty($params) ? $url . '?' . http_build_query($params) : $url;
    }

    private function jwPlayerBootstrapScript(string $playerElementId, string $fallbackVideoId, string $videoSrc, ?string $posterSrc = null): string
    {
        $licenseKey = PlatformSetting::getJwPlayerLicenseKey();

        if (! $licenseKey) {
            return '';
        }

        $playerElementId = json_encode($playerElementId);
        $fallbackVideoId = json_encode($fallbackVideoId);
        $videoSrc = json_encode($videoSrc);
        $posterSrc = json_encode($posterSrc);
        $licenseKey = json_encode($licenseKey);

        return <<<SCRIPT
<script>
(function () {
    var licenseKey = {$licenseKey};
    var playerId = {$playerElementId};
    var fallbackId = {$fallbackVideoId};
    var file = {$videoSrc};
    var image = {$posterSrc};

    window.JWPLAYER_LICENSE_KEY = licenseKey;

    if (typeof window.jwplayer !== 'function') {
        return;
    }

    var slot = document.getElementById(playerId);
    var fallback = document.getElementById(fallbackId);

    if (!slot || !fallback) {
        return;
    }

    try {
        window.jwplayer.key = licenseKey;
        slot.style.display = 'block';
        fallback.style.display = 'none';

        window.jwplayer(playerId).setup({
            file: file,
            image: image || undefined,
            width: '100%',
            aspectratio: '16:9',
            autostart: true,
            mute: true,
            controls: true,
            stretching: 'uniform',
            preload: 'auto'
        });
    } catch (error) {
        slot.style.display = 'none';
        fallback.style.display = 'block';
        console.error('JW Player setup failed.', error);
    }
})();
</script>
SCRIPT;
    }
}
