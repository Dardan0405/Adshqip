@extends('layouts.admin')

@section('title', 'Campaigns')

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
            <h1 class="text-2xl font-bold text-gray-900">Campaigns</h1>
            <p class="text-sm text-gray-500 mt-1">Manage and monitor all advertiser campaigns across the platform.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('exportModal').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                Export
            </button>
            <button type="button" onclick="document.getElementById('newGroupModal').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M17 14v6m-3-3h6M6 10h2a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2zm10 0h2a2 2 0 002-2V6a2 2 0 00-2-2h-2a2 2 0 00-2 2v2a2 2 0 002 2zM6 20h2a2 2 0 002-2v-2a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                New Group
            </button>
            <a href="{{ route('admin.campaigns.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                New Campaign
            </a>
        </div>
    </div>

    {{-- ═══════════ STAT CARDS ═══════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        @php
            $statCards = [
                ['label' => 'Total', 'value' => $stats['total'], 'color' => 'text-gray-900', 'bg' => 'bg-gray-50', 'border' => 'border-gray-200'],
                ['label' => 'Active', 'value' => $stats['active'], 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200'],
                ['label' => 'Paused', 'value' => $stats['paused'], 'color' => 'text-amber-700', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200'],
                ['label' => 'Draft', 'value' => $stats['draft'], 'color' => 'text-gray-600', 'bg' => 'bg-gray-50', 'border' => 'border-gray-200'],
                ['label' => 'Completed', 'value' => $stats['completed'], 'color' => 'text-blue-700', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200'],
                ['label' => 'Pending', 'value' => $stats['pending_review'], 'color' => 'text-orange-700', 'bg' => 'bg-orange-50', 'border' => 'border-orange-200'],
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
                    <svg class="w-5 h-5 text-brand-600" viewBox="0 0 24 24" fill="none"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/></svg>
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

    {{-- ═══════════ FILTERS + SEARCH ═══════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            {{-- Status tabs --}}
            <div class="flex items-center gap-1 overflow-x-auto">
                @php
                    $tabs = [
                        ['key' => 'all', 'label' => 'All Campaigns', 'count' => $stats['total']],
                        ['key' => 'active', 'label' => 'Active', 'count' => $stats['active']],
                        ['key' => 'paused', 'label' => 'Paused', 'count' => $stats['paused']],
                        ['key' => 'draft', 'label' => 'Draft', 'count' => $stats['draft']],
                        ['key' => 'pending_review', 'label' => 'Pending', 'count' => $stats['pending_review']],
                        ['key' => 'completed', 'label' => 'Completed', 'count' => $stats['completed']],
                        ['key' => 'rejected', 'label' => 'Rejected', 'count' => $stats['rejected']],
                    ];
                @endphp
                @foreach($tabs as $tab)
                    <a href="{{ route('admin.campaigns', ['status' => $tab['key'], 'search' => $search]) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition-colors
                              {{ $statusFilter === $tab['key'] ? 'bg-brand-600 text-white' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}">
                        {{ $tab['label'] }}
                        <span class="px-1.5 py-0.5 rounded-md text-[10px] font-bold
                              {{ $statusFilter === $tab['key'] ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500' }}">{{ $tab['count'] }}</span>
                    </a>
                @endforeach
            </div>

            {{-- Search --}}
            <form method="GET" action="{{ route('admin.campaigns') }}" class="relative">
                <input type="hidden" name="status" value="{{ $statusFilter }}">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search campaigns, advertisers..."
                       class="pl-10 pr-4 py-2 w-64 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </form>
        </div>

        {{-- ═══════════ TABLE ═══════════ --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                        <th class="text-left px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="text-left px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Campaign Name</th>
                        <th class="text-left px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Type</th>
                        <th class="text-left px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Start Date</th>
                        <th class="text-left px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">End Date</th>
                        <th class="text-left px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Model</th>
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
                                'active'         => 'bg-emerald-100 text-emerald-700',
                                'paused'         => 'bg-amber-100 text-amber-700',
                                'draft'          => 'bg-gray-100 text-gray-600',
                                'completed'      => 'bg-blue-100 text-blue-700',
                                'rejected'       => 'bg-red-100 text-red-700',
                                'pending_review' => 'bg-orange-100 text-orange-700',
                            ];
                            $statusDots = [
                                'active'         => 'bg-emerald-500',
                                'paused'         => 'bg-amber-500',
                                'draft'          => 'bg-gray-400',
                                'completed'      => 'bg-blue-500',
                                'rejected'       => 'bg-red-500',
                                'pending_review' => 'bg-orange-500',
                            ];
                            $typeColors = [
                                'cpm'     => 'bg-purple-100 text-purple-700',
                                'cpc'     => 'bg-blue-100 text-blue-700',
                                'cpa'     => 'bg-emerald-100 text-emerald-700',
                                'cpv'     => 'bg-pink-100 text-pink-700',
                                'cpv_ctw' => 'bg-indigo-100 text-indigo-700',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            {{-- ID --}}
                            <td class="px-5 py-3.5">
                                <span class="text-xs font-mono font-semibold text-gray-500">#{{ $campaign['id'] }}</span>
                            </td>
                            {{-- Status --}}
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold {{ $statusStyles[$campaign['status']] ?? 'bg-gray-100 text-gray-600' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $statusDots[$campaign['status']] ?? 'bg-gray-400' }}"></span>
                                    {{ ucwords(str_replace('_', ' ', $campaign['status'])) }}
                                </span>
                            </td>
                            {{-- Name --}}
                            <td class="px-5 py-3.5 max-w-[260px]">
                                <div>
                                    <p class="font-semibold text-gray-900 truncate">{{ $campaign['name'] }}</p>
                                    <p class="text-[11px] text-gray-400 mt-0.5 truncate">{{ $campaign['advertiser'] }}</p>
                                </div>
                            </td>
                            {{-- Type --}}
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $typeColors[$campaign['type']] ?? 'bg-gray-100 text-gray-600' }}">{{ strtoupper($campaign['type']) }}</span>
                            </td>
                            {{-- Start Date --}}
                            <td class="px-5 py-3.5 text-gray-600 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($campaign['start_date'])->format('M d, Y') }}
                            </td>
                            {{-- End Date --}}
                            <td class="px-5 py-3.5 text-gray-600 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($campaign['end_date'])->format('M d, Y') }}
                            </td>
                            {{-- Model --}}
                            <td class="px-5 py-3.5">
                                <span class="font-semibold text-gray-700">{{ $campaign['model'] }}</span>
                            </td>
                            {{-- Impressions --}}
                            <td class="px-5 py-3.5 text-right font-medium text-gray-800 tabular-nums">
                                {{ number_format($campaign['impressions']) }}
                            </td>
                            {{-- Clicks --}}
                            <td class="px-5 py-3.5 text-right font-medium text-gray-800 tabular-nums">
                                {{ number_format($campaign['clicks']) }}
                            </td>
                            {{-- Conversions --}}
                            <td class="px-5 py-3.5 text-right font-medium text-gray-800 tabular-nums">
                                {{ number_format($campaign['conversions']) }}
                            </td>
                            {{-- CTR --}}
                            <td class="px-5 py-3.5 text-right">
                                @if($campaign['ctr'] > 0)
                                    <span class="inline-flex items-center gap-1 font-semibold {{ $campaign['ctr'] >= 2.5 ? 'text-emerald-600' : ($campaign['ctr'] >= 1.0 ? 'text-blue-600' : 'text-gray-600') }}">
                                        @if($campaign['ctr'] >= 2.5)
                                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M5 15l7-7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        @endif
                                        {{ number_format($campaign['ctr'], 2) }}%
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            {{-- Actions --}}
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('admin.campaigns.show', $campaign['id']) }}" title="View Details" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke="currentColor" stroke-width="1.5"/></svg>
                                    </a>
                                    <a href="{{ route('admin.campaigns.edit', $campaign['id']) }}" title="Edit Campaign" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    </a>
                                    @if($campaign['status'] === 'pending_review')
                                        <form method="POST" action="{{ route('admin.campaigns.updateStatus', $campaign['id']) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" title="Approve" class="p-1.5 rounded-lg hover:bg-emerald-50 text-gray-400 hover:text-emerald-600 transition-colors">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.campaigns.updateStatus', $campaign['id']) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" title="Reject" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600 transition-colors">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                            </button>
                                        </form>
                                    @elseif($campaign['status'] === 'active')
                                        <form method="POST" action="{{ route('admin.campaigns.updateStatus', $campaign['id']) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="paused">
                                            <button type="submit" title="Pause" class="p-1.5 rounded-lg hover:bg-amber-50 text-gray-400 hover:text-amber-600 transition-colors">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                            </button>
                                        </form>
                                    @elseif($campaign['status'] === 'paused')
                                        <form method="POST" action="{{ route('admin.campaigns.updateStatus', $campaign['id']) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
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
                                            <a href="{{ route('admin.campaigns.show', $campaign['id']) }}" class="flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" stroke="currentColor" stroke-width="1.5"/></svg>
                                                View Details
                                            </a>
                                            <form method="POST" action="{{ route('admin.campaigns.duplicate', $campaign['id']) }}" class="block">
                                                @csrf
                                                <button type="submit" class="flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 w-full text-left">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                                    Duplicate
                                                </button>
                                            </form>
                                           
                                            <button type="button" onclick="openTrackerCode({{ $campaign['id'] }}, '{{ addslashes($campaign['name']) }}')" class="flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 w-full text-left">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                Get Tracker Code
                                            </button>
                                            <button type="button" onclick="openMoveToGroup({{ $campaign['id'] }}, '{{ addslashes($campaign['name']) }}')" class="flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 w-full text-left">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                Move to Group
                                            </button>
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
                            <td colspan="12" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-600">No campaigns found</p>
                                    <p class="text-xs text-gray-400 mt-1">Try adjusting your filters or search query.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Table footer with pagination --}}
        @if(count($campaigns) > 0)
            <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
                <p class="text-xs text-gray-400">
                    Showing <strong class="text-gray-600">{{ $pagination['from'] }}</strong> to <strong class="text-gray-600">{{ $pagination['to'] }}</strong> of <strong class="text-gray-600">{{ $pagination['total'] }}</strong> campaign{{ $pagination['total'] !== 1 ? 's' : '' }}
                </p>

                {{-- Only show pagination controls when there are multiple pages --}}
                @if($pagination['total_pages'] > 1)
                    <div class="flex items-center gap-1">
                        {{-- Previous --}}
                        @if($pagination['current_page'] > 1)
                            <a href="{{ route('admin.campaigns', ['status' => $statusFilter, 'search' => $search, 'page' => $pagination['current_page'] - 1]) }}"
                               class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs text-gray-600 hover:bg-gray-50 transition-colors">Previous</a>
                        @else
                            <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs text-gray-400 bg-gray-50 cursor-not-allowed">Previous</span>
                        @endif

                        {{-- Page numbers --}}
                        @for($p = 1; $p <= $pagination['total_pages']; $p++)
                            @if($p == $pagination['current_page'])
                                <span class="px-3 py-1.5 rounded-lg bg-brand-600 text-xs text-white font-medium">{{ $p }}</span>
                            @else
                                <a href="{{ route('admin.campaigns', ['status' => $statusFilter, 'search' => $search, 'page' => $p]) }}"
                                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs text-gray-600 hover:bg-gray-50 transition-colors">{{ $p }}</a>
                            @endif
                        @endfor

                        {{-- Next --}}
                        @if($pagination['current_page'] < $pagination['total_pages'])
                            <a href="{{ route('admin.campaigns', ['status' => $statusFilter, 'search' => $search, 'page' => $pagination['current_page'] + 1]) }}"
                               class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs text-gray-600 hover:bg-gray-50 transition-colors">Next</a>
                        @else
                            <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs text-gray-400 bg-gray-50 cursor-not-allowed">Next</span>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- ═══════════ MODAL: NEW GROUP ═══════════ --}}
    <div id="newGroupModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Create Campaign Group</h3>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Group Name <span class="text-red-500">*</span></label>
                <input type="text" id="newGroupName" placeholder="e.g. Performance Q4" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>
            <div class="flex justify-end gap-2 mt-5">
                <button type="button" onclick="document.getElementById('newGroupModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">Cancel</button>
                <button type="button" onclick="createGroup()" class="px-4 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700">Create</button>
            </div>
        </div>
    </div>

    {{-- ═══════════ MODAL: DELETE CONFIRMATION ═══════════ --}}
    <div id="deleteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" viewBox="0 0 24 24" fill="none"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Delete Campaign</h3>
                    <p class="text-sm text-gray-500 mt-0.5">This action cannot be undone.</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-5">Are you sure you want to delete <strong id="deleteCampaignName" class="text-gray-900"></strong>?</p>
            <form id="deleteForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('deleteModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-sm font-semibold text-white hover:bg-red-700">Delete</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══════════ MODAL: MOVE TO GROUP ═══════════ --}}
    <div id="moveToGroupModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-brand-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-brand-600" viewBox="0 0 24 24" fill="none"><path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Move to Group</h3>
                    <p class="text-sm text-gray-500 mt-0.5" id="moveToGroupCampaignName"></p>
                </div>
            </div>
            <form id="moveToGroupForm" method="POST" action="">
                @csrf
                @method('PATCH')
                <div class="mb-5">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Select Group <span class="text-red-500">*</span></label>
                    <select name="group_id" id="moveToGroupSelect" required class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        <option value="">— Choose a group —</option>
                        @foreach($campaignGroups as $group)
                            <option value="{{ $group['id'] }}">{{ $group['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('moveToGroupModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700">Move</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══════════ MODAL: TRACKER CODE ═══════════ --}}
    <div id="trackerCodeModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-indigo-600" viewBox="0 0 24 24" fill="none"><path d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Tracker Code</h3>
                        <p class="text-sm text-gray-500 mt-0.5" id="trackerCampaignName"></p>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('trackerCodeModal').classList.add('hidden')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
            </div>

            {{-- Instructions --}}
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 mb-4">
                <h4 class="text-xs font-bold text-blue-800 uppercase tracking-wider mb-2">Instructions for placing your trackers</h4>
                <ol class="text-sm text-blue-700 space-y-1.5 list-decimal list-inside">
                    <li>Copy your pixel code below</li>
                    <li>Insert the code before the <code class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 font-mono text-xs">&lt;/head&gt;</code> tag on all pages on your website (secure &amp; non-secure)</li>
                </ol>
            </div>

            {{-- Tracker Type Tabs --}}
            <div class="flex items-center gap-1 mb-3">
                <button type="button" onclick="switchTrackerType('javascript')" id="trackerTabJs" class="px-3 py-1.5 rounded-lg text-xs font-medium bg-indigo-600 text-white transition-colors">JavaScript Pixel</button>
                <button type="button" onclick="switchTrackerType('image')" id="trackerTabImg" class="px-3 py-1.5 rounded-lg text-xs font-medium text-gray-500 hover:bg-gray-50 transition-colors">Image Pixel</button>
                <button type="button" onclick="switchTrackerType('s2s')" id="trackerTabS2s" class="px-3 py-1.5 rounded-lg text-xs font-medium text-gray-500 hover:bg-gray-50 transition-colors">Server-to-Server</button>
            </div>

            {{-- Code Block --}}
            <div class="relative">
                <pre id="trackerCodeBlock" class="bg-gray-900 text-gray-100 rounded-lg p-4 text-xs font-mono overflow-x-auto leading-relaxed max-h-48 overflow-y-auto"></pre>
                <button type="button" onclick="copyTrackerCode()" id="copyTrackerBtn" class="absolute top-2 right-2 px-2.5 py-1.5 rounded-md bg-gray-700 hover:bg-gray-600 text-gray-300 hover:text-white text-[10px] font-semibold uppercase tracking-wider transition-colors flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Copy
                </button>
            </div>

            <div class="flex justify-end mt-5">
                <button type="button" onclick="document.getElementById('trackerCodeModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">Close</button>
            </div>
        </div>
    </div>

    {{-- ═══════════ MODAL: EXPORT CAMPAIGNS ═══════════ --}}
    <div id="exportModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-full bg-brand-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-brand-600" viewBox="0 0 24 24" fill="none"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Export Campaigns</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Choose your preferred export format</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-5">
                {{-- Excel --}}
                <a href="{{ route('admin.campaigns.export', ['format' => 'excel', 'status' => $statusFilter]) }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 hover:border-emerald-300 hover:bg-emerald-50 transition-colors group cursor-pointer">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                        <svg class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="none"><path d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </div>
                    <span class="text-sm font-semibold text-gray-700 group-hover:text-emerald-700">Excel (.xls)</span>
                    <span class="text-[10px] text-gray-400">Spreadsheet format</span>
                </a>

                {{-- CSV --}}
                <a href="{{ route('admin.campaigns.export', ['format' => 'csv', 'status' => $statusFilter]) }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-colors group cursor-pointer">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                        <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="none"><path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M9 13h6m-6 3h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </div>
                    <span class="text-sm font-semibold text-gray-700 group-hover:text-blue-700">CSV (.csv)</span>
                    <span class="text-[10px] text-gray-400">Comma separated</span>
                </a>

                {{-- PDF --}}
                <button type="button" onclick="exportPDF()" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 hover:border-red-300 hover:bg-red-50 transition-colors group cursor-pointer">
                    <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center group-hover:bg-red-200 transition-colors">
                        <svg class="w-5 h-5 text-red-600" viewBox="0 0 24 24" fill="none"><path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M9 13h2m-2 3h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </div>
                    <span class="text-sm font-semibold text-gray-700 group-hover:text-red-700">PDF</span>
                    <span class="text-[10px] text-gray-400">Save as PDF</span>
                </button>

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

    <script>
    var currentTrackerCampaignId = null;
    var currentTrackerType = 'javascript';

    function openTrackerCode(id, name) {
        currentTrackerCampaignId = id;
        currentTrackerType = 'javascript';
        document.getElementById('trackerCampaignName').textContent = name;
        switchTrackerType('javascript');
        document.getElementById('trackerCodeModal').classList.remove('hidden');
    }

    function switchTrackerType(type) {
        currentTrackerType = type;
        var id = currentTrackerCampaignId;
        var domain = window.location.origin;
        var code = '';

        // Update tab styles
        var tabs = {javascript: 'trackerTabJs', image: 'trackerTabImg', s2s: 'trackerTabS2s'};
        Object.keys(tabs).forEach(function(key) {
            var el = document.getElementById(tabs[key]);
            if (key === type) {
                el.className = 'px-3 py-1.5 rounded-lg text-xs font-medium bg-indigo-600 text-white transition-colors';
            } else {
                el.className = 'px-3 py-1.5 rounded-lg text-xs font-medium text-gray-500 hover:bg-gray-50 transition-colors';
            }
        });

        if (type === 'javascript') {
            code = '<!-- AdsHqip Tracker Pixel - Campaign #' + id + ' -->\n'
                 + '<script type="text/javascript">\n'
                 + '  (function() {\n'
                 + '    var aq = document.createElement("script");\n'
                 + '    aq.type = "text/javascript";\n'
                 + '    aq.async = true;\n'
                 + '    aq.src = "' + domain + '/track/campaign/' + id + '/pixel.js";\n'
                 + '    var s = document.getElementsByTagName("script")[0];\n'
                 + '    s.parentNode.insertBefore(aq, s);\n'
                 + '  })();\n'
                 + '</' + 'script>\n'
                 + '<!-- End AdsHqip Tracker -->';
        } else if (type === 'image') {
            code = '<!-- AdsHqip Image Pixel - Campaign #' + id + ' -->\n'
                 + '<noscript>\n'
                 + '  <img src="' + domain + '/track/campaign/' + id + '/pixel.gif"\n'
                 + '       width="1" height="1" alt="" style="display:none;" />\n'
                 + '</noscript>\n'
                 + '<!-- End AdsHqip Tracker -->';
        } else if (type === 's2s') {
            code = '# AdsHqip Server-to-Server Postback - Campaign #' + id + '\n'
                 + '# Send a GET or POST request to the following URL:\n\n'
                 + domain + '/track/campaign/' + id + '/postback'
                 + '?click_id={click_id}'
                 + '&payout={payout}'
                 + '&tx_id={transaction_id}\n\n'
                 + '# Required parameters:\n'
                 + '#   click_id       - The unique click ID from the original click\n'
                 + '#   payout         - Conversion payout amount (e.g. 2.50)\n'
                 + '#   tx_id          - Your unique transaction ID\n\n'
                 + '# Example with cURL:\n'
                 + 'curl "' + domain + '/track/campaign/' + id + '/postback?click_id=abc123&payout=2.50&tx_id=order_456"';
        }

        document.getElementById('trackerCodeBlock').textContent = code;
        // Reset copy button text
        document.getElementById('copyTrackerBtn').innerHTML = '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg> Copy';
    }

    function copyTrackerCode() {
        var code = document.getElementById('trackerCodeBlock').textContent;
        navigator.clipboard.writeText(code).then(function() {
            var btn = document.getElementById('copyTrackerBtn');
            btn.innerHTML = '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Copied!';
            setTimeout(function() {
                btn.innerHTML = '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg> Copy';
            }, 2000);
        });
    }

    function openMoveToGroup(id, name) {
        document.getElementById('moveToGroupCampaignName').textContent = name;
        document.getElementById('moveToGroupForm').action = '/admin/campaigns/' + id + '/group';
        document.getElementById('moveToGroupSelect').value = '';
        document.getElementById('moveToGroupModal').classList.remove('hidden');
    }

    function confirmDelete(id, name) {
        document.getElementById('deleteCampaignName').textContent = name;
        document.getElementById('deleteForm').action = '/admin/campaigns/' + id;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 px-4 py-3 rounded-lg shadow-lg z-50 ${type === 'success' ? 'bg-emerald-500' : 'bg-red-500'} text-white text-sm font-medium`;
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }

    function exportPrint() {
        document.getElementById('exportModal').classList.add('hidden');
        openPrintableView(false);
    }

    function exportPDF() {
        document.getElementById('exportModal').classList.add('hidden');
        openPrintableView(true);
    }

    function openPrintableView(isPdf) {
        var campaigns = @json($campaigns);
        var w = window.open('', '_blank');
        var title = isPdf ? 'Save as PDF — Campaigns Export' : 'Print — Campaigns Export';
        var html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' + title + '</title>';
        html += '<style>';
        html += 'body{font-family:Arial,Helvetica,sans-serif;margin:20px;color:#333;font-size:12px;}';
        html += 'h1{font-size:18px;margin-bottom:4px;color:#1f2937;}';
        html += 'p.subtitle{color:#6b7280;font-size:12px;margin-bottom:16px;}';
        html += 'table{width:100%;border-collapse:collapse;margin-top:10px;}';
        html += 'th{background:#4f46e5;color:#fff;padding:8px 6px;text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:0.5px;}';
        html += 'td{padding:6px;border-bottom:1px solid #e5e7eb;font-size:11px;}';
        html += 'tr:nth-child(even){background:#f9fafb;}';
        html += '.text-right{text-align:right;}';
        html += '.status{padding:2px 8px;border-radius:4px;font-size:10px;font-weight:600;}';
        html += '.status-active{background:#d1fae5;color:#065f46;}';
        html += '.status-paused{background:#fef3c7;color:#92400e;}';
        html += '.status-draft{background:#f3f4f6;color:#4b5563;}';
        html += '.status-completed{background:#dbeafe;color:#1e40af;}';
        html += '.status-rejected{background:#fee2e2;color:#991b1b;}';
        html += '.status-pending_review{background:#ffedd5;color:#9a3412;}';
        html += '.footer{margin-top:20px;padding-top:10px;border-top:1px solid #e5e7eb;font-size:10px;color:#9ca3af;text-align:center;}';
        html += '@media print{body{margin:0;padding:15px;} @page{size:landscape;margin:10mm;}}';
        html += '</style></head><body>';
        html += '<h1>AdsHqip — Campaigns Report</h1>';
        html += '<p class="subtitle">Exported on ' + new Date().toLocaleDateString('en-US', {year:'numeric',month:'long',day:'numeric'}) + ' at ' + new Date().toLocaleTimeString() + '</p>';
        html += '<table><thead><tr>';
        html += '<th>ID</th><th>Status</th><th>Campaign Name</th><th>Type</th><th>Start Date</th><th>End Date</th><th>Model</th><th class="text-right">Impressions</th><th class="text-right">Clicks</th><th class="text-right">Conversions</th><th class="text-right">CTR</th><th class="text-right">Budget</th><th class="text-right">Spend</th>';
        html += '</tr></thead><tbody>';

        var statusLabels = {active:'Active',paused:'Paused',draft:'Draft',completed:'Completed',rejected:'Rejected',pending_review:'Pending Review'};

        campaigns.forEach(function(c) {
            html += '<tr>';
            html += '<td>#' + c.id + '</td>';
            html += '<td><span class="status status-' + c.status + '">' + (statusLabels[c.status] || c.status) + '</span></td>';
            html += '<td>' + c.name + '</td>';
            html += '<td>' + c.type.toUpperCase() + '</td>';
            html += '<td>' + c.start_date + '</td>';
            html += '<td>' + (c.end_date || '—') + '</td>';
            html += '<td>' + c.model + '</td>';
            html += '<td class="text-right">' + Number(c.impressions).toLocaleString() + '</td>';
            html += '<td class="text-right">' + Number(c.clicks).toLocaleString() + '</td>';
            html += '<td class="text-right">' + Number(c.conversions).toLocaleString() + '</td>';
            html += '<td class="text-right">' + Number(c.ctr).toFixed(2) + '%</td>';
            html += '<td class="text-right">€' + Number(c.budget).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) + '</td>';
            html += '<td class="text-right">€' + Number(c.spend).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) + '</td>';
            html += '</tr>';
        });

        html += '</tbody></table>';
        html += '<div class="footer">Generated by AdsHqip Platform — ' + campaigns.length + ' campaign(s)' + (isPdf ? ' — Use your browser\'s "Save as PDF" option' : '') + '</div>';
        html += '</body></html>';

        w.document.write(html);
        w.document.close();
        w.onload = function() {
            setTimeout(function() { w.print(); }, 300);
        };
    }

    function createGroup() {
        const name = document.getElementById('newGroupName').value.trim();
        if (!name) return alert('Please enter a group name.');

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch('{{ route("admin.campaigns.groups.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ name: name })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('newGroupName').value = '';
                document.getElementById('newGroupModal').classList.add('hidden');
                showNotification('Campaign group "' + data.group.name + '" created successfully!', 'success');
            } else {
                alert('Error creating group: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to create campaign group. Please try again.');
        });
    }
    </script>
@endsection
