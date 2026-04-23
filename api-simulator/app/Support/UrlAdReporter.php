<?php

namespace App\Support;

use App\Models\Ad;
use App\Models\DirectCampaign;
use App\Models\DirectCampaignCreative;
use App\Models\UrlAdReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class UrlAdReporter
{
    private ?bool $tableExists = null;

    public function __construct(private readonly AdDeliveryOptions $deliveryOptions)
    {
    }

    public function logRegularEvent(Request $request, Ad $ad, string $eventType, ?string $trackingUrl = null, ?string $destinationUrl = null): void
    {
        if (! $this->shouldLog()) {
            return;
        }

        UrlAdReport::create([
            'ad_id' => $ad->id,
            'campaign_id' => $ad->campaign_id,
            'zone_id' => $this->zoneIdFromRequest($request),
            'event_type' => $eventType,
            'request_url' => $request->fullUrl(),
            'referrer_url' => $this->referrerUrl($request),
            'tracking_url' => $trackingUrl,
            'destination_url' => $destinationUrl,
            'device_type' => $this->detectDeviceType($request),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'url_hidden' => $this->deliveryOptions->hideUrlEnabled(),
            'url_encoded' => $this->deliveryOptions->urlEncodingEnabled(),
            'created_at' => now(),
        ]);
    }

    public function logDirectEvent(Request $request, DirectCampaign $campaign, ?DirectCampaignCreative $creative, string $eventType, ?string $trackingUrl = null, ?string $destinationUrl = null): void
    {
        if (! $this->shouldLog()) {
            return;
        }

        UrlAdReport::create([
            'campaign_id' => null,
            'direct_campaign_id' => $campaign->id,
            'direct_creative_id' => $creative?->id,
            'zone_id' => $this->zoneIdFromRequest($request),
            'event_type' => $eventType,
            'request_url' => $request->fullUrl(),
            'referrer_url' => $this->referrerUrl($request),
            'tracking_url' => $trackingUrl,
            'destination_url' => $destinationUrl,
            'device_type' => $this->detectDeviceType($request),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'url_hidden' => $this->deliveryOptions->hideUrlEnabled(),
            'url_encoded' => $this->deliveryOptions->urlEncodingEnabled(),
            'created_at' => now(),
        ]);
    }

    private function shouldLog(): bool
    {
        if (! $this->deliveryOptions->reportUrlAdsEnabled()) {
            return false;
        }

        if ($this->tableExists === null) {
            $this->tableExists = Schema::hasTable('aq_url_ad_reports');
        }

        return $this->tableExists;
    }

    private function zoneIdFromRequest(Request $request): ?int
    {
        $zoneId = $request->query('zone_id');

        return is_numeric($zoneId) ? (int) $zoneId : null;
    }

    private function referrerUrl(Request $request): ?string
    {
        $referrer = (string) ($request->query('referrer') ?: $request->headers->get('referer', ''));
        $referrer = trim($referrer);

        return $referrer !== '' ? substr($referrer, 0, 2000) : null;
    }

    private function detectDeviceType(Request $request): string
    {
        if ($this->deliveryOptions->isMobileOrTabletRequest($request)) {
            $ua = strtolower($request->userAgent() ?? '');

            return str_contains($ua, 'tablet') || str_contains($ua, 'ipad') ? 'tablet' : 'mobile';
        }

        return 'desktop';
    }
}
