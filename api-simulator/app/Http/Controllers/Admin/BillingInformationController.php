<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BillingInformationController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load('profile');

        return view('admin.billing-information', [
            'user' => $user,
            'paymentMethods' => [
                'paypal' => 'PayPal',
                'wire_transfer' => 'Wire Transfer',
                'crypto' => 'Crypto',
                'payoneer' => 'Payoneer',
            ],
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user()->load('profile');

        $validated = $request->validate([
            'vat_number' => 'nullable|string|max:50',
            'postal_code' => 'nullable|string|max:20',
            'payment_method' => ['nullable', Rule::in(['paypal', 'wire_transfer', 'crypto', 'payoneer'])],
            'account_holder_name' => 'nullable|string|max:150',
            'paypal_email' => 'nullable|email|max:255',
            'bank_name' => 'nullable|string|max:150',
            'iban' => 'nullable|string|max:64',
            'swift_code' => 'nullable|string|max:32',
            'wallet_address' => 'nullable|string|max:255',
            'payoneer_email' => 'nullable|email|max:255',
            'billing_notes' => 'nullable|string|max:1000',
        ]);

        $paymentDetails = array_filter([
            'account_holder_name' => $validated['account_holder_name'] ?? null,
            'paypal_email' => $validated['paypal_email'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'iban' => $validated['iban'] ?? null,
            'swift_code' => $validated['swift_code'] ?? null,
            'wallet_address' => $validated['wallet_address'] ?? null,
            'payoneer_email' => $validated['payoneer_email'] ?? null,
            'billing_notes' => $validated['billing_notes'] ?? null,
        ], static fn ($value) => filled($value));

        UserProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'vat_number' => $validated['vat_number'] ?? null,
                'postal_code' => $validated['postal_code'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
                'payment_details' => $paymentDetails === [] ? null : $paymentDetails,
            ]
        );

        return redirect()
            ->route('admin.billing-information')
            ->with('success', 'Billing information updated successfully.');
    }
}
