@extends('layouts.publisher')

@section('title', 'Payment History')

@section('content')
    {{-- Hero --}}
    <div class="rounded-2xl bg-gradient-to-r from-indigo-600 to-indigo-500 px-8 py-6 mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Payment History</h1>
            <p class="text-indigo-200 text-sm mt-1">Monthly breakdown of your earnings and invoices.</p>
        </div>
        <a href="{{ route('publisher.payouts') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/15 border border-white/25 text-white text-sm font-medium hover:bg-white/25 transition-colors">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                <path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"
                      stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Request Payment
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900">Monthly Summary</h2>
            <span class="text-xs text-gray-400">{{ $rows->count() }} {{ Str::plural('month', $rows->count()) }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">#</th>
                        <th class="px-6 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Date</th>
                        <th class="px-6 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Publisher Earnings</th>
                        <th class="px-6 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Invoice Amount</th>
                        <th class="px-6 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Balance</th>
                        <th class="px-6 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rows as $i => $row)
                        @php
                            $date       = \Carbon\Carbon::createFromDate($row['year'], $row['month'], 1);
                            $balancePos = $row['balance'] >= 0;
                        @endphp
                        <tr class="hover:bg-gray-50/40 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-500 text-xs">{{ $i + 1 }}</td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-gray-900">{{ $date->format('F Y') }}</span>
                                <span class="ml-2 text-[10px] text-gray-400 font-medium">{{ $date->format('m / Y') }}</span>
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-emerald-700">
                                €{{ number_format($row['earnings'], 2) }}
                            </td>
                            <td class="px-6 py-4 text-right text-gray-700">
                                @if($row['invoice_amount'] > 0)
                                    <span class="font-medium text-gray-900">€{{ number_format($row['invoice_amount'], 2) }}</span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-bold {{ $balancePos ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $balancePos ? '' : '-' }}€{{ number_format(abs($row['balance']), 2) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('publisher.payment-history.show', [$row['year'], str_pad($row['month'], 2, '0', STR_PAD_LEFT)]) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-700 text-xs font-medium hover:bg-indigo-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none">
                                        <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke="currentColor" stroke-width="1.5"/>
                                    </svg>
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-200" viewBox="0 0 24 24" fill="none">
                                        <path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                              stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                    <p class="text-sm text-gray-400">No payment history yet.</p>
                                    <a href="{{ route('publisher.payouts') }}"
                                       class="text-xs text-indigo-600 hover:underline font-medium">Request your first payout</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($rows->isNotEmpty())
                    <tfoot>
                        <tr class="bg-gray-50 border-t-2 border-gray-200">
                            <td colspan="2" class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Totals</td>
                            <td class="px-6 py-3 text-right font-bold text-emerald-700">€{{ number_format($rows->sum('earnings'), 2) }}</td>
                            <td class="px-6 py-3 text-right font-bold text-gray-900">€{{ number_format($rows->sum('invoice_amount'), 2) }}</td>
                            <td class="px-6 py-3 text-right font-bold {{ ($rows->first()['balance'] ?? 0) >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                €{{ number_format(abs($rows->first()['balance'] ?? 0), 2) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection
