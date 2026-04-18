<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Throwable;

class PlatformMemcache
{
    public function remember(string $segment, array $context, int $seconds, callable $callback, bool $enabled): mixed
    {
        if (! $enabled) {
            return $callback();
        }

        $key = $this->cacheKey($segment, $context);

        try {
            return Cache::remember($key, $seconds, $callback);
        } catch (Throwable) {
            return $callback();
        }
    }

    public function has(string $segment, array $context): bool
    {
        try {
            return Cache::has($this->cacheKey($segment, $context));
        } catch (Throwable) {
            return false;
        }
    }

    public function bumpVersion(string $segment): void
    {
        $versionKey = $this->versionKey($segment);

        try {
            $current = (int) Cache::get($versionKey, 1);
            Cache::forever($versionKey, $current + 1);
        } catch (Throwable) {
            // Ignore cache-store failures and keep the app path working.
        }
    }

    public function cacheKey(string $segment, array $context): string
    {
        $version = 1;

        try {
            $version = (int) Cache::get($this->versionKey($segment), 1);
        } catch (Throwable) {
            $version = 1;
        }

        return 'platform_memcache:' . $segment . ':v' . $version . ':' . sha1(json_encode($context));
    }

    private function versionKey(string $segment): string
    {
        return 'platform_memcache_version:' . $segment;
    }
}
