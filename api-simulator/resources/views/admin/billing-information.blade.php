@extends('layouts.admin')

@section('title', 'Billing Information')

@section('content')
    @php
        $profile = $user->profile;
        $initials = strtoupper(substr($user->email, 0, 2));
        $paymentDetails = $profile?->payment_details ?? [];
        $selectedPaymentMethod = old('payment_method', $profile?->payment_method);
    @endphp

    <div class="space-y-6">
        <div class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-gradient-to-br from-slate-950 via-slate-900 to-amber-900 px-6 py-7 text-white shadow-sm">
            <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(251,191,36,0.30),_transparent_55%)]"></div>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-amber-100">Billing Profile</span>
                    <h1 class="mt-4 text-3xl font-semibold tracking-tight">Manage tax and payout details for this admin account.</h1>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-slate-200/85">
                        Keep your billing identifiers, payout method, and payment destination details ready for invoices, settlements, and financial operations.
                    </p>
                </div>
                <div class="flex items-center gap-4 rounded-3xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                    @if($profile?->avatar_url)
                        <img src="{{ $profile->avatar_url }}" alt="Profile picture" class="h-20 w-20 rounded-2xl object-cover ring-2 ring-white/20">
                    @else
                        <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-white/15 text-2xl font-bold text-white ring-2 ring-white/20">
                            {{ $initials }}
                        </div>
                    @endif
                    <div>
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-300">{{ ucfirst($user->role ?? 'admin') }}</p>
                        <p class="mt-1 text-lg font-semibold">{{ $paymentMethods[$profile?->payment_method] ?? 'Billing details not set' }}</p>
                        <p class="mt-1 text-sm text-slate-200/80">{{ $user->email }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                Please review the highlighted billing fields and try again.
            </div>
        @endif

        <form method="POST" action="{{ route('admin.billing-information.update') }}" class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.45fr)_360px]">
            @csrf
            @method('PUT')

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Billing details</h2>
                        <p class="mt-1 text-sm text-slate-500">Update the financial details connected to this account.</p>
                    </div>
                    <div class="rounded-2xl bg-amber-50 px-3 py-2 text-xs font-medium text-amber-700">
                        Profile record: {{ $profile ? 'Connected' : 'Will be created on save' }}
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">VAT Number</label>
                        <input type="text" name="vat_number" value="{{ old('vat_number', $profile?->vat_number) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-amber-400 focus:outline-none focus:ring-4 focus:ring-amber-100" placeholder="Enter VAT or tax number">
                        @error('vat_number') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Postal Code</label>
                        <input type="text" name="postal_code" value="{{ old('postal_code', $profile?->postal_code) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-amber-400 focus:outline-none focus:ring-4 focus:ring-amber-100" placeholder="Enter postal code">
                        @error('postal_code') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Payment Method</label>
                        <select name="payment_method" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-amber-400 focus:outline-none focus:ring-4 focus:ring-amber-100">
                            <option value="">Select payment method</option>
                            @foreach($paymentMethods as $value => $label)
                                <option value="{{ $value }}" {{ $selectedPaymentMethod === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('payment_method') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Account Holder Name</label>
                        <input type="text" name="account_holder_name" value="{{ old('account_holder_name', $paymentDetails['account_holder_name'] ?? '') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-amber-400 focus:outline-none focus:ring-4 focus:ring-amber-100" placeholder="Enter account holder name">
                        @error('account_holder_name') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">PayPal Email</label>
                        <input type="email" name="paypal_email" value="{{ old('paypal_email', $paymentDetails['paypal_email'] ?? '') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-amber-400 focus:outline-none focus:ring-4 focus:ring-amber-100" placeholder="paypal@example.com">
                        @error('paypal_email') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Payoneer Email</label>
                        <input type="email" name="payoneer_email" value="{{ old('payoneer_email', $paymentDetails['payoneer_email'] ?? '') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-amber-400 focus:outline-none focus:ring-4 focus:ring-amber-100" placeholder="payoneer@example.com">
                        @error('payoneer_email') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Bank Name</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $paymentDetails['bank_name'] ?? '') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-amber-400 focus:outline-none focus:ring-4 focus:ring-amber-100" placeholder="Enter bank name">
                        @error('bank_name') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">IBAN / Account Number</label>
                        <input type="text" name="iban" value="{{ old('iban', $paymentDetails['iban'] ?? '') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-amber-400 focus:outline-none focus:ring-4 focus:ring-amber-100" placeholder="Enter IBAN or account number">
                        @error('iban') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">SWIFT Code</label>
                        <input type="text" name="swift_code" value="{{ old('swift_code', $paymentDetails['swift_code'] ?? '') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-amber-400 focus:outline-none focus:ring-4 focus:ring-amber-100" placeholder="Enter SWIFT code">
                        @error('swift_code') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Crypto Wallet Address</label>
                        <input type="text" name="wallet_address" value="{{ old('wallet_address', $paymentDetails['wallet_address'] ?? '') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-amber-400 focus:outline-none focus:ring-4 focus:ring-amber-100" placeholder="Enter wallet address">
                        @error('wallet_address') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Billing Notes</label>
                        <textarea name="billing_notes" rows="4" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-amber-400 focus:outline-none focus:ring-4 focus:ring-amber-100" placeholder="Add any billing instructions or internal notes">{{ old('billing_notes', $paymentDetails['billing_notes'] ?? '') }}</textarea>
                        @error('billing_notes') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <div class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-5">
                        <h2 class="text-lg font-semibold text-slate-900">Billing summary</h2>
                        <p class="mt-1 text-sm text-slate-500">A quick overview of your current billing setup.</p>
                    </div>

                    <div class="rounded-3xl bg-slate-950 p-5 text-white">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Payment profile</p>
                        <p class="mt-3 text-2xl font-semibold">{{ $paymentMethods[$profile?->payment_method] ?? 'Not configured' }}</p>
                        <div class="mt-5 space-y-3 text-sm text-slate-300">
                            <div class="flex items-center justify-between gap-4">
                                <span>VAT</span>
                                <span class="font-medium text-white">{{ $profile?->vat_number ?: 'Not set' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Postal Code</span>
                                <span class="font-medium text-white">{{ $profile?->postal_code ?: 'Not set' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Account Holder</span>
                                <span class="text-right font-medium text-white">{{ $paymentDetails['account_holder_name'] ?? 'Not set' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5">
                        <a href="{{ route('admin.account-settings') }}" class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Back to Account Settings
                        </a>
                    </div>
                </section>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                    Save Billing Information
                </button>
            </div>
        </form>
    </div>
@endsection
