@extends('layouts.publisher')

@section('title', 'Invoice ' . ($invoice?->invoice_number ?? $payout->payment_reference))

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('publisher.payouts') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-800 transition-colors">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Back to Payouts
        </a>
    </div>

    @php
        $statusColors = [
            'draft'      => ['bg'=>'bg-gray-100','text'=>'text-gray-700','ring'=>'ring-gray-200'],
            'sent'       => ['bg'=>'bg-blue-50','text'=>'text-blue-700','ring'=>'ring-blue-200'],
            'paid'       => ['bg'=>'bg-emerald-50','text'=>'text-emerald-700','ring'=>'ring-emerald-200'],
            'overdue'    => ['bg'=>'bg-red-50','text'=>'text-red-700','ring'=>'ring-red-200'],
            'cancelled'  => ['bg'=>'bg-gray-100','text'=>'text-gray-500','ring'=>'ring-gray-200'],
            'pending'    => ['bg'=>'bg-amber-50','text'=>'text-amber-700','ring'=>'ring-amber-200'],
            'processing' => ['bg'=>'bg-blue-50','text'=>'text-blue-700','ring'=>'ring-blue-200'],
            'completed'  => ['bg'=>'bg-emerald-50','text'=>'text-emerald-700','ring'=>'ring-emerald-200'],
            'failed'     => ['bg'=>'bg-red-50','text'=>'text-red-700','ring'=>'ring-red-200'],
        ];
        $invStatus  = $invoice?->status ?? $payout->status;
        $sc         = $statusColors[$invStatus] ?? ['bg'=>'bg-gray-100','text'=>'text-gray-700','ring'=>'ring-gray-200'];
        $invoiceNum = $invoice?->invoice_number ?? $payout->payment_reference;
        $invoiceAmt = $invoice ? $invoice->total_amount : $payout->amount;
        $currency   = $invoice?->currency ?? $payout->currency ?? 'EUR';
        $symbol     = $currency === 'EUR' ? 'EUR ' : ($currency === 'USD' ? 'USD ' : $currency . ' ');
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="px-8 pt-8 pb-6 border-b border-gray-100">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-brand-600 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 133 102" class="w-6 h-auto"><image width="133" height="102" href="{{ asset('AdshqipSVG.svg') }}"></image></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">Adshqip</p>
                                <p class="text-xs text-gray-400">Publisher Platform</p>
                            </div>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900">INVOICE</h1>
                        <p class="text-sm font-mono text-gray-500 mt-1"># {{ $invoiceNum }}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold ring-1 {{ $sc['bg'] }} {{ $sc['text'] }} {{ $sc['ring'] }}">
                            {{ ucfirst($invStatus) }}
                        </span>
                        <p class="text-xs text-gray-400 mt-2">Issued {{ $payout->created_at->format('d M Y') }}</p>
                        @if($invoice?->due_date)
                            <p class="text-xs text-gray-400">Due {{ $invoice->due_date->format('d M Y') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="px-8 py-6 border-b border-gray-100 grid grid-cols-2 gap-6">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Bill To</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $displayName }}</p>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    @if($profile?->company_name)
                        <p class="text-sm text-gray-500">{{ $profile->company_name }}</p>
                    @endif
                    @if($profile?->city || $profile?->country_code)
                        <p class="text-sm text-gray-500">{{ implode(', ', array_filter([$profile?->city, $profile?->country_code])) }}</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Payout Period</p>
                    @if($payout->period_start && $payout->period_end)
                        <p class="text-sm text-gray-700">{{ \Carbon\Carbon::parse($payout->period_start)->format('d M Y') }}</p>
                        <p class="text-sm text-gray-400">to</p>
                        <p class="text-sm text-gray-700">{{ \Carbon\Carbon::parse($payout->period_end)->format('d M Y') }}</p>
                    @else
                        <p class="text-sm text-gray-400">-</p>
                    @endif
                </div>
            </div>

            <div class="px-8 py-6">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider border-b border-gray-100 pb-2">
                            <th class="pb-3">Description</th>
                            <th class="pb-3 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-50">
                            <td class="py-4">
                                <p class="font-medium text-gray-800">Publisher Earnings Payout</p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    Period:
                                    @if($payout->period_start && $payout->period_end)
                                        {{ \Carbon\Carbon::parse($payout->period_start)->format('d M Y') }} - {{ \Carbon\Carbon::parse($payout->period_end)->format('d M Y') }}
                                    @else
                                        {{ $payout->created_at->format('d M Y') }}
                                    @endif
                                </p>
                                @if($payout->notes)
                                    <p class="text-xs text-gray-400 mt-0.5 italic">Note: {{ $payout->notes }}</p>
                                @endif
                            </td>
                            <td class="py-4 text-right font-semibold text-gray-800">{{ $symbol }}{{ number_format((float)$payout->amount, 2) }}</td>
                        </tr>
                        @if($invoice && $invoice->tax_amount > 0)
                            <tr class="border-b border-gray-50">
                                <td class="py-3 text-gray-500">Tax</td>
                                <td class="py-3 text-right text-gray-500">{{ $symbol }}{{ number_format((float)$invoice->tax_amount, 2) }}</td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-200">
                            <td class="pt-4 text-sm font-bold text-gray-900">Total</td>
                            <td class="pt-4 text-right text-lg font-bold text-gray-900">{{ $symbol }}{{ number_format((float)$invoiceAmt, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($invoice?->paid_at)
                <div class="px-8 py-4 bg-emerald-50 border-t border-emerald-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <p class="text-sm text-emerald-700 font-medium">Paid on {{ $invoice->paid_at->format('d M Y, H:i') }}</p>
                </div>
            @endif
        </div>

        <div class="space-y-4">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5 space-y-4">
                <h3 class="text-sm font-bold text-gray-900">Payout Details</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Payout ID</dt>
                        <dd class="font-mono text-gray-800">#{{ $payout->id }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Status</dt>
                        <dd>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 {{ $sc['bg'] }} {{ $sc['text'] }} {{ $sc['ring'] }}">
                                {{ ucfirst($payout->status) }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Method</dt>
                        <dd class="text-gray-800">{{ $payout->payment_method_label }}</dd>
                    </div>
                    @if($payout->payment_provider)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Provider</dt>
                            <dd class="text-gray-800">{{ ucfirst($payout->payment_provider) }}</dd>
                        </div>
                    @endif
                    @if($payout->gateway_reference)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Gateway Ref</dt>
                            <dd class="font-mono text-gray-800 text-right break-all">{{ $payout->gateway_reference }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Currency</dt>
                        <dd class="text-gray-800">{{ $currency }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Requested</dt>
                        <dd class="text-gray-800">{{ $payout->created_at->format('d M Y') }}</dd>
                    </div>
                    @if($payout->processed_at)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Processed</dt>
                            <dd class="text-gray-800">{{ $payout->processed_at->format('d M Y') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            @php
                $details = $profile?->payout_details ?? [];
                $providerKey = $payout->payment_provider ?: $payout->payment_method;
                $methodKey = match($providerKey) {
                    'bankwire' => 'bankwire',
                    'paypal' => 'paypal',
                    'bitcoin' => 'bitcoin',
                    'stripe' => 'stripe',
                    'authorize_net' => 'authorize_net',
                    'wire_transfer' => 'bankwire',
                    'crypto' => 'bitcoin',
                    'payoneer' => 'payoneer',
                    default => null,
                };
                $creds = $methodKey ? ($details[$methodKey] ?? []) : [];
                $credFields = match($methodKey) {
                    'bankwire' => [
                        'Account Holder' => $creds['account_holder'] ?? null,
                        'Bank Name' => $creds['bank_name'] ?? null,
                        'IBAN' => $creds['iban'] ?? null,
                        'SWIFT / BIC' => $creds['swift_code'] ?? null,
                        'Bank Address' => $creds['bank_address'] ?? null,
                    ],
                    'paypal' => [
                        'PayPal Email' => $creds['email'] ?? null,
                    ],
                    'bitcoin' => [
                        'Wallet Address' => $creds['wallet_address'] ?? null,
                        'Network' => $creds['network'] ?? null,
                    ],
                    'stripe' => [
                        'Stripe Account ID' => $creds['account_id'] ?? null,
                        'Stripe Email' => $creds['account_email'] ?? null,
                    ],
                    'authorize_net' => [
                        'Login ID' => $creds['login_id'] ?? null,
                        'Transaction Key' => $creds['transaction_key'] ?? null,
                        'Mode' => $creds['mode'] ?? null,
                    ],
                    'payoneer' => [
                        'Payoneer Email' => $creds['email'] ?? null,
                    ],
                    default => [],
                };
                $credFields = array_filter($credFields, fn($v) => filled($v));
            @endphp

            <div class="rounded-2xl border border-amber-200 bg-amber-50 shadow-sm p-5">
                <div class="flex items-center gap-2 mb-4">
                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <h3 class="text-sm font-bold text-amber-900">Payment Credentials</h3>
                </div>
                <p class="text-xs text-amber-700 mb-4 leading-relaxed">Verify the payout details before finance processes the transfer.</p>

                @if(empty($credFields))
                    <div class="rounded-xl bg-white border border-amber-200 px-4 py-3 text-sm text-amber-800">
                        No credentials saved for this method. <a href="{{ route('publisher.payment-settings') }}" class="font-semibold underline hover:no-underline">Update payment settings -></a>
                    </div>
                @else
                    <dl class="space-y-3">
                        @foreach($credFields as $label => $value)
                            <div class="rounded-xl bg-white border border-amber-200 px-4 py-3">
                                <dt class="text-[11px] font-semibold text-amber-700 uppercase tracking-wider mb-1">{{ $label }}</dt>
                                <dd class="text-sm font-mono text-gray-800 break-all select-all">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif

                <a href="{{ route('publisher.payment-settings') }}"
                   class="mt-4 flex items-center justify-center gap-1.5 w-full rounded-xl border border-amber-300 bg-white px-4 py-2 text-xs font-semibold text-amber-800 hover:bg-amber-100 transition-colors">
                    Update Payment Settings
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
