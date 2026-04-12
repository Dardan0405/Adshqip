@extends('layouts.admin')

@section('title', 'Creative Reports')

@section('content')
    {{-- Flash --}}
    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-700 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Creative Reports</h1>
            <p class="text-sm text-gray-500 mt-1">Monthly performance breakdown by ad creative — impressions, clicks, conversions, spend, CTR & ECPM.</p>
        </div>
    </div>

    {{-- ═══════════ TOP SUMMARY CARDS ═══════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        @php
            $summaryCards = [
                [
                    'label'  => 'Total Clicks',
                    'value'  => number_format($totalClicks),
                    'color'  => 'text-blue-700',
                    'bg'     => 'bg-blue-50',
                    'border' => 'border-blue-200',
                    'icon'   => '<path d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
                ],
                [
                    'label'  => 'Unique Impressions',
                    'value'  => number_format($totalUniqueImpressions),
                    'color'  => 'text-emerald-700',
                    'bg'     => 'bg-emerald-50',
                    'border' => 'border-emerald-200',
                    'icon'   => '<path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke="currentColor" stroke-width="1.5"/>',
                ],
                [
                    'label'  => 'Unique Clicks',
                    'value'  => number_format($totalUniqueClicks),
                    'color'  => 'text-indigo-700',
                    'bg'     => 'bg-indigo-50',
                    'border' => 'border-indigo-200',
                    'icon'   => '<path d="M13 10V3L4 14h7v7l9-11h-7z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',
                ],
                [
                    'label'  => 'Conversions',
                    'value'  => number_format($totalConversions),
                    'color'  => 'text-purple-700',
                    'bg'     => 'bg-purple-50',
                    'border' => 'border-purple-200',
                    'icon'   => '<path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
                ],
            ];
        @endphp
        @foreach($summaryCards as $card)
            <div class="rounded-xl border {{ $card['border'] }} {{ $card['bg'] }} p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                    <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none">{!! $card['icon'] !!}</svg>
                </div>
                <div class="text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- ═══════════ FILTERS & EXPORT BAR ═══════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4">
            <form method="GET" action="{{ route('admin.reports.creative') }}" class="flex items-center gap-2 flex-1 flex-wrap">
                {{-- Search --}}
                <div class="relative flex-1 max-w-xs">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by ad, campaign, advertiser..."
                           class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>

                {{-- Ad dropdown --}}
                <select name="ad_id" onchange="this.form.submit()"
                        class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Creatives</option>
                    @foreach($allAds as $ad)
                        <option value="{{ $ad->id }}" {{ request('ad_id') == $ad->id ? 'selected' : '' }}>
                            {{ $ad->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Campaign dropdown --}}
                <select name="campaign_id" onchange="this.form.submit()"
                        class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Campaigns</option>
                    @foreach($allCampaigns as $campaign)
                        <option value="{{ $campaign->id }}" {{ request('campaign_id') == $campaign->id ? 'selected' : '' }}>
                            {{ $campaign->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Advertiser dropdown --}}
                <select name="advertiser_id" onchange="this.form.submit()"
                        class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Advertisers</option>
                    @foreach($allAdvertisers as $adv)
                        <option value="{{ $adv->id }}" {{ request('advertiser_id') == $adv->id ? 'selected' : '' }}>
                            {{ $adv->userProfile ? trim($adv->userProfile->first_name . ' ' . $adv->userProfile->last_name) : $adv->email }}
                        </option>
                    @endforeach
                </select>

                {{-- Date range --}}
                <input type="month" name="start_month" value="{{ request('start_month') }}"
                       class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                <input type="month" name="end_month" value="{{ request('end_month') }}"
                       class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">

                <button type="submit"
                        class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium text-gray-600 hover:bg-gray-200 transition-colors">
                    Search
                </button>

                @if(request()->hasAny(['search', 'ad_id', 'campaign_id', 'advertiser_id', 'start_month', 'end_month']))
                    <a href="{{ route('admin.reports.creative') }}"
                       class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-500 hover:bg-gray-50 transition-colors">
                        Clear
                    </a>
                @endif
            </form>

            {{-- Export --}}
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Export
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <div x-show="open" @click.away="open = false" x-transition
                     class="absolute right-0 mt-2 w-44 bg-white rounded-xl border border-gray-200 shadow-lg py-1 z-50" style="display:none;">
                    <button onclick="copyTable()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Copy</button>
                    <a href="{{ route('admin.reports.creative.export', request()->all()) }}"
                       class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">CSV</a>
                    <button onclick="window.print()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Print</button>
                </div>
            </div>
        </div>

        {{-- ═══════════ TABLE ═══════════ --}}
        <div class="overflow-x-auto">
            <table id="creativeReportsTable" class="w-full text-sm">
                <thead>
                    <tr class="border-t border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Period</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Creative</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Campaign</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Advertiser</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Impressions</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Clicks</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Conversions</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Spend</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">CTR</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">ECPM</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($reports as $row)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $row->month_formatted }}</div>
                                <div class="text-xs text-gray-400">{{ $row->month }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $row->ad_name }}</div>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    @if($row->ad_type)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-gray-100 text-gray-500">
                                            {{ $row->ad_type }}
                                        </span>
                                    @endif
                                    @php
                                        $statusClasses = match($row->ad_status ?? '') {
                                            'active'   => 'bg-emerald-100 text-emerald-700',
                                            'paused'   => 'bg-amber-100 text-amber-700',
                                            'rejected' => 'bg-red-100 text-red-700',
                                            default    => 'bg-gray-100 text-gray-500',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold uppercase {{ $statusClasses }}">
                                        {{ $row->ad_status ?? 'unknown' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800">{{ $row->campaign_name }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $row->advertiser_name }}</div>
                                <div class="text-xs text-gray-400">{{ $row->advertiser_email }}</div>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="font-medium text-gray-800">{{ number_format($row->total_impressions) }}</div>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="font-medium text-blue-700">{{ number_format($row->total_clicks) }}</div>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="font-medium text-purple-700">{{ number_format($row->total_conversions) }}</div>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="font-semibold text-emerald-700">€{{ number_format($row->total_spend, 2) }}</div>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="font-medium text-gray-700">{{ number_format($row->ctr, 2) }}%</div>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="font-medium text-gray-700">€{{ number_format($row->ecpm, 2) }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    <p class="text-sm text-gray-500">No creative report data found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reports->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    function copyTable() {
        const table = document.getElementById('creativeReportsTable');
        const rows  = table.querySelectorAll('tr');
        let text = '';
        rows.forEach(row => {
            const cells = row.querySelectorAll('th, td');
            const rowData = [];
            cells.forEach(cell => rowData.push(cell.textContent.trim().replace(/\s+/g, ' ')));
            text += rowData.join('\t') + '\n';
        });
        navigator.clipboard.writeText(text).then(() => alert('Table data copied to clipboard!'));
    }
</script>
@endpush
