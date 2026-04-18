@extends('layouts.admin')

@section('title', 'Payment Settings')

@section('content')
    @php
        $wire = old() ? [
            'account_holder' => old('wire_account_holder'),
            'bank_name' => old('wire_bank_name'),
            'account_number' => old('wire_account_number'),
            'swift_code' => old('wire_swift_code'),
            'bank_address' => old('wire_bank_address'),
        ] : ($paymentDetails[\App\Models\PlatformSetting::ADVERTISER_PAYMENT_WIRE_TRANSFER] ?? []);
        $paypal = old() ? [
            'paypal_email' => old('paypal_email'),
            'merchant_id' => old('paypal_merchant_id'),
            'instructions' => old('paypal_instructions'),
        ] : ($paymentDetails[\App\Models\PlatformSetting::ADVERTISER_PAYMENT_PAYPAL] ?? []);
        $bitcoin = old() ? [
            'wallet_address' => old('bitcoin_wallet_address'),
            'network' => old('bitcoin_network'),
            'instructions' => old('bitcoin_instructions'),
        ] : ($paymentDetails[\App\Models\PlatformSetting::ADVERTISER_PAYMENT_BITCOIN] ?? []);
        $stripe = old() ? [
            'publishable_key' => old('stripe_publishable_key'),
            'secret_key' => old('stripe_secret_key'),
            'webhook_secret' => old('stripe_webhook_secret'),
            'account_email' => old('stripe_account_email'),
        ] : ($paymentDetails[\App\Models\PlatformSetting::ADVERTISER_PAYMENT_STRIPE] ?? []);
        $authorize = old() ? [
            'login_id' => old('authorize_login_id'),
            'transaction_key' => old('authorize_transaction_key'),
            'signature_key' => old('authorize_signature_key'),
            'client_key' => old('authorize_client_key'),
        ] : ($paymentDetails[\App\Models\PlatformSetting::ADVERTISER_PAYMENT_AUTHORIZE] ?? []);
        $activeType = old('payment_type', $selectedPaymentType);
    @endphp

    <div class="mx-auto max-w-6xl space-y-6">
        @if(session('success'))
            <div class="rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-[32px] border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-600">Admin Payments</p>
                    <h1 class="mt-2 text-2xl font-semibold text-slate-900">Payment Settings</h1>
                    <p class="mt-2 max-w-2xl text-sm text-slate-500">
                        Choose the active payment method for advertiser deposits and keep the receiving details ready for Bank Wire, PayPal, Bitcoin, Stripe, and Authorize.net.
                    </p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 px-5 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Active Method</p>
                    <p class="mt-2 text-lg font-semibold text-slate-900">{{ $paymentTypes[$activeType] ?? 'Bank Wire' }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.payment-settings.update') }}" class="space-y-8 px-6 py-6">
                @csrf
                @method('PUT')

                <section class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                    <div class="grid gap-5 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
                        <div>
                            <label for="payment_type" class="text-sm font-semibold text-slate-900">Payment Type</label>
                            <p class="mt-1 text-sm text-slate-500">The selected method becomes the default payment type used by the current advertiser payment flow.</p>
                        </div>
                        <div>
                            <select id="payment_type" name="payment_type" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100">
                                @foreach($paymentTypes as $value => $label)
                                    <option value="{{ $value }}" {{ $activeType === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('payment_type') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                <div class="grid gap-6 xl:grid-cols-2">
                    <section class="rounded-3xl border border-slate-200 bg-white p-5">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">Bank Wire</h2>
                                <p class="mt-1 text-sm text-slate-500">Use this when advertisers need manual bank transfer instructions.</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Bank Wire</span>
                        </div>
                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="text-sm font-medium text-slate-700">Account Holder</label>
                                <input type="text" name="wire_account_holder" value="{{ $wire['account_holder'] ?? '' }}" class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100">
                                @error('wire_account_holder') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm font-medium text-slate-700">Bank Name</label>
                                <input type="text" name="wire_bank_name" value="{{ $wire['bank_name'] ?? '' }}" class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100">
                                @error('wire_bank_name') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm font-medium text-slate-700">IBAN / Account Number</label>
                                <input type="text" name="wire_account_number" value="{{ $wire['account_number'] ?? '' }}" class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100">
                                @error('wire_account_number') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm font-medium text-slate-700">SWIFT / BIC</label>
                                <input type="text" name="wire_swift_code" value="{{ $wire['swift_code'] ?? '' }}" class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100">
                                @error('wire_swift_code') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-sm font-medium text-slate-700">Bank Address</label>
                                <textarea name="wire_bank_address" rows="3" class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100">{{ $wire['bank_address'] ?? '' }}</textarea>
                                @error('wire_bank_address') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-200 bg-white p-5">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">PayPal</h2>
                                <p class="mt-1 text-sm text-slate-500">Set the PayPal destination details advertisers should use.</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">PayPal</span>
                        </div>
                        <div class="mt-5 grid gap-4">
                            <div>
                                <label class="text-sm font-medium text-slate-700">PayPal Email</label>
                                <input type="email" name="paypal_email" value="{{ $paypal['paypal_email'] ?? '' }}" class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100">
                                @error('paypal_email') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm font-medium text-slate-700">Merchant ID</label>
                                <input type="text" name="paypal_merchant_id" value="{{ $paypal['merchant_id'] ?? '' }}" class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100">
                                @error('paypal_merchant_id') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm font-medium text-slate-700">Instructions</label>
                                <textarea name="paypal_instructions" rows="3" class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100">{{ $paypal['instructions'] ?? '' }}</textarea>
                                @error('paypal_instructions') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-200 bg-white p-5">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">Bitcoin</h2>
                                <p class="mt-1 text-sm text-slate-500">Keep the wallet and network ready for crypto deposit instructions.</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Bitcoin</span>
                        </div>
                        <div class="mt-5 grid gap-4">
                            <div>
                                <label class="text-sm font-medium text-slate-700">Wallet Address</label>
                                <input type="text" name="bitcoin_wallet_address" value="{{ $bitcoin['wallet_address'] ?? '' }}" class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100">
                                @error('bitcoin_wallet_address') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm font-medium text-slate-700">Network</label>
                                <input type="text" name="bitcoin_network" value="{{ $bitcoin['network'] ?? '' }}" class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100" placeholder="Bitcoin, Lightning, BTC">
                                @error('bitcoin_network') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm font-medium text-slate-700">Instructions</label>
                                <textarea name="bitcoin_instructions" rows="3" class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100">{{ $bitcoin['instructions'] ?? '' }}</textarea>
                                @error('bitcoin_instructions') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-200 bg-white p-5">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">Stripe</h2>
                                <p class="mt-1 text-sm text-slate-500">Store the Stripe keys and receiving email you want to use for card payments.</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Stripe</span>
                        </div>
                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="text-sm font-medium text-slate-700">Publishable Key</label>
                                <input type="text" name="stripe_publishable_key" value="{{ $stripe['publishable_key'] ?? '' }}" class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100">
                                @error('stripe_publishable_key') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm font-medium text-slate-700">Secret Key</label>
                                <input type="text" name="stripe_secret_key" value="{{ $stripe['secret_key'] ?? '' }}" class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100">
                                @error('stripe_secret_key') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm font-medium text-slate-700">Webhook Secret</label>
                                <input type="text" name="stripe_webhook_secret" value="{{ $stripe['webhook_secret'] ?? '' }}" class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100">
                                @error('stripe_webhook_secret') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm font-medium text-slate-700">Stripe Account Email</label>
                                <input type="email" name="stripe_account_email" value="{{ $stripe['account_email'] ?? '' }}" class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100">
                                @error('stripe_account_email') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-200 bg-white p-5">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">Authorize.net</h2>
                                <p class="mt-1 text-sm text-slate-500">Store the Authorize.net credentials you want to use for card-based payment handling.</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Authorize.net</span>
                        </div>
                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="text-sm font-medium text-slate-700">Login ID</label>
                                <input type="text" name="authorize_login_id" value="{{ $authorize['login_id'] ?? '' }}" class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100">
                                @error('authorize_login_id') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm font-medium text-slate-700">Transaction Key</label>
                                <input type="text" name="authorize_transaction_key" value="{{ $authorize['transaction_key'] ?? '' }}" class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100">
                                @error('authorize_transaction_key') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm font-medium text-slate-700">Signature Key</label>
                                <input type="text" name="authorize_signature_key" value="{{ $authorize['signature_key'] ?? '' }}" class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100">
                                @error('authorize_signature_key') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm font-medium text-slate-700">Client Key</label>
                                <input type="text" name="authorize_client_key" value="{{ $authorize['client_key'] ?? '' }}" class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100">
                                @error('authorize_client_key') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-500">Only the selected payment type becomes active, but the details for every method are saved so you can switch without retyping them.</p>
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-700">
                        Save Payment Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
