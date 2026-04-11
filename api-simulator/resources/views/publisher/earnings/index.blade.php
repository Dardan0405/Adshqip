@extends('layouts.publisher')

@section('title', 'Your Earnings')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Your Earnings</h1>
            <p class="text-sm text-gray-500 mt-1">Review your monthly earnings and expand a month to inspect the daily revenue inside it.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
        @php
            $statCards = [
                ['label' => 'Total Revenue', 'value' => '€' . number_format($totalRevenue, 2), 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'icon' => '<path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/>'],
                ['label' => 'This Month', 'value' => '€' . number_format($currentMonthRevenue, 2), 'color' => 'text-blue-700', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'icon' => '<path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Active Months', 'value' => number_format($activeMonths), 'color' => 'text-purple-700', 'bg' => 'bg-purple-50', 'border' => 'border-purple-200', 'icon' => '<path d="M7 8h10M7 12h6m-6 4h8M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
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

    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="p-4 border-b border-gray-100">
            <form method="GET" action="{{ route('publisher.earnings') }}" class="flex items-center gap-2 flex-wrap">
                <input type="month" name="start_month" value="{{ request('start_month') }}" class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                <input type="month" name="end_month" value="{{ request('end_month') }}" class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium text-gray-600 hover:bg-gray-200 transition-colors">Filter</button>
                <a href="{{ route('publisher.earnings') }}" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</a>
            </form>
        </div>

        <div class="overflow-x-auto" x-data="{ openRow: null }">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Month-Year</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Revenue</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rows as $row)
                        <tr class="hover:bg-gray-50/50 transition-colors cursor-pointer" @click="openRow = openRow === {{ $row->row_id }} ? null : {{ $row->row_id }}">
                            <td class="px-4 py-3 font-medium text-gray-900">#{{ $row->row_id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $row->month_formatted }}</div>
                                <div class="text-xs text-gray-400">{{ $row->month }}</div>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-700">€{{ number_format($row->revenue, 2) }}</td>
                            <td class="px-4 py-3 text-right text-gray-500">
                                <svg class="w-4 h-4 inline-block transition-transform" :class="openRow === {{ $row->row_id }} ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none"><path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </td>
                        </tr>
                        <tr x-show="openRow === {{ $row->row_id }}" x-transition style="display: none;" class="bg-gray-50/70">
                            <td colspan="4" class="px-4 py-4">
                                <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
                                    <table class="w-full text-sm">
                                        <thead class="bg-gray-50 border-b border-gray-100">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Date</th>
                                                <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Revenue</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach($row->daily_breakdown as $day)
                                                <tr>
                                                    <td class="px-4 py-3 text-gray-700">{{ $day->date_formatted }}</td>
                                                    <td class="px-4 py-3 text-right font-medium text-emerald-700">€{{ number_format($day->revenue, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center text-gray-500">No earnings found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rows->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $rows->links() }}
            </div>
        @endif
    </div>
@endsection
