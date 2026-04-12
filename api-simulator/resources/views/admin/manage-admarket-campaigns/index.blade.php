@extends('layouts.admin')

@section('title', 'Manage AdMarket Campaign')

@section('content')
    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-700 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manage AdMarket Campaign</h1>
            <p class="text-sm text-gray-500 mt-1">Disallow advertisers or pause individual campaigns from the current AdMarket inventory.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        @php
            $statCards = [
                ['label' => 'Advertisers', 'value' => number_format($totalAdvertisers), 'color' => 'text-gray-900', 'bg' => 'bg-gray-50', 'border' => 'border-gray-200', 'icon' => '<path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Suspended', 'value' => number_format($suspendedAdvertisers), 'color' => 'text-red-700', 'bg' => 'bg-red-50', 'border' => 'border-red-200', 'icon' => '<path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/>'],
                ['label' => 'Campaigns', 'value' => number_format($totalCampaigns), 'color' => 'text-blue-700', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'icon' => '<path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Paused', 'value' => number_format($pausedCampaigns), 'color' => 'text-amber-700', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'icon' => '<path d="M10 9v6m4-6v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/>'],
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
            <form method="GET" action="{{ route('admin.manage-admarket-campaigns') }}" class="flex items-center gap-2 flex-1 flex-wrap">
                <div class="relative flex-1 min-w-[220px] max-w-md">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search id, advertiser, email, campaign..."
                           class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium text-gray-600 hover:bg-gray-200 transition-colors">Filter</button>
                <a href="{{ route('admin.manage-admarket-campaigns') }}" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</a>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-t border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Id</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Advertiser Name</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Action</th>
                    </tr>
                </thead>
                @forelse($advertisers as $advertiser)
                    <tbody x-data="{ open: false }" class="divide-y divide-gray-100">
                        @php
                            $advertiserName = trim(($advertiser->profile->first_name ?? '') . ' ' . ($advertiser->profile->last_name ?? ''))
                                ?: ($advertiser->profile->company_name ?? $advertiser->email);
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors cursor-pointer" @click="open = !open">
                            <td class="px-4 py-3 font-medium text-gray-900">#{{ $advertiser->id }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-semibold text-xs">
                                        {{ strtoupper(substr($advertiserName, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $advertiserName }}</div>
                                        <div class="text-xs text-gray-500">{{ $advertiser->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center" @click.stop>
                                <button onclick="disallowAdvertiser({{ $advertiser->id }})"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs font-semibold hover:bg-red-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    Disallow
                                </button>
                            </td>
                        </tr>
                        <tr x-show="open" x-transition style="display: none;" class="bg-gray-50/60">
                            <td colspan="3" class="px-4 py-4">
                                <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
                                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-900">Campaigns on AdMarket</h3>
                                            <p class="text-xs text-gray-500 mt-0.5">Clicking the advertiser row expands this campaign list.</p>
                                        </div>
                                        <span class="text-xs font-medium text-gray-400">{{ $advertiser->campaigns->count() }} total</span>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="bg-gray-50 border-b border-gray-100">
                                                    <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Campaign Name</th>
                                                    <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                                                    <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach($advertiser->campaigns as $campaign)
                                                    @php
                                                        $statusBadge = match ($campaign->status) {
                                                            'active' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200'],
                                                            'paused' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200'],
                                                            'rejected' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'border' => 'border-red-200'],
                                                            default => ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'border' => 'border-gray-200'],
                                                        };
                                                    @endphp
                                                    <tr>
                                                        <td class="px-4 py-3 text-gray-900 font-medium">{{ $campaign->name }}</td>
                                                        <td class="px-4 py-3">
                                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-xs font-medium {{ $statusBadge['bg'] }} {{ $statusBadge['text'] }} {{ $statusBadge['border'] }}">
                                                                {{ ucwords(str_replace('_', ' ', $campaign->status)) }}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-3 text-center">
                                                            <button onclick="disallowCampaign({{ $campaign->id }})"
                                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs font-semibold hover:bg-red-100 transition-colors">
                                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                                Disallow Campaign
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                @empty
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="3" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    <p class="text-sm text-gray-500">No AdMarket campaign managers found.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                @endforelse
            </table>
        </div>

        @if($advertisers->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $advertisers->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    function disallowAdvertiser(id) {
        if (!confirm('Are you sure you want to disallow this advertiser and pause all of their AdMarket campaigns?')) return;

        fetch(`/admin/manage-admarket-campaigns/${id}/disallow-advertiser`, {
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
        .catch(() => alert('Failed to disallow advertiser.'));
    }

    function disallowCampaign(id) {
        if (!confirm('Are you sure you want to disallow this campaign?')) return;

        fetch(`/admin/manage-admarket-campaigns/${id}/disallow-campaign`, {
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
        .catch(() => alert('Failed to disallow campaign.'));
    }
</script>
@endpush
