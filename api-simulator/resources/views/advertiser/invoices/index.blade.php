@extends('layouts.advertiser')

@section('title', 'Invoice History')

@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Invoice History</h1>
            <p class="mt-1 text-sm text-gray-500">View, filter, export, and download invoices for your advertiser account.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($summary['missing_deposit_invoices'] > 0)
                <form method="POST" action="{{ route('advertiser.payments.invoices.generate') }}">
                    @csrf
                    <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                        Generate Missing
                    </button>
                </form>
            @endif
            <a href="{{ route('advertiser.payments.invoices.export', request()->query()) }}" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                Export CSV
            </a>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach([
            ['label' => 'Total Invoices', 'value' => number_format($summary['total_invoices']), 'color' => 'text-gray-900', 'bg' => 'bg-white', 'border' => 'border-gray-200'],
            ['label' => 'Total Amount', 'value' => $adminCurrency->format($summary['total_amount']), 'color' => 'text-blue-700', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200'],
            ['label' => 'Paid', 'value' => number_format($summary['paid_count']), 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200'],
            ['label' => 'Open', 'value' => number_format($summary['open_count']), 'color' => 'text-amber-700', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200'],
        ] as $card)
            <div class="rounded-lg border {{ $card['border'] }} {{ $card['bg'] }} p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                <div class="mt-2 text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    @if($summary['missing_deposit_invoices'] > 0)
        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            {{ number_format($summary['missing_deposit_invoices']) }} completed deposit(s) do not have invoice records yet.
        </div>
    @endif

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <div class="border-b border-gray-100 p-4">
            <form method="GET" action="{{ route('advertiser.payments.invoices') }}" class="flex flex-wrap items-center gap-2">
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Invoice number or ID" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <select name="status" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="date" name="start_date" value="{{ $filters['start_date'] }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <input type="date" name="end_date" value="{{ $filters['end_date'] }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Filter</button>
                <a href="{{ route('advertiser.payments.invoices') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</a>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/70">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Invoice</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Date</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Amount</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Tax</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Total</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($invoices as $invoice)
                        @php($badge = $invoice->status_color)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">{{ $invoice->invoice_number }}</div>
                                <div class="text-xs text-gray-400">#{{ $invoice->id }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $invoice->created_at?->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-400">Due {{ $invoice->due_date?->format('M d, Y') ?: '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-700">{{ $adminCurrency->format((float) $invoice->amount) }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">{{ $adminCurrency->format((float) $invoice->tax_amount) }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ $adminCurrency->format((float) $invoice->total_amount) }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $badge['bg'] }} {{ $badge['text'] }} {{ $badge['border'] }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                                @if($invoice->paid_at)
                                    <div class="mt-1 text-xs text-gray-400">Paid {{ $invoice->paid_at->format('M d, Y') }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('advertiser.payments.invoices.download', $invoice) }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50">
                                    Download
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="h-12 w-12 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    <p class="text-sm text-gray-500">No invoices found.</p>
                                </div>
                            </td>
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
@endsection
