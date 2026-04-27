@extends('layouts.publisher')

@section('title', 'Group Report')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Group Report</h1>
            <p class="text-sm text-gray-500 mt-1">Aggregate earnings and traffic grouped by zone, site, country, device, or date.</p>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
        @php
            $cards = [
                ['label' => 'Requests',    'value' => number_format($summary['requests']),           'color' => 'text-slate-900',   'bg' => 'bg-slate-50',   'border' => 'border-slate-200'],
                ['label' => 'Impressions', 'value' => number_format($summary['impressions']),         'color' => 'text-blue-700',    'bg' => 'bg-blue-50',    'border' => 'border-blue-200'],
                ['label' => 'Clicks',      'value' => number_format($summary['clicks']),              'color' => 'text-indigo-700',  'bg' => 'bg-indigo-50',  'border' => 'border-indigo-200'],
                ['label' => 'Conversions', 'value' => number_format($summary['conversions']),         'color' => 'text-purple-700',  'bg' => 'bg-purple-50',  'border' => 'border-purple-200'],
                ['label' => 'Revenue',     'value' => '€' . number_format($summary['revenue'], 4),   'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200'],
                ['label' => 'CTR',         'value' => number_format($summary['ctr'], 2) . '%',       'color' => 'text-rose-700',    'bg' => 'bg-rose-50',    'border' => 'border-rose-200'],
                ['label' => 'ECPM',        'value' => '€' . number_format($summary['ecpm'], 2),      'color' => 'text-amber-700',   'bg' => 'bg-amber-50',   'border' => 'border-amber-200'],
            ];
        @endphp
        @foreach($cards as $card)
            <div class="rounded-xl border {{ $card['border'] }} {{ $card['bg'] }} p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-2">{{ $card['label'] }}</div>
                <div class="text-xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('publisher.reports.groups') }}" class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4">
            <div class="flex items-center gap-2 flex-1 flex-wrap">
                {{-- Group By --}}
                <select name="group_by"
                        class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                    @foreach($groupByOptions as $key => $label)
                        <option value="{{ $key }}" {{ request('group_by', 'zone') === $key ? 'selected' : '' }}>
                            Group by: {{ $label }}
                        </option>
                    @endforeach
                </select>

                {{-- Search --}}
                <div class="relative flex-1 min-w-[180px] max-w-xs">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search group..."
                           class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>

                <input type="date" name="start_date" value="{{ request('start_date', $defaults['start_date']) }}"
                       class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                <input type="date" name="end_date" value="{{ request('end_date', $defaults['end_date']) }}"
                       class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-800 transition-colors">Filter</button>
                <a href="{{ route('publisher.reports.groups') }}" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</a>
            </div>

            {{-- Export dropdown --}}
            <div class="relative" x-data="{ open: false }">
                <button type="button" @click="open = !open"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Export
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <div x-show="open" @click.away="open = false" x-transition
                     class="absolute right-0 mt-2 w-44 bg-white rounded-xl border border-gray-200 shadow-lg py-1 z-50" style="display:none;">
                    <button type="button" onclick="copyGroupTable()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Copy</button>
                    <a href="{{ route('publisher.reports.groups.export', request()->all()) }}" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">CSV</a>
                    <button type="button" onclick="window.print()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Print</button>
                </div>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">
                Grouped by: <span class="text-brand-600">{{ $groupByOptions[request('group_by', 'zone')] }}</span>
            </h2>
            <p class="text-xs text-gray-500 mt-1">{{ $reports->total() }} groups in selected period</p>
        </div>

        <div class="overflow-x-auto">
            <table id="groupReportTable" class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">
                            {{ $groupByOptions[request('group_by', 'zone')] }}
                        </th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Requests</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Impressions</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Clicks</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Conversions</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Revenue</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">CTR</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">ECPM</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($reports as $row)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-brand-100 flex items-center justify-center text-brand-700 font-semibold text-xs flex-shrink-0">
                                        {{ strtoupper(substr($row->group_label, 0, 2)) }}
                                    </div>
                                    <span class="font-medium text-gray-900">{{ $row->group_label }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-500 whitespace-nowrap">{{ number_format($row->requests) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900 whitespace-nowrap">{{ number_format($row->impressions) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-indigo-700 whitespace-nowrap">{{ number_format($row->clicks) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-purple-700 whitespace-nowrap">{{ number_format($row->conversions) }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-700 whitespace-nowrap">&euro;{{ number_format($row->revenue, 4) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-rose-700 whitespace-nowrap">{{ number_format($row->ctr, 2) }}%</td>
                            <td class="px-4 py-3 text-right font-medium text-amber-700 whitespace-nowrap">&euro;{{ number_format($row->ecpm, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    <p class="text-sm text-gray-500">No group data found for the selected period.</p>
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
    function copyGroupTable() {
        const rows = document.querySelectorAll('#groupReportTable tr');
        let text = '';
        rows.forEach(row => {
            const cells = row.querySelectorAll('th, td');
            text += Array.from(cells).map(c => c.textContent.trim().replace(/\s+/g, ' ')).join('\t') + '\n';
        });
        navigator.clipboard.writeText(text).then(() => alert('Table data copied to clipboard!'));
    }
</script>
@endpush
