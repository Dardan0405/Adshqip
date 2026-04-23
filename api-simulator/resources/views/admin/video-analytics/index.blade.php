@extends('layouts.admin')

@section('title', 'Video Analytics')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Video Analytics</h1>
            <p class="text-sm text-gray-500 mt-1">Track starts, quartiles, completions, skips, and viewer progress for video ads.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        @php
            $cards = [
                ['label' => 'Video Ads', 'value' => number_format($summary['video_ads']), 'color' => 'text-slate-900', 'bg' => 'bg-slate-50', 'border' => 'border-slate-200'],
                ['label' => 'Tracked Events', 'value' => number_format($summary['events']), 'color' => 'text-blue-700', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200'],
                ['label' => 'Viewers', 'value' => number_format($summary['viewers']), 'color' => 'text-violet-700', 'bg' => 'bg-violet-50', 'border' => 'border-violet-200'],
                ['label' => 'Starts', 'value' => number_format($summary['starts']), 'color' => 'text-cyan-700', 'bg' => 'bg-cyan-50', 'border' => 'border-cyan-200'],
                ['label' => 'Completes', 'value' => number_format($summary['completes']), 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200'],
                ['label' => 'Completion Rate', 'value' => number_format($summary['completion_rate'], 2) . '%', 'color' => 'text-amber-700', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200'],
            ];
        @endphp
        @foreach($cards as $card)
            <div class="rounded-xl border {{ $card['border'] }} {{ $card['bg'] }} p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-2">{{ $card['label'] }}</div>
                <div class="text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.video-analytics') }}" class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4">
            <div class="flex items-center gap-2 flex-1 flex-wrap">
                <div class="relative flex-1 min-w-[180px] max-w-xs">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ad or campaign..."
                           class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <input type="date" name="start_date" value="{{ request('start_date', $defaults['start_date']) }}" class="px-3 py-2 rounded-lg border border-gray-200 text-sm">
                <input type="date" name="end_date" value="{{ request('end_date', $defaults['end_date']) }}" class="px-3 py-2 rounded-lg border border-gray-200 text-sm">
                <select name="event_name" class="px-3 py-2 rounded-lg border border-gray-200 text-sm">
                    <option value="">All Events</option>
                    @foreach(['start', 'firstQuartile', 'midpoint', 'thirdQuartile', 'complete', 'skip'] as $event)
                        <option value="{{ $event }}" {{ request('event_name') === $event ? 'selected' : '' }}>{{ $event }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-800">Filter</button>
                <a href="{{ route('admin.video-analytics') }}" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</a>
            </div>

            <div class="relative" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Export
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-40 bg-white rounded-xl border border-gray-200 shadow-lg py-1 z-50" style="display:none;">
                    <a href="{{ route('admin.video-analytics.export', request()->all()) }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">CSV</a>
                    <button type="button" onclick="window.print()" class="block w-full text-left px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Print</button>
                </div>
            </div>
        </div>
    </form>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Video Event Performance</h2>
            <p class="text-xs text-gray-500 mt-1">{{ $rows->total() }} video ads</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Ad</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Campaign</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Viewers</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Starts</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">25%</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">50%</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">75%</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Completes</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Skips</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Completion</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rows as $row)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $row->ad_name }}</div>
                                <div class="text-xs text-gray-400">#{{ $row->ad_id }} · {{ strtoupper($row->ad_type) }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $row->campaign_name }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($row->unique_viewers) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($row->starts) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($row->first_quartile) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($row->midpoint) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($row->third_quartile) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-emerald-700">{{ number_format($row->completes) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($row->skips) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($row->completion_rate, 2) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center text-sm text-gray-500">No video analytics data found for the selected filters.</td>
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
