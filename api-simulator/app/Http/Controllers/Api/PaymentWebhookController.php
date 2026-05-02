<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Models\Transaction;
use App\Support\AdvertiserPaymentGateway;
use App\Support\SubscriptionPaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
            $sessionId = (string) data_get($event, 'data.object.id');
            $subscriptionId = data_get($event, 'data.object.metadata.subscription_id');

            if ($subscriptionId) {
                app(SubscriptionPaymentGateway::class)->completeStripeFromSession($sessionId, $event);
            } else {
                app(AdvertiserPaymentGateway::class)->completeStripeFromSession(
                    $sessionId,
                    $event
                );
            }
        }

        if (in_array($event['type'] ?? null, ['invoice.paid', 'invoice.payment_succeeded'], true)) {
            $providerSubscriptionId = $this->stripeSubscriptionId($event);
            $gatewayInvoiceId = (string) data_get($event, 'data.object.id');

            if ($providerSubscriptionId && $gatewayInvoiceId) {
                $gateway = app(SubscriptionPaymentGateway::class);

                if (data_get($event, 'data.object.billing_reason') === 'subscription_create') {
                    $subscription = \App\Models\UserSubscription::where('gateway_subscription_id', $providerSubscriptionId)->first();

                    if ($subscription) {
                        $gateway->complete($subscription, $gatewayInvoiceId, $event);
                    }
                } else {
                    $gateway->recordStripeRenewal($providerSubscriptionId, $gatewayInvoiceId, $event);
                }
            }
        }

        if (($event['type'] ?? null) === 'invoice.payment_failed') {
            $providerSubscriptionId = $this->stripeSubscriptionId($event);

            if ($providerSubscriptionId) {
                app(SubscriptionPaymentGateway::class)->markStripePaymentFailed($providerSubscriptionId, $event);
            }
        }

        return response()->json(['received' => true]);
    }

    public function bitpay(Request $request)
    {
        $payload = $request->getContent();
        $event = json_decode($payload, true) ?: [];

        $status = (string) data_get($event, 'status');

        if (in_array($status, ['confirmed', 'complete'], true)) {
            $orderId = (string) data_get($event, 'orderId');
            $transaction = $orderId ? Transaction::deposits()->find($orderId) : null;

            if ($transaction) {
                app(AdvertiserPaymentGateway::class)->complete(
                    $transaction,
                    (string) data_get($event, 'id'),
                    $event
                );
            } else {
                app(SubscriptionPaymentGateway::class)->completeBitpayFromOrderId($orderId, $event);
            }
        }

        return response()->json(['received' => true]);
    }

    public function paypal(Request $request)
    {
        $payload = $request->getContent();
        $webhookId = $this->paymentSecret(PlatformSetting::ADVERTISER_PAYMENT_PAYPAL, 'webhook_id', 'services.paypal.webhook_id');

        abort_unless(! $webhookId || $this->validPaypalSignature($request, $payload, $webhookId), Response::HTTP_BAD_REQUEST);

        $event = json_decode($payload, true) ?: [];
        $eventType = (string) data_get($event, 'event_type');
        $gateway = app(SubscriptionPaymentGateway::class);

        if (in_array($eventType, ['BILLING.SUBSCRIPTION.ACTIVATED', 'BILLING.SUBSCRIPTION.UPDATED'], true)) {
            $providerSubscriptionId = (string) data_get($event, 'resource.id');

            if ($providerSubscriptionId !== '') {
                $gateway->syncPaypalSubscriptionFromWebhook($providerSubscriptionId, $event);
            }
        }

        if (in_array($eventType, ['PAYMENT.SALE.COMPLETED', 'PAYMENT.CAPTURE.COMPLETED'], true)) {
            $gateway->completePaypalFromWebhook($event);
        }

        if (in_array($eventType, ['BILLING.SUBSCRIPTION.CANCELLED', 'BILLING.SUBSCRIPTION.SUSPENDED'], true)) {
            $providerSubscriptionId = (string) data_get($event, 'resource.id');

            if ($providerSubscriptionId !== '') {
                \App\Models\UserSubscription::where('gateway_subscription_id', $providerSubscriptionId)->update([
                    'status' => 'cancelled',
                    'gateway_status' => strtolower(str_replace('BILLING.SUBSCRIPTION.', '', $eventType)),
                    'gateway_response' => $event,
                    'cancelled_at' => now(),
                ]);
            }
        }

        return response()->json(['received' => true]);
    }

    private function validPaypalSignature(Request $request, string $payload, string $webhookId): bool
    {
        $details = PlatformSetting::getPaymentSettingsFor(PlatformSetting::ADVERTISER_PAYMENT_PAYPAL);
        $clientId = $details['client_id'] ?? config('services.paypal.client_id');
        $secret = $details['secret'] ?? config('services.paypal.secret');

        if (! $clientId || ! $secret) {
            return false;
        }

        $mode = $details['mode'] ?? config('services.paypal.mode', 'sandbox');
        $baseUrl = $mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        $accessToken = Http::withBasicAuth($clientId, $secret)
            ->asForm()
            ->post($baseUrl . '/v1/oauth2/token', ['grant_type' => 'client_credentials'])
            ->throw()
            ->json('access_token');

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->asJson()
            ->post($baseUrl . '/v1/notifications/verify-webhook-signature', [
                'auth_algo' => $request->header('PAYPAL-AUTH-ALGO'),
                'cert_url' => $request->header('PAYPAL-CERT-URL'),
                'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID'),
                'transmission_sig' => $request->header('PAYPAL-TRANSMISSION-SIG'),
                'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
                'webhook_id' => $webhookId,
                'webhook_event' => json_decode($payload, true) ?: [],
            ])
            ->throw()
            ->json();

        return ($response['verification_status'] ?? null) === 'SUCCESS';
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

    private function stripeSubscriptionId(array $event): ?string
    {
        $id = data_get($event, 'data.object.subscription')
            ?: data_get($event, 'data.object.parent.subscription_details.subscription');

        return filled($id) ? (string) $id : null;
    }
}
