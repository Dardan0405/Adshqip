@extends('layouts.publisher')

@section('title', 'Overview Report')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Overview Report</h1>
            <p class="text-sm text-gray-500 mt-1">Daily performance across your ad zones.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Impressions</p>
            <p class="mt-2 text-2xl font-bold text-blue-700">{{ number_format($summary->total_impressions) }}</p>
        </div>
        <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Clicks</p>
            <p class="mt-2 text-2xl font-bold text-indigo-700">{{ number_format($summary->total_clicks) }}</p>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Earnings</p>
            <p class="mt-2 text-2xl font-bold text-emerald-700">&euro;{{ number_format($summary->total_earnings, 4) }}</p>
        </div>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">ECPM</p>
            <p class="mt-2 text-2xl font-bold text-amber-700">&euro;{{ number_format($summary->ecpm, 2) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4">
            <form method="GET" action="{{ route('publisher.reports.overview') }}" class="flex items-center gap-2 flex-1 flex-wrap">
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                       class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                       class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium text-gray-600 hover:bg-gray-200 transition-colors">Search</button>
                @if(request()->hasAny(['start_date', 'end_date']))
                    <a href="{{ route('publisher.reports.overview') }}" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-500 hover:bg-gray-50 transition-colors">Clear</a>
                @endif
            </form>

            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" type="button"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Export
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <div x-show="open" @click.away="open = false" x-transition
                     class="absolute right-0 mt-2 w-44 bg-white rounded-xl border border-gray-200 shadow-lg py-1 z-50" style="display:none;">
                    <button type="button" onclick="copyOverviewTable()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Copy</button>
                    <a href="{{ route('publisher.reports.overview.export', request()->all()) }}" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">CSV</a>
                    <button type="button" onclick="window.print()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Print</button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="overviewTable" class="w-full text-sm">
                <thead>
                    <tr class="border-t border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Date</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Impressions</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Clicks</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Earnings</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">ECPM</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($reports as $row)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($row->date)->format('M j, Y') }}</div>
                                <div class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($row->date)->format('Y-m-d') }}</div>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap font-medium text-gray-800">{{ number_format($row->total_impressions) }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap font-medium text-blue-700">{{ number_format($row->total_clicks) }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap font-semibold text-emerald-700">&euro;{{ number_format($row->total_earnings, 4) }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap font-medium text-amber-700">&euro;{{ number_format($row->ecpm, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm6 0V9a2 2 0 00-2-2h-2a2 2 0 00-2 2v10a2 2 0 002 2h2a2 2 0 002-2zm6 0V5a2 2 0 00-2-2h-2a2 2 0 00-2 2v14a2 2 0 002 2h2a2 2 0 002-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    <p class="text-sm text-gray-500">No data found for the selected period.</p>
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
    function copyOverviewTable() {
        const table = document.getElementById('overviewTable');
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
