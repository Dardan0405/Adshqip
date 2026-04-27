<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use App\Support\PublisherNotificationManager;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentSettingsController extends Controller
{
    public function __construct(private readonly PublisherNotificationManager $notifier) {}

    const METHODS = [
        'bankwire'      => 'Bank Wire',
        'paypal'        => 'PayPal',
        'bitcoin'       => 'Bitcoin',
        'stripe'        => 'Stripe',
        'authorize_net' => 'Authorize.net',
    ];

    public function show(Request $request)
    {
        $user    = $request->user()->load('profile');
        $profile = $user->profile;
        $details = $profile?->payout_details ?? [];

        return view('publisher.payment-settings', [
            'user'          => $user,
            'methods'       => self::METHODS,
            'activeMethod'  => $profile?->payout_method ?? 'bankwire',
            'wire'          => $details['bankwire']      ?? [],
            'paypal'        => $details['paypal']        ?? [],
            'bitcoin'       => $details['bitcoin']       ?? [],
            'stripe'        => $details['stripe']        ?? [],
            'authorize'     => $details['authorize_net'] ?? [],
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user()->load('profile');

        $validated = $request->validate([
            'payout_method'               => ['required', Rule::in(array_keys(self::METHODS))],

            // Bank Wire
            'wire_account_holder'         => ['nullable', 'string', 'max:255'],
            'wire_bank_name'              => ['nullable', 'string', 'max:255'],
            'wire_iban'                   => ['nullable', 'string', 'max:255'],
            'wire_swift_code'             => ['nullable', 'string', 'max:50'],
            'wire_bank_address'           => ['nullable', 'string', 'max:500'],

            // PayPal
            'paypal_email'                => ['nullable', 'email', 'max:255'],

            // Bitcoin
            'bitcoin_wallet_address'      => ['nullable', 'string', 'max:500'],
            'bitcoin_network'             => ['nullable', 'string', 'max:100'],

            // Stripe
            'stripe_account_id'           => ['nullable', 'string', 'max:255'],
            'stripe_account_email'        => ['nullable', 'email', 'max:255'],

            // Authorize.net
            'authorize_login_id'          => ['nullable', 'string', 'max:255'],
            'authorize_transaction_key'   => ['nullable', 'string', 'max:255'],
            'authorize_mode'              => ['nullable', Rule::in(['sandbox', 'live'])],
        ]);

        $existing = $user->profile?->payout_details ?? [];

        $details = array_merge($existing, [
            'bankwire' => [
                'account_holder' => trim((string) ($validated['wire_account_holder'] ?? '')),
                'bank_name'      => trim((string) ($validated['wire_bank_name']       ?? '')),
                'iban'           => trim((string) ($validated['wire_iban']             ?? '')),
                'swift_code'     => trim((string) ($validated['wire_swift_code']      ?? '')),
                'bank_address'   => trim((string) ($validated['wire_bank_address']    ?? '')),
            ],
            'paypal' => [
                'email' => trim((string) ($validated['paypal_email'] ?? '')),
            ],
            'bitcoin' => [
                'wallet_address' => trim((string) ($validated['bitcoin_wallet_address'] ?? '')),
                'network'        => trim((string) ($validated['bitcoin_network']        ?? '')),
            ],
            'stripe' => [
                'account_id'    => trim((string) ($validated['stripe_account_id']    ?? '')),
                'account_email' => trim((string) ($validated['stripe_account_email'] ?? '')),
            ],
            'authorize_net' => [
                'login_id'        => trim((string) ($validated['authorize_login_id']        ?? '')),
                'transaction_key' => trim((string) ($validated['authorize_transaction_key'] ?? '')),
                'mode'            => $validated['authorize_mode'] ?? 'sandbox',
            ],
        ]);

        UserProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'payout_method'  => $validated['payout_method'],
                'payout_details' => $details,
            ]
        );

        $this->notifier->deliver(
            $user->fresh('profile'),
            'payment_settings_updated',
            'Payment Settings Updated',
            'Your payment settings have been saved with method: ' . (self::METHODS[$validated['payout_method']] ?? $validated['payout_method']) . '.',
            route('publisher.payment-settings')
        );

        return redirect()
            ->route('publisher.payment-settings')
            ->with('success', 'Payment settings saved successfully.');
    }
}
