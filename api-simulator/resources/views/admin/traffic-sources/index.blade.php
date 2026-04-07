@extends('layouts.admin')

@section('title', 'Traffic Sources')

@section('content')
    {{-- Success/Error flash --}}
    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-700 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Traffic Sources</h1>
            <p class="text-sm text-gray-500 mt-1">Manage traffic sources and bid rates for campaigns.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('addTrafficSourceModal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Add Traffic Source
            </button>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        @php
            $statCards = [
                ['label' => 'Total Sources', 'value' => number_format($totalSources), 'color' => 'text-gray-900', 'bg' => 'bg-gray-50', 'border' => 'border-gray-200', 'icon' => '<path d="M13 10V3L4 14h7v7l9-11h-7z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'],
                ['label' => 'Active', 'value' => number_format($activeSources), 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'icon' => '<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Paused', 'value' => number_format($pausedSources), 'color' => 'text-amber-700', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'icon' => '<path d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Avg Bid Rate', 'value' => '$' . number_format($avgBidRate, 2), 'color' => 'text-blue-700', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'icon' => '<path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/>'],
            ];
        @endphp
        @foreach($statCards as $card)
            <div class="rounded-xl border {{ $card['border'] }} {{ $card['bg'] }} p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                    <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none">{!! $card['icon'] !!}</svg>
                </div>
                <div class="text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Search & Export Bar --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4">
            {{-- Search & Filters --}}
            <form method="GET" action="{{ route('admin.traffic-sources') }}" class="flex items-center gap-2 flex-1 flex-wrap">
                <div class="relative flex-1 max-w-md">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by source or campaign..."
                           class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="paused" {{ request('status') === 'paused' ? 'selected' : '' }}>Paused</option>
                </select>
                <select name="source" onchange="this.form.submit()" class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Sources</option>
                    @foreach($sourceLookups as $src)
                        <option value="{{ $src->id }}" {{ request('source') == $src->id ? 'selected' : '' }}>{{ $src->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium text-gray-600 hover:bg-gray-200 transition-colors">Search</button>
            </form>

            {{-- Export dropdown --}}
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Export
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-44 bg-white rounded-xl border border-gray-200 shadow-lg py-1 z-50" style="display:none;">
                    <button onclick="copyTable()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Copy</button>
                    <a href="{{ route('admin.traffic-sources.export', request()->all()) }}" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">CSV</a>
                    <button onclick="window.print()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Print</button>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table id="trafficSourceTable" class="w-full text-sm">
                <thead>
                    <tr class="border-t border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Source ID</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Source</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Bid Rate</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Campaign</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($trafficSources as $ts)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">#{{ $ts->id }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 flex-shrink-0">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M13 10V3L4 14h7v7l9-11h-7z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </div>
                                    <span class="font-medium text-gray-900">{{ $ts->source->name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-gray-900">${{ number_format($ts->bid_rate, 2) }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($ts->status === 'active')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Paused
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($ts->campaign_id)
                                    <div class="font-medium text-gray-900">
                                        @if($ts->campaign_type === 'direct' && $ts->directCampaign)
                                            {{ $ts->directCampaign->name }}
                                        @elseif($ts->campaign)
                                            {{ $ts->campaign->name }}
                                        @else
                                            Campaign #{{ $ts->campaign_id }}
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        #{{ $ts->campaign_id }}{{ ($ts->campaign_type === 'direct') ? ' - Direct' : '' }}
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <button onclick="deleteTrafficSource({{ $ts->id }})" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors" title="Delete">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M13 10V3L4 14h7v7l9-11h-7z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    <p class="text-sm text-gray-500">No traffic sources found.</p>
                                    <button onclick="document.getElementById('addTrafficSourceModal').classList.remove('hidden')" class="text-sm text-brand-600 hover:text-brand-700 font-medium">Add your first traffic source</button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($trafficSources->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $trafficSources->links() }}
            </div>
        @endif
    </div>

    {{-- Add Traffic Source Modal --}}
    <div id="addTrafficSourceModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Add Traffic Source</h3>
                <button onclick="document.getElementById('addTrafficSourceModal').classList.add('hidden')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.traffic-sources.store') }}" class="p-6 space-y-4">
                @csrf

                {{-- Validation errors --}}
                @if($errors->any())
                    <div class="p-3 rounded-lg border border-red-300 bg-red-50 text-red-700 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Source <span class="text-red-500">*</span></label>
                    <select name="traffic_source_id" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        <option value="">Select Source</option>
                        @foreach($sourceLookups as $src)
                            <option value="{{ $src->id }}" {{ old('traffic_source_id') == $src->id ? 'selected' : '' }}>{{ $src->name }}</option>
                        @endforeach
                    </select>
                    @error('traffic_source_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Campaign <span class="text-red-500">*</span></label>
                    <select name="campaign_id" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        <option value="">Select Campaign</option>
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign['type'] }}:{{ $campaign['id'] }}" {{ old('campaign_id') === $campaign['type'].':'.$campaign['id'] ? 'selected' : '' }}>{{ $campaign['label'] }}</option>
                        @endforeach
                    </select>
                    @error('campaign_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Bid Rate ($) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">$</span>
                            <input type="number" name="bid_rate" required min="0" step="0.01" value="{{ old('bid_rate') }}" placeholder="0.00" class="w-full pl-8 pr-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        </div>
                        @error('bid_rate')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Status <span class="text-red-500">*</span></label>
                        <select name="status" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="paused" {{ old('status') === 'paused' ? 'selected' : '' }}>Paused</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('addTrafficSourceModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm">Add Source</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Delete traffic source
    function deleteTrafficSource(id) {
        if (!confirm('Are you sure you want to delete this traffic source?')) return;

        fetch(`/admin/traffic-sources/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => {
            if (data.success) location.reload();
            else alert(data.message || 'Error deleting traffic source.');
        });
    }

    // Copy table data
    function copyTable() {
        const table = document.getElementById('trafficSourceTable');
        const rows = table.querySelectorAll('tr');
        let text = '';
        rows.forEach(row => {
            const cells = row.querySelectorAll('th, td');
            const rowData = [];
            cells.forEach((cell, i) => {
                if (i < cells.length - 1) rowData.push(cell.textContent.trim().replace(/\s+/g, ' '));
            });
            text += rowData.join('\t') + '\n';
        });
        navigator.clipboard.writeText(text).then(() => alert('Table data copied to clipboard!'));
    }

    // Reopen modal if there are validation errors
    @if($errors->any())
        document.getElementById('addTrafficSourceModal').classList.remove('hidden');
    @endif
</script>
@endpush
