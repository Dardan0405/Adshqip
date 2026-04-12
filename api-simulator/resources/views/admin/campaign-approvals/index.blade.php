@extends('layouts.admin')

@section('title', 'Campaign Approvals')

@section('content')
    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-700 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Campaign Approvals</h1>
            <p class="text-sm text-gray-500 mt-1">Review advertiser campaigns and approve or reject pending submissions.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
        @php
            $statCards = [
                ['label' => 'Total Campaigns', 'value' => number_format($totalCampaigns), 'color' => 'text-gray-900', 'bg' => 'bg-gray-50', 'border' => 'border-gray-200', 'icon' => '<path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Approved', 'value' => number_format($approvedCount), 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'icon' => '<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Pending Review', 'value' => number_format($pendingCount), 'color' => 'text-amber-700', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'icon' => '<path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Rejected', 'value' => number_format($rejectedCount), 'color' => 'text-red-700', 'bg' => 'bg-red-50', 'border' => 'border-red-200', 'icon' => '<path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/>'],
                ['label' => 'Total Budget', 'value' => 'EUR ' . number_format($totalBudget, 2), 'color' => 'text-blue-700', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'icon' => '<path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/>'],
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
            <form method="GET" action="{{ route('admin.campaign-approvals') }}" class="flex items-center gap-2 flex-1 flex-wrap">
                <div class="relative flex-1 min-w-[220px] max-w-md">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search id, campaign, advertiser, email..."
                           class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <select name="advertiser_id" class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Advertisers</option>
                    @foreach($advertisers as $advertiser)
                        @php
                            $advertiserName = trim(($advertiser->userProfile->first_name ?? '') . ' ' . ($advertiser->userProfile->last_name ?? '')) ?: ($advertiser->userProfile->company_name ?? $advertiser->email);
                        @endphp
                        <option value="{{ $advertiser->id }}" {{ request('advertiser_id') == $advertiser->id ? 'selected' : '' }}>
                            {{ $advertiserName }}
                        </option>
                    @endforeach
                </select>
                <select name="campaign_type" class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Campaign Types</option>
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
                <a href="{{ route('admin.campaign-approvals') }}" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</a>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-t border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Id</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Campaign Name</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Email</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Campaign Type</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Type</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Budget</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($campaigns as $campaign)
                        @php
                            $campaignTypeLabel = $campaignTypes[$campaign->campaign_type] ?? strtoupper((string) $campaign->campaign_type);
                            $objectiveLabel = ucwords(str_replace('_', ' ', (string) $campaign->marketing_objective));
                            $statusBadge = match ($campaign->status) {
                                'active' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'label' => 'Approved'],
                                'rejected' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'border' => 'border-red-200', 'label' => 'Rejected'],
                                default => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'label' => 'Pending Review'],
                            };
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-900">#{{ $campaign->id }}</td>
                            <td class="px-4 py-3 text-gray-900 font-medium">{{ $campaign->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $campaign->advertiser->email ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $campaignTypeLabel }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-xs font-medium {{ $statusBadge['bg'] }} {{ $statusBadge['text'] }} {{ $statusBadge['border'] }}">
                                    {{ $statusBadge['label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $objectiveLabel }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-blue-700">EUR {{ number_format((float) $campaign->total_budget, 2) }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="inline-flex items-center gap-1.5 flex-wrap justify-center">
                                    @if($campaign->status === 'pending_review')
                                        <button onclick="approveCampaign({{ $campaign->id }})"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold hover:bg-emerald-100 transition-colors">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            Approve
                                        </button>
                                        <button onclick="rejectCampaign({{ $campaign->id }})"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs font-semibold hover:bg-red-100 transition-colors">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            Reject
                                        </button>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-xs font-medium {{ $statusBadge['bg'] }} {{ $statusBadge['text'] }} {{ $statusBadge['border'] }}">
                                            {{ $statusBadge['label'] }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    <p class="text-sm text-gray-500">No campaign approvals found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($campaigns->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $campaigns->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    function approveCampaign(id) {
        if (!confirm('Are you sure you want to approve this campaign?')) return;

        fetch(`/admin/campaign-approvals/${id}/approve`, {
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
        .catch(() => alert('Failed to approve campaign.'));
    }

    function rejectCampaign(id) {
        if (!confirm('Are you sure you want to reject this campaign?')) return;

        fetch(`/admin/campaign-approvals/${id}/reject`, {
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
        .catch(() => alert('Failed to reject campaign.'));
    }
</script>
@endpush
