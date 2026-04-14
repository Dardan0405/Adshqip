@extends('layouts.admin')

@section('title', 'Network Kit')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    .leaflet-popup-content-wrapper { border-radius: 12px; }
    .leaflet-popup-content { margin: 12px 16px; }
</style>
@endpush

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Network Kit</h1>
            <p class="text-sm text-gray-500 mt-1">Advanced filtering and analytics toolkit for network performance.</p>
        </div>
    </div>

    {{-- Filters Section --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-4 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Filters</h2>
            <p class="text-xs text-gray-500 mt-1">Apply filters to analyze specific segments of your network data</p>
        </div>

        <form method="GET" action="{{ route('admin.network-kit') }}" class="p-4">
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 mb-4">
                {{-- Date Range --}}
                <div>
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ request('start_date', $defaults['start_date']) }}"
                           class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ request('end_date', $defaults['end_date']) }}"
                           class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>

                {{-- Device Filter --}}
                <div>
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1">Devices</label>
                    <select name="device" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        <option value="">All Devices</option>
                        @foreach($filterOptions['devices'] as $key => $label)
                            <option value="{{ $key }}" {{ request('device') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Country Filter --}}
                <div>
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1">Country</label>
                    <select name="country" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        <option value="">All Countries</option>
                        @foreach($filterOptions['countries'] as $code => $name)
                            <option value="{{ $code }}" {{ request('country') == $code ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Revenue Type Filter --}}
                <div>
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1">Revenue Type</label>
                    <select name="revenue_type" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        <option value="">All Types</option>
                        @foreach($filterOptions['revenue_types'] as $key => $label)
                            <option value="{{ $key }}" {{ request('revenue_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Traffic Source Filter --}}
                <div>
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1">Traffic Source</label>
                    <select name="traffic_source" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        <option value="">All Sources</option>
                        @foreach($filterOptions['traffic_sources'] as $id => $name)
                            <option value="{{ $id }}" {{ request('traffic_source') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- AdBlocks Filter --}}
                <div>
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1">AdBlocks</label>
                    <select name="adblock" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        <option value="">All Zones</option>
                        @foreach($filterOptions['adblocks'] as $id => $name)
                            <option value="{{ $id }}" {{ request('adblock') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Browser Filter --}}
                <div>
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1">Browsers</label>
                    <select name="browser" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        <option value="">All Browsers</option>
                        @foreach($filterOptions['browsers'] as $key => $label)
                            <option value="{{ $key }}" {{ request('browser') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
                {{-- OS Filter --}}
                <div>
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1">OS</label>
                    <select name="os" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        <option value="">All OS</option>
                        @foreach($filterOptions['os'] as $key => $label)
                            <option value="{{ $key }}" {{ request('os') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Buttons --}}
                <div class="col-span-2 sm:col-span-4 lg:col-span-7 flex items-end gap-2">
                    <button type="submit" class="px-6 py-2 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-800 transition-colors">
                        <svg class="w-4 h-4 inline mr-1" viewBox="0 0 24 24" fill="none"><path d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Apply Filters
                    </button>
                    <a href="{{ route('admin.network-kit') }}" class="px-6 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                        Reset
                    </a>

                    {{-- Export Dropdown --}}
                    <div class="relative ml-auto" x-data="{ open: false }">
                        <button type="button" @click="open = !open" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            Export
                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-44 bg-white rounded-xl border border-gray-200 shadow-lg py-1 z-50" style="display:none;">
                            <button type="button" onclick="copyTable()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Copy</button>
                            <a href="{{ route('admin.network-kit.export', request()->all()) }}" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">CSV</a>
                            <button type="button" onclick="window.print()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Print</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Summary Stats --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-4 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Summary</h2>
            <p class="text-xs text-gray-500 mt-1">Aggregated metrics based on applied filters</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-0 divide-y sm:divide-y-0 sm:divide-x divide-gray-100">
            {{-- Impressions --}}
            <div class="p-6 text-center">
                <div class="flex items-center justify-center gap-2 mb-2">
                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-slate-600" viewBox="0 0 24 24" fill="none"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke="currentColor" stroke-width="1.5"/></svg>
                    </div>
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Total Impressions</span>
                </div>
                <div class="text-3xl font-bold text-gray-900">{{ number_format($summary['impressions']) }}</div>
            </div>

            {{-- Clicks --}}
            <div class="p-6 text-center">
                <div class="flex items-center justify-center gap-2 mb-2">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="none"><path d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </div>
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Total Clicks</span>
                </div>
                <div class="text-3xl font-bold text-gray-900">{{ number_format($summary['clicks']) }}</div>
            </div>

            {{-- CTR --}}
            <div class="p-6 text-center">
                <div class="flex items-center justify-center gap-2 mb-2">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="none"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">CTR</span>
                </div>
                <div class="text-3xl font-bold text-gray-900">{{ number_format($summary['ctr'], 2) }}%</div>
            </div>
        </div>
    </div>

    {{-- Geographic Distribution Map --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Geographic Distribution</h2>
            <p class="text-xs text-gray-500 mt-1">Click on markers to view country statistics</p>
        </div>
        <div id="map" class="w-full h-[400px] bg-gray-100"></div>
    </div>

    {{-- Country Performance Table --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 border-b border-gray-100">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">Country Performance</h2>
                <p class="text-xs text-gray-500 mt-1">{{ $countryData->count() }} countries with traffic data</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="networkKitTable" class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Country</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Impressions</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Clicks</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Conversions</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">CTR</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($countryData as $row)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-semibold text-xs">
                                        {{ $row->country_code }}
                                    </div>
                                    <span class="font-medium text-gray-900">{{ $countryNames[$row->country_code] ?? $row->country_code }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format($row->impressions) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format($row->clicks) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format($row->conversions) }}</td>
                            <td class="px-4 py-3 text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $row->ctr >= 1 ? 'bg-emerald-100 text-emerald-700' : ($row->ctr >= 0.5 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600') }}">
                                    {{ number_format($row->ctr, 2) }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    <p class="text-sm text-gray-500">No data available for the selected filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    const mapData = @json($mapData);

    document.addEventListener('DOMContentLoaded', function() {
        const map = L.map('map', {
            center: [30, 10],
            zoom: 2,
            scrollWheelZoom: true,
        });

        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 19
        }).addTo(map);

        const maxImpressions = Math.max(...mapData.map(d => d.impressions), 1);

        mapData.forEach(function(data) {
            if (data.lat && data.lng) {
                const radius = Math.max(8, Math.min(30, (data.impressions / maxImpressions) * 30));

                const marker = L.circleMarker([data.lat, data.lng], {
                    radius: radius,
                    fillColor: '#e11d48',
                    color: '#be123c',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.7
                }).addTo(map);

                const popupContent = `
                    <div style="min-width: 180px;">
                        <div style="font-weight: 600; font-size: 14px; margin-bottom: 8px; color: #111827;">${data.country_name} (${data.country_code})</div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px; font-size: 12px;">
                            <span style="color: #6b7280;">Impressions:</span>
                            <span style="font-weight: 500; text-align: right;">${data.impressions.toLocaleString()}</span>
                            <span style="color: #6b7280;">Clicks:</span>
                            <span style="font-weight: 500; text-align: right;">${data.clicks.toLocaleString()}</span>
                            <span style="color: #6b7280;">Conversions:</span>
                            <span style="font-weight: 500; text-align: right;">${data.conversions.toLocaleString()}</span>
                            <span style="color: #6b7280;">CTR:</span>
                            <span style="font-weight: 500; text-align: right;">${data.ctr.toFixed(2)}%</span>
                        </div>
                    </div>
                `;

                marker.bindPopup(popupContent);
            }
        });
    });

    function copyTable() {
        const table = document.getElementById('networkKitTable');
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
