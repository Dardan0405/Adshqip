@extends('layouts.publisher')

@section('title', 'Dashboard')

@section('content')
    {{-- ═══════════ EARNINGS CARDS (Top Row) ═══════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="text-[10px] text-gray-400 uppercase tracking-wide">Today</div>
            <div class="text-2xl font-bold text-gray-900 mt-1">${{ number_format($earnings['today'], 2) }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="text-[10px] text-gray-400 uppercase tracking-wide">This week</div>
            <div class="text-2xl font-bold text-gray-900 mt-1">${{ number_format($earnings['this_week'], 2) }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="text-[10px] text-gray-400 uppercase tracking-wide">This month</div>
            <div class="text-2xl font-bold text-gray-900 mt-1">${{ number_format($earnings['this_month'], 2) }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="text-[10px] text-gray-400 uppercase tracking-wide">Last month</div>
            <div class="text-2xl font-bold text-gray-900 mt-1">${{ number_format($earnings['last_month'], 2) }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="text-[10px] text-gray-400 uppercase tracking-wide">This month forecast</div>
            <div class="flex items-center gap-2">
                <span class="text-2xl font-bold text-gray-900">${{ number_format($earnings['forecast'], 2) }}</span>
                <span class="text-xs font-semibold text-brand-600 bg-brand-50 px-1.5 py-0.5 rounded">↑ {{ $earnings['forecast_pct'] }}%</span>
            </div>
        </div>
    </div>

    {{-- Date selector row --}}
    <div class="flex items-center gap-4 mb-6">
        <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-600">
            <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            <span>01</span> October, 2025 — <span>30</span> October, 2025
        </div>
        <span class="text-xs text-gray-400">Saturday</span>
        <div class="ml-auto flex items-center gap-1">
            <button class="p-1.5 rounded-lg bg-brand-50 text-brand-600 hover:bg-brand-100"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>
        </div>
    </div>

    {{-- ═══════════ IMPRESSIONS / CLICKS / REVENUE ═══════════ --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        {{-- Impressions --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] text-gray-400 uppercase tracking-wide">Impressions</div>
                    <div class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($metrics['impressions']) }}</div>
                    <span class="text-[10px] text-gray-400">Last 30 days</span>
                </div>
                <div class="w-16 h-10">
                    {{-- Mini spark line --}}
                    <svg viewBox="0 0 60 24" class="w-full h-full text-brand-400"><polyline fill="none" stroke="currentColor" stroke-width="2" points="0,18 8,14 16,16 24,10 32,12 40,6 48,8 56,4 60,2"/></svg>
                </div>
            </div>
            <div class="flex items-center gap-2 mt-2">
                <span class="text-xs font-medium {{ $metrics['impressions_change'] >= 0 ? 'text-brand-600' : 'text-red-500' }}">{{ $metrics['impressions_change'] >= 0 ? '↑' : '↓' }} {{ abs($metrics['impressions_change']) }}% ({{ number_format(abs($metrics['impressions_diff'])) }})</span>
                <span class="text-[10px] text-gray-400">from previous 30 days</span>
            </div>
        </div>

        {{-- Clicks --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] text-gray-400 uppercase tracking-wide">Clicks</div>
                    <div class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($metrics['clicks']) }}</div>
                    <span class="text-[10px] text-gray-400">Last 30 days</span>
                </div>
                <div class="w-16 h-10">
                    <svg viewBox="0 0 60 24" class="w-full h-full text-red-400"><polyline fill="none" stroke="currentColor" stroke-width="2" points="0,4 8,6 16,8 24,5 32,10 40,14 48,12 56,18 60,20"/></svg>
                </div>
            </div>
            <div class="flex items-center gap-2 mt-2">
                <span class="text-xs font-medium {{ $metrics['clicks_change'] >= 0 ? 'text-brand-600' : 'text-red-500' }}">{{ $metrics['clicks_change'] >= 0 ? '↑' : '↓' }} {{ abs($metrics['clicks_change']) }}% ({{ number_format(abs($metrics['clicks_diff'])) }})</span>
                <span class="text-[10px] text-gray-400">from previous 30 days</span>
            </div>
        </div>

        {{-- Revenue --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div>
                <div class="text-[10px] text-gray-400 uppercase tracking-wide">Revenue</div>
                <div class="text-3xl font-bold text-brand-600 mt-1">${{ number_format($metrics['revenue'], 2) }}</div>
                <span class="text-[10px] text-gray-400">Last 30 days</span>
            </div>
            <div class="flex items-center gap-2 mt-2">
                <span class="text-xs font-medium {{ $metrics['revenue_change'] >= 0 ? 'text-brand-600' : 'text-red-500' }}">{{ $metrics['revenue_change'] >= 0 ? '↑' : '↓' }} {{ abs($metrics['revenue_change']) }}% (${{ number_format(abs($metrics['revenue_diff']), 2) }})</span>
                <span class="text-[10px] text-gray-400">from previous 30 days</span>
            </div>
        </div>
    </div>

    {{-- ═══════════ STATISTICS CHART ═══════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-1">
                <h3 class="text-sm font-semibold text-gray-700">Statistics</h3>
                <div class="flex items-center gap-1 ml-4">
                    <button class="px-3 py-1 text-xs rounded-lg bg-gray-100 text-gray-500 hover:bg-gray-200">daily</button>
                    <button class="px-3 py-1 text-xs rounded-lg bg-brand-600 text-white font-medium">country</button>
                    <button class="px-3 py-1 text-xs rounded-lg bg-gray-100 text-gray-500 hover:bg-gray-200">sites</button>
                    <button class="px-3 py-1 text-xs rounded-lg bg-gray-100 text-gray-500 hover:bg-gray-200">zones</button>
                    <button class="px-3 py-1 text-xs rounded-lg bg-gray-100 text-gray-500 hover:bg-gray-200">creatives</button>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-400">↕ export</span>
                <div class="flex items-center gap-2">
                    <button class="w-7 h-7 rounded-lg bg-brand-600 text-white flex items-center justify-center text-xs">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                    <button class="w-7 h-7 rounded-lg bg-gray-100 text-gray-500 flex items-center justify-center text-xs hover:bg-gray-200">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </div>
                <button class="text-xs text-gray-400 hover:text-gray-600">⚙ settings</button>
            </div>
        </div>

        {{-- Chart --}}
        <div class="relative h-72">
            <div class="absolute inset-0 flex items-end gap-2 px-2">
                @foreach($chartData as $day)
                    <div class="flex-1 flex flex-col items-center gap-0 group relative">
                        <div class="w-full rounded-t bg-gray-200 group-hover:bg-gray-300 transition-all" style="height: {{ $day['impressions_pct'] }}%"></div>
                        <div class="w-full rounded-t bg-brand-400 group-hover:bg-brand-500 transition-all -mt-0.5" style="height: {{ $day['profit_pct'] }}%"></div>
                        <span class="text-[8px] text-gray-400 mt-1">{{ $day['label'] }}</span>
                    </div>
                @endforeach
            </div>
            {{-- Y-axis --}}
            <div class="absolute left-0 top-0 bottom-6 flex flex-col justify-between text-[9px] text-gray-400 -ml-1">
                <span>400,000</span><span>350,000</span><span>300,000</span><span>250,000</span><span>200,000</span><span>150,000</span><span>100,000</span><span>50,000</span><span>0</span>
            </div>
            <div class="absolute right-0 top-0 bottom-6 flex flex-col justify-between text-[9px] text-gray-400 -mr-1">
                <span>$300,000</span><span></span><span>$200,000</span><span></span><span>$100,000</span><span></span><span>$50,000</span><span></span><span>$0</span>
            </div>
        </div>
        <div class="flex items-center justify-center gap-6 mt-3">
            <span class="flex items-center gap-1.5 text-xs text-gray-500"><span class="w-3 h-3 rounded-sm bg-gray-300"></span> Impressions</span>
            <span class="flex items-center gap-1.5 text-xs text-gray-500"><span class="w-3 h-3 rounded-sm bg-brand-500"></span> Profit</span>
        </div>
    </div>

    {{-- ═══════════ REVENUE AD ZONES + CPC DEVICE TYPE (Bottom Row) ═══════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Revenue - Ad Zones --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700">Revenue – Ad Zones</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[10px] uppercase tracking-wider text-gray-400 border-b border-gray-100">
                            <th class="px-6 py-3 font-medium">ad zone</th>
                            <th class="px-4 py-3 font-medium">revenue</th>
                            <th class="px-4 py-3 font-medium">change</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($adZones as $zone)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-6 py-3 text-gray-600">{{ $zone['name'] }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900">${{ number_format($zone['revenue'], 2) }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-xs font-medium {{ $zone['change'] >= 0 ? 'text-brand-600' : 'text-red-500' }}">
                                        {{ $zone['change'] >= 0 ? '↑' : '↓' }} {{ abs($zone['change']) }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- CPC - Device Type --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700">CPC – Device Type</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[10px] uppercase tracking-wider text-gray-400 border-b border-gray-100">
                            <th class="px-6 py-3 font-medium">device type</th>
                            <th class="px-4 py-3 font-medium">CPC</th>
                            <th class="px-4 py-3 font-medium">change</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($deviceCpc as $device)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-6 py-3 text-gray-600">{{ $device['type'] }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900">${{ number_format($device['cpc'], 2) }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-xs font-medium {{ $device['change'] >= 0 ? 'text-brand-600' : 'text-red-500' }}">
                                        {{ $device['change'] >= 0 ? '↑' : '↓' }} {{ abs($device['change']) }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
