@extends('layouts.advertiser')

@section('title', 'Confirm Payment')

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ session('error') }}</div>
        @endif

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Confirm Payment</h1>
                <p class="text-sm text-gray-500 mt-1">Deposit request #{{ $transaction->id }} is ready for {{ $paymentLabel }} payment.</p>
            </div>
            <span class="inline-flex w-fit rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">{{ ucfirst($transaction->status) }}</span>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1fr,1.2fr]">
            <div class="bg-white rounded-xl border border-gray-200 p-5 h-fit">
                <h2 class="text-sm font-semibold text-gray-900">Deposit Summary</h2>
                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                        <span class="text-gray-500">Amount</span>
                        <span class="font-semibold text-emerald-700">{{ $adminCurrency->format((float) $transaction->amount) }}</span>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                        <span class="text-gray-500">Payment Type</span>
                        <span class="font-medium text-gray-900">{{ $paymentLabel }}</span>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                        <span class="text-gray-500">Reference</span>
                        <span class="font-medium text-gray-900">#{{ $transaction->id }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">Created</span>
                        <span class="font-medium text-gray-900">{{ $transaction->created_at?->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="p-5 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-900">{{ $paymentLabel }} Payment Page</h2>
                    <p class="text-xs text-gray-500 mt-1">Continue to the secure checkout page to complete this deposit.</p>
                </div>

                <div class="p-5">
                    @if($paymentType === \App\Models\PlatformSetting::ADVERTISER_PAYMENT_PAYPAL)
                        <dl class="grid gap-3 text-sm">
                            <div class="rounded-lg border border-gray-100 bg-gray-50 p-3">
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">PayPal Email</dt>
                                <dd class="mt-1 font-semibold text-gray-900">{{ $paymentDetails['paypal_email'] ?? 'Not configured' }}</dd>
                            </div>
                            <div class="rounded-lg border border-gray-100 bg-gray-50 p-3">
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Merchant ID</dt>
                                <dd class="mt-1 font-semibold text-gray-900">{{ $paymentDetails['merchant_id'] ?? 'Not configured' }}</dd>
                            </div>
                            <div class="rounded-lg border border-gray-100 bg-gray-50 p-3">
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Instructions</dt>
                                <dd class="mt-1 text-gray-700">{{ $paymentDetails['instructions'] ?? 'Send the payment using your deposit reference.' }}</dd>
                            </div>
                        </dl>
                    @elseif($paymentType === \App\Models\PlatformSetting::ADVERTISER_PAYMENT_WIRE_TRANSFER)
                        <dl class="grid gap-3 text-sm md:grid-cols-2">
                            @foreach([
                                'account_holder' => 'Account Holder',
                                'bank_name' => 'Bank Name',
                                'account_number' => 'IBAN / Account Number',
                                'swift_code' => 'SWIFT / BIC',
                                'bank_address' => 'Bank Address',
                            ] as $key => $label)
                                <div class="rounded-lg border border-gray-100 bg-gray-50 p-3 {{ $key === 'bank_address' ? 'md:col-span-2' : '' }}">
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">{{ $label }}</dt>
                                    <dd class="mt-1 font-semibold text-gray-900">{{ $paymentDetails[$key] ?? 'Not configured' }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    @elseif($paymentType === \App\Models\PlatformSetting::ADVERTISER_PAYMENT_BITCOIN)
                        <dl class="grid gap-3 text-sm">
                            <div class="rounded-lg border border-gray-100 bg-gray-50 p-3">
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Wallet Address</dt>
                                <dd class="mt-1 break-all font-semibold text-gray-900">{{ $paymentDetails['wallet_address'] ?? 'Not configured' }}</dd>
                            </div>
                            <div class="rounded-lg border border-gray-100 bg-gray-50 p-3">
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Network</dt>
                                <dd class="mt-1 font-semibold text-gray-900">{{ $paymentDetails['network'] ?? 'Bitcoin' }}</dd>
                            </div>
                            <div class="rounded-lg border border-gray-100 bg-gray-50 p-3">
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Instructions</dt>
                                <dd class="mt-1 text-gray-700">{{ $paymentDetails['instructions'] ?? 'Send the exact amount and include your deposit reference.' }}</dd>
                            </div>
                        </dl>
                    @elseif($paymentType === \App\Models\PlatformSetting::ADVERTISER_PAYMENT_STRIPE)
                        <div class="rounded-lg border border-blue-100 bg-blue-50 p-4 text-sm text-blue-800">
                            Stripe is selected for this deposit. The next step opens Stripe Checkout for reference #{{ $transaction->id }}.
                        </div>
                        <dl class="mt-3 grid gap-3 text-sm">
                            <div class="rounded-lg border border-gray-100 bg-gray-50 p-3">
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Stripe Account Email</dt>
                                <dd class="mt-1 font-semibold text-gray-900">{{ $paymentDetails['account_email'] ?? 'Not configured' }}</dd>
                            </div>
                        </dl>
                    @elseif($paymentType === \App\Models\PlatformSetting::ADVERTISER_PAYMENT_AUTHORIZE)
                        <div class="rounded-lg border border-blue-100 bg-blue-50 p-4 text-sm text-blue-800">
                            Authorize.net is selected for this deposit. The next step opens the Authorize.net hosted payment page for reference #{{ $transaction->id }}.
                        </div>
                        <dl class="mt-3 grid gap-3 text-sm">
                            <div class="rounded-lg border border-gray-100 bg-gray-50 p-3">
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Login ID</dt>
                                <dd class="mt-1 font-semibold text-gray-900">{{ $paymentDetails['login_id'] ?? 'Not configured' }}</dd>
                            </div>
                        </dl>
                    @endif
                </div>

                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <p class="text-xs text-gray-500">Your balance updates after the provider confirms the payment.</p>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <form method="POST" action="{{ route('advertiser.payments.add-funds.pay', $transaction) }}">
                            @csrf
                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold text-white {{ $paymentType === \App\Models\PlatformSetting::ADVERTISER_PAYMENT_PAYPAL ? 'bg-[#003087] hover:bg-[#00256a]' : 'bg-brand-600 hover:bg-brand-700' }}">
                                @if($paymentType === \App\Models\PlatformSetting::ADVERTISER_PAYMENT_PAYPAL)
                                    Continue to PayPal
                                @elseif($paymentType === \App\Models\PlatformSetting::ADVERTISER_PAYMENT_WIRE_TRANSFER)
                                    Confirm Bank Wire
                                @elseif($paymentType === \App\Models\PlatformSetting::ADVERTISER_PAYMENT_BITCOIN)
                                    Continue to Bitcoin Checkout
                                @elseif($paymentType === \App\Models\PlatformSetting::ADVERTISER_PAYMENT_STRIPE)
                                    Continue to Stripe Checkout
                                @elseif($paymentType === \App\Models\PlatformSetting::ADVERTISER_PAYMENT_AUTHORIZE)
                                    Continue to Authorize.net
                                @else
                                    Confirm Payment
                                @endif
                            </button>
                        </form>
                        <a href="{{ route('advertiser.payments.history') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            View Payment History
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
