@extends('layouts.admin')

@section('title', 'Transactions')

@section('content')
    @if(session('success'))
        <div class="mb-4 flex items-center gap-2 rounded-xl border border-emerald-300 bg-emerald-50 p-3 text-sm text-emerald-700">
            <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Transactions</h1>
            <p class="mt-1 text-sm text-gray-500">Track the full admin ledger across deposits, withdrawals, spend, refunds, and adjustments.</p>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        @php
            $statCards = [
                ['label' => 'Total Amount', 'value' => $adminCurrency->format($totalAmount), 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200'],
                ['label' => 'Completed', 'value' => number_format($completedCount), 'color' => 'text-blue-700', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200'],
                ['label' => 'Pending', 'value' => number_format($pendingCount), 'color' => 'text-amber-700', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200'],
                ['label' => 'This Month', 'value' => $adminCurrency->format($thisMonthAmount), 'color' => 'text-purple-700', 'bg' => 'bg-purple-50', 'border' => 'border-purple-200'],
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
            <form method="GET" action="{{ route('admin.transactions') }}" class="flex flex-wrap items-center gap-2">
                <div class="relative min-w-[220px] flex-1">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ID, email, gateway ref, notes..." class="w-full rounded-lg border border-gray-200 py-2 pl-10 pr-4 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                </div>
                <select name="user_id" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        @php
                            $userName = trim(($user->userProfile->first_name ?? '') . ' ' . ($user->userProfile->last_name ?? '')) ?: $user->email;
                        @endphp
                        <option value="{{ $user->id }}" {{ (string) request('user_id') === (string) $user->id ? 'selected' : '' }}>
                            {{ $userName }}
                        </option>
                    @endforeach
                </select>
                <select name="type" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Types</option>
                    @foreach($types as $value => $label)
                        <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="gateway" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Gateways</option>
                    @foreach($gateways as $value => $label)
                        <option value="{{ $value }}" {{ request('gateway') === $value ? 'selected' : '' }}>{{ $label }}</option>
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
                <a href="{{ route('admin.transactions') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</a>
            </form>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Date</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">User</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Type</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Amount</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Gateway</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Reference</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transactions as $transaction)
                        @php
                            $name = trim(($transaction->user->userProfile->first_name ?? '') . ' ' . ($transaction->user->userProfile->last_name ?? '')) ?: ($transaction->user->email ?? 'Unknown');
                            $statusBadge = $transaction->status_badge;
                        @endphp
                        <tr class="transition-colors hover:bg-gray-50/50">
                            <td class="px-4 py-3 font-medium text-gray-900">#{{ $transaction->id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ optional($transaction->created_at)->format('M d, Y') ?: '-' }}</div>
                                <div class="text-xs text-gray-400">{{ optional($transaction->created_at)->format('H:i') ?: '-' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $name }}</div>
                                <div class="text-xs text-gray-400">{{ $transaction->user->email ?? 'N/A' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800">{{ $transaction->type_label }}</div>
                                @if($transaction->description)
                                    <div class="max-w-[240px] truncate text-xs text-gray-400">{{ $transaction->description }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-semibold {{ (float) $transaction->amount >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $adminCurrency->format((float) $transaction->amount) }}
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $transaction->gateway_label }}</td>
                            <td class="px-4 py-3">
                                <div class="text-gray-800">{{ $transaction->gateway_txn_id ?: '—' }}</div>
                                @if($transaction->admin_note)
                                    <div class="max-w-[220px] truncate text-xs text-gray-400">{{ $transaction->admin_note }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium {{ $statusBadge['bg'] }} {{ $statusBadge['text'] }} {{ $statusBadge['border'] }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="h-12 w-12 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/></svg>
                                    <p class="text-sm text-gray-500">No transactions found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
@endsection
