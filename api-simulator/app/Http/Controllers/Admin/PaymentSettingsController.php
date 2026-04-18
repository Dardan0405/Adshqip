<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentSettingsController extends Controller
{
    public function show(Request $request)
    {
        return view('admin.payment-settings', [
            'user' => $request->user()->load('profile'),
            'paymentTypes' => PlatformSetting::getAdvertiserPaymentTypes(),
            'selectedPaymentType' => PlatformSetting::getAdvertiserPaymentType(),
            'paymentDetails' => PlatformSetting::getPaymentSettingsDetails(),
        ]);
    }

    public function update(Request $request)
    {
        $paymentTypes = array_keys(PlatformSetting::getAdvertiserPaymentTypes());

        $validated = $request->validate([
            'payment_type' => ['required', Rule::in($paymentTypes)],
            'wire_account_holder' => [
                Rule::requiredIf(fn () => $request->input('payment_type') === PlatformSetting::ADVERTISER_PAYMENT_WIRE_TRANSFER),
                'nullable',
                'string',
                'max:255',
            ],
            'wire_bank_name' => [
                Rule::requiredIf(fn () => $request->input('payment_type') === PlatformSetting::ADVERTISER_PAYMENT_WIRE_TRANSFER),
                'nullable',
                'string',
                'max:255',
            ],
            'wire_account_number' => [
                Rule::requiredIf(fn () => $request->input('payment_type') === PlatformSetting::ADVERTISER_PAYMENT_WIRE_TRANSFER),
                'nullable',
                'string',
                'max:255',
            ],
            'wire_swift_code' => ['nullable', 'string', 'max:255'],
            'wire_bank_address' => ['nullable', 'string', 'max:500'],
            'paypal_email' => [
                Rule::requiredIf(fn () => $request->input('payment_type') === PlatformSetting::ADVERTISER_PAYMENT_PAYPAL),
                'nullable',
                'email',
                'max:255',
            ],
            'paypal_merchant_id' => ['nullable', 'string', 'max:255'],
            'paypal_instructions' => ['nullable', 'string', 'max:500'],
            'bitcoin_wallet_address' => [
                Rule::requiredIf(fn () => $request->input('payment_type') === PlatformSetting::ADVERTISER_PAYMENT_BITCOIN),
                'nullable',
                'string',
                'max:500',
            ],
            'bitcoin_network' => ['nullable', 'string', 'max:100'],
            'bitcoin_instructions' => ['nullable', 'string', 'max:500'],
            'stripe_publishable_key' => [
                Rule::requiredIf(fn () => $request->input('payment_type') === PlatformSetting::ADVERTISER_PAYMENT_STRIPE),
                'nullable',
                'string',
                'max:255',
            ],
            'stripe_secret_key' => [
                Rule::requiredIf(fn () => $request->input('payment_type') === PlatformSetting::ADVERTISER_PAYMENT_STRIPE),
                'nullable',
                'string',
                'max:255',
            ],
            'stripe_webhook_secret' => ['nullable', 'string', 'max:255'],
            'stripe_account_email' => ['nullable', 'email', 'max:255'],
            'authorize_login_id' => [
                Rule::requiredIf(fn () => $request->input('payment_type') === PlatformSetting::ADVERTISER_PAYMENT_AUTHORIZE),
                'nullable',
                'string',
                'max:255',
            ],
            'authorize_transaction_key' => [
                Rule::requiredIf(fn () => $request->input('payment_type') === PlatformSetting::ADVERTISER_PAYMENT_AUTHORIZE),
                'nullable',
                'string',
                'max:255',
            ],
            'authorize_signature_key' => ['nullable', 'string', 'max:255'],
            'authorize_client_key' => ['nullable', 'string', 'max:255'],
        ]);

        $details = [
            PlatformSetting::ADVERTISER_PAYMENT_WIRE_TRANSFER => [
                'account_holder' => trim((string) ($validated['wire_account_holder'] ?? '')),
                'bank_name' => trim((string) ($validated['wire_bank_name'] ?? '')),
                'account_number' => trim((string) ($validated['wire_account_number'] ?? '')),
                'swift_code' => trim((string) ($validated['wire_swift_code'] ?? '')),
                'bank_address' => trim((string) ($validated['wire_bank_address'] ?? '')),
            ],
            PlatformSetting::ADVERTISER_PAYMENT_PAYPAL => [
                'paypal_email' => trim((string) ($validated['paypal_email'] ?? '')),
                'merchant_id' => trim((string) ($validated['paypal_merchant_id'] ?? '')),
                'instructions' => trim((string) ($validated['paypal_instructions'] ?? '')),
            ],
            PlatformSetting::ADVERTISER_PAYMENT_BITCOIN => [
                'wallet_address' => trim((string) ($validated['bitcoin_wallet_address'] ?? '')),
                'network' => trim((string) ($validated['bitcoin_network'] ?? '')),
                'instructions' => trim((string) ($validated['bitcoin_instructions'] ?? '')),
            ],
            PlatformSetting::ADVERTISER_PAYMENT_STRIPE => [
                'publishable_key' => trim((string) ($validated['stripe_publishable_key'] ?? '')),
                'secret_key' => trim((string) ($validated['stripe_secret_key'] ?? '')),
                'webhook_secret' => trim((string) ($validated['stripe_webhook_secret'] ?? '')),
                'account_email' => trim((string) ($validated['stripe_account_email'] ?? '')),
            ],
            PlatformSetting::ADVERTISER_PAYMENT_AUTHORIZE => [
                'login_id' => trim((string) ($validated['authorize_login_id'] ?? '')),
                'transaction_key' => trim((string) ($validated['authorize_transaction_key'] ?? '')),
                'signature_key' => trim((string) ($validated['authorize_signature_key'] ?? '')),
                'client_key' => trim((string) ($validated['authorize_client_key'] ?? '')),
            ],
        ];

        PlatformSetting::setValue(
            'advertiser_payment_type',
            $validated['payment_type'],
            'string',
            'advertiser_payments',
            'Default payment method for advertiser deposits',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'admin_payment_type',
            $validated['payment_type'],
            'string',
            'advertiser_payments',
            'Active admin payment method configuration',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'admin_payment_settings_details',
            $details,
            'json',
            'advertiser_payments',
            'Saved admin payment method details for bank wire, PayPal, Bitcoin, Stripe, and Authorize.net',
            $request->user()?->id,
        );

        return redirect()
            ->route('admin.payment-settings')
            ->with('success', 'Payment settings updated successfully.');
    }
}
