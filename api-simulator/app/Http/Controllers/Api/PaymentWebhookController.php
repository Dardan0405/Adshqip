<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Models\Transaction;
use App\Support\AdvertiserPaymentGateway;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentWebhookController extends Controller
{
    public function stripe(Request $request)
    {
        $payload = $request->getContent();
        $signature = (string) $request->header('Stripe-Signature');
        $secret = $this->paymentSecret(PlatformSetting::ADVERTISER_PAYMENT_STRIPE, 'webhook_secret', 'services.stripe.webhook_secret');

        abort_unless($secret && $this->validStripeSignature($payload, $signature, $secret), Response::HTTP_BAD_REQUEST);

        $event = json_decode($payload, true) ?: [];

        if (($event['type'] ?? null) === 'checkout.session.completed') {
            app(AdvertiserPaymentGateway::class)->completeStripeFromSession(
                (string) data_get($event, 'data.object.id'),
                $event
            );
        }

        return response()->json(['received' => true]);
    }

    public function bitpay(Request $request)
    {
        $payload = $request->getContent();
        $event = json_decode($payload, true) ?: [];

        $status = (string) data_get($event, 'status');

        if (in_array($status, ['confirmed', 'complete'], true)) {
            $transactionId = data_get($event, 'orderId');
            $transaction = $transactionId ? Transaction::deposits()->find($transactionId) : null;

            if ($transaction) {
                app(AdvertiserPaymentGateway::class)->complete(
                    $transaction,
                    (string) data_get($event, 'id'),
                    $event
                );
            }
        }

        return response()->json(['received' => true]);
    }

    private function validStripeSignature(string $payload, string $signature, string $secret): bool
    {
        $parts = collect(explode(',', $signature))
            ->mapWithKeys(function (string $part) {
                [$key, $value] = array_pad(explode('=', $part, 2), 2, null);

                return $key && $value ? [$key => $value] : [];
            });

        $timestamp = $parts->get('t');
        $expected = $parts->get('v1');

        if (! $timestamp || ! $expected) {
            return false;
        }

        if (abs(time() - (int) $timestamp) > 300) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $timestamp . '.' . $payload, $secret), $expected);
    }

    private function paymentSecret(string $paymentType, string $detailKey, string $configKey): ?string
    {
        $settingsSecret = PlatformSetting::getPaymentSettingsFor($paymentType)[$detailKey] ?? null;
        $secret = $settingsSecret ?: config($configKey);

        return filled($secret) ? (string) $secret : null;
    }
}
