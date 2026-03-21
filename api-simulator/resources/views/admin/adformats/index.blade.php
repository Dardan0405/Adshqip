@extends('layouts.admin')

@section('title', 'Ad Formats')

@section('content')
{{-- Flash Messages --}}
@if(session('success'))
    <div class="mb-4 p-4 rounded-xl border border-emerald-300 bg-emerald-50 flex items-center gap-3">
        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <span class="text-sm font-medium text-emerald-800">{{ session('success') }}</span>
    </div>
@endif
@if(session('error'))
    <div class="mb-4 p-4 rounded-xl border border-red-300 bg-red-50 flex items-center gap-3">
        <svg class="w-5 h-5 text-red-600 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
        <span class="text-sm font-medium text-red-800">{{ session('error') }}</span>
    </div>
@endif

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Ad Formats</h1>
        <p class="text-sm text-gray-400 mt-0.5">Manage all creatives across your campaigns</p>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- EARNINGS OVERVIEW                                       --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="px-6 py-4 border-b border-gray-100">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/></svg>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Earnings Overview</h2>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $stats['today_day'] }}, {{ $stats['today_date'] }} &mdash; {{ $stats['today_time'] }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            {{-- Today --}}
            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Today</p>
                <p class="text-xl font-bold text-gray-900">&euro;{{ number_format($stats['today_earnings'], 2) }}</p>
                <p class="text-[10px] text-emerald-600 mt-1 flex items-center gap-1">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    {{ $stats['today_date'] }}
                </p>
            </div>
            {{-- This Month --}}
            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">This Month</p>
                <p class="text-xl font-bold text-gray-900">&euro;{{ number_format($stats['month_earnings'], 2) }}</p>
                <p class="text-[10px] text-gray-500 mt-1">{{ now()->format('F Y') }}</p>
            </div>
            {{-- This Year --}}
            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">This Year</p>
                <p class="text-xl font-bold text-gray-900">&euro;{{ number_format($stats['year_earnings'], 2) }}</p>
                <p class="text-[10px] text-gray-500 mt-1">{{ now()->format('Y') }}</p>
            </div>
            {{-- Total Earnings --}}
            <div class="rounded-xl border border-brand-100 bg-brand-50 p-4">
                <p class="text-[10px] font-semibold text-brand-600 uppercase tracking-wider mb-1">Total Earnings</p>
                <p class="text-xl font-bold text-brand-700">&euro;{{ number_format($stats['total_earnings'], 2) }}</p>
                <p class="text-[10px] text-brand-500 mt-1">All time</p>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- STATS CARDS                                             --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Total</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_creatives'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-[10px] font-semibold text-emerald-500 uppercase tracking-wider">Active</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['active'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-[10px] font-semibold text-amber-500 uppercase tracking-wider">Paused</p>
        <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['paused'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-[10px] font-semibold text-blue-500 uppercase tracking-wider">Pending</p>
        <p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['pending_review'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-[10px] font-semibold text-red-500 uppercase tracking-wider">Rejected</p>
        <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['rejected'] }}</p>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- MANAGE CREATIVES TABLE                                  --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-xl border border-gray-200">
    {{-- Table Header --}}
    <div class="px-6 py-4 border-b border-gray-100">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-600" viewBox="0 0 24 24" fill="none"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Manage Creatives</h2>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $pagination['total'] }} creatives found</p>
                </div>
            </div>

            {{-- Export Buttons --}}
            <div class="flex items-center gap-2">
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Export
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-44 bg-white rounded-xl border border-gray-200 shadow-lg py-1 z-50" style="display: none;">
                        <a href="{{ route('admin.adformats.export', ['format' => 'csv']) }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            CSV
                        </a>
                        <a href="{{ route('admin.adformats.export', ['format' => 'excel']) }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4 text-emerald-500" viewBox="0 0 24 24" fill="none"><path d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            Excel
                        </a>
                        <button type="button" onclick="window.print()" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50 transition-colors w-full">
                            <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            Print
                        </button>
                        <a href="{{ route('admin.adformats.export', ['format' => 'pdf']) }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4 text-red-500" viewBox="0 0 24 24" fill="none"><path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="px-6 py-3 border-b border-gray-100 bg-gray-50/50">
        <form method="GET" action="{{ route('admin.adformats') }}" class="flex flex-col md:flex-row md:items-center gap-3">
            {{-- Search --}}
            <div class="relative flex-1 max-w-sm">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search creatives or campaigns..." class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>

            {{-- Type Filter --}}
            <select name="type" class="px-3 py-2 rounded-lg border border-gray-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                <option value="all" {{ $typeFilter === 'all' ? 'selected' : '' }}>All Types</option>
                @foreach($adTypes as $key => $label)
                    <option value="{{ $key }}" {{ $typeFilter === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            {{-- Status Filter --}}
            <select name="status" class="px-3 py-2 rounded-lg border border-gray-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Status</option>
                <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active</option>
                <option value="paused" {{ $statusFilter === 'paused' ? 'selected' : '' }}>Paused</option>
                <option value="pending_review" {{ $statusFilter === 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                <option value="rejected" {{ $statusFilter === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>

            <button type="submit" class="px-4 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 transition-colors">Filter</button>

            @if($search !== '' || $typeFilter !== 'all' || $statusFilter !== 'all')
                <a href="{{ route('admin.adformats') }}" class="px-3 py-2 rounded-lg border border-gray-200 text-sm text-gray-500 hover:bg-gray-50 transition-colors">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm" id="creativesTable">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                    <th class="text-left px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Creative Name</th>
                    <th class="text-left px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Campaign Name</th>
                    <th class="text-left px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Creative Type</th>
                    <th class="text-left px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Image Type</th>
                    <th class="text-left px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Size</th>
                    <th class="text-left px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                    <th class="text-center px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Weight</th>
                    <th class="text-left px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Destination URL</th>
                    <th class="text-center px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($ads as $ad)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-4 py-3 text-xs font-mono text-gray-500">#{{ $ad['id'] }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                    @if($ad['ad_type'] === 'video')
                                        <svg class="w-4 h-4 text-purple-500" viewBox="0 0 24 24" fill="none"><path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    @elseif($ad['ad_type'] === 'text' || $ad['ad_type'] === 'native')
                                        <svg class="w-4 h-4 text-blue-500" viewBox="0 0 24 24" fill="none"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    @elseif($ad['ad_type'] === 'html' || $ad['ad_type'] === 'rich_media')
                                        <svg class="w-4 h-4 text-amber-500" viewBox="0 0 24 24" fill="none"><path d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    @else
                                        <svg class="w-4 h-4 text-emerald-500" viewBox="0 0 24 24" fill="none"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    @endif
                                </div>
                                <span class="text-sm font-medium text-gray-800 truncate max-w-[200px]">{{ $ad['name'] }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 truncate max-w-[150px]">{{ $ad['campaign_name'] }}</td>
                        <td class="px-4 py-3">
                            @php
                                $typeColors = [
                                    'image' => 'bg-emerald-100 text-emerald-700',
                                    'video' => 'bg-purple-100 text-purple-700',
                                    'html' => 'bg-amber-100 text-amber-700',
                                    'text' => 'bg-blue-100 text-blue-700',
                                    'native' => 'bg-indigo-100 text-indigo-700',
                                    'rich_media' => 'bg-pink-100 text-pink-700',
                                    'vast' => 'bg-cyan-100 text-cyan-700',
                                ];
                                $color = $typeColors[$ad['ad_type']] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $color }}">{{ str_replace('_', ' ', $ad['ad_type']) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs text-gray-600">{{ $ad['file_type'] }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-mono text-gray-600">{{ $ad['size'] }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $statusColors = [
                                    'active' => 'bg-emerald-100 text-emerald-700',
                                    'paused' => 'bg-amber-100 text-amber-700',
                                    'pending_review' => 'bg-blue-100 text-blue-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                    'archived' => 'bg-gray-100 text-gray-600',
                                ];
                                $sColor = $statusColors[$ad['status']] ?? 'bg-gray-100 text-gray-600';
                                $statusLabels = [
                                    'active' => 'Active',
                                    'paused' => 'Paused',
                                    'pending_review' => 'Pending',
                                    'rejected' => 'Rejected',
                                    'archived' => 'Archived',
                                ];
                                $sLabel = $statusLabels[$ad['status']] ?? ucfirst($ad['status']);
                            @endphp
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $sColor }}">{{ $sLabel }}</span>
                        </td>
                        {{-- Weight --}}
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1" x-data="{ weight: {{ $ad['weight'] }}, saving: false }">
                                <button type="button" @click="if(weight > 1){ weight--; saving=true; fetch('{{ route('admin.adformats.updateWeight', $ad['id']) }}', { method:'PATCH', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body:JSON.stringify({weight}) }).then(()=>saving=false) }" class="w-5 h-5 rounded bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 hover:text-gray-700 transition-colors text-xs font-bold">−</button>
                                <span class="w-6 text-center text-xs font-bold tabular-nums" :class="weight >= 7 ? 'text-emerald-600' : (weight >= 4 ? 'text-amber-600' : 'text-red-600')" x-text="weight"></span>
                                <button type="button" @click="if(weight < 10){ weight++; saving=true; fetch('{{ route('admin.adformats.updateWeight', $ad['id']) }}', { method:'PATCH', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body:JSON.stringify({weight}) }).then(()=>saving=false) }" class="w-5 h-5 rounded bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 hover:text-gray-700 transition-colors text-xs font-bold">+</button>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs text-gray-500 truncate block max-w-[200px]" title="{{ $ad['destination_url'] }}">{{ $ad['destination_url'] }}</span>
                        </td>
                        {{-- Actions: Preview + Status Change + Delete --}}
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1" x-data>
                                {{-- Edit --}}
                                <a href="{{ route('admin.adformats.edit', $ad['id']) }}" class="p-1.5 rounded-lg hover:bg-amber-50 text-gray-400 hover:text-amber-600 transition-colors" title="Edit">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </a>
                                {{-- Demo --}}
                                <a href="{{ route('admin.adformats.demo', $ad['id']) }}" class="p-1.5 rounded-lg hover:bg-indigo-50 text-gray-400 hover:text-indigo-600 transition-colors" title="Demo">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </a>
                                {{-- Reports --}}
                                <a href="{{ route('admin.adformats.reports', $ad['id']) }}" class="p-1.5 rounded-lg hover:bg-emerald-50 text-gray-400 hover:text-emerald-600 transition-colors" title="Reports">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                </a>
                                {{-- Preview --}}
                                <button type="button"
                                    @click="$dispatch('open-preview', {
                                        id: {{ $ad['id'] }},
                                        name: @js($ad['name']),
                                        campaign_name: @js($ad['campaign_name']),
                                        ad_type: @js($ad['ad_type']),
                                        file_type: @js($ad['file_type']),
                                        size: @js($ad['size']),
                                        file_size_kb: @js($ad['file_size_kb']),
                                        status: @js($ad['status']),
                                        weight: {{ $ad['weight'] }},
                                        destination_url: @js($ad['destination_url']),
                                        display_url: @js($ad['display_url'] ?? ''),
                                        file_path: @js($ad['file_path'] ?? ''),
                                        headline: @js($ad['headline'] ?? ''),
                                        body_text: @js($ad['body_text'] ?? ''),
                                        call_to_action: @js($ad['call_to_action'] ?? ''),
                                        brand_name: @js($ad['brand_name'] ?? ''),
                                        created_at: @js($ad['created_at']),
                                    })"
                                    class="p-1.5 rounded-lg hover:bg-brand-50 text-gray-400 hover:text-brand-600 transition-colors" title="Preview">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                </button>
                                {{-- Status dropdown --}}
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors" title="Change Status">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-1 w-40 bg-white rounded-xl border border-gray-200 shadow-lg py-1 z-50" style="display: none;">
                                        @foreach(['active' => 'Active', 'paused' => 'Paused', 'pending_review' => 'Pending Review', 'rejected' => 'Rejected'] as $sVal => $sName)
                                            @if($ad['status'] !== $sVal)
                                                <form method="POST" action="{{ route('admin.adformats.updateStatus', $ad['id']) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="{{ $sVal }}">
                                                    <button type="submit" class="flex items-center gap-2 w-full px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition-colors">
                                                        @php
                                                            $dotColor = match($sVal) {
                                                                'active' => 'bg-emerald-500',
                                                                'paused' => 'bg-amber-500',
                                                                'pending_review' => 'bg-blue-500',
                                                                'rejected' => 'bg-red-500',
                                                                default => 'bg-gray-400',
                                                            };
                                                        @endphp
                                                        <span class="w-2 h-2 rounded-full {{ $dotColor }}"></span>
                                                        {{ $sName }}
                                                    </button>
                                                </form>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                {{-- Delete --}}
                                <form method="POST" action="{{ route('admin.adformats.destroy', $ad['id']) }}" onsubmit="return confirm('Are you sure you want to delete this creative?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600 transition-colors" title="Delete">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-300 mb-3" viewBox="0 0 24 24" fill="none"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                <p class="text-sm font-medium text-gray-500">No creatives found</p>
                                <p class="text-xs text-gray-400 mt-1">Try adjusting your filters or create a new campaign with creatives.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($pagination['total_pages'] > 1)
        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
            <p class="text-xs text-gray-500">
                Showing {{ $pagination['from'] }} to {{ $pagination['to'] }} of {{ $pagination['total'] }} creatives
            </p>
            <div class="flex items-center gap-1">
                @if($pagination['current_page'] > 1)
                    <a href="{{ route('admin.adformats', array_merge(request()->query(), ['page' => $pagination['current_page'] - 1])) }}" class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs text-gray-600 hover:bg-gray-50 transition-colors">Prev</a>
                @endif

                @for($p = max(1, $pagination['current_page'] - 2); $p <= min($pagination['total_pages'], $pagination['current_page'] + 2); $p++)
                    <a href="{{ route('admin.adformats', array_merge(request()->query(), ['page' => $p])) }}" class="px-3 py-1.5 rounded-lg text-xs {{ $p === $pagination['current_page'] ? 'bg-brand-600 text-white' : 'border border-gray-200 text-gray-600 hover:bg-gray-50' }} transition-colors">{{ $p }}</a>
                @endfor

                @if($pagination['current_page'] < $pagination['total_pages'])
                    <a href="{{ route('admin.adformats', array_merge(request()->query(), ['page' => $pagination['current_page'] + 1])) }}" class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs text-gray-600 hover:bg-gray-50 transition-colors">Next</a>
                @endif
            </div>
        </div>
    @endif
</div>
{{-- ═══════════════════════════════════════════════════════ --}}
{{-- PREVIEW MODAL                                          --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div x-data="{
        open: false,
        ad: {},
        imgLoaded: false,
        imgError: false,
        statusColors: {
            active: 'bg-emerald-100 text-emerald-700',
            paused: 'bg-amber-100 text-amber-700',
            pending_review: 'bg-blue-100 text-blue-700',
            rejected: 'bg-red-100 text-red-700',
            archived: 'bg-gray-100 text-gray-600',
        },
        statusLabels: {
            active: 'Active',
            paused: 'Paused',
            pending_review: 'Pending',
            rejected: 'Rejected',
            archived: 'Archived',
        },
        typeColors: {
            image: 'bg-emerald-100 text-emerald-700',
            video: 'bg-purple-100 text-purple-700',
            html: 'bg-amber-100 text-amber-700',
            text: 'bg-blue-100 text-blue-700',
            native: 'bg-indigo-100 text-indigo-700',
            rich_media: 'bg-pink-100 text-pink-700',
            vast: 'bg-cyan-100 text-cyan-700',
        },
    }"
    @open-preview.window="ad = $event.detail; imgLoaded = false; imgError = false; open = true"
    @keydown.escape.window="open = false"
>
    {{-- Backdrop --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50 z-[60]" @click="open = false" style="display: none;"></div>

    {{-- Modal Panel --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="fixed inset-0 z-[70] flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.stop>

            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-brand-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-brand-600" viewBox="0 0 24 24" fill="none"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Creative Preview</h3>
                        <p class="text-xs text-gray-400">ID: #<span x-text="ad.id"></span></p>
                    </div>
                </div>
                <button @click="open = false" class="p-2 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>

            {{-- Ad Preview Area --}}
            <div class="px-6 py-5">
                {{-- Visual Preview --}}
                <div class="mb-5 rounded-xl border border-gray-200 bg-gray-50 overflow-hidden">
                    {{-- Try loading actual image (hidden until loaded) --}}
                    <div x-show="ad.file_path && (ad.file_type === 'image' || ad.file_type === 'gif') && imgLoaded && !imgError" class="flex items-center justify-center p-6">
                        <img x-bind:src="ad.file_path" x-bind:alt="ad.name" class="rounded-lg shadow-sm object-contain" x-on:load="imgLoaded = true" x-on:error="imgError = true" style="display: none;" x-bind:style="(() => { let s = 'max-width:100%;'; if (!imgLoaded || imgError) return 'display:none;'; if (ad.size && ad.size !== '—') { let parts = ad.size.split('x'); let w = parseInt(parts[0]); let h = parseInt(parts[1]); if (w && h) { s += 'width:' + Math.min(w, 600) + 'px; height:' + Math.min(h, 400) + 'px;'; } } else { s += 'max-height:16rem;'; } return s; })()">
                    </div>

                    {{-- Image type placeholder (shown when image fails or while loading) --}}
                    <div x-show="ad.file_path && (ad.file_type === 'image' || ad.file_type === 'gif') && (!imgLoaded || imgError)" class="flex items-center justify-center p-6">
                        <div class="w-full max-w-sm border-2 border-dashed border-gray-300 rounded-xl bg-white p-8 text-center">
                            <div class="w-14 h-14 rounded-2xl bg-emerald-100 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-7 h-7 text-emerald-600" viewBox="0 0 24 24" fill="none"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-700 mb-1" x-text="ad.name"></p>
                            <p class="text-xs text-gray-400 mb-3" x-show="ad.size && ad.size !== '—'" x-text="ad.size + ' px'"></p>
                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-100 text-xs text-gray-500">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                <span x-text="ad.file_path ? ad.file_path.split('/').pop() : ''"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Video type placeholder --}}
                    <div x-show="ad.file_type === 'video'" class="flex items-center justify-center p-6">
                        <div class="w-full max-w-sm bg-gray-900 rounded-xl p-8 text-center relative overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-br from-purple-900/30 to-gray-900"></div>
                            <div class="relative">
                                <div class="w-14 h-14 rounded-full bg-white/10 backdrop-blur flex items-center justify-center mx-auto mb-3 border border-white/20">
                                    <svg class="w-6 h-6 text-white ml-0.5" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                                </div>
                                <p class="text-sm font-semibold text-white mb-1" x-text="ad.name"></p>
                                <p class="text-xs text-white/50" x-text="'Video Creative'"></p>
                                <p class="text-[10px] text-white/30 mt-2 truncate" x-text="ad.file_path ? ad.file_path.split('/').pop() : ''"></p>
                            </div>
                        </div>
                    </div>

                    {{-- HTML/Rich Media preview --}}
                    <div x-show="ad.file_type === 'html5' || ad.ad_type === 'html' || ad.ad_type === 'rich_media'"
                         x-data="{ fileExists: false, checked: false }"
                         x-init="if (ad.file_path) { fetch(ad.file_path, { method: 'HEAD' }).then(r => { fileExists = r.ok; checked = true; }).catch(() => { checked = true; }); } else { checked = true; }"
                         class="p-6">

                        {{-- Live iframe (only when file actually exists) --}}
                        <template x-if="checked && fileExists">
                            <div>
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="w-6 h-6 rounded-lg bg-amber-100 flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5 text-amber-600" viewBox="0 0 24 24" fill="none"><path d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </div>
                                    <p class="text-xs font-semibold text-gray-700" x-text="ad.name"></p>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-100 text-amber-700">HTML5</span>
                                    <span class="text-[10px] text-gray-400 ml-auto" x-show="ad.size && ad.size !== '—'" x-text="ad.size + ' px'"></span>
                                </div>
                                <div class="rounded-lg border border-gray-200 overflow-hidden bg-white flex items-center justify-center"
                                     :style="(() => {
                                         let w = 600, h = 400;
                                         if (ad.size && ad.size !== '—' && ad.size.includes('x')) {
                                             let parts = ad.size.split('x');
                                             w = Math.min(parseInt(parts[0]) || 600, 600);
                                             h = Math.min(parseInt(parts[1]) || 400, 400);
                                         }
                                         return 'width:' + w + 'px; height:' + h + 'px; max-width:100%;';
                                     })()">
                                    <iframe :src="ad.file_path"
                                            frameborder="0"
                                            scrolling="no"
                                            sandbox="allow-scripts allow-same-origin"
                                            style="width:100%; height:100%; border:0;"
                                            :title="ad.name"></iframe>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-2 font-mono truncate" x-text="ad.file_path"></p>
                            </div>
                        </template>

                        {{-- Fallback: no file or file not found --}}
                        <template x-if="checked && !fileExists">
                            <div class="w-full">
                                {{-- Header --}}
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-amber-600" viewBox="0 0 24 24" fill="none"><path d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate" x-text="ad.name"></p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-100 text-amber-700" x-text="ad.ad_type ? ad.ad_type.replace('_', ' ') : 'Rich Media'"></span>
                                            <span class="text-[10px] text-gray-400" x-text="ad.file_path ? 'File not found on server' : 'URL-only ad (no creative file)'"></span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Ad Details Card --}}
                                <div class="rounded-xl border border-amber-200 bg-amber-50/50 overflow-hidden">
                                    {{-- Headline + Body --}}
                                    <div class="p-4" x-show="ad.headline || ad.body_text">
                                        <div class="flex items-center gap-2 mb-2" x-show="ad.brand_name">
                                            <div class="w-6 h-6 rounded-full bg-amber-200 flex items-center justify-center">
                                                <span class="text-[9px] font-bold text-amber-700" x-text="ad.brand_name ? ad.brand_name.charAt(0).toUpperCase() : ''"></span>
                                            </div>
                                            <span class="text-xs font-medium text-gray-700" x-text="ad.brand_name"></span>
                                            <span class="text-[9px] text-gray-400 ml-auto">Sponsored</span>
                                        </div>
                                        <h4 class="text-sm font-semibold text-gray-900 mb-1" x-show="ad.headline" x-text="ad.headline"></h4>
                                        <p class="text-xs text-gray-600 leading-relaxed" x-show="ad.body_text" x-text="ad.body_text"></p>
                                        <div class="mt-3" x-show="ad.call_to_action">
                                            <span class="inline-block px-3 py-1 rounded-lg bg-brand-600 text-white text-xs font-semibold" x-text="ad.call_to_action"></span>
                                        </div>
                                    </div>

                                    {{-- Info Grid --}}
                                    <div class="border-t border-amber-200 p-4">
                                        <div class="grid grid-cols-2 gap-3">
                                            <div x-show="ad.campaign_name">
                                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Campaign</p>
                                                <p class="text-xs font-medium text-gray-700 truncate" x-text="ad.campaign_name"></p>
                                            </div>
                                            <div x-show="ad.size && ad.size !== '—'">
                                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Size</p>
                                                <p class="text-xs font-mono font-medium text-gray-700" x-text="ad.size + ' px'"></p>
                                            </div>
                                            <div x-show="ad.file_type && ad.file_type !== '—'">
                                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">File Type</p>
                                                <p class="text-xs font-medium text-gray-700 uppercase" x-text="ad.file_type"></p>
                                            </div>
                                            <div x-show="ad.file_size_kb && ad.file_size_kb !== '—'">
                                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">File Size</p>
                                                <p class="text-xs font-medium text-gray-700" x-text="ad.file_size_kb + ' KB'"></p>
                                            </div>
                                            <div x-show="ad.display_url">
                                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Display URL</p>
                                                <p class="text-xs font-medium text-gray-700 truncate" x-text="ad.display_url"></p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Status</p>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase" :class="statusColors[ad.status] || 'bg-gray-100 text-gray-600'" x-text="statusLabels[ad.status] || ad.status"></span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Destination URL --}}
                                    <div class="border-t border-amber-200 p-4" x-show="ad.destination_url">
                                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Destination URL</p>
                                        <div class="flex items-center gap-2">
                                            <p class="text-xs text-brand-600 truncate flex-1" x-text="ad.destination_url"></p>
                                            <a :href="ad.destination_url" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-brand-600 text-white text-xs font-semibold hover:bg-brand-700 transition-colors flex-shrink-0">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                Open URL
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Text / Native / Other without file --}}
                    <div x-show="!ad.file_path && ad.file_type !== 'html5' && ad.ad_type !== 'html' && ad.ad_type !== 'rich_media' && ad.file_type !== 'video'" class="flex items-center justify-center p-6">
                        <div class="w-full max-w-sm border-2 border-dashed border-gray-300 rounded-xl bg-white p-8 text-center">
                            <div class="w-14 h-14 rounded-2xl bg-blue-100 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-7 h-7 text-blue-600" viewBox="0 0 24 24" fill="none"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-700 mb-1" x-text="ad.name"></p>
                            <p class="text-xs text-gray-400" x-text="ad.ad_type ? ad.ad_type.replace('_', ' ').charAt(0).toUpperCase() + ad.ad_type.replace('_', ' ').slice(1) + ' Creative' : 'Creative'"></p>
                        </div>
                    </div>

                    {{-- Native/Text ad preview with headline + body --}}
                    <div x-show="ad.headline || ad.body_text" class="px-6 py-4 border-t border-gray-200 bg-white">
                        <div class="flex items-center gap-2 mb-2" x-show="ad.brand_name">
                            <div class="w-6 h-6 rounded-full bg-brand-100 flex items-center justify-center">
                                <span class="text-[9px] font-bold text-brand-600" x-text="ad.brand_name ? ad.brand_name.charAt(0).toUpperCase() : ''"></span>
                            </div>
                            <span class="text-xs font-medium text-gray-700" x-text="ad.brand_name"></span>
                            <span class="text-[9px] text-gray-400 ml-auto">Sponsored</span>
                        </div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-1" x-show="ad.headline" x-text="ad.headline"></h4>
                        <p class="text-xs text-gray-600 leading-relaxed" x-show="ad.body_text" x-text="ad.body_text"></p>
                        <div class="mt-3" x-show="ad.call_to_action">
                            <span class="inline-block px-3 py-1 rounded-lg bg-brand-600 text-white text-xs font-semibold" x-text="ad.call_to_action"></span>
                        </div>
                    </div>
                </div>

                {{-- Creative Details --}}
                <div class="space-y-3">
                    <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Creative Details</h4>

                    <div class="grid grid-cols-2 gap-3">
                        {{-- Creative Name --}}
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-3">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Creative Name</p>
                            <p class="text-sm font-medium text-gray-900 truncate" x-text="ad.name"></p>
                        </div>

                        {{-- Campaign --}}
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-3">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Campaign</p>
                            <p class="text-sm font-medium text-gray-900 truncate" x-text="ad.campaign_name"></p>
                        </div>

                        {{-- Creative Type --}}
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-3">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Creative Type</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase" :class="typeColors[ad.ad_type] || 'bg-gray-100 text-gray-700'" x-text="ad.ad_type ? ad.ad_type.replace('_', ' ') : ''"></span>
                                <span class="text-xs text-gray-500" x-show="ad.file_type && ad.file_type !== '—'" x-text="'(' + ad.file_type + ')'"></span>
                            </div>
                        </div>

                        {{-- Creative Size --}}
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-3">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Creative Size</p>
                            <p class="text-sm font-medium text-gray-900">
                                <span class="font-mono" x-text="ad.size"></span>
                                <span class="text-xs text-gray-400 ml-1" x-show="ad.file_size_kb && ad.file_size_kb !== '—'" x-text="'(' + ad.file_size_kb + ' KB)'"></span>
                            </p>
                        </div>

                        {{-- Created Date --}}
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-3">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Created Date</p>
                            <p class="text-sm font-medium text-gray-900" x-text="ad.created_at || '—'"></p>
                        </div>

                        {{-- Status --}}
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-3">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Status</p>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase" :class="statusColors[ad.status] || 'bg-gray-100 text-gray-600'" x-text="statusLabels[ad.status] || ad.status"></span>
                        </div>
                    </div>

                    {{-- URL --}}
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-3">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Destination URL</p>
                        <a :href="ad.destination_url" target="_blank" rel="noopener noreferrer" class="text-sm text-brand-600 hover:text-brand-700 hover:underline break-all" x-text="ad.destination_url"></a>
                        <p class="text-[10px] text-gray-400 mt-0.5" x-show="ad.display_url" x-text="ad.display_url"></p>
                    </div>

                    {{-- Weight --}}
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-3">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Weight / Priority</p>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all" :class="ad.weight >= 7 ? 'bg-emerald-500' : (ad.weight >= 4 ? 'bg-amber-500' : 'bg-red-500')" :style="'width: ' + (ad.weight * 10) + '%'"></div>
                            </div>
                            <span class="text-sm font-bold tabular-nums" :class="ad.weight >= 7 ? 'text-emerald-600' : (ad.weight >= 4 ? 'text-amber-600' : 'text-red-600')" x-text="ad.weight + '/10'"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-2">
                <button @click="open = false" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection
