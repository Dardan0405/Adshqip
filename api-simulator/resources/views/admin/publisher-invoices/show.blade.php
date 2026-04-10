@extends('layouts.admin')

@section('title', 'Invoice Details')

@section('content')
    <div class="max-w-4xl mx-auto">
        {{-- Back button --}}
        <div class="mb-6">
            <a href="{{ route('admin.publisher-invoices') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Back to Invoices
            </a>
        </div>

        {{-- Invoice Card --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">
            {{-- Header --}}
            <div class="flex items-start justify-between mb-8 pb-8 border-b border-gray-200">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">INVOICE</h1>
                    <p class="text-sm text-gray-500">Invoice #{{ $invoice->invoice_number }}</p>
                </div>
                <div class="text-right">
                    @php $statusColor = $invoice->statusColor; @endphp
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold {{ $statusColor['bg'] }} {{ $statusColor['text'] }} border {{ $statusColor['border'] }}">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </div>
            </div>

            {{-- Dates --}}
            <div class="grid grid-cols-2 gap-8 mb-8">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Invoice Date</p>
                    <p class="text-lg font-medium text-gray-900">{{ $invoice->created_at->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Due Date</p>
                    <p class="text-lg font-medium text-gray-900">{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}</p>
                </div>
            </div>

            {{-- Bill To --}}
            <div class="mb-8 p-4 rounded-lg bg-gray-50">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Bill To</p>
                <p class="text-lg font-bold text-gray-900">
                    {{ $invoice->user->userProfile ? trim($invoice->user->userProfile->first_name . ' ' . $invoice->user->userProfile->last_name) : 'N/A' }}
                </p>
                <p class="text-sm text-gray-600">{{ $invoice->user->email }}</p>
            </div>

            {{-- Invoice Details --}}
            <div class="mb-8">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Description</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-100">
                            <td class="px-4 py-4 text-gray-900">Publisher Payout - {{ $invoice->created_at->format('F Y') }}</td>
                            <td class="px-4 py-4 text-right font-medium text-gray-900">€{{ number_format($invoice->amount, 2) }}</td>
                        </tr>
                        @if($invoice->tax_amount > 0)
                        <tr class="border-b border-gray-100">
                            <td class="px-4 py-4 text-gray-900">Tax</td>
                            <td class="px-4 py-4 text-right font-medium text-gray-900">€{{ number_format($invoice->tax_amount, 2) }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Total --}}
            <div class="flex justify-end mb-8">
                <div class="w-64">
                    <div class="flex items-center justify-between p-4 rounded-lg bg-brand-50 border border-brand-200">
                        <span class="text-sm font-semibold text-brand-900 uppercase">Total Amount</span>
                        <span class="text-2xl font-bold text-brand-700">€{{ number_format($invoice->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Payment Info --}}
            @if($invoice->paid_at)
            <div class="mb-8 p-4 rounded-lg bg-emerald-50 border border-emerald-200">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-emerald-700" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <span class="text-sm font-semibold text-emerald-900">Payment Received</span>
                </div>
                <p class="text-sm text-emerald-700">Paid on {{ $invoice->paid_at->format('M d, Y \a\t H:i') }}</p>
            </div>
            @endif

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.publisher-invoices.download', $invoice->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Download Invoice
                </a>
            </div>
        </div>
    </div>
@endsection
