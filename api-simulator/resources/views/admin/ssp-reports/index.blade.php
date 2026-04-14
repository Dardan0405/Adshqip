@extends('layouts.admin')

@section('title', 'SSP Report')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">SSP Report</h1>
            <p class="text-sm text-gray-500 mt-1">Supply-Side Platform performance analytics and inventory metrics.</p>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 mb-6">
        @php
            $statCards = [
                ['label' => 'Bid Requests', 'value' => number_format($summary['total_requests']), 'color' => 'text-slate-900', 'bg' => 'bg-slate-50', 'border' => 'border-slate-200', 'icon' => '<path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Bid Responses', 'value' => number_format($summary['total_responses']), 'color' => 'text-blue-700', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'icon' => '<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Won Responses', 'value' => number_format($summary['won_responses']), 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'icon' => '<path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Fill Rate', 'value' => $summary['fill_rate'] . '%', 'color' => 'text-violet-700', 'bg' => 'bg-violet-50', 'border' => 'border-violet-200', 'icon' => '<path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'],
                ['label' => 'Avg Floor Price', 'value' => '$' . number_format($summary['avg_floor_price'], 4), 'color' => 'text-cyan-700', 'bg' => 'bg-cyan-50', 'border' => 'border-cyan-200', 'icon' => '<path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/>'],
                ['label' => 'Avg Won Price', 'value' => '$' . number_format($summary['avg_won_price'], 4), 'color' => 'text-pink-700', 'bg' => 'bg-pink-50', 'border' => 'border-pink-200', 'icon' => '<path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Total Revenue', 'value' => '$' . number_format($summary['total_revenue'], 2), 'color' => 'text-amber-700', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'icon' => '<path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/>'],
                ['label' => 'Active Partners', 'value' => $summary['active_exchanges'], 'color' => 'text-indigo-700', 'bg' => 'bg-indigo-50', 'border' => 'border-indigo-200', 'icon' => '<path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
            ];
        @endphp
        @foreach($statCards as $card)
            <div class="rounded-xl border {{ $card['border'] }} {{ $card['bg'] }} p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                    <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none">{!! $card['icon'] !!}</svg>
                </div>
                <div class="text-xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Filters Section --}}
    <form method="GET" action="{{ route('admin.reports.ssp') }}" class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4">
            <div class="flex items-center gap-2 flex-1 flex-wrap">
                {{-- Search --}}
                <div class="relative flex-1 min-w-[180px] max-w-xs">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search exchange..."
                           class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>

                {{-- Date Range --}}
                <div>
                    <input type="date" name="start_date" value="{{ request('start_date', $defaults['start_date']) }}"
                           class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                </div>
                <div>
                    <input type="date" name="end_date" value="{{ request('end_date', $defaults['end_date']) }}"
                           class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                </div>

                {{-- Exchange Filter --}}
                <select name="exchange_id" class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Partners</option>
                    @foreach($exchanges as $id => $name)
                        <option value="{{ $id }}" {{ request('exchange_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>

                {{-- Type Filter --}}
                <select name="type" class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Types</option>
                    <option value="SSP" {{ request('type') == 'SSP' ? 'selected' : '' }}>SSP</option>
                    <option value="ad_network" {{ request('type') == 'ad_network' ? 'selected' : '' }}>Ad Network</option>
                </select>

                {{-- Status Filter --}}
                <select name="status" class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="testing" {{ request('status') == 'testing' ? 'selected' : '' }}>Testing</option>
                </select>

                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-800 transition-colors">Filter</button>
                <a href="{{ route('admin.reports.ssp') }}" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</a>
            </div>

            {{-- Export Dropdown --}}
            <div class="relative" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Export
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-44 bg-white rounded-xl border border-gray-200 shadow-lg py-1 z-50" style="display:none;">
                    <button type="button" onclick="copyTable()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Copy</button>
                    <a href="{{ route('admin.reports.ssp.export', request()->all()) }}" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">CSV</a>
                    <button type="button" onclick="window.print()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Print</button>
                </div>
            </div>
        </div>
    </form>

    {{-- SSP Performance Table --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 border-b border-gray-100">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">Supply Partner Performance</h2>
                <p class="text-xs text-gray-500 mt-1">{{ $sspData->count() }} supply partners with inventory data</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="sspTable" class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Ad-Exchange Name</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Type</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Bid Requests</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Bid Responses</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Won Responses</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Floor Price</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Won Price</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Fill Rate</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Revenue</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sspData as $row)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-700 font-semibold text-xs">
                                        {{ strtoupper(substr($row->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-900">{{ $row->name }}</span>
                                        <p class="text-[10px] text-gray-400 truncate max-w-[200px]">{{ $row->endpoint_url }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $row->type === 'SSP' ? 'bg-purple-100 text-purple-700' : 'bg-orange-100 text-orange-700' }}">
                                    {{ $row->type === 'ad_network' ? 'Ad Network' : $row->type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format($row->bid_requests) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format($row->bid_responses) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-emerald-600">{{ number_format($row->won_responses) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">${{ number_format($row->avg_floor_price, 4) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">${{ number_format($row->avg_won_price, 4) }}</td>
                            <td class="px-4 py-3 text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $row->fill_rate >= 30 ? 'bg-emerald-100 text-emerald-700' : ($row->fill_rate >= 15 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600') }}">
                                    {{ number_format($row->fill_rate, 2) }}%
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-600">${{ number_format($row->total_revenue, 2) }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($row->status === 'active')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Active
                                    </span>
                                @elseif($row->status === 'testing')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Testing
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('admin.rtb') }}?search={{ urlencode($row->name) }}" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600" title="View Details">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke="currentColor" stroke-width="1.5"/></svg>
                                    </a>
                                    <button type="button" onclick="viewInventory({{ $row->id }})" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-purple-600" title="View Inventory">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M4 6.5A2.5 2.5 0 016.5 4h11A2.5 2.5 0 0120 6.5v11a2.5 2.5 0 01-2.5 2.5h-11A2.5 2.5 0 014 17.5v-11z" stroke="currentColor" stroke-width="1.5"/><path d="M4 9h16M9 20V9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    </button>
                                    <button type="button" onclick="toggleStatus({{ $row->id }}, '{{ $row->status }}')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-amber-600" title="{{ $row->status === 'active' ? 'Pause' : 'Activate' }}">
                                        @if($row->status === 'active')
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        @else
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" stroke="currentColor" stroke-width="1.5"/><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/></svg>
                                        @endif
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    <p class="text-sm text-gray-500">No SSP data available for the selected period.</p>
                                    <a href="{{ route('admin.rtb') }}" class="text-sm text-brand-600 hover:underline">Add Supply Partner</a>
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
<script>
    function copyTable() {
        const table = document.getElementById('sspTable');
        const rows = table.querySelectorAll('tr');
        let text = '';

        rows.forEach((row) => {
            const cells = row.querySelectorAll('th, td');
            const rowData = [];

            cells.forEach((cell, index) => {
                // Skip the Actions column
                if (index < cells.length - 1) {
                    rowData.push(cell.textContent.trim().replace(/\s+/g, ' '));
                }
            });

            text += rowData.join('\t') + '\n';
        });

        navigator.clipboard.writeText(text).then(() => alert('Table data copied to clipboard!'));
    }

    function viewInventory(id) {
        // Redirect to RTB page with exchange filter
        window.location.href = '{{ route("admin.rtb") }}?id=' + id;
    }

    function toggleStatus(id, currentStatus) {
        const action = currentStatus === 'active' ? 'block' : 'unblock';
        const message = currentStatus === 'active'
            ? 'Are you sure you want to pause this supply partner?'
            : 'Are you sure you want to activate this supply partner?';

        if (confirm(message)) {
            fetch(`/admin/rtb/${id}/${action}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'An error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }
    }
</script>
@endpush
