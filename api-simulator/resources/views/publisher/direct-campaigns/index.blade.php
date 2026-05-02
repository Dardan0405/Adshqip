@extends('layouts.publisher')

@section('title', 'Direct Campaigns')

@section('content')
<div x-data="publisherDirectCampaigns()" class="space-y-6">
    @if(session('success'))
        <div class="p-3 rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-700 text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Direct Campaigns</h1>
            <p class="text-sm text-gray-500 mt-1">Direct deals assigned to your publisher AdBlocks.</p>
        </div>
        <a href="{{ route('publisher.adblocks') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" stroke="currentColor" stroke-width="1.5"/></svg>
            Manage AdBlocks
        </a>
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-6">
        @php
            $cards = [
                ['label' => 'Total', 'value' => number_format($totals['total']), 'class' => 'text-gray-900'],
                ['label' => 'Active', 'value' => number_format($totals['active']), 'class' => 'text-emerald-700'],
                ['label' => 'Pending', 'value' => number_format($totals['pending_review']), 'class' => 'text-orange-700'],
                ['label' => 'Impressions', 'value' => number_format($totals['impressions']), 'class' => 'text-gray-900'],
                ['label' => 'Clicks', 'value' => number_format($totals['clicks']), 'class' => 'text-blue-700'],
                ['label' => 'Revenue', 'value' => '$' . number_format($totals['publisher_revenue'], 2), 'class' => 'text-brand-700'],
            ];
        @endphp
        @foreach($cards as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                <div class="mt-1 text-xl font-bold {{ $card['class'] }}">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="rounded-xl border border-gray-200 bg-white">
        <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap items-center gap-1">
                @php
                    $tabs = [
                        'all' => 'All',
                        'active' => 'Active',
                        'paused' => 'Paused',
                        'pending_review' => 'Pending',
                        'completed' => 'Completed',
                    ];
                @endphp
                @foreach($tabs as $key => $label)
                    <a href="{{ route('publisher.direct-campaigns', ['status' => $key, 'search' => $search]) }}"
                       class="rounded-lg px-3 py-1.5 text-xs font-medium {{ $statusFilter === $key ? 'bg-brand-600 text-white' : 'text-gray-500 hover:bg-gray-50' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('publisher.direct-campaigns') }}" class="relative">
                <input type="hidden" name="status" value="{{ $statusFilter }}">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search campaigns..." class="w-full rounded-lg border border-gray-200 py-2 pl-10 pr-4 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 sm:w-72">
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">
                        <th class="px-5 py-3">Campaign</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">AdBlocks</th>
                        <th class="px-5 py-3">Pricing</th>
                        <th class="px-5 py-3 text-right">Impressions</th>
                        <th class="px-5 py-3 text-right">Clicks</th>
                        <th class="px-5 py-3 text-right">CTR</th>
                        <th class="px-5 py-3 text-right">Revenue</th>
                        <th class="px-5 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($campaigns as $campaign)
                        @php
                            $stat = $campaign->publisher_stats;
                            $statusStyles = [
                                'active' => 'bg-emerald-100 text-emerald-700',
                                'paused' => 'bg-amber-100 text-amber-700',
                                'pending_review' => 'bg-orange-100 text-orange-700',
                                'completed' => 'bg-blue-100 text-blue-700',
                            ];
                            $assignedZones = $campaign->zones->where('is_active', true)->filter(fn ($link) => $link->zone);
                        @endphp
                        <tr class="hover:bg-gray-50/60">
                            <td class="px-5 py-4">
                                <div class="max-w-[260px]">
                                    <p class="truncate font-semibold text-gray-900">{{ $campaign->name }}</p>
                                    <p class="mt-0.5 truncate text-xs text-gray-400">{{ $campaign->brand_name ?: $campaign->headline ?: $campaign->advertiser?->email }}</p>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-lg px-2.5 py-1 text-[11px] font-semibold {{ $statusStyles[$campaign->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ ucwords(str_replace('_', ' ', $campaign->status)) }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="max-w-[240px] space-y-1">
                                    @foreach($assignedZones->take(2) as $link)
                                        <p class="truncate text-xs text-gray-600">#{{ $link->zone->id }} {{ $link->zone->name }}</p>
                                    @endforeach
                                    @if($assignedZones->count() > 2)
                                        <p class="text-[11px] text-gray-400">+{{ $assignedZones->count() - 2 }} more</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="space-y-1">
                                    <span class="rounded bg-gray-100 px-2 py-0.5 text-[10px] font-bold uppercase text-gray-600">{{ strtoupper(str_replace('_', ' ', $campaign->pricing_model)) }}</span>
                                    <p class="text-xs font-semibold text-brand-700">${{ number_format((float) $campaign->bid_amount, 4) }}</p>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-right font-medium tabular-nums text-gray-800">{{ number_format($stat['impressions']) }}</td>
                            <td class="px-5 py-4 text-right font-medium tabular-nums text-gray-800">{{ number_format($stat['clicks']) }}</td>
                            <td class="px-5 py-4 text-right font-medium tabular-nums text-gray-800">{{ number_format($stat['ctr'], 2) }}%</td>
                            <td class="px-5 py-4 text-right font-semibold tabular-nums text-gray-900">${{ number_format($stat['publisher_revenue'], 2) }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" @click="openDetails({{ $campaign->id }})" title="Details" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke="currentColor" stroke-width="1.5"/></svg>
                                    </button>
                                    <button type="button" @click="openTag({{ $campaign->id }}, @js($campaign->name), @js($assignedZones->pluck('zone_id')->values()))" title="Get tag" class="rounded-lg p-1.5 text-gray-400 hover:bg-brand-50 hover:text-brand-600">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"><path d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                    <a href="{{ route('direct.serve', $campaign->id) }}?debug=1" target="_blank" title="Debug serve" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"><path d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.868v4.264a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" stroke="currentColor" stroke-width="1.5"/><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-16 text-center">
                                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100">
                                    <svg class="h-6 w-6 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                </div>
                                <p class="font-medium text-gray-600">No direct campaigns found</p>
                                <p class="mt-1 text-sm text-gray-400">Assigned direct deals will appear here after admin or advertiser setup.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($campaigns->hasPages())
            <div class="border-t border-gray-100 px-5 py-4">
                {{ $campaigns->links() }}
            </div>
        @endif
    </div>

    <div x-show="showTagModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50" @click.self="showTagModal = false" style="display:none">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="flex max-h-[85vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Direct Campaign Tag</h2>
                        <p class="text-sm text-gray-500" x-text="tagCampaignName"></p>
                    </div>
                    <button type="button" @click="showTagModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </button>
                </div>
                <div class="flex-1 space-y-4 overflow-y-auto p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div class="flex-1">
                            <label class="mb-1 block text-xs font-medium text-gray-500">AdBlock</label>
                            <select x-model="tagZoneId" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                                <option value="">Select an assigned AdBlock</option>
                                @foreach($publisherZones as $zone)
                                    <option value="{{ $zone->id }}" x-show="allowedZoneIds.includes({{ $zone->id }})">
                                        #{{ $zone->id }} {{ $zone->name }} - {{ $zone->site?->name ?? $zone->mobileApp?->app_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" @click="generateTag()" :disabled="!tagZoneId || generatingTag" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
                            <span x-text="generatingTag ? 'Generating...' : 'Generate Tag'"></span>
                        </button>
                    </div>

                    <template x-if="tagError">
                        <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" x-text="tagError"></div>
                    </template>

                    <div x-show="tagCodes" class="space-y-4">
                        <template x-for="block in codeBlocks()" :key="block.key">
                            <div>
                                <div class="mb-1 flex items-center justify-between">
                                    <label class="text-xs font-medium text-gray-600" x-text="block.label"></label>
                                    <button type="button" @click="copyCode(block.value, $event)" class="text-xs font-medium text-brand-600 hover:underline">Copy</button>
                                </div>
                                <pre class="max-h-44 overflow-auto rounded-lg bg-gray-900 p-3 text-xs text-green-400"><code x-text="block.value"></code></pre>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="showDetailsModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50" @click.self="showDetailsModal = false" style="display:none">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="w-full max-w-2xl rounded-2xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-bold text-gray-900" x-text="details?.name || 'Campaign details'"></h2>
                    <button type="button" @click="showDetailsModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </button>
                </div>
                <div class="space-y-5 p-6">
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div><p class="text-xs text-gray-400">Brand</p><p class="font-medium text-gray-800" x-text="details?.brand_name || '-'"></p></div>
                        <div><p class="text-xs text-gray-400">Advertiser</p><p class="font-medium text-gray-800" x-text="details?.advertiser || '-'"></p></div>
                        <div><p class="text-xs text-gray-400">Pricing</p><p class="font-medium uppercase text-gray-800" x-text="details?.pricing_model || '-'"></p></div>
                        <div><p class="text-xs text-gray-400">Bid</p><p class="font-medium text-gray-800" x-text="details ? '$' + Number(details.bid_amount || 0).toFixed(4) : '-'"></p></div>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Ad copy</p>
                        <p class="mt-1 font-semibold text-gray-900" x-text="details?.headline || details?.name || '-'"></p>
                        <p class="mt-1 text-sm text-gray-500" x-text="details?.body_text || ''"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Assigned AdBlocks</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <template x-for="zone in details?.zones || []" :key="zone.id">
                                <span class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600" x-text="'#' + zone.id + ' ' + zone.name"></span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function publisherDirectCampaigns() {
    return {
        csrfToken: document.querySelector('meta[name="csrf-token"]').content,
        showTagModal: false,
        showDetailsModal: false,
        tagCampaignId: null,
        tagCampaignName: '',
        allowedZoneIds: [],
        tagZoneId: '',
        tagCodes: null,
        tagError: '',
        generatingTag: false,
        details: null,

        openTag(id, name, zoneIds) {
            this.tagCampaignId = id;
            this.tagCampaignName = name;
            this.allowedZoneIds = zoneIds.map(Number);
            this.tagZoneId = this.allowedZoneIds.length === 1 ? String(this.allowedZoneIds[0]) : '';
            this.tagCodes = null;
            this.tagError = '';
            this.showTagModal = true;
        },

        async generateTag() {
            this.generatingTag = true;
            this.tagError = '';
            this.tagCodes = null;

            try {
                const res = await fetch('/publisher/direct-campaigns/' + this.tagCampaignId + '/tag', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ zone_id: this.tagZoneId }),
                });
                const json = await res.json();
                if (!res.ok || !json.success) {
                    this.tagError = json.message || 'Could not generate tag.';
                    return;
                }
                this.tagCodes = json.codes;
            } catch (error) {
                this.tagError = 'Network error: ' + error.message;
            } finally {
                this.generatingTag = false;
            }
        },

        async openDetails(id) {
            this.details = null;
            this.showDetailsModal = true;
            try {
                const res = await fetch('/publisher/direct-campaigns/' + id, { headers: { 'Accept': 'application/json' } });
                const json = await res.json();
                if (json.success) this.details = json.campaign;
            } catch (error) {
                this.details = { name: 'Unable to load campaign details' };
            }
        },

        codeBlocks() {
            if (!this.tagCodes) return [];
            return [
                { key: 'iframe', label: 'iFrame Tag', value: this.tagCodes.iframe },
                { key: 'js', label: 'JavaScript Tag', value: this.tagCodes.js },
                { key: 'conversion', label: 'Conversion Pixel', value: this.tagCodes.conversion_pixel },
                { key: 'postback', label: 'S2S Postback URL', value: this.tagCodes.postback_url },
                { key: 'debug', label: 'Debug URL', value: this.tagCodes.debug_url },
            ];
        },

        async copyCode(value, event) {
            if (!value) return;
            await navigator.clipboard.writeText(value);
            const button = event.target;
            const old = button.textContent;
            button.textContent = 'Copied';
            setTimeout(() => button.textContent = old, 1200);
        },
    };
}
</script>
@endpush
