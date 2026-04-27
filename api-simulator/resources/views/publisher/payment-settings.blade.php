@extends('layouts.publisher')

@section('title', 'Payment Settings')

@section('content')
    @php
        $active = old('payout_method', $activeMethod);

        $wireD = old() ? [
            'account_holder' => old('wire_account_holder'),
            'bank_name'      => old('wire_bank_name'),
            'iban'           => old('wire_iban'),
            'swift_code'     => old('wire_swift_code'),
            'bank_address'   => old('wire_bank_address'),
        ] : $wire;

        $paypalD = old() ? [
            'email' => old('paypal_email'),
        ] : $paypal;

        $bitcoinD = old() ? [
            'wallet_address' => old('bitcoin_wallet_address'),
            'network'        => old('bitcoin_network'),
        ] : $bitcoin;

        $stripeD = old() ? [
            'account_id'    => old('stripe_account_id'),
            'account_email' => old('stripe_account_email'),
        ] : $stripe;

        $authorizeD = old() ? [
            'login_id'        => old('authorize_login_id'),
            'transaction_key' => old('authorize_transaction_key'),
            'mode'            => old('authorize_mode', 'sandbox'),
        ] : $authorize;
    @endphp

    <div class="mx-auto max-w-5xl space-y-6">

        @if(session('success'))
            <div class="rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-3xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
                Please review the highlighted fields and try again.
            </div>
        @endif

        {{-- Header --}}
        <div class="rounded-[28px] border border-slate-200 bg-gradient-to-br from-slate-950 via-slate-900 to-brand-900 px-6 py-7 text-white shadow-sm" style="--tw-gradient-to: #1e1b4b;">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <span class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-sky-100">Payout Settings</span>
                    <h1 class="mt-4 text-2xl font-semibold tracking-tight">Payment Settings</h1>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-slate-200/85">
                        Select how you want to receive your publisher payouts and fill in the required details. All methods are saved; only the selected one will be used for your next payout.
                    </p>
                </div>
                <div class="shrink-0 rounded-3xl border border-white/10 bg-white/10 px-5 py-4 backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Active Method</p>
                    <p class="mt-2 text-lg font-semibold">{{ $methods[$activeMethod] ?? 'Bank Wire' }}</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('publisher.payment-settings.update') }}" x-data="{ active: '{{ $active }}' }">
            @csrf
            @method('PUT')

            {{-- Method selector --}}
            <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                <p class="mb-4 text-sm font-semibold text-slate-900">Select your payout method</p>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                    @foreach($methods as $key => $label)
                        @php
                            $icons = [
                                'bankwire'      => '<path d="M3 6l9-4 9 4M3 6v12l9 4 9-4V6M3 6l9 4 9-4M12 10v12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',
                                'paypal'        => '<path d="M7 11l1.5-6H15a3 3 0 013 3c0 2.5-2 3.5-4 3.5H11l-1 5H7l1-5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',
                                'bitcoin'       => '<path d="M12 1v22M9 5h7a3 3 0 010 6H9m0 0h7a3 3 0 010 6H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
                                'stripe'        => '<path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
                                'authorize_net' => '<path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
                            ];
                        @endphp
                        <label class="cursor-pointer">
                            <input type="radio" name="payout_method" value="{{ $key }}" x-model="active" class="sr-only">
                            <div :class="active === '{{ $key }}' ? 'border-brand-500 bg-brand-50 text-brand-700 ring-2 ring-brand-400/30' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                                 class="flex flex-col items-center gap-2 rounded-2xl border px-3 py-4 text-center transition-all">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">{!! $icons[$key] !!}</svg>
                                <span class="text-xs font-semibold">{{ $label }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Bank Wire --}}
            <div x-show="active === 'bankwire'" x-cloak class="mt-4 rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Bank Wire</h2>
                        <p class="mt-1 text-sm text-slate-500">Provide your bank account details to receive payouts by wire transfer.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Bank Wire</span>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Account Holder Name</label>
                        <input type="text" name="wire_account_holder" value="{{ $wireD['account_holder'] ?? '' }}"
                               class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-100"
                               placeholder="Full legal name">
                        @error('wire_account_holder') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Bank Name</label>
                        <input type="text" name="wire_bank_name" value="{{ $wireD['bank_name'] ?? '' }}"
                               class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-100"
                               placeholder="e.g. Raiffeisen Bank">
                        @error('wire_bank_name') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">IBAN / Account Number</label>
                        <input type="text" name="wire_iban" value="{{ $wireD['iban'] ?? '' }}"
                               class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-100"
                               placeholder="AL47 2121 1009 0000 0002 3569 8741">
                        @error('wire_iban') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">SWIFT / BIC Code</label>
                        <input type="text" name="wire_swift_code" value="{{ $wireD['swift_code'] ?? '' }}"
                               class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-100"
                               placeholder="e.g. AAALTIR0">
                        @error('wire_swift_code') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-sm font-medium text-slate-700">Bank Address</label>
                        <textarea name="wire_bank_address" rows="2"
                                  class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-100"
                                  placeholder="Street, city, country">{{ $wireD['bank_address'] ?? '' }}</textarea>
                        @error('wire_bank_address') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- PayPal --}}
            <div x-show="active === 'paypal'" x-cloak class="mt-4 rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">PayPal</h2>
                        <p class="mt-1 text-sm text-slate-500">Enter your PayPal email address to receive payouts directly to your PayPal account.</p>
                    </div>
                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">PayPal</span>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">PayPal Email Address</label>
                    <input type="email" name="paypal_email" value="{{ $paypalD['email'] ?? '' }}"
                           class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-100"
                           placeholder="you@example.com">
                    @error('paypal_email') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    <p class="mt-2 text-xs text-slate-400">Make sure this email is linked to an active PayPal account.</p>
                </div>
            </div>

            {{-- Bitcoin --}}
            <div x-show="active === 'bitcoin'" x-cloak class="mt-4 rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Bitcoin</h2>
                        <p class="mt-1 text-sm text-slate-500">Provide your cryptocurrency wallet address for crypto payouts.</p>
                    </div>
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Bitcoin</span>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="text-sm font-medium text-slate-700">Wallet Address</label>
                        <input type="text" name="bitcoin_wallet_address" value="{{ $bitcoinD['wallet_address'] ?? '' }}"
                               class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-mono text-slate-900 focus:border-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-100"
                               placeholder="bc1qxy2kgdygjrsqtzq2n0yrf...">
                        @error('bitcoin_wallet_address') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Network</label>
                        <input type="text" name="bitcoin_network" value="{{ $bitcoinD['network'] ?? '' }}"
                               class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-100"
                               placeholder="e.g. Bitcoin, Lightning, USDT TRC-20">
                        @error('bitcoin_network') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Stripe --}}
            <div x-show="active === 'stripe'" x-cloak class="mt-4 rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Stripe</h2>
                        <p class="mt-1 text-sm text-slate-500">Provide your Stripe Connect account details to receive payouts via Stripe.</p>
                    </div>
                    <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-semibold text-purple-700">Stripe</span>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Stripe Account ID</label>
                        <input type="text" name="stripe_account_id" value="{{ $stripeD['account_id'] ?? '' }}"
                               class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-100"
                               placeholder="acct_1234567890">
                        @error('stripe_account_id') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Stripe Account Email</label>
                        <input type="email" name="stripe_account_email" value="{{ $stripeD['account_email'] ?? '' }}"
                               class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-100"
                               placeholder="you@example.com">
                        @error('stripe_account_email') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Authorize.net --}}
            <div x-show="active === 'authorize_net'" x-cloak class="mt-4 rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Authorize.net</h2>
                        <p class="mt-1 text-sm text-slate-500">Enter your Authorize.net credentials to enable payouts through this gateway.</p>
                    </div>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Authorize.net</span>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Mode</label>
                        <select name="authorize_mode"
                                class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-100">
                            <option value="sandbox" {{ ($authorizeD['mode'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                            <option value="live"    {{ ($authorizeD['mode'] ?? 'sandbox') === 'live'    ? 'selected' : '' }}>Live</option>
                        </select>
                        @error('authorize_mode') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">API Login ID</label>
                        <input type="text" name="authorize_login_id" value="{{ $authorizeD['login_id'] ?? '' }}"
                               class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-100"
                               placeholder="Enter Login ID">
                        @error('authorize_login_id') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-sm font-medium text-slate-700">Transaction Key</label>
                        <input type="text" name="authorize_transaction_key" value="{{ $authorizeD['transaction_key'] ?? '' }}"
                               class="mt-1.5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-100"
                               placeholder="Enter Transaction Key">
                        @error('authorize_transaction_key') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-500">All methods are saved. Only the selected method is used for your next payout.</p>
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                    Save Payment Settings
                </button>
            </div>
        </form>
    </div>
@endsection
