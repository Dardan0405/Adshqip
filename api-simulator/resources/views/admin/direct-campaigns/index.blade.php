@extends('layouts.admin')

@section('title', 'Direct Campaigns')

@section('content')
    {{-- Success flash --}}
    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-700 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Direct Campaigns</h1>
            <p class="text-sm text-gray-500 mt-1">Manage direct-deal campaigns with premium advertisers.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('exportModal').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                Export
            </button>
            <a href="{{ route('admin.direct-campaigns.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                New Direct Campaign
            </a>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 mb-6">
        @php
            $statCards = [
                ['label' => 'Total', 'value' => $stats['total'], 'color' => 'text-gray-900', 'bg' => 'bg-gray-50', 'border' => 'border-gray-200'],
                ['label' => 'Active', 'value' => $stats['active'], 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200'],
                ['label' => 'Paused', 'value' => $stats['paused'], 'color' => 'text-amber-700', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200'],
                ['label' => 'Draft', 'value' => $stats['draft'], 'color' => 'text-gray-600', 'bg' => 'bg-gray-50', 'border' => 'border-gray-200'],
                ['label' => 'Completed', 'value' => $stats['completed'], 'color' => 'text-blue-700', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200'],
                ['label' => 'Pending', 'value' => $stats['pending_review'], 'color' => 'text-orange-700', 'bg' => 'bg-orange-50', 'border' => 'border-orange-200'],
                ['label' => 'Rejected', 'value' => $stats['rejected'], 'color' => 'text-red-700', 'bg' => 'bg-red-50', 'border' => 'border-red-200'],
                ['label' => 'Archived', 'value' => $stats['archived'], 'color' => 'text-purple-700', 'bg' => 'bg-purple-50', 'border' => 'border-purple-200'],
            ];
        @endphp
        @foreach($statCards as $card)
            <div class="rounded-xl border {{ $card['border'] }} {{ $card['bg'] }} p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                <div class="text-2xl font-bold {{ $card['color'] }} mt-1">{{ number_format($card['value']) }}</div>
            </div>
        @endforeach
    </div>

    {{-- Budget overview --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Total Budget Allocated</div>
                    <div class="text-xl font-bold text-gray-900 mt-1">&euro;{{ number_format($stats['total_budget'], 2) }}</div>
                </div>
                <div class="w-10 h-10 rounded-lg bg-brand-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand-600" viewBox="0 0 24 24" fill="none"><path d="M13 10V3L4 14h7v7l9-11h-7z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Total Spend</div>
                    <div class="text-xl font-bold text-gray-900 mt-1">&euro;{{ number_format($stats['total_spend'], 2) }}</div>
                    @php $spendPct = $stats['total_budget'] > 0 ? round(($stats['total_spend'] / $stats['total_budget']) * 100, 1) : 0; @endphp
                    <div class="flex items-center gap-2 mt-2">
                        <div class="flex-1 h-1.5 rounded-full bg-gray-100">
                            <div class="h-1.5 rounded-full bg-brand-500" style="width: {{ min($spendPct, 100) }}%"></div>
                        </div>
                        <span class="text-xs font-medium text-gray-500">{{ $spendPct }}%</span>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="none"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters + Search --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-1 overflow-x-auto">
                @php
                    $tabs = [
                        ['key' => 'all', 'label' => 'All', 'count' => $stats['total']],
                        ['key' => 'active', 'label' => 'Active', 'count' => $stats['active']],
                        ['key' => 'paused', 'label' => 'Paused', 'count' => $stats['paused']],
                        ['key' => 'draft', 'label' => 'Draft', 'count' => $stats['draft']],
                        ['key' => 'pending_review', 'label' => 'Pending', 'count' => $stats['pending_review']],
                        ['key' => 'completed', 'label' => 'Completed', 'count' => $stats['completed']],
                        ['key' => 'rejected', 'label' => 'Rejected', 'count' => $stats['rejected']],
                        ['key' => 'archived', 'label' => 'Archived', 'count' => $stats['archived']],
                    ];
                @endphp
                @foreach($tabs as $tab)
                    <a href="{{ route('admin.direct-campaigns', ['status' => $tab['key'], 'search' => $search]) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition-colors
                              {{ $statusFilter === $tab['key'] ? 'bg-brand-600 text-white' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}">
                        {{ $tab['label'] }}
                        <span class="px-1.5 py-0.5 rounded-md text-[10px] font-bold
                              {{ $statusFilter === $tab['key'] ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500' }}">{{ $tab['count'] }}</span>
                    </a>
                @endforeach
            </div>
            <form method="GET" action="{{ route('admin.direct-campaigns') }}" class="relative">
                <input type="hidden" name="status" value="{{ $statusFilter }}">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search campaigns, brands..."
                       class="pl-10 pr-4 py-2 w-64 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                        <th class="text-left px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="text-left px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Campaign Name</th>
                        <th class="text-left px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Pricing</th>
                        <th class="text-left px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Destination</th>
                        <th class="text-center px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Priority</th>
                        <th class="text-right px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Impressions</th>
                        <th class="text-right px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Clicks</th>
                        <th class="text-right px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Conversions</th>
                        <th class="text-right px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">CTR (%)</th>
                        <th class="text-center px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($campaigns as $campaign)
                        @php
                            $statusStyles = [
                                'active' => 'bg-emerald-100 text-emerald-700',
                                'paused' => 'bg-amber-100 text-amber-700',
                                'draft' => 'bg-gray-100 text-gray-600',
                                'completed' => 'bg-blue-100 text-blue-700',
                                'rejected' => 'bg-red-100 text-red-700',
                                'pending_review' => 'bg-orange-100 text-orange-700',
                                'archived' => 'bg-purple-100 text-purple-700',
                            ];
                            $statusDots = [
                                'active' => 'bg-emerald-500', 'paused' => 'bg-amber-500', 'draft' => 'bg-gray-400',
                                'completed' => 'bg-blue-500', 'rejected' => 'bg-red-500', 'pending_review' => 'bg-orange-500',
                                'archived' => 'bg-purple-500',
                            ];
                            $pricingColors = [
                                'cpm' => 'bg-purple-100 text-purple-700', 'cpc' => 'bg-blue-100 text-blue-700',
                                'cpa' => 'bg-emerald-100 text-emerald-700', 'cpv' => 'bg-pink-100 text-pink-700',
                                'cpv_ctw' => 'bg-indigo-100 text-indigo-700', 'flat_rate' => 'bg-amber-100 text-amber-700',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            <td class="px-5 py-3.5">
                                <span class="text-xs font-mono font-semibold text-gray-500">#{{ $campaign['id'] }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold {{ $statusStyles[$campaign['status']] ?? 'bg-gray-100 text-gray-600' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $statusDots[$campaign['status']] ?? 'bg-gray-400' }}"></span>
                                    {{ ucwords(str_replace('_', ' ', $campaign['status'])) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 max-w-[260px]">
                                <div>
                                    <p class="font-semibold text-gray-900 truncate">{{ $campaign['name'] }}</p>
                                    <p class="text-[11px] text-gray-400 mt-0.5 truncate">{{ $campaign['brand_name'] ?? $campaign['advertiser'] }}</p>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $pricingColors[$campaign['pricing_model']] ?? 'bg-gray-100 text-gray-600' }}">{{ strtoupper(str_replace('_', ' ', $campaign['pricing_model'])) }}</span>
                            </td>
                            <td class="px-5 py-3.5 max-w-[200px]">
                                @if($campaign['destination_url'])
                                    <span class="text-xs text-gray-500 truncate block" title="{{ $campaign['destination_url'] }}">{{ \Illuminate\Support\Str::limit($campaign['destination_url'], 35) }}</span>
                                @else
                                    <span class="text-xs text-gray-400">&mdash;</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-xs font-bold text-gray-700">{{ $campaign['priority'] }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right font-medium text-gray-800 tabular-nums">
                                {{ number_format($campaign['impressions']) }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-medium text-gray-800 tabular-nums">
                                {{ number_format($campaign['clicks']) }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-medium text-gray-800 tabular-nums">
                                {{ number_format($campaign['conversions']) }}
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                @if($campaign['ctr'] > 0)
                                    <span class="inline-flex items-center gap-1 font-semibold {{ $campaign['ctr'] >= 2.5 ? 'text-emerald-600' : ($campaign['ctr'] >= 1.0 ? 'text-blue-600' : 'text-gray-600') }}">
                                        {{ number_format($campaign['ctr'], 2) }}%
                                    </span>
                                @else
                                    <span class="text-gray-400">&mdash;</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('admin.direct-campaigns.show', $campaign['id']) }}" title="View Details" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke="currentColor" stroke-width="1.5"/></svg>
                                    </a>
                                    <a href="{{ route('admin.direct-campaigns.edit', $campaign['id']) }}" title="Edit" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    </a>
                                    @if($campaign['status'] === 'pending_review')
                                        <form method="POST" action="{{ route('admin.direct-campaigns.updateStatus', $campaign['id']) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" title="Approve" class="p-1.5 rounded-lg hover:bg-emerald-50 text-gray-400 hover:text-emerald-600 transition-colors">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.direct-campaigns.updateStatus', $campaign['id']) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" title="Reject" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600 transition-colors">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                            </button>
                                        </form>
                                    @elseif($campaign['status'] === 'active')
                                        <form method="POST" action="{{ route('admin.direct-campaigns.updateStatus', $campaign['id']) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="paused">
                                            <button type="submit" title="Pause" class="p-1.5 rounded-lg hover:bg-amber-50 text-gray-400 hover:text-amber-600 transition-colors">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                            </button>
                                        </form>
                                    @elseif($campaign['status'] === 'paused')
                                        <form method="POST" action="{{ route('admin.direct-campaigns.updateStatus', $campaign['id']) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" title="Resume" class="p-1.5 rounded-lg hover:bg-emerald-50 text-gray-400 hover:text-emerald-600 transition-colors">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" stroke="currentColor" stroke-width="1.5"/><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                    <div class="relative" x-data="{ open: false }">
                                        <button @click="open = !open" title="More" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                        </button>
                                        <div x-show="open" @click.away="open = false"
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="opacity-100 scale-100"
                                             x-transition:leave-end="opacity-0 scale-95"
                                             class="absolute right-0 mt-1 w-44 bg-white rounded-xl border border-gray-200 shadow-lg py-1 z-50" style="display: none;">
                                            <a href="{{ route('admin.direct-campaigns.show', $campaign['id']) }}" class="flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" stroke="currentColor" stroke-width="1.5"/></svg>
                                                View Details
                                            </a>
                                            <form method="POST" action="{{ route('admin.direct-campaigns.duplicate', $campaign['id']) }}" class="block">
                                                @csrf
                                                <button type="submit" class="flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 w-full text-left">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                                    Duplicate
                                                </button>
                                            </form>
                                            <div class="border-t border-gray-100 my-1"></div>
                                            <button type="button" onclick="confirmDelete({{ $campaign['id'] }}, '{{ addslashes($campaign['name']) }}')" class="flex items-center gap-2 px-3 py-2 text-xs text-red-600 hover:bg-red-50 w-full text-left">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                                Delete Campaign
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M13 10V3L4 14h7v7l9-11h-7z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-600">No direct campaigns found</p>
                                    <p class="text-xs text-gray-400 mt-1">Try adjusting your filters or create a new direct campaign.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(count($campaigns) > 0)
            <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
                <p class="text-xs text-gray-400">
                    Showing <strong class="text-gray-600">{{ $pagination['from'] }}</strong> to <strong class="text-gray-600">{{ $pagination['to'] }}</strong> of <strong class="text-gray-600">{{ $pagination['total'] }}</strong> campaign{{ $pagination['total'] !== 1 ? 's' : '' }}
                </p>
                @if($pagination['total_pages'] > 1)
                    <div class="flex items-center gap-1">
                        @if($pagination['current_page'] > 1)
                            <a href="{{ route('admin.direct-campaigns', ['status' => $statusFilter, 'search' => $search, 'page' => $pagination['current_page'] - 1]) }}"
                               class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs text-gray-600 hover:bg-gray-50 transition-colors">Previous</a>
                        @else
                            <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs text-gray-400 bg-gray-50 cursor-not-allowed">Previous</span>
                        @endif
                        @for($p = 1; $p <= $pagination['total_pages']; $p++)
                            @if($p == $pagination['current_page'])
                                <span class="px-3 py-1.5 rounded-lg bg-brand-600 text-xs text-white font-medium">{{ $p }}</span>
                            @else
                                <a href="{{ route('admin.direct-campaigns', ['status' => $statusFilter, 'search' => $search, 'page' => $p]) }}"
                                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs text-gray-600 hover:bg-gray-50 transition-colors">{{ $p }}</a>
                            @endif
                        @endfor
                        @if($pagination['current_page'] < $pagination['total_pages'])
                            <a href="{{ route('admin.direct-campaigns', ['status' => $statusFilter, 'search' => $search, 'page' => $pagination['current_page'] + 1]) }}"
                               class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs text-gray-600 hover:bg-gray-50 transition-colors">Next</a>
                        @else
                            <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs text-gray-400 bg-gray-50 cursor-not-allowed">Next</span>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Delete Modal --}}
    <div id="deleteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" viewBox="0 0 24 24" fill="none"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Delete Direct Campaign</h3>
                    <p class="text-sm text-gray-500 mt-0.5">This action cannot be undone.</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-5">Are you sure you want to delete <strong id="deleteCampaignName" class="text-gray-900"></strong>?</p>
            <form id="deleteForm" method="POST" action="">
                @csrf @method('DELETE')
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('deleteModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-sm font-semibold text-white hover:bg-red-700">Delete</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Export Modal --}}
    <div id="exportModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-full bg-brand-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-brand-600" viewBox="0 0 24 24" fill="none"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Export Direct Campaigns</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Choose your preferred export format</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 mb-5">
                <a href="{{ route('admin.direct-campaigns.export', ['format' => 'excel', 'status' => $statusFilter]) }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 hover:border-emerald-300 hover:bg-emerald-50 transition-colors group cursor-pointer">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                        <svg class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="none"><path d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </div>
                    <span class="text-sm font-semibold text-gray-700 group-hover:text-emerald-700">Excel (.xls)</span>
                </a>
                <a href="{{ route('admin.direct-campaigns.export', ['format' => 'csv', 'status' => $statusFilter]) }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-colors group cursor-pointer">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                        <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="none"><path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </div>
                    <span class="text-sm font-semibold text-gray-700 group-hover:text-blue-700">CSV (.csv)</span>
                </a>
            </div>
            <div class="flex justify-end">
                <button type="button" onclick="document.getElementById('exportModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">Cancel</button>
            </div>
        </div>
    </div>

    <script>
    function confirmDelete(id, name) {
        document.getElementById('deleteCampaignName').textContent = name;
        document.getElementById('deleteForm').action = '/admin/direct-campaigns/' + id;
        document.getElementById('deleteModal').classList.remove('hidden');
    }
    </script>
@endsection
