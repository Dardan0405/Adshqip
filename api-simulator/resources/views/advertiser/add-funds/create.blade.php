@extends('layouts.advertiser')

@section('title', 'Add Funds')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div>
            <h1 class="text-2xl font-bold text-gray-900">Add Funds</h1>
            <p class="text-sm text-gray-500 mt-1">Create a deposit request and continue to the payment confirmation page.</p>
        </div>

        <form method="POST" action="{{ route('advertiser.payments.add-funds.store') }}" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            @csrf
            <div class="p-5 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Payment Details</h2>
                <p class="text-xs text-gray-500 mt-1">Use the same payment methods configured by the admin payment settings.</p>
            </div>

            <div class="p-5 grid gap-5 md:grid-cols-2">
                <div>
                    <label for="payment_type" class="block text-sm font-medium text-gray-700 mb-1">Select Payment Type</label>
                    <select id="payment_type" name="payment_type" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        @foreach($paymentTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('payment_type', $defaultPaymentType) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('payment_type') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Enter the Amount</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400">{{ $adminCurrency->symbol() }}</span>
                        <input id="amount" type="number" name="amount" value="{{ old('amount') }}" min="1" max="999999.99" step="0.01" class="w-full rounded-lg border border-gray-200 pl-8 pr-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="100.00">
                    </div>
                    @error('amount') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="px-5 py-4 border-t border-gray-100 bg-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-xs text-gray-500">The deposit will be created as pending until the payment is reviewed or confirmed.</p>
                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                    Continue to Confirmation
                </button>
            </div>
        </form>
    </div>
@endsection
