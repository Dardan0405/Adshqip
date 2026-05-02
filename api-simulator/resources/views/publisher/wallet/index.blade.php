@extends('layouts.publisher')

@section('title', 'Manage Wallet')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manage Wallet</h1>
                <p class="mt-1 text-sm text-gray-500">Track earnings, payout requests, invoices, and available balance.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('publisher.payouts') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"><path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Request Payment
                </a>
                <a href="{{ route('publisher.payment-settings') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" stroke="currentColor" stroke-width="1.5"/></svg>
                    Payout Settings
                </a>
            </div>
        </div>
    </div>

    @php
        $activeMethod = $profile?->payout_method ?? 'bankwire';
        $summaryCards = [
            ['label' => 'Available Balance', 'value' => 'EUR ' . number_format($summary['available'], 2), 'style' => 'border-emerald-200 bg-emerald-50 text-emerald-700'],
            ['label' => 'Total Earned', 'value' => 'EUR ' . number_format($summary['earned'], 2), 'style' => 'border-gray-200 bg-white text-gray-900'],
            ['label' => 'Pending Payouts', 'value' => 'EUR ' . number_format($summary['pending'], 2), 'style' => 'border-amber-200 bg-amber-50 text-amber-700'],
            ['label' => 'Paid Out', 'value' => 'EUR ' . number_format($summary['paid'], 2), 'style' => 'border-blue-200 bg-blue-50 text-blue-700'],
            ['label' => 'This Month', 'value' => 'EUR ' . number_format($summary['this_month'], 2), 'style' => 'border-purple-200 bg-purple-50 text-purple-700'],
            ['label' => 'Invoiced', 'value' => 'EUR ' . number_format($summary['invoiced'], 2), 'style' => 'border-gray-200 bg-white text-gray-900'],
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

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Active Payout Method</p>
                    <p class="mt-2 text-lg font-bold text-gray-900">{{ $methodLabels[$activeMethod] ?? ucfirst($activeMethod) }}</p>
                </div>
                <a href="{{ route('publisher.payment-settings') }}" class="text-xs font-medium text-brand-600 hover:underline">Change</a>
            </div>
            <p class="mt-3 text-sm text-gray-500">This method will be used when your next payout request is processed.</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Balance Formula</p>
            <div class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Earned</span><span class="font-semibold text-gray-900">EUR {{ number_format($summary['earned'], 2) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Committed</span><span class="font-semibold text-gray-900">- EUR {{ number_format($summary['committed'], 2) }}</span></div>
                <div class="border-t border-gray-100 pt-2 flex justify-between"><span class="font-medium text-gray-700">Available</span><span class="font-bold text-emerald-700">EUR {{ number_format($summary['available'], 2) }}</span></div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Quick Links</p>
            <div class="mt-3 grid grid-cols-2 gap-2">
                <a href="{{ route('publisher.payouts') }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Payments</a>
                <a href="{{ route('publisher.invoices') }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Invoices</a>
                <a href="{{ route('publisher.earnings') }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Earnings</a>
                <a href="{{ route('publisher.payment-settings') }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Settings</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-sm font-bold text-gray-900">Recent Payouts</h2>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentPayouts as $payout)
                    @php $badge = $payout->status_badge; @endphp
                    <a href="{{ route('publisher.payouts.show', $payout->id) }}" class="flex items-center justify-between gap-4 px-5 py-3 hover:bg-gray-50">
                        <div>
                            <p class="font-medium text-gray-900">{{ $payout->payment_reference ?: 'Payout #' . $payout->id }}</p>
                            <p class="mt-0.5 text-xs text-gray-400">{{ $payout->created_at?->format('Y-m-d H:i') }} · {{ $payout->payment_method_label }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-900">EUR {{ number_format((float) $payout->amount, 2) }}</p>
                            <span class="mt-1 inline-flex rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $badge['bg'] }} {{ $badge['text'] }} {{ $badge['border'] }}">{{ ucfirst($payout->status) }}</span>
                        </div>
                    </a>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-gray-400">No payout requests yet.</div>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-sm font-bold text-gray-900">Recent Invoices</h2>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentInvoices as $invoice)
                    @php $badge = $invoice->status_color; @endphp
                    <div class="flex items-center justify-between gap-4 px-5 py-3">
                        <div>
                            <p class="font-medium text-gray-900">{{ $invoice->invoice_number }}</p>
                            <p class="mt-0.5 text-xs text-gray-400">{{ $invoice->created_at?->format('Y-m-d H:i') }} · Due {{ $invoice->due_date?->format('Y-m-d') ?? 'N/A' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-900">{{ $invoice->currency }} {{ number_format((float) $invoice->total_amount, 2) }}</p>
                            <span class="mt-1 inline-flex rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $badge['bg'] }} {{ $badge['text'] }} {{ $badge['border'] }}">{{ ucfirst($invoice->status) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-gray-400">No payout invoices yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-100 p-4">
            <form method="GET" action="{{ route('publisher.wallet') }}" class="flex flex-wrap items-center gap-2">
                <div class="relative min-w-[220px] flex-1">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search reference, status, description..." class="w-full rounded-lg border border-gray-200 py-2 pl-10 pr-4 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                </div>

                <select name="type" onchange="this.form.submit()" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Activity</option>
                    <option value="earning" {{ request('type') === 'earning' ? 'selected' : '' }}>Earnings</option>
                    <option value="payout" {{ request('type') === 'payout' ? 'selected' : '' }}>Payouts</option>
                    <option value="invoice" {{ request('type') === 'invoice' ? 'selected' : '' }}>Invoices</option>
                </select>

                <select name="status" onchange="this.form.submit()" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Statuses</option>
                    @foreach(['earned','pending','processing','completed','failed','cancelled','draft','sent','paid','overdue'] as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>

                <input type="date" name="start_date" value="{{ request('start_date', $defaults['start_date']) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                <input type="date" name="end_date" value="{{ request('end_date', $defaults['end_date']) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">

                <button type="submit" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-200">Search</button>
                <a href="{{ route('publisher.wallet') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</a>
                <a href="{{ route('publisher.wallet.export', request()->all()) }}" class="ml-auto rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">CSV</a>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/70 text-[10px] font-semibold uppercase tracking-wider text-gray-400">
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Type</th>
                        <th class="px-4 py-3 text-left">Reference</th>
                        <th class="px-4 py-3 text-left">Description</th>
                        <th class="px-4 py-3 text-right">Credit</th>
                        <th class="px-4 py-3 text-right">Debit</th>
                        <th class="px-4 py-3 text-right">Impact</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($activity as $row)
                        <tr class="hover:bg-gray-50/60">
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $row['date'] }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $row['type_label'] }}</div>
                                <span class="mt-1 inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-600">{{ ucfirst($row['status']) }}</span>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $row['reference'] }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $row['description'] }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-700">{{ $row['credit'] > 0 ? 'EUR ' . number_format($row['credit'], 2) : '-' }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-red-600">{{ $row['debit'] > 0 ? 'EUR ' . number_format($row['debit'], 2) : '-' }}</td>
                            <td class="px-4 py-3 text-right font-bold {{ $row['net'] >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                                {{ $row['net'] >= 0 ? '+' : '-' }}EUR {{ number_format(abs($row['net']), 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-14 text-center text-sm text-gray-400">No wallet activity found for these filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($activity->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $activity->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
