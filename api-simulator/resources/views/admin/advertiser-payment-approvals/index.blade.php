@extends('layouts.admin')

@section('title', 'Advertiser Payment Approvals')

@section('content')
    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-700 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Advertiser Payment Approvals</h1>
            <p class="text-sm text-gray-500 mt-1">Review advertiser payment requests and approve or reject pending payouts.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        @php
            $statCards = [
                ['label' => 'Total Amount', 'value' => 'EUR ' . number_format($totalAmount, 2), 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'icon' => '<path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/>'],
                ['label' => 'Approved', 'value' => number_format($completedCount), 'color' => 'text-blue-700', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'icon' => '<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Pending Review', 'value' => number_format($pendingCount), 'color' => 'text-amber-700', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'icon' => '<path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'This Month', 'value' => 'EUR ' . number_format($thisMonthAmount, 2), 'color' => 'text-purple-700', 'bg' => 'bg-purple-50', 'border' => 'border-purple-200', 'icon' => '<path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
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
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4">
            <form method="GET" action="{{ route('admin.advertiser-payment-approvals') }}" class="flex items-center gap-2 flex-1 flex-wrap">
                <div class="relative flex-1 min-w-[220px] max-w-md">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ID, name, email, reference..."
                           class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <select name="user_id" class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Advertisers</option>
                    @foreach($users as $user)
                        @php
                            $userName = trim(($user->userProfile->first_name ?? '') . ' ' . ($user->userProfile->last_name ?? '')) ?: $user->email;
                        @endphp
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $userName }}
                        </option>
                    @endforeach
                </select>
                <select name="payment_method" class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Payment Types</option>
                    @foreach($paymentMethods as $value => $label)
                        <option value="{{ $value }}" {{ request('payment_method') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="status" class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium text-gray-600 hover:bg-gray-200 transition-colors">Filter</button>
                <a href="{{ route('admin.advertiser-payment-approvals') }}" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</a>
            </form>

            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Export
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-44 bg-white rounded-xl border border-gray-200 shadow-lg py-1 z-50" style="display:none;">
                    <button onclick="copyTable()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Copy</button>
                    <a href="{{ route('admin.advertiser-payment-approvals.export', request()->all()) }}" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">CSV</a>
                    <button onclick="window.print()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Print</button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="advertiserPaymentApprovalsTable" class="w-full text-sm">
                <thead>
                    <tr class="border-t border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Paid Date</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Name</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Email</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Amount</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Payment Type</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payouts as $payout)
                        @php
                            $name = trim(($payout->user->userProfile->first_name ?? '') . ' ' . ($payout->user->userProfile->last_name ?? '')) ?: 'Unknown';
                            $statusBadge = $payout->status_badge;
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors" id="row-{{ $payout->id }}">
                            <td class="px-4 py-3 font-medium text-gray-900">#{{ $payout->id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ optional($payout->processed_at)->format('M d, Y') ?: '-' }}</div>
                                <div class="text-xs text-gray-400">{{ optional($payout->processed_at)->format('H:i') ?: 'Awaiting review' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-semibold text-xs">
                                        {{ strtoupper(substr($name, 0, 2)) }}
                                    </div>
                                    <span class="font-medium text-gray-900">{{ $name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $payout->user->email ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-700"> €{{ number_format((float) $payout->amount, 2) }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $payout->payment_method_label }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="inline-flex items-center gap-1.5 flex-wrap justify-center">
                                    <button onclick="viewDetails({{ $payout->id }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-gray-50 border border-gray-200 text-gray-600 text-xs font-medium hover:bg-gray-100 transition-colors">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke="currentColor" stroke-width="1.5"/></svg>
                                        View Details
                                    </button>
                                    @if($payout->status === 'pending')
                                        <button onclick="approvePayment({{ $payout->id }})"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold hover:bg-emerald-100 transition-colors">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            Approve
                                        </button>
                                        <button onclick="rejectPayment({{ $payout->id }})"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs font-semibold hover:bg-red-100 transition-colors">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            Reject
                                        </button>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-xs font-medium {{ $statusBadge['bg'] }} {{ $statusBadge['text'] }} {{ $statusBadge['border'] }}">
                                            {{ ucfirst($payout->status) }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    <p class="text-sm text-gray-500">No advertiser payment approvals found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payouts->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $payouts->links() }}
            </div>
        @endif
    </div>

    <div id="detailsModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900/50 transition-opacity" onclick="document.getElementById('detailsModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg z-10">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900">Advertiser Payment Details</h3>
                    <button onclick="document.getElementById('detailsModal').classList.add('hidden')" class="p-1 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                </div>
                <div class="p-6" id="detailsContent">
                    <div class="flex items-center justify-center py-8">
                        <svg class="animate-spin h-6 w-6 text-brand-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function viewDetails(id) {
        const modal = document.getElementById('detailsModal');
        const content = document.getElementById('detailsContent');

        content.innerHTML = '<div class="flex items-center justify-center py-8"><svg class="animate-spin h-6 w-6 text-brand-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg></div>';
        modal.classList.remove('hidden');

        fetch(`/admin/advertiser-payment-approvals/${id}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            content.innerHTML = `
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Name</div>
                            <div class="text-sm font-medium text-gray-900">${data.name}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Email</div>
                            <div class="text-sm text-gray-700">${data.email}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Role</div>
                            <div class="text-sm text-gray-700 capitalize">${data.role}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Amount</div>
                            <div class="text-sm font-semibold text-emerald-700">EUR ${data.amount}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Payment Type</div>
                            <div class="text-sm text-gray-700">${data.payment_method}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Reference</div>
                            <div class="text-sm text-gray-700">${data.payment_reference || 'N/A'}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Status</div>
                            <div class="text-sm font-medium text-gray-900">${data.status}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Paid Date</div>
                            <div class="text-sm text-gray-700">${data.processed_at || 'Awaiting approval'}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Period</div>
                            <div class="text-sm text-gray-700">${data.period_start || 'N/A'} - ${data.period_end || 'N/A'}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Created</div>
                            <div class="text-sm text-gray-700">${data.created_at}</div>
                        </div>
                    </div>
                    ${data.notes ? `<div><div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Notes</div><div class="text-sm text-gray-700 bg-gray-50 rounded-lg p-3">${data.notes}</div></div>` : ''}
                </div>
            `;
        })
        .catch(() => {
            content.innerHTML = '<p class="text-sm text-red-500 text-center py-8">Failed to load advertiser payment details.</p>';
        });
    }

    function approvePayment(id) {
        if (!confirm('Are you sure you want to approve this advertiser payment?')) return;

        fetch(`/admin/advertiser-payment-approvals/${id}/approve`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            }
        })
        .catch(() => alert('Failed to approve advertiser payment.'));
    }

    function rejectPayment(id) {
        if (!confirm('Are you sure you want to reject this advertiser payment?')) return;

        fetch(`/admin/advertiser-payment-approvals/${id}/reject`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            }
        })
        .catch(() => alert('Failed to reject advertiser payment.'));
    }

    function copyTable() {
        const table = document.getElementById('advertiserPaymentApprovalsTable');
        const rows = table.querySelectorAll('tr');
        let text = '';

        rows.forEach((row) => {
            const cells = row.querySelectorAll('th, td');
            const rowData = [];

            cells.forEach((cell) => {
                rowData.push(cell.textContent.trim().replace(/\s+/g, ' '));
            });

            text += rowData.join('\t') + '\n';
        });

        navigator.clipboard.writeText(text).then(() => alert('Table data copied to clipboard!'));
    }
</script>
@endpush
