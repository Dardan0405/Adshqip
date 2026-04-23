@extends('layouts.advertiser')

@section('title', 'Authorize.net Checkout')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="rounded-xl border border-gray-200 bg-white p-6 text-center shadow-sm">
            <h1 class="text-2xl font-bold text-gray-900">Redirecting to Authorize.net</h1>
            <p class="mt-2 text-sm text-gray-500">Deposit request #{{ $transaction->id }} is being opened in the secure hosted payment page.</p>

            @if($hostedPaymentUrl && $token)
                <form id="authorizeHostedForm" method="POST" action="{{ $hostedPaymentUrl }}" class="mt-6">
                    <input type="hidden" name="token" value="{{ $token }}">
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                        Continue to Authorize.net
                    </button>
                </form>

                <script>
                    document.getElementById('authorizeHostedForm').submit();
                </script>
            @else
                <div class="mt-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    Authorize.net checkout token is missing. Return to the deposit confirmation page and try again.
                </div>
            @endif
        </div>
    </div>
@endsection
