<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Models\Transaction;
use App\Support\AdvertiserPaymentGateway;
use App\Support\AdvertiserPaymentManager;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class AddFundsController extends Controller
{
    public function create()
    {
        $paymentManager = app(AdvertiserPaymentManager::class);

        return view('advertiser.add-funds.create', [
            'paymentTypes' => $paymentManager->paymentTypeOptions(),
            'defaultPaymentType' => $paymentManager->activePaymentType(),
        ]);
    }

    public function store(Request $request)
    {
        $paymentManager = app(AdvertiserPaymentManager::class);
        $paymentTypes = array_keys($paymentManager->paymentTypeOptions());

        $validated = $request->validate([
            'payment_type' => ['required', Rule::in($paymentTypes)],
            'amount' => ['required', 'numeric', 'min:1', 'max:999999.99'],
        ]);

        $paymentType = $paymentManager->normalizePaymentType($validated['payment_type']);
        $transaction = $paymentManager->createDeposit(
            $request->user(),
            (float) $validated['amount'],
            $paymentType,
            $request->user()->profile?->currency ?: 'EUR',
            'pending',
            'Advertiser add funds via ' . $paymentManager->paymentTypeLabel($paymentType),
        );

        return redirect()->route('advertiser.payments.add-funds.confirm', $transaction)
            ->with('success', 'Deposit request created. Confirm the payment details to continue.');
    }

    public function confirm(Transaction $transaction)
    {
        abort_unless(
            $transaction->user_id === auth()->id()
            && $transaction->type === 'deposit',
            404
        );

        $paymentManager = app(AdvertiserPaymentManager::class);
        $paymentType = $paymentManager->normalizePaymentType($transaction->payment_gateway);

        return view('advertiser.add-funds.confirm', [
            'transaction' => $transaction,
            'paymentType' => $paymentType,
            'paymentLabel' => $paymentManager->paymentTypeLabel($paymentType),
            'paymentDetails' => PlatformSetting::getPaymentSettingsFor($paymentType),
        ]);
    }

    public function pay(Transaction $transaction)
    {
        abort_unless(
            $transaction->user_id === auth()->id()
            && $transaction->type === 'deposit',
            404
        );

        $paymentManager = app(AdvertiserPaymentManager::class);
        $paymentType = $paymentManager->normalizePaymentType($transaction->payment_gateway);
        $paymentDetails = PlatformSetting::getPaymentSettingsFor($paymentType);

        if ($paymentType === PlatformSetting::ADVERTISER_PAYMENT_WIRE_TRANSFER) {
            $transaction->forceFill([
                'gateway_status' => 'processing',
                'admin_note' => trim((string) $transaction->admin_note . ' Advertiser confirmed Bank Wire payment instructions.'),
            ])->save();

            return redirect()
                ->route('advertiser.payments.add-funds.confirm', $transaction)
                ->with('success', 'Bank Wire confirmation submitted. Admin will review and complete this deposit.');
        }

        try {
            $checkoutUrl = app(AdvertiserPaymentGateway::class)->start($transaction, $paymentType, $paymentDetails);
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('advertiser.payments.add-funds.confirm', $transaction)
                ->with('error', $exception->getMessage());
        }

        return redirect()->away($checkoutUrl);
    }

    public function paymentReturn(Request $request, Transaction $transaction)
    {
        abort_unless(
            $transaction->user_id === auth()->id()
            && $transaction->type === 'deposit',
            404
        );

        $provider = (string) $request->query('provider');
        $paymentGateway = app(AdvertiserPaymentGateway::class);

        try {
            if ($provider === 'paypal') {
                $details = PlatformSetting::getPaymentSettingsFor(PlatformSetting::ADVERTISER_PAYMENT_PAYPAL);
                $orderId = (string) $request->query('token', $transaction->gateway_txn_id);
                $paymentGateway->capturePaypal($transaction, $orderId, $details);
            } elseif ($provider === 'stripe') {
                if (! $request->query('session_id')) {
                    throw new RuntimeException('Stripe session ID is missing.');
                }

                $details = PlatformSetting::getPaymentSettingsFor(PlatformSetting::ADVERTISER_PAYMENT_STRIPE);
                $paymentGateway->completeStripeReturn($transaction, (string) $request->query('session_id'), $details);
            } elseif ($provider === 'bitpay') {
                return redirect()
                    ->route('advertiser.payments.history')
                    ->with('success', 'Bitcoin payment submitted. Your balance updates after BitPay confirms the payment.');
            } elseif ($provider === 'authorize') {
                $paymentGateway->complete($transaction, $transaction->gateway_txn_id, [
                    'provider' => 'authorize',
                    'completed_from_return_url' => true,
                ]);
            }
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('advertiser.payments.add-funds.confirm', $transaction)
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('advertiser.payments.history')
            ->with('success', 'Payment completed and your balance has been updated.');
    }

    public function cancel(Transaction $transaction)
    {
        abort_unless(
            $transaction->user_id === auth()->id()
            && $transaction->type === 'deposit',
            404
        );

        $transaction->forceFill(['gateway_status' => 'cancelled'])->save();

        return redirect()
            ->route('advertiser.payments.add-funds.confirm', $transaction)
            ->with('error', 'Payment was cancelled. You can try again from this page.');
    }

    public function authorizeHosted(Transaction $transaction)
    {
        abort_unless(
            $transaction->user_id === auth()->id()
            && $transaction->type === 'deposit'
            && $transaction->payment_gateway === PlatformSetting::ADVERTISER_PAYMENT_AUTHORIZE,
            404
        );

        $gatewayResponse = $transaction->gateway_response ?: [];

        return view('advertiser.add-funds.authorize-hosted', [
            'transaction' => $transaction,
            'hostedPaymentUrl' => data_get($gatewayResponse, 'hosted_payment_url'),
            'token' => $transaction->gateway_txn_id,
        ]);
    }
}
