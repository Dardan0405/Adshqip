<?php

namespace App\Support;

use App\Models\Invoice;
use App\Models\PlatformSetting;
use App\Models\PricingPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SubscriptionPaymentGateway
{
    public function start(
        UserSubscription $subscription,
        PricingPlan $plan,
        User $user,
        string $paymentType,
        array $details,
        string $returnUrl,
        string $cancelUrl,
    ): array {
        return match ($paymentType) {
            PlatformSetting::ADVERTISER_PAYMENT_PAYPAL => $this->paypal($subscription, $plan, $user, $details, $returnUrl, $cancelUrl),
            PlatformSetting::ADVERTISER_PAYMENT_STRIPE => $this->stripe($subscription, $plan, $user, $details, $returnUrl, $cancelUrl),
            PlatformSetting::ADVERTISER_PAYMENT_BITCOIN => $this->bitpay($subscription, $plan, $user, $details, $returnUrl, $cancelUrl),
            PlatformSetting::ADVERTISER_PAYMENT_AUTHORIZE => $this->authorizeHosted($subscription, $plan, $user, $details, $returnUrl, $cancelUrl),
            default => throw new RuntimeException('This payment type does not support gateway checkout.'),
        };
    }

    public function complete(UserSubscription $subscription, ?string $gatewayTxnId = null, array $gatewayResponse = []): UserSubscription
    {
        return DB::transaction(function () use ($subscription, $gatewayTxnId, $gatewayResponse) {
            $subscription = UserSubscription::lockForUpdate()->with('invoice')->findOrFail($subscription->id);

            UserSubscription::query()
                ->where('user_id', $subscription->user_id)
                ->whereIn('status', ['active', 'trial'])
                ->where('id', '!=', $subscription->id)
                ->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);

            $gatewaySubscriptionId = $this->gatewaySubscriptionId($gatewayResponse) ?: $subscription->gateway_subscription_id;
            $gatewayCustomerId = $this->gatewayCustomerId($gatewayResponse) ?: $subscription->gateway_customer_id;
            $periodEnd = $this->periodEndFromPayload($gatewayResponse) ?: $subscription->current_period_end;

            $subscription->forceFill([
                'status' => 'active',
                'gateway_status' => 'confirmed',
                'gateway_txn_id' => $gatewayTxnId ?: $subscription->gateway_txn_id,
                'gateway_subscription_id' => $gatewaySubscriptionId,
                'gateway_customer_id' => $gatewayCustomerId,
                'gateway_response' => $gatewayResponse ?: $subscription->gateway_response,
                'current_period_end' => $periodEnd,
                'next_renewal_at' => $periodEnd,
                'last_renewed_at' => now(),
                'renewal_attempts' => 0,
                'cancelled_at' => null,
            ])->save();

            if ($subscription->invoice) {
                $subscription->invoice->forceFill([
                    'status' => 'paid',
                    'paid_at' => now(),
                ])->save();
            }

            return $subscription->fresh(['plan', 'invoice']);
        });
    }

    public function completeStripeFromSession(string $sessionId, array $payload): ?UserSubscription
    {
        $subscriptionId = data_get($payload, 'data.object.metadata.subscription_id');

        if (! $subscriptionId) {
            return null;
        }

        $subscription = UserSubscription::with('invoice')->find($subscriptionId);
        if (! $subscription) {
            return null;
        }

        return $this->complete($subscription, $sessionId, $payload);
    }

    public function completePaypal(UserSubscription $subscription, string $orderId, array $details): UserSubscription
    {
        $token = $this->paypalAccessToken($details);
        $baseUrl = $this->paypalBaseUrl($details);

        if ($subscription->gateway_subscription_id) {
            $response = Http::withToken($token)
                ->acceptJson()
                ->get($baseUrl . '/v1/billing/subscriptions/' . urlencode($subscription->gateway_subscription_id))
                ->throw()
                ->json();

            if (! in_array($response['status'] ?? null, ['ACTIVE', 'APPROVAL_PENDING', 'APPROVED'], true)) {
                throw new RuntimeException('PayPal subscription was not approved.');
            }

            return $this->complete($subscription, $subscription->gateway_subscription_id, [
                'provider' => 'paypal',
                'subscription' => $response,
            ]);
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->post($baseUrl . '/v2/checkout/orders/' . urlencode($orderId) . '/capture')
            ->throw()
            ->json();

        if (($response['status'] ?? null) !== 'COMPLETED') {
            throw new RuntimeException('PayPal order was not completed.');
        }

        return $this->complete($subscription, $orderId, $response);
    }

    public function completeAuthorize(UserSubscription $subscription, ?string $gatewayTxnId = null, array $gatewayResponse = []): UserSubscription
    {
        return $this->complete($subscription, $gatewayTxnId, $gatewayResponse);
    }

    public function completeBitpay(UserSubscription $subscription, string $invoiceId, array $payload): UserSubscription
    {
        return $this->complete($subscription, $invoiceId, $payload);
    }

    public function completeBitpayFromOrderId(?string $orderId, array $payload): ?UserSubscription
    {
        if (! $orderId) {
            return null;
        }

        $subscription = UserSubscription::with('invoice')->find($orderId);
        if (! $subscription) {
            return null;
        }

        return $this->completeBitpay($subscription, (string) data_get($payload, 'id', $orderId), $payload);
    }

    public function completePaypalFromWebhook(array $payload): ?UserSubscription
    {
        $providerSubscriptionId = (string) (
            data_get($payload, 'resource.billing_agreement_id')
            ?: data_get($payload, 'resource.subscription_id')
            ?: data_get($payload, 'resource.id')
        );

        if ($providerSubscriptionId === '') {
            return null;
        }

        $subscription = UserSubscription::with('invoice')
            ->where('gateway_subscription_id', $providerSubscriptionId)
            ->first();

        if (! $subscription) {
            return null;
        }

        return $this->recordRenewalPayment(
            $subscription,
            (string) data_get($payload, 'resource.id', $providerSubscriptionId),
            $payload
        );
    }

    public function recordStripeRenewal(string $providerSubscriptionId, string $gatewayInvoiceId, array $payload): ?UserSubscription
    {
        $subscription = UserSubscription::with(['invoice', 'plan', 'user'])
            ->where('gateway_subscription_id', $providerSubscriptionId)
            ->first();

        if (! $subscription) {
            return null;
        }

        return $this->recordRenewalPayment($subscription, $gatewayInvoiceId, $payload);
    }

    public function markStripePaymentFailed(string $providerSubscriptionId, array $payload): ?UserSubscription
    {
        $subscription = UserSubscription::where('gateway_subscription_id', $providerSubscriptionId)->first();

        if (! $subscription) {
            return null;
        }

        $subscription->forceFill([
            'gateway_status' => 'failed',
            'gateway_response' => $payload,
            'renewal_attempts' => (int) $subscription->renewal_attempts + 1,
        ])->save();

        return $subscription;
    }

    public function syncPaypalSubscriptionFromWebhook(string $providerSubscriptionId, array $payload): ?UserSubscription
    {
        $subscription = UserSubscription::with('invoice')
            ->where('gateway_subscription_id', $providerSubscriptionId)
            ->first();

        if (! $subscription) {
            return null;
        }

        return $this->complete($subscription, $providerSubscriptionId, $payload);
    }

    public function cancelProviderSubscription(UserSubscription $subscription): void
    {
        if (! $subscription->gateway_subscription_id || ! $subscription->payment_gateway) {
            return;
        }

        $details = PlatformSetting::getPaymentSettingsFor($subscription->payment_gateway);

        if ($subscription->payment_gateway === PlatformSetting::ADVERTISER_PAYMENT_STRIPE) {
            $secret = $details['secret_key'] ?? config('services.stripe.secret');
            if (! $secret) {
                throw new RuntimeException('Stripe secret key is required to cancel the recurring subscription.');
            }

            Http::withToken($secret)
                ->asForm()
                ->delete('https://api.stripe.com/v1/subscriptions/' . urlencode($subscription->gateway_subscription_id))
                ->throw();

            return;
        }

        if ($subscription->payment_gateway === PlatformSetting::ADVERTISER_PAYMENT_PAYPAL) {
            $token = $this->paypalAccessToken($details);
            $baseUrl = $this->paypalBaseUrl($details);

            Http::withToken($token)
                ->acceptJson()
                ->asJson()
                ->post($baseUrl . '/v1/billing/subscriptions/' . urlencode($subscription->gateway_subscription_id) . '/cancel', [
                    'reason' => 'User cancelled the subscription in Adshqip.',
                ])
                ->throw();
        }
    }

    private function paypal(UserSubscription $subscription, PricingPlan $plan, User $user, array $details, string $returnUrl, string $cancelUrl): array
    {
        $clientId = $details['client_id'] ?? config('services.paypal.client_id');
        $secret = $details['secret'] ?? config('services.paypal.secret');

        if (! $clientId || ! $secret) {
            throw new RuntimeException('PayPal credentials are required for subscription checkout.');
        }

        $token = $this->paypalAccessToken($details);
        $baseUrl = $this->paypalBaseUrl($details);
        $amount = $subscription->invoice?->total_amount ?? $plan->price_monthly ?? 0;
        $interval = $subscription->billing_cycle === 'yearly' ? 'YEAR' : 'MONTH';

        $product = Http::withToken($token)
            ->acceptJson()
            ->asJson()
            ->post($baseUrl . '/v1/catalogs/products', [
                'name' => $plan->name ?: ('Adshqip plan #' . $plan->id),
                'type' => 'SERVICE',
                'category' => 'SOFTWARE',
            ])
            ->throw()
            ->json();

        $productId = $product['id'] ?? null;
        if (! $productId) {
            throw new RuntimeException('PayPal product ID was not returned.');
        }

        $billingPlan = Http::withToken($token)
            ->acceptJson()
            ->asJson()
            ->post($baseUrl . '/v1/billing/plans', [
                'product_id' => $productId,
                'name' => ($plan->name ?: 'Adshqip plan') . ' ' . ucfirst($subscription->billing_cycle),
                'status' => 'ACTIVE',
                'billing_cycles' => [[
                    'frequency' => [
                        'interval_unit' => $interval,
                        'interval_count' => 1,
                    ],
                    'tenure_type' => 'REGULAR',
                    'sequence' => 1,
                    'total_cycles' => 0,
                    'pricing_scheme' => [
                        'fixed_price' => [
                            'value' => number_format((float) $amount, 2, '.', ''),
                            'currency_code' => $plan->currency ?: 'EUR',
                        ],
                    ],
                ]],
                'payment_preferences' => [
                    'auto_bill_outstanding' => true,
                    'setup_fee_failure_action' => 'CONTINUE',
                    'payment_failure_threshold' => 3,
                ],
            ])
            ->throw()
            ->json();

        $paypalPlanId = $billingPlan['id'] ?? null;
        if (! $paypalPlanId) {
            throw new RuntimeException('PayPal billing plan ID was not returned.');
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->asJson()
            ->post($baseUrl . '/v1/billing/subscriptions', [
                'plan_id' => $paypalPlanId,
                'custom_id' => (string) $subscription->id,
                'subscriber' => [
                    'email_address' => $user->email,
                ],
                'application_context' => [
                    'brand_name' => 'Adshqip',
                    'locale' => 'en-US',
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'SUBSCRIBE_NOW',
                    'return_url' => $returnUrl,
                    'cancel_url' => $cancelUrl,
                ],
            ])
            ->throw()
            ->json();

        $approvalLink = collect($response['links'] ?? [])->firstWhere('rel', 'approve');
        $approvalUrl = is_array($approvalLink) ? ($approvalLink['href'] ?? null) : null;

        if (! $approvalUrl) {
            throw new RuntimeException('PayPal approval URL was not returned.');
        }

        $subscription->forceFill([
            'payment_gateway' => 'paypal',
            'gateway_txn_id' => $response['id'] ?? $subscription->gateway_txn_id,
            'gateway_subscription_id' => $response['id'] ?? $subscription->gateway_subscription_id,
            'gateway_status' => 'processing',
            'gateway_response' => [
                'product' => $product,
                'billing_plan' => $billingPlan,
                'subscription' => $response,
            ],
            'payment_reference' => $response['id'] ?? $subscription->payment_reference,
        ])->save();

        return [
            'url' => $approvalUrl,
            'gateway_txn_id' => $response['id'] ?? null,
            'gateway_response' => $response,
        ];
    }

    private function stripe(UserSubscription $subscription, PricingPlan $plan, User $user, array $details, string $returnUrl, string $cancelUrl): array
    {
        $secret = $details['secret_key'] ?? config('services.stripe.secret');

        if (! $secret) {
            throw new RuntimeException('Stripe secret key is required for subscription checkout.');
        }

        $amount = $subscription->invoice?->total_amount ?? $plan->price_monthly ?? 0;

        $response = Http::withToken($secret)
            ->asForm()
            ->post('https://api.stripe.com/v1/checkout/sessions', [
                'mode' => 'subscription',
                'client_reference_id' => (string) $subscription->id,
                'success_url' => $returnUrl . '&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $cancelUrl,
                'line_items[0][quantity]' => 1,
                'line_items[0][price_data][currency]' => strtolower($plan->currency ?: 'eur'),
                'line_items[0][price_data][unit_amount]' => (int) round(((float) $amount) * 100),
                'line_items[0][price_data][recurring][interval]' => $subscription->billing_cycle === 'yearly' ? 'year' : 'month',
                'line_items[0][price_data][recurring][interval_count]' => 1,
                'line_items[0][price_data][product_data][name]' => 'Adshqip subscription plan #' . $plan->id,
                'metadata[subscription_id]' => (string) $subscription->id,
                'metadata[plan_id]' => (string) $plan->id,
                'subscription_data[metadata][subscription_id]' => (string) $subscription->id,
                'subscription_data[metadata][plan_id]' => (string) $plan->id,
            ])
            ->throw()
            ->json();

        if (empty($response['url'])) {
            throw new RuntimeException('Stripe Checkout URL was not returned.');
        }

        $subscription->forceFill([
            'payment_gateway' => 'stripe',
            'gateway_txn_id' => $response['id'] ?? $subscription->gateway_txn_id,
            'gateway_status' => 'processing',
            'gateway_response' => $response,
            'payment_reference' => $response['id'] ?? $subscription->payment_reference,
        ])->save();

        return [
            'url' => $response['url'],
            'gateway_txn_id' => $response['id'] ?? null,
            'gateway_response' => $response,
        ];
    }

    private function bitpay(UserSubscription $subscription, PricingPlan $plan, User $user, array $details, string $returnUrl, string $cancelUrl): array
    {
        $apiToken = $details['bitpay_api_token'] ?? config('services.bitpay.api_token');
        $mode = $details['mode'] ?? config('services.bitpay.mode', 'test');

        if (! $apiToken) {
            throw new RuntimeException('BitPay API token is required for subscription checkout.');
        }

        $apiUrl = $mode === 'live'
            ? 'https://bitpay.com/invoices'
            : 'https://test.bitpay.com/invoices';

        $amount = $subscription->invoice?->total_amount ?? $plan->price_monthly ?? 0;
        $invoiceNumber = $subscription->invoice?->invoice_number ?? ('SUB-' . $subscription->id);

        $response = Http::withHeaders([
            'X-Accept-Version' => '2.0.0',
            'Content-Type' => 'application/json',
        ])->withToken($apiToken)
            ->acceptJson()
            ->asJson()
            ->post($apiUrl, [
                'price' => (float) $amount,
                'currency' => $plan->currency ?: 'EUR',
                'orderId' => (string) $subscription->id,
                'itemDesc' => 'Adshqip subscription plan #' . $plan->id,
                'notificationURL' => route('api.payments.bitpay.webhook'),
                'redirectURL' => $returnUrl,
                'closeURL' => $cancelUrl,
                'buyer' => [
                    'email' => $user->email ?? null,
                ],
                'token' => $apiToken,
                'invoiceNumber' => $invoiceNumber,
            ])->throw()->json();

        $invoiceUrl = $response['url'] ?? null;
        if (! $invoiceUrl) {
            throw new RuntimeException('BitPay invoice URL was not returned.');
        }

        $subscription->forceFill([
            'payment_gateway' => 'bitcoin',
            'gateway_txn_id' => $response['id'] ?? $subscription->gateway_txn_id,
            'gateway_status' => 'processing',
            'gateway_response' => $response,
            'payment_reference' => $response['id'] ?? $invoiceNumber,
        ])->save();

        return [
            'url' => $invoiceUrl,
            'gateway_txn_id' => $response['id'] ?? null,
            'gateway_response' => $response,
        ];
    }

    private function authorizeHosted(UserSubscription $subscription, PricingPlan $plan, User $user, array $details, string $returnUrl, string $cancelUrl): array
    {
        $loginId = $details['login_id'] ?? config('services.authorize_net.login_id');
        $transactionKey = $details['transaction_key'] ?? config('services.authorize_net.transaction_key');

        if (! $loginId || ! $transactionKey) {
            throw new RuntimeException('Authorize.net credentials are required for subscription checkout.');
        }

        $mode = $details['mode'] ?? config('services.authorize_net.mode', 'sandbox');
        $apiUrl = $mode === 'live'
            ? 'https://api.authorize.net/xml/v1/request.api'
            : 'https://apitest.authorize.net/xml/v1/request.api';
        $hostedPaymentUrl = $mode === 'live'
            ? 'https://accept.authorize.net/payment/payment'
            : 'https://test.authorize.net/payment/payment';

        $amount = $subscription->invoice?->total_amount ?? $plan->price_monthly ?? 0;

        $response = Http::acceptJson()->asJson()->post($apiUrl, [
            'getHostedPaymentPageRequest' => [
                'merchantAuthentication' => [
                    'name' => $loginId,
                    'transactionKey' => $transactionKey,
                ],
                'transactionRequest' => [
                    'transactionType' => 'authCaptureTransaction',
                    'amount' => number_format((float) $amount, 2, '.', ''),
                    'order' => [
                        'invoiceNumber' => (string) $subscription->id,
                        'description' => 'Adshqip subscription plan #' . $plan->id,
                    ],
                    'userFields' => [
                        'userField' => [[
                            'name' => 'subscription_id',
                            'value' => (string) $subscription->id,
                        ]],
                    ],
                ],
                'hostedPaymentSettings' => [
                    'setting' => [
                        [
                            'settingName' => 'hostedPaymentReturnOptions',
                            'settingValue' => json_encode([
                                'showReceipt' => false,
                                'url' => $returnUrl,
                                'urlText' => 'Return to Adshqip',
                                'cancelUrl' => $cancelUrl,
                                'cancelUrlText' => 'Cancel payment',
                            ]),
                        ],
                    ],
                ],
            ],
        ])->throw()->json();

        $token = data_get($response, 'token');
        if (! $token) {
            throw new RuntimeException('Authorize.net hosted payment token was not returned.');
        }

        $subscription->forceFill([
            'payment_gateway' => 'authorize',
            'gateway_txn_id' => $token,
            'gateway_status' => 'processing',
            'gateway_response' => $response,
            'payment_reference' => $token,
        ])->save();

        return [
            'url' => $hostedPaymentUrl . '?token=' . urlencode((string) $token),
            'gateway_txn_id' => (string) $token,
            'gateway_response' => $response,
        ];
    }

    public function recordRenewalPayment(UserSubscription $subscription, string $gatewayInvoiceId, array $payload): UserSubscription
    {
        return DB::transaction(function () use ($subscription, $gatewayInvoiceId, $payload) {
            $subscription = UserSubscription::lockForUpdate()->with(['invoice', 'plan', 'user'])->findOrFail($subscription->id);
            $plan = $subscription->plan;

            if (! $plan) {
                throw new RuntimeException('Subscription plan is missing.');
            }

            $amount = $this->amountFromPayload($payload, (float) ($subscription->invoice?->total_amount ?? 0));
            $currency = strtoupper($this->currencyFromPayload($payload, $subscription->invoice?->currency ?: $plan->currency ?: 'EUR'));
            $periodStart = $this->periodStartFromPayload($payload) ?: now()->toDateString();
            $periodEnd = $this->periodEndFromPayload($payload)
                ?: ($subscription->billing_cycle === 'yearly' ? now()->addYear()->toDateString() : now()->addMonth()->toDateString());

            if (
                $subscription->current_period_end
                && $subscription->current_period_end->toDateString() >= $periodEnd
                && $subscription->last_renewed_at
                && $subscription->last_renewed_at->greaterThan(now()->subDay())
            ) {
                $subscription->forceFill([
                    'gateway_status' => 'confirmed',
                    'gateway_txn_id' => $gatewayInvoiceId,
                    'gateway_subscription_id' => $this->gatewaySubscriptionId($payload) ?: $subscription->gateway_subscription_id,
                    'gateway_customer_id' => $this->gatewayCustomerId($payload) ?: $subscription->gateway_customer_id,
                    'gateway_response' => $payload,
                    'renewal_attempts' => 0,
                ])->save();

                return $subscription->fresh(['plan', 'invoice']);
            }

            $invoiceNumber = 'SUBREN-' . now()->format('YmdHis') . '-' . $subscription->id . '-' . random_int(1000, 9999);

            $invoice = Invoice::create([
                'user_id' => $subscription->user_id,
                'invoice_number' => $invoiceNumber,
                'type' => 'subscription_charge',
                'amount' => $amount,
                'tax_amount' => 0,
                'total_amount' => $amount,
                'currency' => $currency,
                'status' => 'paid',
                'due_date' => now()->toDateString(),
                'paid_at' => now(),
            ]);

            $subscription->forceFill([
                'invoice_id' => $invoice->id,
                'status' => 'active',
                'gateway_status' => 'confirmed',
                'gateway_txn_id' => $gatewayInvoiceId,
                'gateway_subscription_id' => $this->gatewaySubscriptionId($payload) ?: $subscription->gateway_subscription_id,
                'gateway_customer_id' => $this->gatewayCustomerId($payload) ?: $subscription->gateway_customer_id,
                'gateway_response' => $payload,
                'payment_reference' => $invoiceNumber,
                'current_period_start' => $periodStart,
                'current_period_end' => $periodEnd,
                'next_renewal_at' => $periodEnd,
                'last_renewed_at' => now(),
                'renewal_attempts' => 0,
                'cancelled_at' => null,
            ])->save();

            return $subscription->fresh(['plan', 'invoice']);
        });
    }

    public function createManualRenewalInvoice(UserSubscription $subscription): ?Invoice
    {
        if (! $subscription->plan) {
            return null;
        }

        $price = $subscription->billing_cycle === 'yearly'
            ? $subscription->plan->price_yearly
            : $subscription->plan->price_monthly;

        if ($price === null || (float) $price <= 0) {
            $this->complete($subscription, null, ['provider' => 'free_renewal']);

            return null;
        }

        return DB::transaction(function () use ($subscription, $price) {
            $subscription = UserSubscription::lockForUpdate()->with('plan')->findOrFail($subscription->id);

            $invoice = Invoice::create([
                'user_id' => $subscription->user_id,
                'invoice_number' => 'SUBMAN-' . now()->format('YmdHis') . '-' . $subscription->id . '-' . random_int(1000, 9999),
                'type' => 'subscription_charge',
                'amount' => $price,
                'tax_amount' => 0,
                'total_amount' => $price,
                'currency' => $subscription->plan->currency ?: 'EUR',
                'status' => 'sent',
                'due_date' => now()->addDays(7)->toDateString(),
            ]);

            $subscription->forceFill([
                'invoice_id' => $invoice->id,
                'gateway_status' => 'pending_renewal_invoice',
                'payment_reference' => $invoice->invoice_number,
                'renewal_attempts' => (int) $subscription->renewal_attempts + 1,
            ])->save();

            return $invoice;
        });
    }

    private function gatewaySubscriptionId(array $payload): ?string
    {
        return data_get($payload, 'data.object.subscription')
            ?: data_get($payload, 'data.object.parent.subscription_details.subscription')
            ?: data_get($payload, 'subscription')
            ?: data_get($payload, 'subscription.id')
            ?: data_get($payload, 'id')
            ?: data_get($payload, 'resource.billing_agreement_id')
            ?: data_get($payload, 'resource.subscription_id')
            ?: data_get($payload, 'resource.id');
    }

    private function gatewayCustomerId(array $payload): ?string
    {
        return data_get($payload, 'data.object.customer')
            ?: data_get($payload, 'data.object.customer_id')
            ?: data_get($payload, 'subscription.subscriber.payer_id')
            ?: data_get($payload, 'resource.subscriber.payer_id');
    }

    private function periodStartFromPayload(array $payload): ?string
    {
        $timestamp = data_get($payload, 'data.object.lines.data.0.period.start')
            ?: data_get($payload, 'data.object.period_start');

        return $timestamp ? now()->setTimestamp((int) $timestamp)->toDateString() : null;
    }

    private function periodEndFromPayload(array $payload): ?string
    {
        $timestamp = data_get($payload, 'data.object.lines.data.0.period.end')
            ?: data_get($payload, 'data.object.period_end');

        if ($timestamp) {
            return now()->setTimestamp((int) $timestamp)->toDateString();
        }

        $paypalNextBilling = data_get($payload, 'subscription.billing_info.next_billing_time')
            ?: data_get($payload, 'resource.billing_info.next_billing_time');

        return $paypalNextBilling ? \Carbon\Carbon::parse($paypalNextBilling)->toDateString() : null;
    }

    private function amountFromPayload(array $payload, float $fallback): float
    {
        $stripeAmount = data_get($payload, 'data.object.amount_paid')
            ?: data_get($payload, 'data.object.amount_due');

        if ($stripeAmount !== null) {
            return round(((float) $stripeAmount) / 100, 4);
        }

        $paypalAmount = data_get($payload, 'resource.amount.value')
            ?: data_get($payload, 'resource.billing_info.last_payment.amount.value');

        return round((float) ($paypalAmount ?? $fallback), 4);
    }

    private function currencyFromPayload(array $payload, string $fallback): string
    {
        return (string) (
            data_get($payload, 'data.object.currency')
            ?: data_get($payload, 'resource.amount.currency_code')
            ?: data_get($payload, 'resource.billing_info.last_payment.amount.currency_code')
            ?: $fallback
        );
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
