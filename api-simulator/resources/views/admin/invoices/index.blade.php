@extends('layouts.admin')

@section('title', 'Invoices')

@section('content')
    @if(session('success'))
        <div class="mb-4 flex items-center gap-2 rounded-xl border border-emerald-300 bg-emerald-50 p-3 text-sm text-emerald-700">
            <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Invoices</h1>
            <p class="mt-1 text-sm text-gray-500">Track advertiser charge invoices and download invoice records from the admin billing ledger.</p>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        @php
            $statCards = [
                ['label' => 'Total Invoices', 'value' => number_format($totalInvoices), 'color' => 'text-gray-900', 'bg' => 'bg-gray-50', 'border' => 'border-gray-200'],
                ['label' => 'Invoice Amount', 'value' => $adminCurrency->format($totalAmount), 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200'],
                ['label' => 'Paid', 'value' => number_format($paidCount), 'color' => 'text-blue-700', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200'],
                ['label' => 'Paid This Month', 'value' => number_format($thisMonthCount), 'color' => 'text-purple-700', 'bg' => 'bg-purple-50', 'border' => 'border-purple-200'],
            ];
        @endphp
        @foreach($statCards as $card)
            <div class="rounded-xl border {{ $card['border'] }} {{ $card['bg'] }} p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                <div class="mt-2 text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="mb-6 rounded-xl border border-gray-200 bg-white">
        <div class="flex flex-col gap-3 p-4">
            <form method="GET" action="{{ route('admin.invoices') }}" class="flex flex-wrap items-center gap-2">
                <div class="relative min-w-[220px] flex-1">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search id, email, invoice number..." class="w-full rounded-lg border border-gray-200 py-2 pl-10 pr-4 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                </div>
                <select name="advertiser_id" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Advertisers</option>
                    @foreach($allAdvertisers as $advertiser)
                        <option value="{{ $advertiser->id }}" {{ request('advertiser_id') == $advertiser->id ? 'selected' : '' }}>
                            {{ $advertiser->email }}
                        </option>
                    @endforeach
                </select>
                <select name="status" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                <button type="submit" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-200">Filter</button>
                <a href="{{ route('admin.invoices') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</a>
                <a href="{{ route('admin.invoices.export', request()->all()) }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">CSV</a>
            </form>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Email</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Invoice Number</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Date</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Total</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($invoices as $invoice)
                        @php
                            $statusColor = $invoice->statusColor;
                        @endphp
                        <tr class="transition-colors hover:bg-gray-50/50">
                            <td class="px-4 py-3 font-medium text-gray-900">#{{ $invoice->id }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $invoice->user->email ?? 'N/A' }}</td>
                            <td class="px-4 py-3 font-mono text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</td>
                            <td class="px-4 py-3">
                                <div class="text-gray-900">{{ $invoice->created_at?->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-400">{{ $invoice->created_at?->format('H:i') }}</div>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-700">{{ $adminCurrency->format((float) $invoice->total_amount) }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusColor['bg'] }} {{ $statusColor['text'] }} {{ $statusColor['border'] }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('admin.invoices.download', $invoice->id) }}" class="inline-flex items-center gap-1 rounded-lg bg-gray-50 px-2.5 py-1.5 text-xs font-medium text-gray-600 border border-gray-200 hover:bg-gray-100">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"><path d="M12 10v6m0 0-3-3m3 3 3-3M5 20h14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    Download
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="h-12 w-12 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    <p class="text-sm text-gray-500">No advertiser invoices found.</p>
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
