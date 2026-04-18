<?php

namespace App\Support;

use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class AdDeliveryOptions
{
    public function videoAdsEnabled(): bool
    {
        return (bool) PlatformSetting::getValue('video_ads_enabled', true);
    }

    public function mobileAdsEnabled(): bool
    {
        return (bool) PlatformSetting::getValue('mobile_ads_enabled', true);
    }

    public function bannerAutoloadEnabled(): bool
    {
        return (bool) PlatformSetting::getValue('banner_autoload_enabled', true);
    }

    public function hideUrlEnabled(): bool
    {
        return (bool) PlatformSetting::getValue('hide_url_enabled', false);
    }

    public function encryptionEnabled(): bool
    {
        return (bool) PlatformSetting::getValue('encryption_enabled', true);
    }

    public function urlEncodingEnabled(): bool
    {
        return $this->encryptionEnabled()
            && (bool) PlatformSetting::getValue('url_encoding_enabled', false);
    }

    public function reportUrlAdsEnabled(): bool
    {
        return (bool) PlatformSetting::getValue('report_url_ads_enabled', false);
    }

    public function isMobileOrTabletRequest(Request $request): bool
    {
        $override = strtolower((string) $request->query('device', ''));
        if (in_array($override, ['mobile', 'tablet'], true)) {
            return true;
        }

        $ua = strtolower($request->userAgent() ?? '');

        return str_contains($ua, 'mobile')
            || str_contains($ua, 'android')
            || str_contains($ua, 'tablet')
            || str_contains($ua, 'ipad');
    }

    public function visibleUrl(?string $displayUrl, ?string $destinationUrl): string
    {
        if ($this->hideUrlEnabled()) {
            return '';
        }

        $candidate = trim((string) ($displayUrl ?: ''));
        if ($candidate !== '') {
            return $candidate;
        }

        return (string) (parse_url((string) $destinationUrl, PHP_URL_HOST) ?: '');
    }

    public function appendEncodedTarget(array $params, ?string $destinationUrl): array
    {
        if (! $this->urlEncodingEnabled()) {
            return $params;
        }

        $destinationUrl = trim((string) $destinationUrl);
        if ($destinationUrl === '') {
            return $params;
        }

        $params['target'] = Crypt::encryptString($destinationUrl);

        return $params;
    }

    public function resolveDestinationUrl(?string $encodedTarget, ?string $fallbackDestinationUrl): ?string
    {
        $encodedTarget = trim((string) $encodedTarget);
        if ($encodedTarget !== '') {
            try {
                return Crypt::decryptString($encodedTarget);
            } catch (\Throwable) {
                // Ignore invalid payloads and fall back to the saved destination.
            }
        }

        $fallbackDestinationUrl = trim((string) $fallbackDestinationUrl);

        return $fallbackDestinationUrl !== '' ? $fallbackDestinationUrl : null;
    }
}
