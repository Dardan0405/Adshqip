@extends('layouts.publisher')

@section('title', 'Geographic Report')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    .leaflet-popup-content-wrapper { border-radius: 12px; }
    .leaflet-popup-content { margin: 12px 16px; }
    #map { height: 300px; min-height: 300px; width: 100%; }
    .leaflet-container { height: 100%; width: 100%; }
    .leaflet-container img,
    .leaflet-container .leaflet-tile {
        max-width: none !important;
        max-height: none !important;
    }
    @media (max-width: 640px) {
        #map { height: 220px; min-height: 220px; }
    }
</style>
@endpush

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Geographic Report</h1>
            <p class="text-sm text-gray-500 mt-1">Daily earnings breakdown by country with interactive map.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Total Impressions</p>
            <p class="mt-2 text-2xl font-bold text-blue-700">{{ number_format($summary['impressions']) }}</p>
        </div>
        <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Total Clicks</p>
            <p class="mt-2 text-2xl font-bold text-indigo-700">{{ number_format($summary['clicks']) }}</p>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Total Earnings</p>
            <p class="mt-2 text-2xl font-bold text-emerald-700">&euro;{{ number_format($summary['earnings'], 4) }}</p>
        </div>
    </div>

    <form method="GET" action="{{ route('publisher.reports.geo') }}" class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4">
            <div class="flex items-center gap-2 flex-1 flex-wrap">
                <div class="relative flex-1 min-w-[180px] max-w-xs">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search country..."
                           class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <input type="date" name="start_date" value="{{ request('start_date', $defaults['start_date']) }}"
                       class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                <input type="date" name="end_date" value="{{ request('end_date', $defaults['end_date']) }}"
                       class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-800 transition-colors">Filter</button>
                <a href="{{ route('publisher.reports.geo') }}" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</a>
            </div>

            <div class="relative" x-data="{ open: false }">
                <button type="button" @click="open = !open"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Export
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <div x-show="open" @click.away="open = false" x-transition
                     class="absolute right-0 mt-2 w-44 bg-white rounded-xl border border-gray-200 shadow-lg py-1 z-50" style="display:none;">
                    <button type="button" onclick="copyGeoTable()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Copy</button>
                    <a href="{{ route('publisher.reports.geo.export', request()->all()) }}" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">CSV</a>
                    <button type="button" onclick="window.print()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Print</button>
                </div>
            </div>
        </div>
    </form>

    <div class="bg-white rounded-xl border border-gray-200 mb-6 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Geographic Distribution</h2>
            <p class="text-xs text-gray-500 mt-1">Click markers to view country earnings</p>
        </div>
        <div id="map" class="w-full bg-gray-100"></div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 border-b border-gray-100">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">Country &amp; Date Breakdown</h2>
                <p class="text-xs text-gray-500 mt-1">{{ $tableData->count() }} rows in selected period</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="geoTable" class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Date</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Country</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Impressions</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Clicks</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Earnings</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tableData as $row)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($row->date)->format('M j, Y') }}</div>
                                <div class="text-xs text-gray-400">{{ $row->date }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-semibold text-xs flex-shrink-0">
                                        {{ $row->country_code }}
                                    </div>
                                    <span class="font-medium text-gray-900">{{ $countryNames[$row->country_code] ?? $row->country_code }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format($row->impressions) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format($row->clicks) }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-700">&euro;{{ number_format($row->earnings, 4) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    <p class="text-sm text-gray-500">No geographic data for the selected period.</p>
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
        const mapElement = document.getElementById('map');
        if (!mapElement || typeof L === 'undefined') {
            return;
        }

        const map = L.map(mapElement, {
            center: [30, 10],
            zoom: 2,
            scrollWheelZoom: true,
            worldCopyJump: true,
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
                            <span style="color: #6b7280;">Earnings:</span>
                            <span style="font-weight: 500; text-align: right;">&euro;${data.earnings.toFixed(4)}</span>
                        </div>
                    </div>
                `;

                marker.bindPopup(popupContent);
            }
        });

        const refreshMapSize = () => map.invalidateSize({ pan: false });
        setTimeout(refreshMapSize, 100);
        setTimeout(refreshMapSize, 500);
        window.addEventListener('resize', refreshMapSize);
    });

    function copyGeoTable() {
        const table = document.getElementById('geoTable');
        const rows  = table.querySelectorAll('tr');
        let text    = '';
        rows.forEach(row => {
            const cells = row.querySelectorAll('th, td');
            const data  = [];
            cells.forEach(cell => data.push(cell.textContent.trim().replace(/\s+/g, ' ')));
            text += data.join('\t') + '\n';
        });
        navigator.clipboard.writeText(text).then(() => alert('Table data copied to clipboard!'));
    }
</script>
@endpush
