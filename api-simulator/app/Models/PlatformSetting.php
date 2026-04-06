<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    protected $table = 'aq_platform_settings';

    public $timestamps = false;

    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_type',
        'category',
        'description',
        'updated_by',
    ];

    /**
     * Get a setting value by key, with optional default.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('setting_key', $key)->first();
        if (!$setting) {
            return $default;
        }

        return match ($setting->setting_type) {
            'integer' => (int) $setting->setting_value,
            'boolean' => filter_var($setting->setting_value, FILTER_VALIDATE_BOOLEAN),
            'decimal' => (float) $setting->setting_value,
            'json' => json_decode($setting->setting_value, true),
            default => $setting->setting_value,
        };
    }

    /**
     * Set a setting value by key.
     */
    public static function setValue(string $key, mixed $value, string $type = 'string', string $category = 'general', ?string $description = null): void
    {
        $storeValue = is_array($value) ? json_encode($value) : (string) $value;

        static::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => $storeValue,
                'setting_type' => $type,
                'category' => $category,
                'description' => $description,
            ]
        );
    }

    /**
     * Get the active serving domain (from DB, fallback to env, fallback to APP_URL).
     */
    public static function getServeDomain(): string
    {
        $domain = static::normalizeServeUrl(static::getValue('ad_serve_domain'));
        if ($domain) {
            return $domain;
        }

        return static::normalizeServeUrl(config('app.ad_serve_domain', config('app.url')));
    }

    /**
     * Get the serve path prefix (obfuscated, rotatable).
     */
    public static function getServePath(): string
    {
        $path = static::getValue('ad_serve_path');
        if ($path) {
            return '/' . ltrim($path, '/');
        }

        return config('app.ad_serve_path', '/d');
    }

    public static function getServeDomains(): array
    {
        $domains = static::getValue('ad_serve_domains', []);
        if (! is_array($domains)) {
            $domains = [];
        }

        $normalized = array_values(array_filter(array_map(
            fn ($domain) => static::normalizeServeUrl(is_string($domain) ? $domain : null),
            $domains
        )));

        $activeDomain = static::getServeDomain();
        if ($activeDomain && ! in_array($activeDomain, $normalized, true)) {
            array_unshift($normalized, $activeDomain);
        }

        return array_values(array_unique($normalized));
    }

    public static function setServeDomains(array $domains): void
    {
        $normalized = array_values(array_unique(array_filter(array_map(
            fn ($domain) => static::normalizeServeUrl(is_string($domain) ? $domain : null),
            $domains
        ))));

        static::setValue(
            'ad_serve_domains',
            $normalized,
            'json',
            'serving',
            'Available anti-block serving domains'
        );
    }

    public static function normalizeServeUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $url = trim($url);
        if ($url === '') {
            return null;
        }

        if (! preg_match('#^https?://#i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }

        return rtrim($url, '/');
    }
}
