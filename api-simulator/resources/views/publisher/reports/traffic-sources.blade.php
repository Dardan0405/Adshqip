@extends('layouts.publisher')

@section('title', 'Traffic Sources')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Traffic Sources</h1>
            <p class="mt-1 text-sm text-gray-500">Referrer performance for traffic reaching your sites, apps, and AdBlocks.</p>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-6">
        @php
            $cards = [
                ['label' => 'Sources', 'value' => number_format($summary['sources']), 'class' => 'text-gray-900'],
                ['label' => 'Impressions', 'value' => number_format($summary['impressions']), 'class' => 'text-emerald-700'],
                ['label' => 'Clicks', 'value' => number_format($summary['clicks']), 'class' => 'text-blue-700'],
                ['label' => 'Conversions', 'value' => number_format($summary['conversions']), 'class' => 'text-purple-700'],
                ['label' => 'Revenue', 'value' => '$' . number_format($summary['revenue'], 2), 'class' => 'text-brand-700'],
                ['label' => 'CTR', 'value' => number_format($summary['ctr'], 2) . '%', 'class' => 'text-gray-900'],
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
        <div class="border-b border-gray-100 p-4">
            <form method="GET" action="{{ route('publisher.reports.traffic-sources') }}" class="flex flex-wrap items-center gap-2">
                <div class="relative min-w-[220px] flex-1">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search source, domain, AdBlock..." class="w-full rounded-lg border border-gray-200 py-2 pl-10 pr-4 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                </div>

                <select name="zone_id" onchange="this.form.submit()" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All AdBlocks</option>
                    @foreach($zones as $zone)
                        <option value="{{ $zone->id }}" {{ (string) request('zone_id') === (string) $zone->id ? 'selected' : '' }}>
                            #{{ $zone->id }} {{ $zone->name }}
                        </option>
                    @endforeach
                </select>

                <select name="source_id" onchange="this.form.submit()" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Sources</option>
                    @foreach($sourceOptions as $source)
                        <option value="{{ $source->id }}" {{ (string) request('source_id') === (string) $source->id ? 'selected' : '' }}>{{ $source->name }}</option>
                    @endforeach
                </select>

                <select name="delivery_type" onchange="this.form.submit()" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Types</option>
                    <option value="network" {{ request('delivery_type') === 'network' ? 'selected' : '' }}>Network</option>
                    <option value="direct" {{ request('delivery_type') === 'direct' ? 'selected' : '' }}>Direct</option>
                </select>

                <input type="date" name="start_date" value="{{ request('start_date', $defaults['start_date']) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                <input type="date" name="end_date" value="{{ request('end_date', $defaults['end_date']) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">

                <button type="submit" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-200">Search</button>
                <a href="{{ route('publisher.reports.traffic-sources') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</a>

                <div class="ml-auto flex items-center gap-2">
                    <button type="button" onclick="copyTrafficSourceTable()" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Copy</button>
                    <a href="{{ route('publisher.reports.traffic-sources.export', request()->all()) }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">CSV</a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table id="trafficSourceReportsTable" class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/70 text-[10px] font-semibold uppercase tracking-wider text-gray-400">
                        <th class="px-4 py-3 text-left">Traffic Source</th>
                        <th class="px-4 py-3 text-left">AdBlock</th>
                        <th class="px-4 py-3 text-left">Property</th>
                        <th class="px-4 py-3 text-right">Impressions</th>
                        <th class="px-4 py-3 text-right">Clicks</th>
                        <th class="px-4 py-3 text-right">Conversions</th>
                        <th class="px-4 py-3 text-right">Revenue</th>
                        <th class="px-4 py-3 text-right">CTR</th>
                        <th class="px-4 py-3 text-right">ECPM</th>
                        <th class="px-4 py-3 text-left">Last Activity</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($reports as $row)
                        <tr class="hover:bg-gray-50/60">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">{{ $row->traffic_source }}</div>
                                <div class="mt-0.5 text-xs text-gray-400">{{ $row->domain }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800">{{ $row->zone_name }}</div>
                                <span class="mt-1 inline-flex rounded bg-gray-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-gray-500">{{ $row->delivery_type }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $row->property_name }}</td>
                            <td class="px-4 py-3 text-right font-medium tabular-nums text-gray-800">{{ number_format($row->impressions) }}</td>
                            <td class="px-4 py-3 text-right font-medium tabular-nums text-blue-700">{{ number_format($row->clicks) }}</td>
                            <td class="px-4 py-3 text-right font-medium tabular-nums text-purple-700">{{ number_format($row->conversions) }}</td>
                            <td class="px-4 py-3 text-right font-semibold tabular-nums text-brand-700">${{ number_format($row->revenue, 2) }}</td>
                            <td class="px-4 py-3 text-right font-medium tabular-nums text-gray-800">{{ number_format($row->ctr, 2) }}%</td>
                            <td class="px-4 py-3 text-right font-medium tabular-nums text-gray-800">${{ number_format($row->ecpm, 4) }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $row->last_activity }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-14 text-center">
                                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100">
                                    <svg class="h-6 w-6 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M12 21a9 9 0 100-18 9 9 0 000 18zm0 0c2.5-2.25 4-5.25 4-9s-1.5-6.75-4-9m0 18c-2.5-2.25-4-5.25-4-9s1.5-6.75 4-9M3.6 9h16.8M3.6 15h16.8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                </div>
                                <p class="font-medium text-gray-600">No traffic source data found.</p>
                                <p class="mt-1 text-sm text-gray-400">Serve ads from your AdBlocks with referrer traffic, then refresh this report.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reports->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
function copyTrafficSourceTable() {
    const table = document.getElementById('trafficSourceReportsTable');
    const rows = table.querySelectorAll('tr');
    let text = '';

    rows.forEach(row => {
        const cells = row.querySelectorAll('th, td');
        const values = [];
        cells.forEach(cell => values.push(cell.textContent.trim().replace(/\s+/g, ' ')));
        text += values.join('\t') + '\n';
    });

    navigator.clipboard.writeText(text).then(() => alert('Traffic source report copied.'));
}
</script>
@endpush
