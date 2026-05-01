<?php

namespace App\Support;

use App\Models\AdvertiserAudience;
use App\Models\Campaign;
use Illuminate\Http\Request;

class AudienceTargetingMatcher
{
    public function passes(Campaign $campaign, Request $request): bool
    {
        $result = $this->evaluate($campaign, $request);

        return $result['passes'];
    }

    public function evaluate(Campaign $campaign, Request $request): array
    {
        if (($campaign->audience_targeting_mode ?? 'none') === 'none') {
            return ['passes' => true, 'reason' => null, 'matched' => []];
        }

        $audiences = $campaign->audiences()
            ->where('aq_advertiser_audiences.is_deleted', false)
            ->where('aq_advertiser_audiences.status', 'active')
            ->get();

        if ($audiences->isEmpty()) {
            return ['passes' => true, 'reason' => null, 'matched' => []];
        }

        $matchedInclude = [];
        $matchedExclude = [];
        $hasInclude = false;

        foreach ($audiences as $audience) {
            $mode = $audience->pivot->mode ?? 'include';

            if ($mode === 'include') {
                $hasInclude = true;
            }

            if (! $this->audienceMatches($audience, $request)) {
                continue;
            }

            if ($mode === 'exclude') {
                $matchedExclude[] = $audience->name;
            } else {
                $matchedInclude[] = $audience->name;
            }
        }

        if ($matchedExclude !== []) {
            return [
                'passes' => false,
                'reason' => 'audience_excluded',
                'matched' => $matchedExclude,
            ];
        }

        if ($hasInclude && $matchedInclude === []) {
            return [
                'passes' => false,
                'reason' => 'audience_not_matched',
                'matched' => [],
            ];
        }

        return [
            'passes' => true,
            'reason' => null,
            'matched' => $matchedInclude,
        ];
    }

    private function audienceMatches(AdvertiserAudience $audience, Request $request): bool
    {
        if ($this->explicitAudienceMatch($audience, $request)) {
            return true;
        }

        if (! $this->matchesCountries($audience->countries ?? [], $request)) {
            return false;
        }

        if (! $this->matchesDevices($audience->devices ?? [], $request)) {
            return false;
        }

        if (! $this->matchesTerms($audience->interests ?? [], $this->requestTerms($request, ['interest', 'interests', 'category', 'categories']))) {
            return false;
        }

        if (! $this->matchesTerms($audience->keywords ?? [], $this->requestTerms($request, ['keyword', 'keywords', 'kw', 'page_keywords', 'meta_keywords']))) {
            return false;
        }

        return true;
    }

    private function explicitAudienceMatch(AdvertiserAudience $audience, Request $request): bool
    {
        $values = $this->requestTerms($request, ['audience', 'audience_id', 'audiences', 'segment', 'segments']);

        if ($values === []) {
            return false;
        }

        $tokens = [
            (string) $audience->id,
            strtolower((string) $audience->slug),
            strtolower((string) $audience->name),
        ];

        foreach ($values as $value) {
            if (in_array(strtolower($value), $tokens, true)) {
                return true;
            }
        }

        return false;
    }

    private function matchesCountries(array $countries, Request $request): bool
    {
        if ($countries === []) {
            return true;
        }

        $country = $this->detectCountry($request);

        return $country !== null && in_array(strtoupper($country), array_map('strtoupper', $countries), true);
    }

    private function matchesDevices(array $devices, Request $request): bool
    {
        if ($devices === []) {
            return true;
        }

        $device = $this->detectDevice($request);

        return in_array($device, array_map('strtolower', $devices), true);
    }

    private function matchesTerms(array $audienceTerms, array $requestTerms): bool
    {
        if ($audienceTerms === []) {
            return true;
        }

        if ($requestTerms === []) {
            return false;
        }

        foreach ($audienceTerms as $audienceTerm) {
            $needle = strtolower(trim((string) $audienceTerm));

            foreach ($requestTerms as $requestTerm) {
                $haystack = strtolower(trim($requestTerm));

                if ($needle !== '' && ($needle === $haystack || str_contains($haystack, $needle) || str_contains($needle, $haystack))) {
                    return true;
                }
            }
        }

        return false;
    }

    private function requestTerms(Request $request, array $keys): array
    {
        $values = [];

        foreach ($keys as $key) {
            $value = $request->query($key);

            if ($value === null || $value === '') {
                continue;
            }

            if (is_array($value)) {
                $values = array_merge($values, $value);
                continue;
            }

            $values = array_merge($values, preg_split('/[\s,;|]+/', (string) $value) ?: []);
        }

        return array_values(array_filter(array_map('trim', $values), fn ($value) => $value !== ''));
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

    private function detectDevice(Request $request): string
    {
        $override = strtolower((string) $request->query('device', ''));
        if (in_array($override, ['desktop', 'mobile', 'tablet', 'smart_tv'], true)) {
            return $override;
        }

        $ua = strtolower($request->userAgent() ?? '');

        if (str_contains($ua, 'smart-tv') || str_contains($ua, 'smarttv') || str_contains($ua, 'hbbtv')) {
            return 'smart_tv';
        }

        if (str_contains($ua, 'ipad') || str_contains($ua, 'tablet')) {
            return 'tablet';
        }

        if (str_contains($ua, 'mobile') || str_contains($ua, 'android')) {
            return 'mobile';
        }

        return 'desktop';
    }
}
