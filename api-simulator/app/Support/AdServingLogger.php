<?php

namespace App\Support;

use App\Models\AdServingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdServingLogger
{
    public function log(Request $request, array $context): void
    {
        try {
            if (! Schema::hasTable('aq_ad_serving_logs')) {
                return;
            }

            AdServingLog::create([
                'delivery_type' => $context['delivery_type'] ?? 'network',
                'event_type' => $context['event_type'] ?? 'serve',
                'status' => $context['status'] ?? 'served',
                'campaign_id' => $context['campaign_id'] ?? null,
                'ad_id' => $context['ad_id'] ?? null,
                'direct_campaign_id' => $context['direct_campaign_id'] ?? null,
                'direct_creative_id' => $context['direct_creative_id'] ?? null,
                'zone_id' => $context['zone_id'] ?? null,
                'site_id' => $context['site_id'] ?? null,
                'publisher_id' => $context['publisher_id'] ?? null,
                'advertiser_id' => $context['advertiser_id'] ?? null,
                'request_id' => $context['request_id'] ?? $request->headers->get('X-Request-Id') ?? (string) Str::uuid(),
                'viewer_id' => $context['viewer_id'] ?? $request->cookie('aq_viewer_id'),
                'click_id' => $context['click_id'] ?? $request->query('click_id'),
                'country_code' => $context['country_code'] ?? $this->country($request),
                'device_type' => $context['device_type'] ?? $this->device($request),
                'pricing_model' => $context['pricing_model'] ?? null,
                'bid_amount' => $context['bid_amount'] ?? null,
                'revenue' => $context['revenue'] ?? 0,
                'publisher_earnings' => $context['publisher_earnings'] ?? 0,
                'ip_address' => $request->ip(),
                'referer' => Str::limit((string) $request->headers->get('referer'), 500, ''),
                'request_url' => Str::limit($request->fullUrl(), 1000, ''),
                'destination_url' => isset($context['destination_url']) ? Str::limit((string) $context['destination_url'], 1000, '') : null,
                'user_agent' => Str::limit((string) $request->userAgent(), 2000, ''),
                'meta' => $context['meta'] ?? null,
            ]);
        } catch (\Throwable) {
            // Serving should never fail because logging failed.
        }
    }

    private function country(Request $request): ?string
    {
        $override = strtoupper((string) $request->query('country', ''));
        if (preg_match('/^[A-Z]{2}$/', $override)) {
            return $override;
        }

        $headerCountry = strtoupper((string) ($request->header('CF-IPCountry') ?? $request->header('X-Country-Code') ?? ''));
        return preg_match('/^[A-Z]{2}$/', $headerCountry) ? $headerCountry : null;
    }

    private function device(Request $request): string
    {
        $override = strtolower((string) $request->query('device', ''));
        if (in_array($override, ['desktop', 'mobile', 'tablet'], true)) {
            return $override;
        }

        $ua = strtolower((string) $request->userAgent());
        if (str_contains($ua, 'ipad') || str_contains($ua, 'tablet')) {
            return 'tablet';
        }

        if (str_contains($ua, 'mobile') || str_contains($ua, 'android')) {
            return 'mobile';
        }

        return 'desktop';
    }
}
