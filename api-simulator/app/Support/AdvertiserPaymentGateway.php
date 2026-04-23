<?php

namespace App\Support;

use App\Models\Invoice;
use App\Models\PlatformSetting;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AdvertiserPaymentGateway
{
    public function start(Transaction $transaction, string $paymentType, array $details): string
    {
        return match ($paymentType) {
            PlatformSetting::ADVERTISER_PAYMENT_PAYPAL => $this->paypal($transaction, $details),
            PlatformSetting::ADVERTISER_PAYMENT_STRIPE => $this->stripe($transaction, $details),
            PlatformSetting::ADVERTISER_PAYMENT_BITCOIN => $this->bitpay($transaction, $details),
            PlatformSetting::ADVERTISER_PAYMENT_AUTHORIZE => $this->authorizeHosted($transaction, $details),
            default => throw new RuntimeException('This payment type does not support automatic checkout.'),
        };
    }

    public function complete(Transaction $transaction, ?string $gatewayTxnId = null, array $gatewayResponse = []): Transaction
    {
        return DB::transaction(function () use ($transaction, $gatewayTxnId, $gatewayResponse) {
            $transaction = Transaction::lockForUpdate()->findOrFail($transaction->id);

            if ($transaction->status === 'completed') {
                return $transaction;
            }

            $profile = $transaction->user->profile()->firstOrCreate(
                ['user_id' => $transaction->user_id],
                ['balance' => 0, 'currency' => $transaction->currency ?: 'EUR']
            );

            $balanceBefore = (float) ($profile->balance ?? 0);
            $balanceAfter = $balanceBefore + (float) $transaction->amount;

            $transaction->forceFill([
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'gateway_txn_id' => $gatewayTxnId ?: $transaction->gateway_txn_id,
                'gateway_status' => 'confirmed',
                'gateway_response' => $gatewayResponse ?: $transaction->gateway_response,
                'status' => 'completed',
                'completed_at' => now(),
            ])->save();

            if ($transaction->invoice_id) {
                Invoice::whereKey($transaction->invoice_id)->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            }

            $profile->forceFill(['balance' => $balanceAfter])->save();

            return $transaction->fresh();
        });
    }

    public function capturePaypal(Transaction $transaction, string $orderId, array $details): Transaction
    {
        $token = $this->paypalAccessToken($details);
        $baseUrl = $this->paypalBaseUrl($details);

        $response = Http::withToken($token)
            ->acceptJson()
            ->post($baseUrl . '/v2/checkout/orders/' . urlencode($orderId) . '/capture')
            ->throw()
            ->json();

        if (($response['status'] ?? null) !== 'COMPLETED') {
            throw new RuntimeException('PayPal order was not completed.');
        }

        return $this->complete($transaction, $orderId, $response);
    }

    public function completeStripeFromSession(string $sessionId, array $payload): ?Transaction
    {
        $transactionId = data_get($payload, 'data.object.metadata.transaction_id');

        if (! $transactionId) {
            return null;
        }

        $transaction = Transaction::whereKey($transactionId)
            ->where('type', 'deposit')
            ->first();

        if (! $transaction) {
            return null;
        }

        return $this->complete($transaction, $sessionId, $payload);
    }

    public function completeStripeReturn(Transaction $transaction, string $sessionId, array $details): Transaction
    {
        $secret = $details['secret_key'] ?? config('services.stripe.secret');

        if (! $secret) {
            throw new RuntimeException('Stripe secret key is required to verify the checkout session.');
        }

        $session = Http::withToken($secret)
            ->acceptJson()
            ->get('https://api.stripe.com/v1/checkout/sessions/' . urlencode($sessionId))
            ->throw()
            ->json();

        if (($session['payment_status'] ?? null) !== 'paid') {
            throw new RuntimeException('Stripe payment is not marked paid yet.');
        }

        $metadataTransactionId = (string) data_get($session, 'metadata.transaction_id');

        if ($metadataTransactionId !== (string) $transaction->id) {
            throw new RuntimeException('Stripe session does not match this deposit.');
        }

        return $this->complete($transaction, $sessionId, [
            'provider' => 'stripe',
            'session' => $session,
            'completed_from_return_url' => true,
        ]);
    }

    private function paypal(Transaction $transaction, array $details): string
    {
        $clientId = $details['client_id'] ?? config('services.paypal.client_id');
        $secret = $details['secret'] ?? config('services.paypal.secret');

        if (! $clientId || ! $secret) {
            return $this->paypalClassic($transaction, $details);
        }

        $token = $this->paypalAccessToken($details);
        $baseUrl = $this->paypalBaseUrl($details);

        $response = Http::withToken($token)
            ->acceptJson()
            ->asJson()
            ->post($baseUrl . '/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => (string) $transaction->id,
                    'description' => 'Adshqip advertiser deposit #' . $transaction->id,
                    'amount' => [
                        'currency_code' => $transaction->currency ?: 'EUR',
                        'value' => number_format((float) $transaction->amount, 2, '.', ''),
                    ],
                ]],
                'application_context' => [
                    'return_url' => route('advertiser.payments.add-funds.return', [$transaction, 'provider' => 'paypal']),
                    'cancel_url' => route('advertiser.payments.add-funds.cancel', $transaction),
                    'user_action' => 'PAY_NOW',
                ],
            ])
            ->throw()
            ->json();

        $approvalLink = collect($response['links'] ?? [])->firstWhere('rel', 'approve');
        $approvalUrl = is_array($approvalLink) ? ($approvalLink['href'] ?? null) : null;

        if (! $approvalUrl) {
            throw new RuntimeException('PayPal approval URL was not returned.');
        }

        $transaction->forceFill([
            'gateway_txn_id' => $response['id'] ?? $transaction->gateway_txn_id,
            'gateway_status' => 'processing',
            'gateway_response' => $response,
        ])->save();

        return $approvalUrl;
    }

    private function paypalClassic(Transaction $transaction, array $details): string
    {
        $business = $details['paypal_email'] ?? null;

        if (! $business) {
            throw new RuntimeException('PayPal email is required when PayPal API credentials are not configured.');
        }

        $mode = $details['mode'] ?? config('services.paypal.mode', 'sandbox');
        $checkoutUrl = $mode === 'live'
            ? 'https://www.paypal.com/cgi-bin/webscr'
            : 'https://www.sandbox.paypal.com/cgi-bin/webscr';

        $transaction->forceFill([
            'gateway_status' => 'processing',
            'gateway_response' => [
                'provider' => 'paypal_classic',
                'message' => 'Redirected with PayPal classic checkout because REST API client ID/secret are not configured.',
            ],
        ])->save();

        return $checkoutUrl . '?' . http_build_query([
            'cmd' => '_xclick',
            'business' => $business,
            'item_name' => 'Adshqip advertiser deposit #' . $transaction->id,
            'invoice' => (string) $transaction->id,
            'amount' => number_format((float) $transaction->amount, 2, '.', ''),
            'currency_code' => $transaction->currency ?: 'EUR',
            'return' => route('advertiser.payments.history'),
            'cancel_return' => route('advertiser.payments.add-funds.cancel', $transaction),
            'notify_url' => route('advertiser.payments.add-funds.confirm', $transaction),
        ], '', '&', PHP_QUERY_RFC3986);
    }

    private function paypalAccessToken(array $details): string
    {
        $clientId = $details['client_id'] ?? config('services.paypal.client_id');
        $secret = $details['secret'] ?? config('services.paypal.secret');

        if (! $clientId || ! $secret) {
            throw new RuntimeException('PayPal client ID and secret are required for production checkout.');
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

    private function stripe(Transaction $transaction, array $details): string
    {
        $secret = $details['secret_key'] ?? config('services.stripe.secret');

        if (! $secret) {
            throw new RuntimeException('Stripe secret key is required for Checkout.');
        }

        $response = Http::withToken($secret)
            ->asForm()
            ->post('https://api.stripe.com/v1/checkout/sessions', [
                'mode' => 'payment',
                'client_reference_id' => (string) $transaction->id,
                'success_url' => route('advertiser.payments.add-funds.return', [$transaction, 'provider' => 'stripe']) . '&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('advertiser.payments.add-funds.cancel', $transaction),
                'line_items[0][quantity]' => 1,
                'line_items[0][price_data][currency]' => strtolower($transaction->currency ?: 'eur'),
                'line_items[0][price_data][unit_amount]' => (int) round(((float) $transaction->amount) * 100),
                'line_items[0][price_data][product_data][name]' => 'Adshqip advertiser deposit #' . $transaction->id,
                'metadata[transaction_id]' => (string) $transaction->id,
            ])
            ->throw()
            ->json();

        if (empty($response['url'])) {
            throw new RuntimeException('Stripe Checkout URL was not returned.');
        }

        $transaction->forceFill([
            'gateway_txn_id' => $response['id'] ?? $transaction->gateway_txn_id,
            'gateway_status' => 'processing',
            'gateway_response' => $response,
        ])->save();

        return $response['url'];
    }

    private function bitpay(Transaction $transaction, array $details): string
    {
        $apiToken = $details['bitpay_api_token'] ?? config('services.bitpay.api_token');
        $mode = $details['mode'] ?? config('services.bitpay.mode', 'test');

        if (! $apiToken) {
            throw new RuntimeException('BitPay API token is required for Bitcoin checkout.');
        }

        $apiUrl = $mode === 'live'
            ? 'https://bitpay.com/invoices'
            : 'https://test.bitpay.com/invoices';

        $response = Http::withHeaders([
            'X-Accept-Version' => '2.0.0',
            'Content-Type' => 'application/json',
        ])->withToken($apiToken)
            ->acceptJson()
            ->asJson()
            ->post($apiUrl, [
                'price' => (float) $transaction->amount,
                'currency' => $transaction->currency ?: 'EUR',
                'orderId' => (string) $transaction->id,
                'itemDesc' => 'Adshqip advertiser deposit #' . $transaction->id,
                'notificationURL' => route('api.payments.bitpay.webhook'),
                'redirectURL' => route('advertiser.payments.add-funds.return', [$transaction, 'provider' => 'bitpay']),
                'closeURL' => route('advertiser.payments.add-funds.cancel', $transaction),
                'buyer' => [
                    'email' => $transaction->user->email ?? null,
                ],
                'token' => $apiToken,
            ])->throw()->json();

        $invoiceUrl = $response['url'] ?? null;

        if (! $invoiceUrl) {
            throw new RuntimeException('BitPay invoice URL was not returned.');
        }

        $transaction->forceFill([
            'gateway_txn_id' => $response['id'] ?? $transaction->gateway_txn_id,
            'gateway_status' => 'processing',
            'gateway_response' => $response,
        ])->save();

        return $invoiceUrl;
    }

    private function authorizeHosted(Transaction $transaction, array $details): string
    {
        $loginId = $details['login_id'] ?? config('services.authorize_net.login_id');
        $transactionKey = $details['transaction_key'] ?? config('services.authorize_net.transaction_key');

        if (! $loginId || ! $transactionKey) {
            throw new RuntimeException('Authorize.net Login ID and Transaction Key are required for hosted checkout.');
        }

        $mode = $details['mode'] ?? config('services.authorize_net.mode', 'sandbox');
        $apiUrl = $mode === 'live'
            ? 'https://api.authorize.net/xml/v1/request.api'
            : 'https://apitest.authorize.net/xml/v1/request.api';
        $hostedPaymentUrl = $mode === 'live'
            ? 'https://accept.authorize.net/payment/payment'
            : 'https://test.authorize.net/payment/payment';

        $response = Http::acceptJson()->asJson()->post($apiUrl, [
            'getHostedPaymentPageRequest' => [
                'merchantAuthentication' => [
                    'name' => $loginId,
                    'transactionKey' => $transactionKey,
                ],
                'transactionRequest' => [
                    'transactionType' => 'authCaptureTransaction',
                    'amount' => number_format((float) $transaction->amount, 2, '.', ''),
                    'order' => [
                        'invoiceNumber' => (string) $transaction->id,
                        'description' => 'Adshqip advertiser deposit #' . $transaction->id,
                    ],
                    'userFields' => [
                        'userField' => [[
                            'name' => 'transaction_id',
                            'value' => (string) $transaction->id,
                        ]],
                    ],
                ],
                'hostedPaymentSettings' => [
                    'setting' => [
                        [
                            'settingName' => 'hostedPaymentReturnOptions',
                            'settingValue' => json_encode([
                                'showReceipt' => false,
                                'url' => route('advertiser.payments.add-funds.return', [$transaction, 'provider' => 'authorize']),
                                'urlText' => 'Return to Adshqip',
                                'cancelUrl' => route('advertiser.payments.add-funds.cancel', $transaction),
                                'cancelUrlText' => 'Cancel payment',
                            ]),
                        ],
                    ],
                ],
            ],
        ])->throw()->json();

        $resultCode = data_get($response, 'messages.resultCode');
        $token = $response['token'] ?? null;

        if ($resultCode !== 'Ok' || ! $token) {
            $message = data_get($response, 'messages.message.0.text', 'Authorize.net did not return a hosted payment token.');
            throw new RuntimeException($message);
        }

        $transaction->forceFill([
            'gateway_txn_id' => $token,
            'gateway_status' => 'processing',
            'gateway_response' => [
                'provider' => 'authorize',
                'hosted_payment_url' => $hostedPaymentUrl,
                'response' => $response,
            ],
        ])->save();

        return route('advertiser.payments.add-funds.authorize-hosted', $transaction);
    }
}
