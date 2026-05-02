<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PlatformSetting;
use App\Models\PricingPlan;
use App\Models\UserSubscription;
use App\Support\SubscriptionPaymentGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SubscriptionPlanController extends Controller
{
    public function __construct(private readonly SubscriptionPaymentGateway $gateway) {}

    public function index(Request $request): View
    {
        $cycle = $request->query('cycle') === 'yearly' ? 'yearly' : 'monthly';
        $userId = $request->user()->id;

        $plans = PricingPlan::query()
            ->active()
            ->whereIn('target_audience', ['advertiser', 'both'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $currentSubscription = UserSubscription::query()
            ->with(['plan', 'invoice'])
            ->forUser($userId)
            ->current()
            ->orderByDesc('current_period_end')
            ->orderByDesc('id')
            ->first();

        $subscriptionHistory = UserSubscription::query()
            ->with(['plan', 'invoice'])
            ->forUser($userId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('advertiser.subscriptions.index', [
            'plans' => $plans,
            'cycle' => $cycle,
            'currentSubscription' => $currentSubscription,
            'subscriptionHistory' => $subscriptionHistory,
        ]);
    }

    public function subscribe(Request $request, PricingPlan $plan): RedirectResponse
    {
        $data = $request->validate([
            'billing_cycle' => ['required', 'in:monthly,yearly'],
        ]);

        if ($plan->status !== 'active' || ! in_array($plan->target_audience, ['advertiser', 'both'], true)) {
            return back()->withErrors(['plan' => 'This subscription plan is not available for advertisers.']);
        }

        $user = $request->user();
        $cycle = $data['billing_cycle'];
        $price = $cycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;
        $periodStart = now()->toDateString();
        $periodEnd = $cycle === 'yearly' ? now()->addYear()->toDateString() : now()->addMonth()->toDateString();
        $createdInvoice = null;
        $createdSubscription = null;

        DB::transaction(function () use ($user, $plan, $cycle, $price, $periodStart, $periodEnd, &$createdInvoice, &$createdSubscription) {
            $createdSubscription = UserSubscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $cycle,
                'status' => 'pending',
                'current_period_start' => $periodStart,
                'current_period_end' => $periodEnd,
            ]);

            if ($price !== null && (float) $price > 0) {
                $createdInvoice = Invoice::create([
                    'user_id' => $user->id,
                    'invoice_number' => $this->invoiceNumber($user->id, $plan->id),
                    'type' => 'subscription_charge',
                    'amount' => $price,
                    'tax_amount' => 0,
                    'total_amount' => $price,
                    'currency' => $plan->currency ?: 'EUR',
                    'status' => 'sent',
                    'due_date' => now()->addDays(7)->toDateString(),
                ]);

                $createdSubscription->forceFill([
                    'invoice_id' => $createdInvoice->id,
                    'payment_reference' => $createdInvoice->invoice_number,
                ])->save();
            }
        });

        $paymentType = PlatformSetting::getAdvertiserPaymentType();

        if ($price !== null && (float) $price > 0 && $paymentType !== PlatformSetting::ADVERTISER_PAYMENT_WIRE_TRANSFER) {
            $paymentDetails = PlatformSetting::getPaymentSettingsFor($paymentType);
            $returnUrl = route('advertiser.payments.subscription-plan.return', $createdSubscription) . '?provider=' . $paymentType;
            $cancelUrl = route('advertiser.payments.subscription-plan.payment-cancel', $createdSubscription);

            try {
                $checkout = $this->gateway->start(
                    $createdSubscription,
                    $plan,
                    $user,
                    $paymentType,
                    $paymentDetails,
                    $returnUrl,
                    $cancelUrl
                );

                if (! empty($checkout['url'])) {
                    return redirect()->away($checkout['url']);
                }
            } catch (\RuntimeException $exception) {
                $createdSubscription->forceFill([
                    'status' => 'cancelled',
                    'gateway_status' => 'failed',
                    'gateway_response' => ['error' => $exception->getMessage()],
                    'cancelled_at' => now(),
                ])->save();

                return back()->withErrors(['plan' => 'Checkout could not be started: ' . $exception->getMessage()]);
            }
        }

        if ($price === null || (float) $price <= 0) {
            $this->gateway->complete($createdSubscription, null, ['provider' => 'free', 'completed_from' => 'zero_price_plan']);

            return redirect()
                ->route('advertiser.payments.subscription-plan', ['cycle' => $cycle])
                ->with('success', 'Subscription plan updated.');
        }

        return redirect()
            ->route('advertiser.payments.subscription-plan', ['cycle' => $cycle])
            ->with('success', 'Subscription invoice created and is awaiting manual payment review.');
    }

    public function paymentReturn(Request $request, UserSubscription $subscription): RedirectResponse
    {
        if ((int) $subscription->user_id !== (int) $request->user()->id) {
            abort(404);
        }

        $provider = (string) $request->query('provider', $subscription->payment_gateway ?: PlatformSetting::getAdvertiserPaymentType());
        $invoice = $subscription->invoice;

        try {
            if ($provider === PlatformSetting::ADVERTISER_PAYMENT_PAYPAL) {
                $details = PlatformSetting::getPaymentSettingsFor(PlatformSetting::ADVERTISER_PAYMENT_PAYPAL);
                $orderId = (string) $request->query('token', $subscription->gateway_txn_id);
                if ($orderId === '') {
                    throw new \RuntimeException('PayPal order token is missing.');
                }

                $this->gateway->completePaypal($subscription, $orderId, $details);
            } elseif ($provider === PlatformSetting::ADVERTISER_PAYMENT_STRIPE) {
                $sessionId = (string) $request->query('session_id');
                if ($sessionId === '') {
                    throw new \RuntimeException('Stripe session ID is missing.');
                }

                $details = PlatformSetting::getPaymentSettingsFor(PlatformSetting::ADVERTISER_PAYMENT_STRIPE);
                $secret = $details['secret_key'] ?? config('services.stripe.secret');
                if (! $secret) {
                    throw new \RuntimeException('Stripe secret key is required.');
                }

                $response = \Illuminate\Support\Facades\Http::withToken($secret)
                    ->acceptJson()
                    ->get('https://api.stripe.com/v1/checkout/sessions/' . urlencode($sessionId))
                    ->throw()
                    ->json();

                if (($response['payment_status'] ?? null) !== 'paid') {
                    throw new \RuntimeException('Stripe payment is not marked paid yet.');
                }

                if ((string) data_get($response, 'metadata.subscription_id') !== (string) $subscription->id) {
                    throw new \RuntimeException('Stripe session does not match this subscription.');
                }

                $this->gateway->complete($subscription, $sessionId, ['provider' => 'stripe', 'session' => $response]);
            } elseif ($provider === PlatformSetting::ADVERTISER_PAYMENT_AUTHORIZE) {
                $this->gateway->completeAuthorize($subscription, $subscription->gateway_txn_id, [
                    'provider' => 'authorize',
                    'completed_from_return_url' => true,
                ]);
            } elseif ($provider === PlatformSetting::ADVERTISER_PAYMENT_BITCOIN) {
                if ($subscription->status !== 'active' && $invoice?->status !== 'paid') {
                    return redirect()
                        ->route('advertiser.payments.subscription-plan')
                        ->with('success', 'Bitcoin payment submitted. The subscription activates after BitPay confirms it.');
                }
            }
        } catch (\RuntimeException $exception) {
            return redirect()
                ->route('advertiser.payments.subscription-plan', ['cycle' => $subscription->billing_cycle])
                ->withErrors(['plan' => $exception->getMessage()]);
        }

        return redirect()
            ->route('advertiser.payments.subscription-plan', ['cycle' => $subscription->billing_cycle])
            ->with('success', 'Subscription payment completed and access has been updated.');
    }

    public function paymentCancel(Request $request, UserSubscription $subscription): RedirectResponse
    {
        if ((int) $subscription->user_id !== (int) $request->user()->id) {
            abort(404);
        }

        if ($subscription->status === 'pending') {
            $subscription->update([
                'status' => 'cancelled',
                'gateway_status' => 'cancelled',
                'cancelled_at' => now(),
            ]);
        }

        return redirect()
            ->route('advertiser.payments.subscription-plan', ['cycle' => $subscription->billing_cycle])
            ->with('error', 'Subscription checkout was cancelled.');
    }

    public function cancel(Request $request, UserSubscription $subscription): RedirectResponse
    {
        if ((int) $subscription->user_id !== (int) $request->user()->id) {
            abort(404);
        }

        if (! in_array($subscription->status, ['active', 'trial'], true)) {
            return back()->withErrors(['subscription' => 'This subscription is already closed.']);
        }

        try {
            $this->gateway->cancelProviderSubscription($subscription);
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['subscription' => $exception->getMessage()]);
        } catch (\Throwable $exception) {
            return back()->withErrors(['subscription' => 'The payment gateway did not accept the cancellation. Please try again.']);
        }

        $subscription->update([
            'status' => 'cancelled',
            'gateway_status' => 'cancelled',
            'auto_renew' => false,
            'cancelled_at' => now(),
        ]);

        return redirect()
            ->route('advertiser.payments.subscription-plan')
            ->with('success', 'Subscription cancelled.');
    }

    private function invoiceNumber(int $userId, int $planId): string
    {
        return 'SUB-' . now()->format('YmdHis') . '-' . $userId . '-' . $planId . '-' . random_int(1000, 9999);
    }
}
