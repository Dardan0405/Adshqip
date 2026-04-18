<?php

namespace App\Support;

use App\Models\PlatformSetting;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AntiFraudClickGuard
{
    public function inspect(Request $request, array $context = []): array
    {
        $enabled = PlatformSetting::getAntiFraudClicksEnabled();
        $zoneId = isset($context['zone_id']) && is_numeric($context['zone_id']) ? (int) $context['zone_id'] : null;
        $viewerId = substr((string) ($context['viewer_id'] ?? $request->cookie('aq_viewer_id', 'guest')), 0, 64);
        $ipAddress = (string) $request->ip();
        $userAgent = substr((string) ($request->userAgent() ?? ''), 0, 500);

        if (! $enabled) {
            return [
                'allowed' => true,
                'blocked' => false,
                'reason' => null,
                'viewer_id' => $viewerId,
            ];
        }

        $windowSeconds = PlatformSetting::getAntiFraudResetCounterSeconds();
        $thresholdClicks = PlatformSetting::getAntiFraudThresholdClicks();
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

        DB::table('aq_fraud_events')->insert([
            'event_type' => 'click',
            'ad_id' => $context['ad_id'] ?? null,
            'zone_id' => $zoneId,
            'viewer_id' => $viewerId !== '' ? $viewerId : 'guest',
            'fingerprint_id' => null,
            'ip_address' => substr($ipAddress, 0, 45),
            'user_agent' => $userAgent,
            'fraud_reason' => $blocked ? 'click_flood' : 'other',
            'severity' => $blocked ? 'high' : 'low',
            'blocked' => $blocked,
            'created_at' => now(),
        ]);

        if ($blocked) {
            $this->recordPublisherFraud($zoneId);
        }

        return [
            'allowed' => ! $blocked,
            'blocked' => $blocked,
            'reason' => $blocked ? 'click_flood' : null,
            'viewer_id' => $viewerId,
        ];
    }

    private function recordPublisherFraud(?int $zoneId): void
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
            ->where('reason', 'Anti-fraud click flood detected')
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
            'reason' => 'Anti-fraud click flood detected',
            'flagged_impressions' => 0,
            'flagged_clicks' => 1,
            'action_taken' => 'warning',
            'created_at' => now(),
            'resolved_at' => null,
        ]);
    }
}
