@extends('layouts.advertiser')

@section('title', 'Reports — ' . $ad['name'])

@section('content')
{{-- Page Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-1">
            <a href="{{ route('advertiser.adformats') }}" class="hover:text-gray-600 transition-colors">Ad Formats</a>
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            <span class="text-gray-600">#{{ $ad['id'] }} — Reports</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $ad['name'] }}</h1>
        <p class="text-sm text-gray-400 mt-0.5">{{ $ad['campaign_name'] }} &mdash; {{ $ad['ad_type'] }} &mdash; {{ $ad['size'] }}</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('advertiser.adformats') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Back
        </a>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- TOP STATS CARDS                                         --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Impressions</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totals['impressions']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-[10px] font-semibold text-blue-500 uppercase tracking-wider">Clicks</p>
        <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($totals['clicks']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-[10px] font-semibold text-purple-500 uppercase tracking-wider">Unique Impressions</p>
        <p class="text-2xl font-bold text-purple-600 mt-1">{{ number_format($totals['unique_impressions']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-[10px] font-semibold text-indigo-500 uppercase tracking-wider">Unique Clicks</p>
        <p class="text-2xl font-bold text-indigo-600 mt-1">{{ number_format($totals['unique_clicks']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-[10px] font-semibold text-emerald-500 uppercase tracking-wider">Conversions</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($totals['conversions']) }}</p>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- REPORTS TABLE                                           --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-xl border border-gray-200">
    {{-- Table Header --}}
    <div class="px-6 py-4 border-b border-gray-100">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-brand-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-brand-600" viewBox="0 0 24 24" fill="none"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Daily Performance</h2>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $pagination['total'] }} days of data</p>
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
                        <a href="{{ route('advertiser.adformats.reports.export', array_merge(['id' => $ad['id'], 'format' => 'csv'], request()->only(['date_from', 'date_to', 'device', 'country']))) }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            CSV
                        </a>
                        <a href="{{ route('advertiser.adformats.reports.export', array_merge(['id' => $ad['id'], 'format' => 'excel'], request()->only(['date_from', 'date_to', 'device', 'country']))) }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4 text-emerald-500" viewBox="0 0 24 24" fill="none"><path d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            Excel
                        </a>
                        <button type="button" onclick="window.print()" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50 transition-colors w-full">
                            <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            Print
                        </button>
                        <a href="{{ route('advertiser.adformats.reports.export', array_merge(['id' => $ad['id'], 'format' => 'pdf'], request()->only(['date_from', 'date_to', 'device', 'country']))) }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50 transition-colors">
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
        <form method="GET" action="{{ route('advertiser.adformats.reports', $ad['id']) }}" class="flex flex-col md:flex-row md:items-center gap-3">
            {{-- Date From --}}
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 whitespace-nowrap">From</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="px-3 py-2 rounded-lg border border-gray-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>

            {{-- Date To --}}
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 whitespace-nowrap">To</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="px-3 py-2 rounded-lg border border-gray-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>

            {{-- Device Filter --}}
            <select name="device" class="px-3 py-2 rounded-lg border border-gray-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                <option value="all" {{ $deviceFilter === 'all' ? 'selected' : '' }}>All Devices</option>
                <option value="desktop" {{ $deviceFilter === 'desktop' ? 'selected' : '' }}>Desktop</option>
                <option value="mobile" {{ $deviceFilter === 'mobile' ? 'selected' : '' }}>Mobile</option>
                <option value="tablet" {{ $deviceFilter === 'tablet' ? 'selected' : '' }}>Tablet</option>
            </select>

            {{-- Country Filter --}}
            <select name="country" class="px-3 py-2 rounded-lg border border-gray-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                <option value="all" {{ $countryFilter === 'all' ? 'selected' : '' }}>All Countries</option>
                @foreach($availableCountries as $code)
                    <option value="{{ $code }}" {{ $countryFilter === $code ? 'selected' : '' }}>{{ $code }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 transition-colors">Filter</button>

            @if($dateFrom !== now()->subDays(29)->format('Y-m-d') || $dateTo !== now()->format('Y-m-d') || $deviceFilter !== 'all' || $countryFilter !== 'all')
                <a href="{{ route('advertiser.adformats.reports', $ad['id']) }}" class="px-3 py-2 rounded-lg border border-gray-200 text-sm text-gray-500 hover:bg-gray-50 transition-colors">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Date</th>
                    <th class="text-right px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Impressions</th>
                    <th class="text-right px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Clicks</th>
                    <th class="text-right px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Conversions</th>
                    <th class="text-right px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Spend</th>
                    <th class="text-right px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">CTR</th>
                    <th class="text-right px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">eCPM</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($dailyStats as $row)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $row['date'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 text-right tabular-nums">{{ number_format($row['impressions']) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 text-right tabular-nums">{{ number_format($row['clicks']) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 text-right tabular-nums">{{ number_format($row['conversions']) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 text-right tabular-nums">${{ number_format($row['spend'], 2) }}</td>
                        <td class="px-4 py-3 text-sm text-right tabular-nums">
                            <span class="{{ $row['ctr'] >= 2 ? 'text-emerald-600' : ($row['ctr'] >= 1 ? 'text-amber-600' : 'text-gray-600') }}">{{ $row['ctr'] }}%</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 text-right tabular-nums">${{ number_format($row['ecpm'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-300 mb-3" viewBox="0 0 24 24" fill="none"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                <p class="text-sm font-medium text-gray-500">No report data found</p>
                                <p class="text-xs text-gray-400 mt-1">Try adjusting your date range or filters.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($dailyStats) > 0)
                <tfoot>
                    <tr class="border-t-2 border-gray-200 bg-gray-50/50 font-semibold">
                        <td class="px-4 py-3 text-sm text-gray-900">Total</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right tabular-nums">{{ number_format($totals['impressions']) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right tabular-nums">{{ number_format($totals['clicks']) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right tabular-nums">{{ number_format($totals['conversions']) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right tabular-nums">${{ number_format($totals['spend'], 2) }}</td>
                        <td class="px-4 py-3 text-sm text-right tabular-nums">
                            <span class="{{ $totals['ctr'] >= 2 ? 'text-emerald-600' : ($totals['ctr'] >= 1 ? 'text-amber-600' : 'text-gray-600') }} font-semibold">{{ $totals['ctr'] }}%</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right tabular-nums">${{ number_format($totals['ecpm'], 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    {{-- Pagination --}}
    @if($pagination['total_pages'] > 1)
        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
            <p class="text-xs text-gray-500">
                Showing {{ $pagination['from'] }} to {{ $pagination['to'] }} of {{ $pagination['total'] }} days
            </p>
            <div class="flex items-center gap-1">
                @if($pagination['current_page'] > 1)
                    <a href="{{ route('advertiser.adformats.reports', array_merge(['id' => $ad['id']], request()->query(), ['page' => $pagination['current_page'] - 1])) }}" class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs text-gray-600 hover:bg-gray-50 transition-colors">Prev</a>
                @endif

                @for($p = max(1, $pagination['current_page'] - 2); $p <= min($pagination['total_pages'], $pagination['current_page'] + 2); $p++)
                    <a href="{{ route('advertiser.adformats.reports', array_merge(['id' => $ad['id']], request()->query(), ['page' => $p])) }}" class="px-3 py-1.5 rounded-lg text-xs {{ $p === $pagination['current_page'] ? 'bg-brand-600 text-white' : 'border border-gray-200 text-gray-600 hover:bg-gray-50' }} transition-colors">{{ $p }}</a>
                @endfor

                @if($pagination['current_page'] < $pagination['total_pages'])
                    <a href="{{ route('advertiser.adformats.reports', array_merge(['id' => $ad['id']], request()->query(), ['page' => $pagination['current_page'] + 1])) }}" class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs text-gray-600 hover:bg-gray-50 transition-colors">Next</a>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection

