<?php

namespace App\Support;

use App\Models\AdminSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionTracker
{
    public function trackLogin(Request $request, User $user): void
    {
        $sessionId = (string) $request->session()->getId();
        if ($sessionId === '') {
            return;
        }

        $agent = UserAgentDetails::parse($request->userAgent());
        $deviceType = $this->detectDeviceType((string) $request->userAgent());

        AdminSession::updateOrCreate(
            ['token' => $sessionId],
            [
                'user_id' => $user->id,
                'ip_address' => (string) $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
                'browser' => $agent['browser'] ?? 'Unknown',
                'os' => $agent['os'] ?? 'Unknown',
                'device_type' => $deviceType,
                'expires_at' => now()->addMinutes((int) config('session.lifetime', 120)),
            ]
        );
    }

    public function revokeCurrent(Request $request): void
    {
        $sessionId = (string) $request->session()->getId();
        if ($sessionId === '') {
            return;
        }

        AdminSession::where('token', $sessionId)->delete();

        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('id', $sessionId)->delete();
        }
    }

    public function revokeToken(string $token): void
    {
        AdminSession::where('token', $token)->delete();

        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('id', $token)->delete();
        }
    }

    public function clearExpired(): int
    {
        return AdminSession::where('expires_at', '<', now())->delete();
    }

    private function detectDeviceType(string $userAgent): ?string
    {
        $agent = strtolower($userAgent);

        return match (true) {
            str_contains($agent, 'ipad'),
            str_contains($agent, 'tablet') => 'tablet',
            str_contains($agent, 'mobile'),
            str_contains($agent, 'iphone'),
            str_contains($agent, 'android') => 'mobile',
            $agent !== '' => 'desktop',
            default => null,
        };
    }
}
