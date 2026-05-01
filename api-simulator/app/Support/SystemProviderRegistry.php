<?php

namespace App\Support;

use App\Models\PlatformSetting;
use App\Models\SystemProvider;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SystemProviderRegistry
{
    public function active(?string $type = null, ?string $slug = null): ?SystemProvider
    {
        return SystemProvider::query()
            ->active()
            ->when($type, fn ($query) => $query->where('provider_type', $type))
            ->when($slug, fn ($query) => $query->where('slug', $slug))
            ->orderBy('priority')
            ->orderBy('name')
            ->first();
    }

    public function activePayment(?string $paymentType = null): ?SystemProvider
    {
        $paymentType = $paymentType ?: PlatformSetting::getAdvertiserPaymentType();

        return SystemProvider::query()
            ->active()
            ->where('provider_type', 'payment')
            ->where('source_key', $paymentType)
            ->orderBy('priority')
            ->first();
    }

    public function activeTelegram(): ?SystemProvider
    {
        return SystemProvider::query()
            ->active()
            ->source('telegram_settings', 'telegram-bot')
            ->orderBy('priority')
            ->first();
    }

    public function webPush(): ?SystemProvider
    {
        return SystemProvider::query()
            ->active()
            ->source('webpush', 'vapid')
            ->orderBy('priority')
            ->first();
    }

    public function syncFromPlatformSettings(?int $updatedBy = null): array
    {
        $synced = [];
        $paymentType = PlatformSetting::getAdvertiserPaymentType();
        $paymentDetails = PlatformSetting::getPaymentSettingsFor($paymentType);

        if ($paymentType !== PlatformSetting::ADVERTISER_PAYMENT_WIRE_TRANSFER) {
            $synced[] = $this->syncPaymentProvider($paymentType, $paymentDetails, $updatedBy);
        }

        $telegram = $this->syncTelegramProvider($updatedBy);
        if ($telegram) {
            $synced[] = $telegram;
        }

        $webPush = $this->syncWebPushProvider($updatedBy);
        if ($webPush) {
            $synced[] = $webPush;
        }

        return $synced;
    }

    public function syncPaymentProvider(string $paymentType, array $details, ?int $updatedBy = null): SystemProvider
    {
        $labels = PlatformSetting::getAdvertiserPaymentTypes();
        $name = ($labels[$paymentType] ?? Str::headline($paymentType)) . ' Payments';
        $environment = $this->paymentEnvironment($paymentType, $details);
        $credentials = $this->paymentCredentials($paymentType, $details);

        return SystemProvider::updateOrCreate(
            ['source' => 'payment_settings', 'source_key' => $paymentType],
            [
                'name' => $name,
                'slug' => 'payment-' . Str::slug($paymentType),
                'provider_type' => 'payment',
                'environment' => $environment,
                'status' => 'active',
                'base_url' => $this->paymentBaseUrl($paymentType, $environment),
                'webhook_url' => $details['webhook_url'] ?? null,
                'auth_type' => $credentials['auth_type'],
                'api_key' => $credentials['api_key'],
                'api_secret' => $credentials['api_secret'],
                'priority' => 20,
                'timeout_seconds' => 10,
                'config' => [
                    'payment_type' => $paymentType,
                    'settings_source' => 'admin_payment_settings_details',
                    'updated_by' => $updatedBy,
                    'public' => $credentials['public'] ?? [],
                ],
                'notes' => 'Synced automatically from Admin Payment Settings.',
            ]
        );
    }

    public function syncTelegramProvider(?int $updatedBy = null): ?SystemProvider
    {
        $username = trim((string) PlatformSetting::getValue('telegram_bot_username', ''));
        $webhookUrl = trim((string) PlatformSetting::getValue('telegram_webhook_url', ''));
        $token = PlatformSetting::getValue('telegram_bot_token_encrypted');

        if ($username === '' && blank($token) && $webhookUrl === '') {
            return null;
        }

        return SystemProvider::updateOrCreate(
            ['source' => 'telegram_settings', 'source_key' => 'telegram-bot'],
            [
                'name' => $username ? 'Telegram Bot @' . ltrim($username, '@') : 'Telegram Bot',
                'slug' => 'telegram-bot',
                'provider_type' => 'messaging',
                'environment' => 'production',
                'status' => PlatformSetting::getValue('telegram_notifications_enabled', false) ? 'active' : 'inactive',
                'base_url' => 'https://api.telegram.org',
                'webhook_url' => $webhookUrl ?: null,
                'auth_type' => 'bearer_token',
                'api_key' => $username ?: null,
                'api_secret' => filled($token) ? $this->decryptSetting((string) $token) : null,
                'priority' => 30,
                'timeout_seconds' => 10,
                'config' => [
                    'bot_username' => $username,
                    'login_enabled' => (bool) PlatformSetting::getValue('telegram_login_enabled', false),
                    'notifications_enabled' => (bool) PlatformSetting::getValue('telegram_notifications_enabled', false),
                    'updated_by' => $updatedBy,
                ],
                'notes' => 'Synced automatically from Telegram Integration settings.',
            ]
        );
    }

    public function syncWebPushProvider(?int $updatedBy = null): ?SystemProvider
    {
        $publicKey = config('services.webpush.public_key', env('VAPID_PUBLIC_KEY'));
        $privateKey = config('services.webpush.private_key', env('VAPID_PRIVATE_KEY'));
        $subject = config('services.webpush.subject', env('VAPID_SUBJECT', 'mailto:admin@adshqip.com'));

        if (blank($publicKey) && blank($privateKey)) {
            return null;
        }

        return SystemProvider::updateOrCreate(
            ['source' => 'webpush', 'source_key' => 'vapid'],
            [
                'name' => 'Web Push VAPID',
                'slug' => 'web-push-vapid',
                'provider_type' => 'messaging',
                'environment' => 'production',
                'status' => 'active',
                'base_url' => null,
                'webhook_url' => null,
                'auth_type' => 'custom',
                'api_key' => $publicKey,
                'api_secret' => $privateKey,
                'priority' => 40,
                'timeout_seconds' => 10,
                'config' => [
                    'subject' => $subject,
                    'updated_by' => $updatedBy,
                ],
                'notes' => 'Synced automatically from VAPID configuration.',
            ]
        );
    }

    public function test(SystemProvider $provider): array
    {
        if (blank($provider->base_url)) {
            return [
                'status' => 'warning',
                'message' => 'No base URL configured. Provider settings are saved, but connectivity cannot be tested.',
            ];
        }

        try {
            $response = $this->http($provider)->get($provider->base_url);

            return [
                'status' => $response->successful() ? 'success' : 'error',
                'message' => 'HTTP ' . $response->status() . ' returned from ' . $provider->base_url,
                'response' => $response,
            ];
        } catch (\Throwable $exception) {
            return [
                'status' => 'error',
                'message' => Str::limit($exception->getMessage(), 500),
            ];
        }
    }

    public function http(SystemProvider $provider)
    {
        $pending = Http::timeout(max(1, min(60, $provider->timeout_seconds)))->acceptJson();

        if ($provider->auth_type === 'bearer_token' && filled($provider->api_secret)) {
            $pending = $pending->withToken($provider->api_secret);
        }

        if ($provider->auth_type === 'api_key' && filled($provider->api_key)) {
            $pending = $pending->withHeaders(['X-API-Key' => $provider->api_key]);
        }

        if ($provider->auth_type === 'basic' && filled($provider->api_key) && filled($provider->api_secret)) {
            $pending = $pending->withBasicAuth($provider->api_key, $provider->api_secret);
        }

        return $pending;
    }

    private function paymentEnvironment(string $paymentType, array $details): string
    {
        $mode = strtolower((string) ($details['mode'] ?? ''));

        return in_array($mode, ['sandbox', 'test'], true) ? 'sandbox' : 'production';
    }

    private function paymentBaseUrl(string $paymentType, string $environment): ?string
    {
        return match ($paymentType) {
            PlatformSetting::ADVERTISER_PAYMENT_PAYPAL => $environment === 'sandbox'
                ? 'https://api-m.sandbox.paypal.com'
                : 'https://api-m.paypal.com',
            PlatformSetting::ADVERTISER_PAYMENT_STRIPE => 'https://api.stripe.com',
            PlatformSetting::ADVERTISER_PAYMENT_BITCOIN => 'https://bitpay.com',
            PlatformSetting::ADVERTISER_PAYMENT_AUTHORIZE => $environment === 'sandbox'
                ? 'https://apitest.authorize.net'
                : 'https://api.authorize.net',
            default => null,
        };
    }

    private function paymentCredentials(string $paymentType, array $details): array
    {
        return match ($paymentType) {
            PlatformSetting::ADVERTISER_PAYMENT_PAYPAL => [
                'auth_type' => 'oauth2',
                'api_key' => $details['client_id'] ?? null,
                'api_secret' => $details['secret'] ?? null,
                'public' => ['merchant_id' => $details['merchant_id'] ?? null, 'paypal_email' => $details['paypal_email'] ?? null],
            ],
            PlatformSetting::ADVERTISER_PAYMENT_STRIPE => [
                'auth_type' => 'bearer_token',
                'api_key' => $details['publishable_key'] ?? null,
                'api_secret' => $details['secret_key'] ?? null,
                'public' => ['account_email' => $details['account_email'] ?? null, 'webhook_secret_saved' => filled($details['webhook_secret'] ?? null)],
            ],
            PlatformSetting::ADVERTISER_PAYMENT_BITCOIN => [
                'auth_type' => 'bearer_token',
                'api_key' => null,
                'api_secret' => $details['bitpay_api_token'] ?? null,
                'public' => ['network' => $details['network'] ?? null, 'wallet_address' => $details['wallet_address'] ?? null],
            ],
            PlatformSetting::ADVERTISER_PAYMENT_AUTHORIZE => [
                'auth_type' => 'api_key',
                'api_key' => $details['login_id'] ?? null,
                'api_secret' => $details['transaction_key'] ?? null,
                'public' => ['client_key' => $details['client_key'] ?? null, 'signature_key_saved' => filled($details['signature_key'] ?? null)],
            ],
            default => [
                'auth_type' => 'none',
                'api_key' => null,
                'api_secret' => null,
                'public' => [],
            ],
        };
    }

    private function decryptSetting(string $value): ?string
    {
        try {
            return decrypt($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
