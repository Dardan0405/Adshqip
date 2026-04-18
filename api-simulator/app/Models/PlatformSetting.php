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

    public const CREATIVE_APPROVAL_MANUAL = 'manual_review';
    public const CREATIVE_APPROVAL_AUTO = 'auto_approve';
    public const ADVERTISER_APPROVAL_EMAIL_VERIFICATION = 'email_verification';
    public const ADVERTISER_APPROVAL_ADMIN = 'approve_by_admin';
    public const PUBLISHER_APPROVAL_EMAIL_VERIFICATION = 'email_verification';
    public const PUBLISHER_APPROVAL_ADMIN = 'approve_by_admin';
    public const MESSAGE_DELIVERY_DEFAULT = 'default';
    public const MESSAGE_DELIVERY_SEND_MESSAGE = 'send_message';
    public const MESSAGE_DELIVERY_SEND_EMAIL = 'send_email';

    public const ADVERTISER_PAYMENT_WIRE_TRANSFER = 'wire_transfer';
    public const ADVERTISER_PAYMENT_PAYPAL = 'paypal';
    public const ADVERTISER_PAYMENT_BITCOIN = 'bitcoin';
    public const ADVERTISER_PAYMENT_STRIPE = 'stripe';
    public const ADVERTISER_PAYMENT_AUTHORIZE = 'authorize';
    public const ADVERTISER_PAYMENT_CRYPTO = self::ADVERTISER_PAYMENT_BITCOIN;
    public const ADVERTISER_PAYMENT_CREDIT_CARD = self::ADVERTISER_PAYMENT_STRIPE;

    public const CAMPAIGN_CREATIVE_TYPE_IMAGE = 'image';
    public const CAMPAIGN_CREATIVE_TYPE_HTML = 'html';
    public const CAMPAIGN_CREATIVE_TYPE_VIDEO = 'video';
    public const CAMPAIGN_CREATIVE_TYPE_TEXT = 'text';
    public const CAMPAIGN_CREATIVE_TYPE_RICH_MEDIA = 'rich_media';
    public const CAMPAIGN_CREATIVE_TYPE_NATIVE = 'native';
    public const CAMPAIGN_CREATIVE_TYPE_VAST = 'vast';

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
    public static function setValue(string $key, mixed $value, string $type = 'string', string $category = 'general', ?string $description = null, ?int $updatedBy = null): void
    {
        $storeValue = is_array($value) ? json_encode($value) : (string) $value;

        static::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => $storeValue,
                'setting_type' => $type,
                'category' => $category,
                'description' => $description,
                'updated_by' => $updatedBy,
            ]
        );
    }

    public static function getCreativeMinimumImpressionsPerDay(): int
    {
        return max(0, (int) static::getValue('creative_minimum_impressions_per_day', 1000));
    }

    public static function getCreativeApprovalType(): string
    {
        $value = (string) static::getValue('creative_approval_type', self::CREATIVE_APPROVAL_MANUAL);

        return in_array($value, [self::CREATIVE_APPROVAL_MANUAL, self::CREATIVE_APPROVAL_AUTO], true)
            ? $value
            : self::CREATIVE_APPROVAL_MANUAL;
    }

    public static function getCreativeInitialStatus(): string
    {
        return static::getCreativeApprovalType() === self::CREATIVE_APPROVAL_AUTO ? 'active' : 'pending_review';
    }

    public static function getAntiFraudClicksEnabled(): bool
    {
        return (bool) static::getValue('anti_fraud_clicks_enabled', false);
    }

    public static function getAntiFraudResetCounterSeconds(): int
    {
        return max(1, (int) static::getValue('anti_fraud_reset_counter_seconds', 300));
    }

    public static function getAntiFraudThresholdClicks(): int
    {
        return max(1, (int) static::getValue('anti_fraud_threshold_clicks', 10));
    }

    public static function getTerawurlMobileTrackingUrl(): ?string
    {
        $url = static::normalizeServeUrl(static::getValue('terawurl_mobile_tracking_url'));
        if ($url) {
            return $url;
        }

        $legacyPath = static::getValue('terawurl_mobile_tracking_path');
        if (! is_string($legacyPath)) {
            return null;
        }

        $normalized = trim($legacyPath, " \t\n\r\0\x0B/");

        return $normalized !== '' ? url('/t/' . $normalized) : null;
    }

    public static function getJwPlayerLicenseKey(): ?string
    {
        $key = static::getValue('jw_player_license_key');

        return is_string($key) && trim($key) !== '' ? trim($key) : null;
    }

    public static function getAdvertiserApprovalType(): string
    {
        $value = (string) static::getValue('advertiser_approval_type', self::ADVERTISER_APPROVAL_EMAIL_VERIFICATION);

        return in_array($value, [
            self::ADVERTISER_APPROVAL_EMAIL_VERIFICATION,
            self::ADVERTISER_APPROVAL_ADMIN,
        ], true) ? $value : self::ADVERTISER_APPROVAL_EMAIL_VERIFICATION;
    }

    public static function getAdvertiserPaymentType(): string
    {
        $value = (string) static::getValue('advertiser_payment_type', self::ADVERTISER_PAYMENT_WIRE_TRANSFER);

        $legacyMap = [
            'crypto' => self::ADVERTISER_PAYMENT_BITCOIN,
            'credit_card' => self::ADVERTISER_PAYMENT_STRIPE,
        ];

        $value = $legacyMap[$value] ?? $value;

        return array_key_exists($value, static::getAdvertiserPaymentTypes())
            ? $value
            : self::ADVERTISER_PAYMENT_WIRE_TRANSFER;
    }

    public static function getAdvertiserPaymentTypes(): array
    {
        return [
            self::ADVERTISER_PAYMENT_WIRE_TRANSFER => 'Bank Wire',
            self::ADVERTISER_PAYMENT_PAYPAL => 'PayPal',
            self::ADVERTISER_PAYMENT_BITCOIN => 'Bitcoin',
            self::ADVERTISER_PAYMENT_STRIPE => 'Stripe',
            self::ADVERTISER_PAYMENT_AUTHORIZE => 'Authorize.net',
        ];
    }

    public static function getPaymentSettingsDetails(): array
    {
        $settings = static::getValue('admin_payment_settings_details', []);

        if (! is_array($settings)) {
            $settings = [];
        }

        return array_replace_recursive(static::defaultPaymentSettingsDetails(), $settings);
    }

    public static function getPaymentSettingsFor(?string $paymentType = null): array
    {
        $paymentType = $paymentType ?: static::getAdvertiserPaymentType();
        $details = static::getPaymentSettingsDetails();

        return $details[$paymentType] ?? [];
    }

    public static function defaultPaymentSettingsDetails(): array
    {
        return [
            self::ADVERTISER_PAYMENT_WIRE_TRANSFER => [
                'account_holder' => '',
                'bank_name' => '',
                'account_number' => '',
                'swift_code' => '',
                'bank_address' => '',
            ],
            self::ADVERTISER_PAYMENT_PAYPAL => [
                'paypal_email' => '',
                'merchant_id' => '',
                'instructions' => '',
            ],
            self::ADVERTISER_PAYMENT_BITCOIN => [
                'wallet_address' => '',
                'network' => '',
                'instructions' => '',
            ],
            self::ADVERTISER_PAYMENT_STRIPE => [
                'publishable_key' => '',
                'secret_key' => '',
                'webhook_secret' => '',
                'account_email' => '',
            ],
            self::ADVERTISER_PAYMENT_AUTHORIZE => [
                'login_id' => '',
                'transaction_key' => '',
                'signature_key' => '',
                'client_key' => '',
            ],
        ];
    }

    public static function getCampaignMinimumBudget(): float
    {
        return max(0, (float) static::getValue('campaign_minimum_budget', 10));
    }

    public static function getCampaignMinimumBidRate(): float
    {
        return max(0, (float) static::getValue('campaign_minimum_bid_rate', 0.01));
    }

    public static function getCampaignCreativeType(): string
    {
        $value = (string) static::getValue('campaign_creative_type', self::CAMPAIGN_CREATIVE_TYPE_IMAGE);

        $legacyMap = [
            'banner' => self::CAMPAIGN_CREATIVE_TYPE_IMAGE,
            'popup' => self::CAMPAIGN_CREATIVE_TYPE_RICH_MEDIA,
            'interstitial' => self::CAMPAIGN_CREATIVE_TYPE_HTML,
        ];

        $value = $legacyMap[$value] ?? $value;

        return in_array($value, [
            self::CAMPAIGN_CREATIVE_TYPE_IMAGE,
            self::CAMPAIGN_CREATIVE_TYPE_HTML,
            self::CAMPAIGN_CREATIVE_TYPE_VIDEO,
            self::CAMPAIGN_CREATIVE_TYPE_TEXT,
            self::CAMPAIGN_CREATIVE_TYPE_RICH_MEDIA,
            self::CAMPAIGN_CREATIVE_TYPE_NATIVE,
            self::CAMPAIGN_CREATIVE_TYPE_VAST,
        ], true) ? $value : self::CAMPAIGN_CREATIVE_TYPE_IMAGE;
    }

    public static function getCampaignCreativeTypes(): array
    {
        return [
            self::CAMPAIGN_CREATIVE_TYPE_IMAGE => 'Image',
            self::CAMPAIGN_CREATIVE_TYPE_HTML => 'HTML',
            self::CAMPAIGN_CREATIVE_TYPE_VIDEO => 'Video',
            self::CAMPAIGN_CREATIVE_TYPE_TEXT => 'Text',
            self::CAMPAIGN_CREATIVE_TYPE_RICH_MEDIA => 'Rich Media',
            self::CAMPAIGN_CREATIVE_TYPE_NATIVE => 'Native',
            self::CAMPAIGN_CREATIVE_TYPE_VAST => 'VAST',
        ];
    }

    public static function getPublisherFloorPriceEnabled(): bool
    {
        return (bool) static::getValue('publisher_floor_price_enabled', false);
    }

    public static function getPublisherApprovalType(): string
    {
        $value = (string) static::getValue('publisher_approval_type', self::PUBLISHER_APPROVAL_EMAIL_VERIFICATION);

        return in_array($value, [
            self::PUBLISHER_APPROVAL_EMAIL_VERIFICATION,
            self::PUBLISHER_APPROVAL_ADMIN,
        ], true) ? $value : self::PUBLISHER_APPROVAL_EMAIL_VERIFICATION;
    }

    public static function getMessageDeliveryMode(): string
    {
        $value = (string) static::getValue('message_delivery_mode', self::MESSAGE_DELIVERY_DEFAULT);

        return in_array($value, [
            self::MESSAGE_DELIVERY_DEFAULT,
            self::MESSAGE_DELIVERY_SEND_MESSAGE,
            self::MESSAGE_DELIVERY_SEND_EMAIL,
        ], true) ? $value : self::MESSAGE_DELIVERY_DEFAULT;
    }

    public static function getAdvertiserMessagesEnabled(): bool
    {
        return (bool) static::getValue('advertiser_messages_enabled', true);
    }

    public static function getPublisherMessagesEnabled(): bool
    {
        return (bool) static::getValue('publisher_messages_enabled', true);
    }

    public static function messagesEnabledForRole(?string $role): bool
    {
        return match ($role) {
            'advertiser' => static::getAdvertiserMessagesEnabled(),
            'publisher' => static::getPublisherMessagesEnabled(),
            default => true,
        };
    }

    public static function getPublisherMinimumFloorPricePercent(): float
    {
        return max(0, (float) static::getValue('publisher_minimum_floor_price_percent', 0));
    }

    public static function getPublisherMinimumFloorPriceAmount(): float
    {
        return round(static::getPublisherMinimumFloorPricePercent() / 100, 4);
    }

    public static function getPublisherMinimumAmountForInvoiceGeneration(): float
    {
        return max(0, (float) static::getValue('publisher_minimum_amount_for_invoice_generation', 50));
    }

    public static function getPublisherAutoInvoiceEnabled(): bool
    {
        return (bool) static::getValue('publisher_auto_invoice_enabled', false);
    }

    public static function getPublisherMinimumPaymentAmount(): float
    {
        return max(0, (float) static::getValue('publisher_minimum_payment_amount', 100));
    }

    public static function getVideoAdsEnabled(): bool
    {
        return (bool) static::getValue('video_ads_enabled', true);
    }

    public static function getMobileAdsEnabled(): bool
    {
        return (bool) static::getValue('mobile_ads_enabled', true);
    }

    public static function getBannerAutoloadEnabled(): bool
    {
        return (bool) static::getValue('banner_autoload_enabled', true);
    }

    public static function getReviveMemcacheEnabled(): bool
    {
        return (bool) static::getValue('revive_memcache_enabled', false);
    }

    public static function getAdshqipMemcacheEnabled(): bool
    {
        return (bool) static::getValue('adshqip_memcache_enabled', false);
    }

    public static function getHideUrlEnabled(): bool
    {
        return (bool) static::getValue('hide_url_enabled', false);
    }

    public static function getEncryptionEnabled(): bool
    {
        return (bool) static::getValue('encryption_enabled', true);
    }

    public static function getUrlEncodingEnabled(): bool
    {
        return (bool) static::getValue('url_encoding_enabled', false);
    }

    public static function getReportUrlAdsEnabled(): bool
    {
        return (bool) static::getValue('report_url_ads_enabled', false);
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
