<?php

namespace App\Support;

use App\Models\PlatformSetting;
use App\Models\AntiFraudRule;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AntiFraudClickGuard
{
    public function inspect(Request $request, array $context = []): array
    {
        $zoneId = isset($context['zone_id']) && is_numeric($context['zone_id']) ? (int) $context['zone_id'] : null;
        $viewerId = substr((string) ($context['viewer_id'] ?? $request->cookie('aq_viewer_id', 'guest')), 0, 64);
        $ipAddress = (string) $request->ip();
        $userAgent = substr((string) ($request->userAgent() ?? ''), 0, 500);
        $countryCode = strtoupper((string) ($request->query('country') ?: $request->header('CF-IPCountry') ?: $request->header('X-Country-Code') ?: ''));

        $matchedRule = $this->matchingRule($ipAddress, $userAgent, $countryCode, $viewerId);
        if ($matchedRule) {
            $blocked = $matchedRule->action !== 'flag';
            $this->insertEvent($matchedRule, $zoneId, $viewerId, $ipAddress, $userAgent, $blocked, $this->reasonForRule($matchedRule));
            $matchedRule->markTriggered();

            if ($blocked) {
                $this->recordPublisherFraud($zoneId, 'Anti-fraud rule triggered: ' . $matchedRule->rule_name);
            }

            return [
                'allowed' => ! $blocked,
                'blocked' => $blocked,
                'reason' => $this->reasonForRule($matchedRule),
                'viewer_id' => $viewerId,
            ];
        }

        if (! PlatformSetting::getAntiFraudClicksEnabled() && ! AntiFraudRule::active()->where('rule_type', 'click_cap')->exists()) {
            return $this->allowed($viewerId);
        }

        $clickCapRule = AntiFraudRule::active()
            ->where('rule_type', 'click_cap')
            ->orderBy('threshold_value')
            ->first();

        $windowSeconds = (int) ($clickCapRule?->reset_period_seconds ?: PlatformSetting::getAntiFraudResetCounterSeconds());
        $thresholdClicks = (int) ($clickCapRule?->threshold_value ?: PlatformSetting::getAntiFraudThresholdClicks());
        $windowStart = now()->subSeconds($windowSeconds);

        $recentClicks = (int) DB::table('aq_clicks')
            ->where('ip_address', $ipAddress)
            ->where('created_at', '>=', $windowStart)
            ->count();

        $recentDirectClicks = 0;
        if (DB::getSchemaBuilder()->hasTable('aq_direct_campaign_clicks')) {
            $recentDirectClicks = (int) DB::table('aq_direct_campaign_clicks')
                ->where('ip_address', $ipAddress)
                ->where('created_at', '>=', $windowStart)
                ->count();
        }

        $blocked = ($recentClicks + $recentDirectClicks) >= $thresholdClicks;
        if ($clickCapRule && $clickCapRule->action === 'flag') {
            $blocked = false;
        }

        $this->insertEvent($clickCapRule, $zoneId, $viewerId, $ipAddress, $userAgent, $blocked, $blocked ? 'click_flood' : 'other', $context['ad_id'] ?? null);

        if ($blocked) {
            $this->recordPublisherFraud($zoneId);
            $clickCapRule?->markTriggered();
        }

        return [
            'allowed' => ! $blocked,
            'blocked' => $blocked,
            'reason' => $blocked ? 'click_flood' : null,
            'viewer_id' => $viewerId,
        ];
    }

    private function allowed(string $viewerId): array
    {
        return [
            'allowed' => true,
            'blocked' => false,
            'reason' => null,
            'viewer_id' => $viewerId,
        ];
    }

    private function matchingRule(string $ipAddress, string $userAgent, string $countryCode, string $viewerId): ?AntiFraudRule
    {
        foreach (AntiFraudRule::active()->whereIn('rule_type', ['ip_blacklist', 'ua_blacklist', 'geo_block', 'fingerprint'])->orderBy('id')->get() as $rule) {
            $values = $this->ruleValues($rule);

            if ($rule->rule_type === 'ip_blacklist' && $this->matchesIpRule($ipAddress, $values)) {
                return $rule;
            }

            if ($rule->rule_type === 'ua_blacklist' && $this->containsAny(strtolower($userAgent), $values)) {
                return $rule;
            }

            if ($rule->rule_type === 'geo_block' && $countryCode !== '' && in_array($countryCode, array_map('strtoupper', $values), true)) {
                return $rule;
            }

            if ($rule->rule_type === 'fingerprint' && $viewerId !== '' && in_array($viewerId, $values, true)) {
                return $rule;
            }
        }

        return null;
    }

    private function ruleValues(AntiFraudRule $rule): array
    {
        return collect(preg_split('/[\r\n,]+/', (string) $rule->match_value, -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($value) => trim($value))
            ->filter()
            ->values()
            ->all();
    }

    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($haystack, strtolower($needle))) {
                return true;
            }
        }

        return false;
    }

    private function matchesIpRule(string $ipAddress, array $values): bool
    {
        foreach ($values as $value) {
            if ($value === $ipAddress) {
                return true;
            }

            if (str_ends_with($value, '.*') && str_starts_with($ipAddress, rtrim($value, '*'))) {
                return true;
            }
        }

        return false;
    }

    private function reasonForRule(AntiFraudRule $rule): string
    {
        return match ($rule->rule_type) {
            'ip_blacklist' => 'datacenter_ip',
            'ua_blacklist' => 'bot',
            'geo_block' => 'geo_mismatch',
            'impression_cap' => 'impression_stacking',
            default => 'other',
        };
    }

    private function insertEvent(?AntiFraudRule $rule, ?int $zoneId, string $viewerId, string $ipAddress, string $userAgent, bool $blocked, string $reason, ?int $adId = null): void
    {
        DB::table('aq_fraud_events')->insert([
            'event_type' => 'click',
            'ad_id' => $adId,
            'zone_id' => $zoneId,
            'viewer_id' => $viewerId !== '' ? $viewerId : 'guest',
            'fingerprint_id' => null,
            'ip_address' => substr($ipAddress, 0, 45),
            'user_agent' => $userAgent,
            'fraud_reason' => $reason,
            'severity' => $rule?->severity ?? ($blocked ? 'high' : 'low'),
            'blocked' => $blocked,
            'created_at' => now(),
        ]);
    }

    private function recordPublisherFraud(?int $zoneId, string $reason = 'Anti-fraud click flood detected'): void
    {
        if (! $zoneId) {
            return;
        }

        $zone = Zone::with('site:id,publisher_id')->find($zoneId);
        $publisherId = $zone?->site?->publisher_id;

        if (! $publisherId) {
            return;
        }

        $existingRecord = DB::table('aq_publisher_fraud_records')
            ->where('publisher_id', $publisherId)
            ->where('record_type', 'fraud')
            ->whereNull('resolved_at')
            ->where('reason', $reason)
            ->orderByDesc('id')
            ->first();

        if ($existingRecord) {
            DB::table('aq_publisher_fraud_records')
                ->where('id', $existingRecord->id)
                ->update([
                    'flagged_clicks' => (int) $existingRecord->flagged_clicks + 1,
                ]);

            return;
        }

        DB::table('aq_publisher_fraud_records')->insert([
            'publisher_id' => $publisherId,
            'record_type' => 'fraud',
            'reason' => $reason,
            'flagged_impressions' => 0,
            'flagged_clicks' => 1,
            'action_taken' => 'warning',
            'created_at' => now(),
            'resolved_at' => null,
        ]);
    }
}
