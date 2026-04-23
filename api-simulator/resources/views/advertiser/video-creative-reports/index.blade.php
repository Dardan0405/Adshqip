@extends('layouts.advertiser')

@section('title', 'Video Creative Report')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Video Creative Report</h1>
            <p class="text-sm text-gray-500 mt-1">Video creative engagement by quartile and player interaction.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 mb-6">
        @php
            $cards = [
                ['label' => 'Creatives', 'value' => number_format($summary['creatives']), 'color' => 'text-slate-900', 'bg' => 'bg-slate-50', 'border' => 'border-slate-200'],
                ['label' => 'Impressions', 'value' => number_format($summary['impressions']), 'color' => 'text-blue-700', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200'],
                ['label' => 'Complete', 'value' => number_format($summary['complete']), 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200'],
                ['label' => 'Pause', 'value' => number_format($summary['pause']), 'color' => 'text-amber-700', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200'],
                ['label' => 'Resume', 'value' => number_format($summary['resume']), 'color' => 'text-cyan-700', 'bg' => 'bg-cyan-50', 'border' => 'border-cyan-200'],
                ['label' => 'Mute / Unmute', 'value' => number_format($summary['mute']) . ' / ' . number_format($summary['unmute']), 'color' => 'text-violet-700', 'bg' => 'bg-violet-50', 'border' => 'border-violet-200'],
            ];
        @endphp
        @foreach($cards as $card)
            <div class="rounded-xl border {{ $card['border'] }} {{ $card['bg'] }} p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-2">{{ $card['label'] }}</div>
                <div class="text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    <form method="GET" action="{{ route('advertiser.reports.video-creative') }}" class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4">
            <div class="flex items-center gap-2 flex-1 flex-wrap">
                <div class="relative flex-1 min-w-[180px] max-w-xs">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search creative..."
                           class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <input type="date" name="start_date" value="{{ request('start_date', $defaults['start_date']) }}" class="px-3 py-2 rounded-lg border border-gray-200 text-sm">
                <input type="date" name="end_date" value="{{ request('end_date', $defaults['end_date']) }}" class="px-3 py-2 rounded-lg border border-gray-200 text-sm">
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-800">Filter</button>
                <a href="{{ route('advertiser.reports.video-creative') }}" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</a>
            </div>

            <div class="relative" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Export
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-40 bg-white rounded-xl border border-gray-200 shadow-lg py-1 z-50" style="display:none;">
                    <a href="{{ route('advertiser.reports.video-creative.export', request()->all()) }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">CSV</a>
                    <button type="button" onclick="window.print()" class="block w-full text-left px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Print</button>
                </div>
            </div>
        </div>
    </form>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Video Creative Performance</h2>
            <p class="text-xs text-gray-500 mt-1">{{ $rows->total() }} video creatives</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Creative</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Impressions</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">View 25%</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">View 50%</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">View 75%</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Complete</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Pause</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Resume</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Full Screen</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Unmute</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Mute</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rows as $row)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $row->creative }}</div>
                                <div class="text-xs text-gray-400">#{{ $row->ad_id }} &middot; {{ strtoupper($row->ad_type) }}</div>
                            </td>
                            <td class="px-4 py-3 text-right">{{ number_format($row->impressions) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($row->view_25) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($row->view_50) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($row->view_75) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-emerald-700">{{ number_format($row->complete) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($row->pause) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($row->resume) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($row->full_screen) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($row->unmute) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($row->mute) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-4 py-12 text-center text-sm text-gray-500">No video creative report data found for the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rows->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $rows->links() }}</div>
        @endif
    </div>
@endsection
