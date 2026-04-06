@extends('layouts.admin')

@section('title', 'AdBlock Reports - ' . $zone->name)

@section('content')
    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.adblocks') }}" class="p-2 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $zone->name }} - Reports</h1>
                    <p class="text-sm text-gray-500 mt-1">Performance analytics for {{ $zone->site->name ?? 'Unknown Site' }}</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('exportModal').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                Export
            </button>
        </div>
    </div>

    {{-- ═══════════ STAT CARDS ═══════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        @php
            $statCards = [
                ['label' => 'Impressions', 'value' => number_format($totals->total_impressions ?? 0), 'color' => 'text-blue-700', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200'],
                ['label' => 'Clicks', 'value' => number_format($totals->total_clicks ?? 0), 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200'],
                ['label' => 'Earnings', 'value' => '€' . number_format($totals->total_earnings ?? 0, 2), 'color' => 'text-brand-700', 'bg' => 'bg-brand-50', 'border' => 'border-brand-200'],
                ['label' => 'eCPM', 'value' => '€' . number_format($totals->avg_ecpm ?? 0, 2), 'color' => 'text-purple-700', 'bg' => 'bg-purple-50', 'border' => 'border-purple-200'],
            ];
        @endphp
        @foreach($statCards as $card)
            <div class="rounded-xl border {{ $card['border'] }} {{ $card['bg'] }} p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                <div class="text-2xl font-bold {{ $card['color'] }} mt-1">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Performance overview --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Average CTR</div>
                    <div class="text-xl font-bold text-gray-900 mt-1">{{ number_format($totals->avg_ctr ?? 0, 2) }}%</div>
                </div>
                <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="none"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Total Earnings</div>
                    <div class="text-xl font-bold text-gray-900 mt-1">&euro;{{ number_format($totals->total_earnings ?? 0, 2) }}</div>
                </div>
                <div class="w-10 h-10 rounded-lg bg-brand-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand-600" viewBox="0 0 24 24" fill="none"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════ CHART SECTION ═══════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900">Performance Trend</h2>
            <div class="flex items-center gap-2">
                <button type="button" onclick="downloadChart('png')" class="p-2 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors" title="Download PNG">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <button type="button" onclick="downloadChart('jpg')" class="p-2 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors" title="Download JPG">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <button type="button" onclick="downloadChart('pdf')" class="p-2 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors" title="Download PDF">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
                <button type="button" onclick="printChart()" class="p-2 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors" title="Print Chart">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>
        </div>
        <div class="relative" style="height: 350px;">
            <canvas id="performanceChart"></canvas>
        </div>
    </div>

    {{-- ═══════════ FILTERS ═══════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-5 py-4 border-b border-gray-100">
            <form method="GET" action="{{ route('admin.adblocks.reports', $zone->id) }}" class="flex flex-col sm:flex-row sm:items-end gap-3">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-600 mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Group By</label>
                    <select name="group_by" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        <option value="day" {{ $groupBy === 'day' ? 'selected' : '' }}>Day</option>
                        <option value="week" {{ $groupBy === 'week' ? 'selected' : '' }}>Week</option>
                        <option value="month" {{ $groupBy === 'month' ? 'selected' : '' }}>Month</option>
                    </select>
                </div>
                <button type="submit" class="px-6 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm transition-colors">Apply</button>
            </form>
        </div>

        {{-- ═══════════ TABLE ═══════════ --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Period</th>
                        <th class="text-right px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Impressions</th>
                        <th class="text-right px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Clicks</th>
                        <th class="text-right px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Earnings</th>
                        <th class="text-right px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">eCPM</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($stats as $stat)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-3.5 font-medium text-gray-900">
                                {{ \Carbon\Carbon::parse($stat->period_start)->format('M d, Y') }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-medium text-gray-800 tabular-nums">
                                {{ number_format($stat->impressions) }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-medium text-gray-800 tabular-nums">
                                {{ number_format($stat->clicks) }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-semibold text-brand-600 tabular-nums">
                                &euro;{{ number_format($stat->earnings, 2) }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-medium text-gray-800 tabular-nums">
                                &euro;{{ number_format($stat->ecpm, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" stroke="currentColor" stroke-width="1.5"/></svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-600">No data available</p>
                                    <p class="text-xs text-gray-400 mt-1">Try adjusting your date range or filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Table footer with pagination --}}
        @if($stats->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">
                {{ $stats->links() }}
            </div>
        @endif
    </div>

    {{-- ═══════════ MODAL: EXPORT REPORTS ═══════════ --}}
    <div id="exportModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-full bg-brand-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-brand-600" viewBox="0 0 24 24" fill="none"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Export Reports</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Choose your preferred export format</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-5">
                {{-- CSV --}}
                <a href="{{ route('admin.adblocks.reports.export', ['id' => $zone->id, 'format' => 'csv', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-colors group cursor-pointer">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                        <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="none"><path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M9 13h6m-6 3h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </div>
                    <span class="text-sm font-semibold text-gray-700 group-hover:text-blue-700">CSV (.csv)</span>
                    <span class="text-[10px] text-gray-400">Comma separated</span>
                </a>

                {{-- Print --}}
                <button type="button" onclick="exportPrint()" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 hover:border-purple-300 hover:bg-purple-50 transition-colors group cursor-pointer">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                        <svg class="w-5 h-5 text-purple-600" viewBox="0 0 24 24" fill="none"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <span class="text-sm font-semibold text-gray-700 group-hover:text-purple-700">Print</span>
                    <span class="text-[10px] text-gray-400">Send to printer</span>
                </button>
            </div>

            <div class="flex justify-end">
                <button type="button" onclick="document.getElementById('exportModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">Cancel</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <script>
        // Chart data
        var chartData = @json($chartData);
        var labels = chartData.map(d => d.date);
        var impressionsData = chartData.map(d => parseInt(d.impressions));
        var clicksData = chartData.map(d => parseInt(d.clicks));
        var earningsData = chartData.map(d => parseFloat(d.earnings));

        // Create chart
        var ctx = document.getElementById('performanceChart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Impressions',
                        data: impressionsData,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Clicks',
                        data: clicksData,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Earnings (€)',
                        data: earningsData,
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    if (label.includes('Earnings')) {
                                        label += '€' + context.parsed.y.toFixed(2);
                                    } else {
                                        label += context.parsed.y.toLocaleString();
                                    }
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Impressions / Clicks'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Earnings (€)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });

        function downloadChart(format) {
            var canvas = document.getElementById('performanceChart');
            var link = document.createElement('a');
            link.download = 'adblock_{{ $zone->id }}_chart.' + format;

            if (format === 'png' || format === 'jpg') {
                var imageType = format === 'png' ? 'image/png' : 'image/jpeg';
                link.href = canvas.toDataURL(imageType);
                link.click();
            } else if (format === 'pdf') {
                var imgData = canvas.toDataURL('image/png');
                var win = window.open('', '_blank');
                win.document.write('<html><head><title>Chart PDF</title></head><body>');
                win.document.write('<img src="' + imgData + '" style="max-width:100%;" />');
                win.document.write('<script>window.onload = function() { window.print(); }<\/script>');
                win.document.write('</body></html>');
                win.document.close();
            }
        }

        function printChart() {
            var canvas = document.getElementById('performanceChart');
            var imgData = canvas.toDataURL('image/png');
            var win = window.open('', '_blank');
            win.document.write('<html><head><title>Print Chart</title><style>body{margin:0;padding:20px;}img{max-width:100%;}</style></head><body>');
            win.document.write('<h2>{{ $zone->name }} - Performance Chart</h2>');
            win.document.write('<img src="' + imgData + '" />');
            win.document.write('<script>window.onload = function() { window.print(); window.onafterprint = function() { window.close(); }; }<\/script>');
            win.document.write('</body></html>');
            win.document.close();
        }

        function exportPrint() {
            document.getElementById('exportModal').classList.add('hidden');
            window.print();
        }
    </script>
@endsection
