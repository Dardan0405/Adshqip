@extends('layouts.admin')

@section('title', 'Requests')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Requests</h1>
            <p class="text-sm text-gray-500 mt-1">Review advertiser direct campaign requests and approve or reject them from one place.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
        @php
            $cards = [
                ['label' => 'Total Requests', 'value' => number_format($totalRequests), 'color' => 'text-gray-900', 'bg' => 'bg-gray-50', 'border' => 'border-gray-200'],
                ['label' => 'Approved', 'value' => number_format($approvedCount), 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200'],
                ['label' => 'Pending', 'value' => number_format($pendingCount), 'color' => 'text-amber-700', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200'],
                ['label' => 'Rejected', 'value' => number_format($rejectedCount), 'color' => 'text-red-700', 'bg' => 'bg-red-50', 'border' => 'border-red-200'],
                ['label' => 'Budget', 'value' => $adminCurrency->format($totalBudget), 'color' => 'text-blue-700', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200'],
            ];
        @endphp

        @foreach($cards as $card)
            <div class="rounded-xl border {{ $card['border'] }} {{ $card['bg'] }} p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                <div class="mt-2 text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-xl border border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4">
            <form method="GET" action="{{ route('admin.requests') }}" class="flex items-center gap-2 flex-1 flex-wrap">
                <div class="relative flex-1 min-w-[220px] max-w-md">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search request, advertiser, email..."
                           class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <select name="user_id" class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Advertisers</option>
                    @foreach($users as $user)
                        @php
                            $userName = trim(($user->userProfile->first_name ?? '') . ' ' . ($user->userProfile->last_name ?? '')) ?: ($user->userProfile->company_name ?? $user->email);
                        @endphp
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $userName }}</option>
                    @endforeach
                </select>
                <select name="campaign_type" class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Types</option>
                    @foreach($campaignTypes as $value => $label)
                        <option value="{{ $value }}" {{ request('campaign_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="status" class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium text-gray-600 hover:bg-gray-200 transition-colors">Filter</button>
                <a href="{{ route('admin.requests') }}" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</a>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-t border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Request</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Advertiser</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Type</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Objective</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Budget</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($requests as $requestItem)
                        @php
                            $campaignTypeLabel = $campaignTypes[$requestItem->pricing_model] ?? strtoupper((string) $requestItem->pricing_model);
                            $objectiveLabel = ucwords(str_replace('_', ' ', (string) $requestItem->marketing_objective));
                            $statusBadge = match ($requestItem->status) {
                                'active' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'label' => 'Approved'],
                                'rejected' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'border' => 'border-red-200', 'label' => 'Rejected'],
                                default => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'label' => 'Pending Review'],
                            };
                            $advertiserName = trim(($requestItem->advertiser->userProfile->first_name ?? '') . ' ' . ($requestItem->advertiser->userProfile->last_name ?? '')) ?: ($requestItem->advertiser->userProfile->company_name ?? $requestItem->advertiser->email ?? 'N/A');
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-900">#{{ $requestItem->id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $requestItem->name }}</div>
                                <div class="text-xs text-gray-400">{{ $requestItem->brand_name ?: 'No brand name' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800">{{ $advertiserName }}</div>
                                <div class="text-xs text-gray-400">{{ $requestItem->advertiser->email ?? 'N/A' }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $campaignTypeLabel }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $objectiveLabel }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-xs font-medium {{ $statusBadge['bg'] }} {{ $statusBadge['text'] }} {{ $statusBadge['border'] }}">
                                    {{ $statusBadge['label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-blue-700">{{ $adminCurrency->format((float) $requestItem->approval_budget) }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($requestItem->status === 'pending_review')
                                    <div class="inline-flex items-center gap-1.5 flex-wrap justify-center">
                                        <button onclick="rejectRequest({{ $requestItem->id }})" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs font-semibold hover:bg-red-100 transition-colors">Reject</button>
                                        <button onclick="approveRequest({{ $requestItem->id }})" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold hover:bg-emerald-100 transition-colors">Approve</button>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">No action</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-sm text-gray-500">No request records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    function approveRequest(id) {
        if (!confirm('Approve this request?')) return;

        fetch(`/admin/requests/${id}/approve`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            }
        })
        .catch(() => alert('Failed to approve request.'));
    }

    function rejectRequest(id) {
        if (!confirm('Reject this request?')) return;

        fetch(`/admin/requests/${id}/reject`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            }
        })
        .catch(() => alert('Failed to reject request.'));
    }
</script>
@endpush
