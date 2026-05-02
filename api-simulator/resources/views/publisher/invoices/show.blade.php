@extends('layouts.publisher')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <a href="{{ route('publisher.invoices') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Back to Invoices History
        </a>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-8">
        <div class="flex items-start justify-between gap-4 border-b border-gray-200 pb-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Invoice</p>
                <h1 class="mt-2 text-3xl font-bold text-gray-900">{{ $invoice->invoice_number }}</h1>
                <p class="mt-1 text-sm text-gray-500">Publisher payout invoice detail.</p>
            </div>
            @php $badge = $invoice->status_color; @endphp
            <span class="inline-flex rounded-full border px-3 py-1.5 text-sm font-semibold {{ $badge['bg'] }} {{ $badge['text'] }} {{ $badge['border'] }}">
                {{ ucfirst($invoice->status) }}
            </span>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Invoice Date</p>
                <p class="mt-2 text-sm font-medium text-gray-900">{{ $invoice->created_at?->format('Y-m-d H:i') }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Due Date</p>
                <p class="mt-2 text-sm font-medium text-gray-900">{{ $invoice->due_date?->format('Y-m-d') ?? 'N/A' }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Paid At</p>
                <p class="mt-2 text-sm font-medium text-gray-900">{{ $invoice->paid_at?->format('Y-m-d H:i') ?? 'Not paid yet' }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Currency</p>
                <p class="mt-2 text-sm font-medium text-gray-900">{{ $invoice->currency }}</p>
            </div>
        </div>

        <div class="mt-6 rounded-xl border border-gray-200 bg-white overflow-hidden">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-sm font-bold text-gray-900">Bill To</h2>
            </div>
            <div class="grid grid-cols-1 gap-4 px-5 py-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Name</p>
                    <p class="mt-2 text-sm text-gray-900">
                        {{ trim(($invoice->user->userProfile->first_name ?? '') . ' ' . ($invoice->user->userProfile->last_name ?? '')) ?: $invoice->user->email }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Email</p>
                    <p class="mt-2 text-sm text-gray-900">{{ $invoice->user->email }}</p>
                </div>
            </div>
        </div>

        <div class="mt-6 rounded-xl border border-gray-200 bg-white overflow-hidden">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-sm font-bold text-gray-900">Invoice Breakdown</h2>
            </div>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-100">
                    <tr>
                        <td class="px-5 py-4 text-gray-600">Publisher Payout</td>
                        <td class="px-5 py-4 text-right font-medium text-gray-900">EUR {{ number_format((float) $invoice->amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="px-5 py-4 text-gray-600">Tax</td>
                        <td class="px-5 py-4 text-right font-medium text-gray-900">EUR {{ number_format((float) $invoice->tax_amount, 2) }}</td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="px-5 py-4 text-sm font-semibold text-gray-900">Total</td>
                        <td class="px-5 py-4 text-right text-lg font-bold text-emerald-700">EUR {{ number_format((float) $invoice->total_amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex flex-wrap items-center justify-end gap-2 border-t border-gray-200 pt-6">
            <a href="{{ route('publisher.invoices.download', $invoice->id) }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                Download Invoice
            </a>
        </div>
    </div>
</div>
@endsection
