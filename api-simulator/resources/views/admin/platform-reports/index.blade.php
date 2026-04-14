@extends('layouts.admin')

@section('title', 'Platform Reports')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Platform Reports</h1>
            <p class="text-sm text-gray-500 mt-1">Live platform reporting from `aq_stats_daily` with configurable filters, dimensions, metrics, and export.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 xl:grid-cols-6 gap-3 mb-6">
        @php
            $summaryCards = [
                ['label' => 'Impressions', 'value' => number_format($summary['impressions']), 'color' => 'text-slate-900', 'bg' => 'bg-slate-50', 'border' => 'border-slate-200'],
                ['label' => 'Clicks', 'value' => number_format($summary['clicks']), 'color' => 'text-blue-700', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200'],
                ['label' => 'Revenue', 'value' => 'EUR ' . number_format($summary['revenue'], 2), 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200'],
                ['label' => 'Profit', 'value' => 'EUR ' . number_format($summary['profit'], 2), 'color' => 'text-violet-700', 'bg' => 'bg-violet-50', 'border' => 'border-violet-200'],
                ['label' => 'CTR', 'value' => number_format($summary['ctr'], 2) . '%', 'color' => 'text-amber-700', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200'],
                ['label' => 'Active Accounts', 'value' => number_format($summary['advertisers']) . ' / ' . number_format($summary['publishers']), 'color' => 'text-cyan-700', 'bg' => 'bg-cyan-50', 'border' => 'border-cyan-200'],
            ];
        @endphp

        @foreach($summaryCards as $card)
            <div class="rounded-xl border {{ $card['border'] }} {{ $card['bg'] }} p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                <div class="mt-2 text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.reports.platform') }}" class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="p-4 border-b border-gray-100">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Filters and Report Builder</h2>
                    <p class="text-xs text-gray-500 mt-1">This version uses live daily stats. Hourly interval and timezone conversion are not available from the current schema.</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-800 transition-colors">
                        Run Report
                    </button>
                    <a href="{{ route('admin.reports.platform.export', request()->query()) }}" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">
                        Export CSV
                    </a>
                    <a href="{{ route('admin.reports.platform') }}" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">
                        Reset
                    </a>
                </div>
            </div>
        </div>

        <div class="p-4 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Search</label>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Campaign, creative, user, site..."
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                    >
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Start Date</label>
                    <input
                        type="date"
                        name="start_date"
                        value="{{ request('start_date', $defaults['start_date']) }}"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                    >
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">End Date</label>
                    <input
                        type="date"
                        name="end_date"
                        value="{{ request('end_date', $defaults['end_date']) }}"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                    >
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Interval</label>
                    <select name="interval" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        @foreach($availableIntervals as $value => $label)
                            <option value="{{ $value }}" {{ $interval === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Environment</label>
                    <select name="device_type" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <option value="">All Devices</option>
                        @foreach($availableDeviceTypes as $value => $label)
                            <option value="{{ $value }}" {{ request('device_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Advertiser</label>
                    <select name="advertiser_id" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <option value="">All Advertisers</option>
                        @foreach($advertisers as $advertiser)
                            @php
                                $advertiserName = trim(($advertiser->userProfile->first_name ?? '') . ' ' . ($advertiser->userProfile->last_name ?? '')) ?: $advertiser->email;
                            @endphp
                            <option value="{{ $advertiser->id }}" {{ (string) request('advertiser_id') === (string) $advertiser->id ? 'selected' : '' }}>
                                {{ $advertiserName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Campaign</label>
                    <select name="campaign_id" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <option value="">All Campaigns</option>
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}" {{ (string) request('campaign_id') === (string) $campaign->id ? 'selected' : '' }}>
                                {{ $campaign->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Creative</label>
                    <select name="creative_id" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <option value="">All Creatives</option>
                        @foreach($creatives as $creative)
                            <option value="{{ $creative->id }}" {{ (string) request('creative_id') === (string) $creative->id ? 'selected' : '' }}>
                                {{ $creative->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Publisher</label>
                    <select name="publisher_id" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <option value="">All Publishers</option>
                        @foreach($publishers as $publisher)
                            @php
                                $publisherName = trim(($publisher->userProfile->first_name ?? '') . ' ' . ($publisher->userProfile->last_name ?? '')) ?: $publisher->email;
                            @endphp
                            <option value="{{ $publisher->id }}" {{ (string) request('publisher_id') === (string) $publisher->id ? 'selected' : '' }}>
                                {{ $publisherName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Site</label>
                    <select name="site_id" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <option value="">All Sites</option>
                        @foreach($sites as $site)
                            <option value="{{ $site->id }}" {{ (string) request('site_id') === (string) $site->id ? 'selected' : '' }}>
                                {{ $site->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Country</label>
                    <select name="country_code" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <option value="">All Countries</option>
                        @foreach($countries as $country)
                            <option value="{{ $country }}" {{ request('country_code') === $country ? 'selected' : '' }}>
                                {{ $country }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Ad Size</label>
                    <select name="ad_size_id" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <option value="">All Sizes</option>
                        @foreach($adSizes as $size)
                            <option value="{{ $size->id }}" {{ (string) request('ad_size_id') === (string) $size->id ? 'selected' : '' }}>
                                {{ ($size->width && $size->height) ? $size->width . 'x' . $size->height : ($size->name ?: 'Unknown') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Revenue Type</label>
                    <select name="revenue_type" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <option value="">All Revenue Types</option>
                        @foreach($availableRevenueTypes as $value => $label)
                            <option value="{{ $value }}" {{ request('revenue_type') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Adblock</label>
                    <select name="adblock" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <option value="any" {{ request('adblock', 'any') === 'any' ? 'selected' : '' }}>All</option>
                        <option value="detected" {{ request('adblock') === 'detected' ? 'selected' : '' }}>Detected</option>
                        <option value="clean" {{ request('adblock') === 'clean' ? 'selected' : '' }}>Clean</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                <div class="rounded-xl border border-gray-200 p-4">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <h3 class="text-sm font-semibold text-gray-900">Metrics</h3>
                        <span class="text-xs text-gray-400">Choose what to calculate</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        @foreach($availableMetrics as $metricKey => $metric)
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input
                                    type="checkbox"
                                    name="metrics[]"
                                    value="{{ $metricKey }}"
                                    {{ in_array($metricKey, $selectedMetrics, true) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-brand-600 focus:ring-brand-500"
                                >
                                <span>{{ $metric['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <h3 class="text-sm font-semibold text-gray-900">Dimensions</h3>
                        <span class="text-xs text-gray-400">Choose how rows are grouped</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        @foreach($availableDimensions as $dimensionKey => $dimension)
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input
                                    type="checkbox"
                                    name="dimensions[]"
                                    value="{{ $dimensionKey }}"
                                    {{ in_array($dimensionKey, $selectedDimensions, true) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-brand-600 focus:ring-brand-500"
                                >
                                <span>{{ $dimension['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-4 py-3 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">Report Results</h2>
                <p class="text-xs text-gray-500 mt-1">{{ count($tableColumns) }} columns selected.</p>
            </div>
            <button onclick="copyPlatformTable()" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">
                Copy Table
            </button>
        </div>

        <div class="overflow-x-auto">
            <table id="platformReportsTable" class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/70">
                        @foreach($tableColumns as $column)
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">
                                {{ $column['label'] }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($reports as $row)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            @foreach($tableColumns as $column)
                                <td class="px-4 py-3 whitespace-nowrap {{ $column['type'] !== 'text' ? 'text-right font-medium' : 'text-left text-gray-700' }}">
                                    {{ $row->{$column['key'] . '_formatted'} }}
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ max(count($tableColumns), 1) }}" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-300" viewBox="0 0 24 24" fill="none">
                                        <path d="M4 7h16M7 4v3m10-3v3M6 11h12M6 15h8m-8 6h12a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                    <p class="text-sm text-gray-500">No platform report rows matched the selected filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reports instanceof \Illuminate\Contracts\Pagination\Paginator && $reports->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    function copyPlatformTable() {
        const table = document.getElementById('platformReportsTable');
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
