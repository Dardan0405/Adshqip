@extends('layouts.publisher')

@section('title', 'Payment History — ' . \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y'))

@section('content')
    @php $monthLabel = \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y'); @endphp

    {{-- Back + breadcrumb --}}
    <div class="flex items-center gap-2 mb-5 text-sm text-gray-500">
        <a href="{{ route('publisher.payment-history') }}" class="inline-flex items-center gap-1.5 hover:text-indigo-600 transition-colors">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                <path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Payment History
        </a>
        <svg class="w-3 h-3 text-gray-300" viewBox="0 0 24 24" fill="none">
            <path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        <span class="text-gray-800 font-medium">{{ $monthLabel }}</span>
    </div>

    {{-- Hero --}}
    <div class="rounded-2xl bg-gradient-to-r from-indigo-600 to-indigo-500 px-8 py-6 mb-6">
        <h1 class="text-2xl font-bold text-white">{{ $monthLabel }}</h1>
        <p class="text-indigo-200 text-sm mt-1">Invoice detail for this period.</p>

        <div class="mt-5 grid grid-cols-2 sm:grid-cols-3 gap-4">
            <div class="rounded-xl bg-white/15 border border-white/20 px-4 py-3">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-indigo-200 mb-1">Publisher Earnings</div>
                <div class="text-xl font-bold text-white">€{{ number_format($totalEarnings, 2) }}</div>
            </div>
            <div class="rounded-xl bg-white/15 border border-white/20 px-4 py-3">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-indigo-200 mb-1">Invoice Total</div>
                <div class="text-xl font-bold text-white">€{{ number_format($totalInvoiced, 2) }}</div>
            </div>
            <div class="rounded-xl bg-white/15 border border-white/20 px-4 py-3">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-indigo-200 mb-1">Invoices</div>
                <div class="text-xl font-bold text-white">{{ $rows->count() }}</div>
            </div>
        </div>
    </div>

    {{-- Invoices Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Invoices — {{ $monthLabel }}</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                        <th class="px-6 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Date</th>
                        <th class="px-6 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Invoice #</th>
                        <th class="px-6 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="px-6 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Earnings</th>
                        <th class="px-6 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Invoice Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rows as $row)
                        @php
                            $sc = $row['status_color'];
                        @endphp
                        <tr class="hover:bg-gray-50/40 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900">#{{ $row['id'] }}</td>
                            <td class="px-6 py-4 text-gray-700">
                                {{ $row['date']->format('d/m/Y') }}
                                <span class="block text-[10px] text-gray-400">{{ $row['date']->format('H:i') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($row['payout_id'])
                                    <a href="{{ route('publisher.payouts.show', $row['payout_id']) }}"
                                       class="text-indigo-600 hover:underline font-mono text-xs font-semibold">
                                        {{ $row['invoice_number'] }}
                                    </a>
                                @else
                                    <span class="font-mono text-xs text-gray-500">{{ $row['invoice_number'] }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-xs font-medium
                                    {{ $sc['bg'] }} {{ $sc['text'] }} {{ $sc['border'] }}">
                                    {{ ucfirst($row['status']) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-emerald-700">
                                @if($row['earnings'] > 0)
                                    €{{ number_format($row['earnings'], 2) }}
                                @else
                                    <span class="text-gray-300 font-normal">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-gray-900">
                                €{{ number_format($row['invoice_amount'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-200" viewBox="0 0 24 24" fill="none">
                                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                              stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                    <p class="text-sm text-gray-400">No invoices found for {{ $monthLabel }}.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($rows->isNotEmpty())
                    <tfoot>
                        <tr class="bg-gray-50 border-t-2 border-gray-200">
                            <td colspan="4" class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Totals</td>
                            <td class="px-6 py-3 text-right font-bold text-emerald-700">
                                €{{ number_format($totalEarnings, 2) }}
                            </td>
                            <td class="px-6 py-3 text-right font-bold text-gray-900">
                                €{{ number_format($totalInvoiced, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection
