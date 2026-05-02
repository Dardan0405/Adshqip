@extends('layouts.publisher')

@section('title', 'Invoice History')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Invoice History</h1>
                <p class="mt-1 text-sm text-gray-500">All publisher payout invoices for your account.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('publisher.payment-history') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Payment History
                </a>
                <a href="{{ route('publisher.invoices.export', request()->all()) }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                    CSV Export
                </a>
            </div>
        </div>
    </div>

    @php
        $summaryCards = [
            ['label' => 'Total Invoices', 'value' => number_format($summary['total_invoices']), 'style' => 'border-gray-200 bg-white text-gray-900'],
            ['label' => 'Invoice Amount', 'value' => 'EUR ' . number_format($summary['total_amount'], 2), 'style' => 'border-emerald-200 bg-emerald-50 text-emerald-700'],
            ['label' => 'Paid', 'value' => number_format($summary['paid_count']), 'style' => 'border-blue-200 bg-blue-50 text-blue-700'],
            ['label' => 'Draft', 'value' => number_format($summary['draft_count']), 'style' => 'border-amber-200 bg-amber-50 text-amber-700'],
            ['label' => 'Sent', 'value' => number_format($summary['sent_count']), 'style' => 'border-purple-200 bg-purple-50 text-purple-700'],
            ['label' => 'Overdue', 'value' => number_format($summary['overdue_count']), 'style' => 'border-rose-200 bg-rose-50 text-rose-700'],
        ];
    @endphp

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-6">
        @foreach($summaryCards as $card)
            <div class="rounded-xl border p-4 {{ $card['style'] }}">
                <p class="text-[10px] font-semibold uppercase tracking-wider opacity-70">{{ $card['label'] }}</p>
                <p class="mt-2 text-xl font-bold">{{ $card['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-100 p-4">
            <form method="GET" action="{{ route('publisher.invoices') }}" class="flex flex-wrap items-center gap-2">
                <div class="relative min-w-[240px] flex-1">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Search invoice number, ID, or email..." class="w-full rounded-lg border border-gray-200 py-2 pl-10 pr-4 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                </div>

                <select name="status" onchange="this.form.submit()" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="all">All Statuses</option>
                    <option value="draft" {{ $filters['status'] === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="sent" {{ $filters['status'] === 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="paid" {{ $filters['status'] === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="overdue" {{ $filters['status'] === 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="cancelled" {{ $filters['status'] === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>

                <input type="date" name="start_date" value="{{ $filters['start_date'] }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                <input type="date" name="end_date" value="{{ $filters['end_date'] }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">

                <button type="submit" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-200">Filter</button>
                <a href="{{ route('publisher.invoices') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</a>
                <a href="{{ route('publisher.invoices.export') }}" class="ml-auto rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">CSV</a>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/70 text-[10px] font-semibold uppercase tracking-wider text-gray-400">
                        <th class="px-4 py-3 text-left">Invoice</th>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Due</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                        <th class="px-4 py-3 text-right">Tax</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($invoices as $invoice)
                        @php $badge = $invoice->status_color; @endphp
                        <tr class="hover:bg-gray-50/60">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $invoice->invoice_number }}</div>
                                <div class="mt-0.5 text-xs text-gray-400">#{{ $invoice->id }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $invoice->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $invoice->due_date?->format('Y-m-d') ?? 'N/A' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $badge['bg'] }} {{ $badge['text'] }} {{ $badge['border'] }}">{{ ucfirst($invoice->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-700">EUR {{ number_format((float) $invoice->amount, 2) }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">EUR {{ number_format((float) $invoice->tax_amount, 2) }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-700">EUR {{ number_format((float) $invoice->total_amount, 2) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('publisher.invoices.show', $invoice->id) }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50">Details</a>
                                    <a href="{{ route('publisher.invoices.download', $invoice->id) }}" class="rounded-lg border border-blue-200 px-3 py-1.5 text-xs font-semibold text-blue-600 hover:bg-blue-50">Download</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-14 text-center text-sm text-gray-400">No invoices found for these filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
