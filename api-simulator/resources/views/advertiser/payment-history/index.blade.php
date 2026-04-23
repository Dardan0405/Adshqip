@extends('layouts.advertiser')

@section('title', 'Payment History')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Payment History</h1>
            <p class="text-sm text-gray-500 mt-1">Monthly deposits and campaign spend for your advertiser account.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        @php
            $statCards = [
                ['label' => 'Total Deposits', 'value' => $adminCurrency->format($summary['total_deposits']), 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'icon' => '<path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/>'],
                ['label' => 'Total Spend', 'value' => $adminCurrency->format($summary['total_spend']), 'color' => 'text-rose-700', 'bg' => 'bg-rose-50', 'border' => 'border-rose-200', 'icon' => '<path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'],
                ['label' => 'Net Balance', 'value' => $adminCurrency->format($summary['net_balance']), 'color' => 'text-blue-700', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'icon' => '<path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'This Month Deposits', 'value' => $adminCurrency->format($summary['current_month_deposits']), 'color' => 'text-violet-700', 'bg' => 'bg-violet-50', 'border' => 'border-violet-200', 'icon' => '<path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
            ];
        @endphp
        @foreach($statCards as $card)
            <div class="rounded-xl border {{ $card['border'] }} {{ $card['bg'] }} p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                    <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none">{!! $card['icon'] !!}</svg>
                </div>
                <div class="text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="p-4 border-b border-gray-100">
            <form method="GET" action="{{ route('advertiser.payments.history') }}" class="flex flex-wrap items-center gap-2">
                <input type="month" name="start_month" value="{{ $filters['start_month'] }}" class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                <input type="month" name="end_month" value="{{ $filters['end_month'] }}" class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-800 transition-colors">Filter</button>
                <a href="{{ route('advertiser.payments.history') }}" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</a>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table id="paymentHistoryTable" class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Date</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Deposit Amount</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Spend Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payments as $payment)
                        <tr x-data="{ open: false }" class="align-top">
                            <td colspan="3" class="p-0">
                                <button type="button" @click="open = !open" class="w-full grid grid-cols-[1fr,160px,160px] max-sm:grid-cols-1 gap-3 px-4 py-3 text-left hover:bg-gray-50/80 transition-colors">
                                    <span class="flex items-center gap-3">
                                        <span class="w-8 h-8 rounded-lg bg-brand-50 text-brand-700 flex items-center justify-center">
                                            <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-90' : ''" viewBox="0 0 24 24" fill="none"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </span>
                                        <span>
                                            <span class="block font-semibold text-gray-900">#{{ $payment->id }} · {{ $payment->month_formatted }}</span>
                                            <span class="block text-xs text-gray-400">{{ $payment->details->count() }} daily {{ Str::plural('entry', $payment->details->count()) }}</span>
                                        </span>
                                    </span>
                                    <span class="text-right max-sm:text-left font-semibold text-emerald-700">{{ $adminCurrency->format($payment->total_deposits) }}</span>
                                    <span class="text-right max-sm:text-left font-semibold text-rose-700">{{ $adminCurrency->format($payment->total_spend) }}</span>
                                </button>

                                <div x-show="open" x-transition class="bg-gray-50/70 border-t border-gray-100" style="display: none;">
                                    <div class="overflow-x-auto px-4 py-3">
                                        <table class="w-full text-xs bg-white rounded-lg overflow-hidden border border-gray-100">
                                            <thead>
                                                <tr class="bg-gray-50">
                                                    <th class="px-3 py-2 text-left font-semibold uppercase tracking-wider text-gray-400">Date</th>
                                                    <th class="px-3 py-2 text-right font-semibold uppercase tracking-wider text-gray-400">Paid</th>
                                                    <th class="px-3 py-2 text-right font-semibold uppercase tracking-wider text-gray-400">Spend</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach($payment->details as $detail)
                                                    <tr>
                                                        <td class="px-3 py-2 font-medium text-gray-700">{{ $detail->date_formatted }}</td>
                                                        <td class="px-3 py-2 text-right font-semibold text-emerald-700">{{ $adminCurrency->format($detail->paid) }}</td>
                                                        <td class="px-3 py-2 text-right font-semibold text-rose-700">{{ $adminCurrency->format($detail->spend) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    <p class="text-sm text-gray-500">No payment history found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
@endsection
