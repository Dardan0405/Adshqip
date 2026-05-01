@extends('layouts.advertiser')

@section('title', 'Campaign AdMarket')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Campaign AdMarket</h1>
            <p class="mt-1 text-sm text-gray-500">List campaign inventory for publishers and manage network distribution settings.</p>
        </div>
        <a href="{{ route('advertiser.campaigns.create') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">New Campaign</a>
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach([
            ['label' => 'Campaigns', 'value' => $summary['total']],
            ['label' => 'Listed', 'value' => $summary['listed']],
            ['label' => 'Eligible', 'value' => $summary['eligible']],
            ['label' => 'Suspended', 'value' => $summary['suspended']],
        ] as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($card['value']) }}</div>
            </div>
        @endforeach
    </div>

    <div class="rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-100 p-4">
            <form method="GET" action="{{ route('advertiser.campaign-admarket') }}" class="flex flex-wrap gap-3">
                <input name="search" value="{{ request('search') }}" class="min-w-64 flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Search campaigns...">
                <select name="market_status" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" onchange="this.form.submit()">
                    <option value="">All AdMarket states</option>
                    <option value="listed" @selected(request('market_status') === 'listed')>Listed</option>
                    <option value="unlisted" @selected(request('market_status') === 'unlisted')>Unlisted</option>
                    <option value="suspended" @selected(request('market_status') === 'suspended')>Suspended</option>
                </select>
                <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
                <a href="{{ route('advertiser.campaign-admarket') }}" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-600">Reset</a>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-gray-400">Campaign</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-gray-400">Market</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-gray-400">Distribution</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-gray-400">Eligibility</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($campaigns as $campaign)
                        @php
                            $listed = $campaign->admarket_enabled && $campaign->admarket_status === 'listed';
                            $eligible = in_array($campaign->status, ['active', 'paused', 'pending_review'], true) && $campaign->ads_count > 0;
                            $statusClass = $listed ? 'bg-emerald-100 text-emerald-700' : ($campaign->admarket_status === 'suspended' ? 'bg-rose-100 text-rose-700' : 'bg-gray-100 text-gray-600');
                        @endphp
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">{{ $campaign->name }}</div>
                                <div class="text-xs text-gray-400">#{{ $campaign->id }} &middot; {{ strtoupper($campaign->campaign_type) }} &middot; {{ ucwords(str_replace('_', ' ', $campaign->status)) }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($campaign->description, 100) }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase {{ $statusClass }}">
                                    {{ $listed ? 'Listed' : ucfirst($campaign->admarket_status ?? 'unlisted') }}
                                </span>
                                <div class="mt-1 text-xs text-gray-500">{{ $campaign->admarket_published_at ? 'Published ' . $campaign->admarket_published_at->format('M d, Y') : 'Not published' }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                <div>{{ ucwords(str_replace('_', ' ', $campaign->distribution_mode ?? 'all_networks')) }}</div>
                                <div>MSN: {{ $campaign->msn_enabled || $campaign->msn_exclusive ? 'Enabled' : 'Off' }}</div>
                                <div>Bid adj: {{ $campaign->msn_bid_adjustment !== null ? $campaign->msn_bid_adjustment . '%' : 'None' }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs">
                                <div class="{{ $eligible ? 'text-emerald-600' : 'text-rose-600' }} font-semibold">{{ $eligible ? 'Eligible' : 'Needs setup' }}</div>
                                <div class="text-gray-500">{{ number_format($campaign->ads_count) }} creatives</div>
                                <div class="text-gray-500">Bid EUR {{ number_format((float) $campaign->bid_amount, 4) }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <button type="button"
                                        class="settings-btn rounded-lg border border-blue-200 px-3 py-1.5 text-xs font-semibold text-blue-600 hover:bg-blue-50"
                                        data-id="{{ $campaign->id }}"
                                        data-name="{{ e($campaign->name) }}"
                                        data-distribution-mode="{{ $campaign->distribution_mode ?? 'all_networks' }}"
                                        data-msn-enabled="{{ $campaign->msn_enabled ? '1' : '0' }}"
                                        data-msn-exclusive="{{ $campaign->msn_exclusive ? '1' : '0' }}"
                                        data-msn-bid-adjustment="{{ $campaign->msn_bid_adjustment }}"
                                        data-admarket-notes="{{ e($campaign->admarket_notes) }}">Settings</button>
                                    @if($listed)
                                        <form method="POST" action="{{ route('advertiser.campaign-admarket.unpublish', $campaign) }}">@csrf @method('PATCH')<button class="rounded-lg border border-amber-200 px-3 py-1.5 text-xs font-semibold text-amber-600 hover:bg-amber-50">Unlist</button></form>
                                    @else
                                        <form method="POST" action="{{ route('advertiser.campaign-admarket.publish', $campaign) }}">@csrf @method('PATCH')<button class="rounded-lg border border-emerald-200 px-3 py-1.5 text-xs font-semibold text-emerald-600 hover:bg-emerald-50">List</button></form>
                                    @endif
                                    <a href="{{ route('advertiser.campaigns.show', $campaign->id) }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50">View</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-12 text-center text-gray-500">No campaigns found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($campaigns->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $campaigns->links() }}</div>
        @endif
    </div>

    <div id="settingsModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/40 px-4">
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">AdMarket Settings</h3>
                    <p id="settingsCampaignName" class="text-xs text-gray-500"></p>
                </div>
                <button type="button" onclick="closeSettings()" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100">x</button>
            </div>
            <form id="settingsForm" method="POST" class="space-y-4 p-6">
                @csrf
                @method('PUT')
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Distribution Mode</label>
                    <select id="settings_distribution_mode" name="distribution_mode" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        <option value="all_networks">All Networks</option>
                        <option value="selected_networks">Selected Networks</option>
                        <option value="msn_exclusive">MSN Exclusive</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600">
                        <input id="settings_msn_enabled" type="checkbox" name="msn_enabled" value="1">
                        MSN enabled
                    </label>
                    <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600">
                        <input id="settings_msn_exclusive" type="checkbox" name="msn_exclusive" value="1">
                        MSN exclusive
                    </label>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">MSN Bid Adjustment %</label>
                    <input id="settings_msn_bid_adjustment" type="number" step="0.01" min="-100" max="500" name="msn_bid_adjustment" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Internal Notes</label>
                    <textarea id="settings_admarket_notes" name="admarket_notes" rows="3" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeSettings()" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-500">Cancel</button>
                    <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('settingsModal');
    const form = document.getElementById('settingsForm');

    document.querySelectorAll('.settings-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            form.action = '{{ url("advertisers/campaign-admarket") }}/' + this.dataset.id + '/settings';
            document.getElementById('settingsCampaignName').textContent = this.dataset.name || '';
            document.getElementById('settings_distribution_mode').value = this.dataset.distributionMode || 'all_networks';
            document.getElementById('settings_msn_enabled').checked = this.dataset.msnEnabled === '1';
            document.getElementById('settings_msn_exclusive').checked = this.dataset.msnExclusive === '1';
            document.getElementById('settings_msn_bid_adjustment').value = this.dataset.msnBidAdjustment || '';
            document.getElementById('settings_admarket_notes').value = this.dataset.admarketNotes || '';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });
});

function closeSettings() {
    const modal = document.getElementById('settingsModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endpush
