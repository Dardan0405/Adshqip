<?php

namespace App\Support;

use App\Models\Payout;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PublisherPayoutGateway
{
    public function supportsAutomaticPayout(string $payoutMethod): bool
    {
        return in_array($payoutMethod, ['paypal', 'stripe'], true);
    }

    public function send(Payout $payout, User $user, string $payoutMethod, array $payoutDetails): array
    {
        return match ($payoutMethod) {
            'paypal' => $this->paypal($payout, $user, $payoutDetails),
            'stripe' => $this->stripe($payout, $user, $payoutDetails),
            default => throw new RuntimeException('This payout method does not support automatic gateway execution.'),
        };
    }

    private function paypal(Payout $payout, User $user, array $payoutDetails): array
    {
        $recipientEmail = trim((string) data_get($payoutDetails, 'paypal.email', ''));
        if ($recipientEmail === '') {
            $recipientEmail = trim((string) $user->email);
        }

        if ($recipientEmail === '') {
            throw new RuntimeException('PayPal email is required to send the payout.');
        }

        $settings = PlatformSetting::getPaymentSettingsFor(PlatformSetting::ADVERTISER_PAYMENT_PAYPAL);
        $clientId = $settings['client_id'] ?? config('services.paypal.client_id');
        $secret = $settings['secret'] ?? config('services.paypal.secret');

        if (! $clientId || ! $secret) {
            throw new RuntimeException('PayPal payout credentials are not configured.');
        }

        $baseUrl = $this->paypalBaseUrl($settings);
        $token = $this->paypalAccessToken($settings);

        $response = Http::withToken($token)
            ->acceptJson()
            ->asJson()
            ->post($baseUrl . '/v1/payments/payouts', [
                'sender_batch_header' => [
                    'sender_batch_id' => 'PUB-PAYOUT-' . $payout->id,
                    'email_subject' => 'Your publisher payout from Adshqip',
                    'email_message' => 'A payout request has been submitted to your PayPal account.',
                ],
                'items' => [[
                    'recipient_type' => 'EMAIL',
                    'amount' => [
                        'value' => number_format((float) $payout->amount, 2, '.', ''),
                        'currency' => $payout->currency ?: 'EUR',
                    ],
                    'receiver' => $recipientEmail,
                    'note' => 'Publisher payout #' . $payout->id,
                    'sender_item_id' => (string) $payout->id,
                ]],
            ])
            ->throw()
            ->json();

        $batchId = data_get($response, 'batch_header.payout_batch_id');

        if (! $batchId) {
            throw new RuntimeException('PayPal payout batch identifier was not returned.');
        }

        return [
            'status' => 'processing',
            'gateway_reference' => (string) $batchId,
            'gateway_response' => $response,
            'message' => 'PayPal payout submitted successfully.',
        ];
    }

    private function stripe(Payout $payout, User $user, array $payoutDetails): array
    {
        $accountId = trim((string) data_get($payoutDetails, 'stripe.account_id', ''));
        if ($accountId === '') {
            throw new RuntimeException('Stripe Connect account ID is required to send the payout.');
        }

        $settings = PlatformSetting::getPaymentSettingsFor(PlatformSetting::ADVERTISER_PAYMENT_STRIPE);
        $secret = $settings['secret_key'] ?? config('services.stripe.secret');

        if (! $secret) {
            throw new RuntimeException('Stripe payout credentials are not configured.');
        }

        $response = Http::withToken($secret)
            ->acceptJson()
            ->asForm()
            ->post('https://api.stripe.com/v1/transfers', [
                'amount' => (int) round(((float) $payout->amount) * 100),
                'currency' => strtolower($payout->currency ?: 'EUR'),
                'destination' => $accountId,
                'description' => 'Publisher payout #' . $payout->id . ' for ' . ($user->email ?? 'publisher'),
                'metadata[payout_id]' => (string) $payout->id,
                'metadata[user_id]' => (string) $user->id,
            ])
            ->throw()
            ->json();

        $transferId = $response['id'] ?? null;

        if (! $transferId) {
            throw new RuntimeException('Stripe transfer identifier was not returned.');
        }

        return [
            'status' => 'processing',
            'gateway_reference' => (string) $transferId,
            'gateway_response' => $response,
            'message' => 'Stripe transfer created successfully.',
        ];
    }

    private function paypalAccessToken(array $details): string
    {
        $clientId = $details['client_id'] ?? config('services.paypal.client_id');
        $secret = $details['secret'] ?? config('services.paypal.secret');

        if (! $clientId || ! $secret) {
            throw new RuntimeException('PayPal client credentials are required.');
        }

        return Http::withBasicAuth($clientId, $secret)
            ->asForm()
            ->post($this->paypalBaseUrl($details) . '/v1/oauth2/token', ['grant_type' => 'client_credentials'])
            ->throw()
            ->json('access_token');
    }

    private function paypalBaseUrl(array $details): string
    {
        $mode = $details['mode'] ?? config('services.paypal.mode', 'sandbox');

        return $mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }
}
