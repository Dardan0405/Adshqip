<?php

namespace App\Support;

class UserAgentDetails
{
    public static function parse(?string $userAgent): array
    {
        $userAgent = trim((string) $userAgent);

        return [
            'browser' => self::detectBrowser($userAgent),
            'os' => self::detectOs($userAgent),
        ];
    }

    private static function detectBrowser(string $userAgent): string
    {
        if ($userAgent === '') {
            return 'Unknown';
        }

        $browsers = [
            'Edg/' => 'Edge',
            'OPR/' => 'Opera',
            'Opera' => 'Opera',
            'Chrome/' => 'Chrome',
            'Firefox/' => 'Firefox',
            'Safari/' => str_contains($userAgent, 'Chrome/') ? null : 'Safari',
            'MSIE' => 'Internet Explorer',
            'Trident/' => 'Internet Explorer',
        ];

        foreach ($browsers as $needle => $name) {
            if ($name !== null && str_contains($userAgent, $needle)) {
                return $name;
            }
        }

        return 'Unknown';
    }

    private static function detectOs(string $userAgent): string
    {
        if ($userAgent === '') {
            return 'Unknown';
        }

        $systems = [
            'Windows NT' => 'Windows',
            'Mac OS X' => 'macOS',
            'Android' => 'Android',
            'iPhone' => 'iOS',
            'iPad' => 'iOS',
            'Linux' => 'Linux',
            'CrOS' => 'Chrome OS',
        ];

        foreach ($systems as $needle => $name) {
            if (str_contains($userAgent, $needle)) {
                return $name;
            }
        }

        return 'Unknown';
    }
}
