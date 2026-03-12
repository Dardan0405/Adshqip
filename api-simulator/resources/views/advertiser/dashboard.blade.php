@extends('layouts.advertiser')

@section('title', 'Dashboard')

@section('content')
    {{-- Breadcrumb --}}
    <div class="text-xs text-gray-400 mb-1">
        <span class="text-brand-600">⌂</span> &gt; dashboard
    </div>

    {{-- Title --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Overview</h1>
            <p class="text-sm text-gray-500">welcome back, {{ Auth::user()->email }}</p>
        </div>
        <div class="hidden md:flex items-center gap-2 text-xs text-brand-600 bg-brand-50 px-3 py-2 rounded-lg border border-brand-200">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            AdShqip is introducing new creative partner. <a href="#" class="underline font-medium">Learn more</a> →
        </div>
    </div>

    {{-- ═══════════ STAT CARDS ═══════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        {{-- Total Campaigns --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-2 text-xs text-gray-400 mb-2">
                <svg class="w-4 h-4 text-blue-500" viewBox="0 0 24 24" fill="none"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                Total Campaigns
            </div>
            <div class="text-3xl font-bold text-gray-900">{{ $stats['total_campaigns'] }}</div>
            <a href="#" class="text-xs text-brand-600 hover:underline mt-1 inline-block">View All</a>
        </div>

        {{-- Active Campaigns --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-2 text-xs text-gray-400 mb-2">
                <svg class="w-4 h-4 text-green-500" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                Active Campaigns
            </div>
            <div class="text-3xl font-bold text-gray-900">{{ $stats['active_campaigns'] }}</div>
            <a href="#" class="text-xs text-brand-600 hover:underline mt-1 inline-block">View All</a>
        </div>

        {{-- Current Balance --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-2 text-xs text-gray-400 mb-2">
                <svg class="w-4 h-4 text-emerald-500" viewBox="0 0 24 24" fill="none"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/></svg>
                Current Balance
            </div>
            <div class="text-3xl font-bold text-gray-900">${{ number_format($stats['balance'], 2) }}</div>
            <a href="#" class="text-xs text-brand-600 hover:underline mt-1 inline-block">Manage</a>
        </div>

        {{-- Spendings --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-2 text-xs text-gray-400 mb-2">
                <svg class="w-4 h-4 text-orange-500" viewBox="0 0 24 24" fill="none"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Spendings
            </div>
            <div class="text-3xl font-bold text-gray-900">${{ number_format($stats['spendings'], 2) }}</div>
            <span class="text-[10px] text-gray-400">Last 7 days</span>
        </div>

        {{-- ROI --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-2 text-xs text-gray-400 mb-2">
                <svg class="w-4 h-4 text-purple-500" viewBox="0 0 24 24" fill="none"><path d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                ROI
            </div>
            <div class="text-3xl font-bold text-brand-600">{{ $stats['roi'] }}%</div>
            <span class="text-[10px] text-gray-400">Last 7 days</span>
        </div>
    </div>

    {{-- ═══════════ ALL CAMPAIGNS METRICS ═══════════ --}}
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900">All Campaigns ▾</h2>
            <div class="flex items-center gap-3 text-xs text-gray-400">
                <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2">
                    <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <span>Last 30 days</span>
                </div>
            </div>
        </div>

        {{-- Metric boxes --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="text-[10px] text-gray-400 uppercase tracking-wide">Impressions</div>
                <div class="text-2xl font-bold mt-1">{{ number_format($metrics['impressions']) }}</div>
                <span class="text-[10px] text-gray-400">Last 30 days</span>
                <div class="flex items-center gap-1 mt-1">
                    <span class="text-xs font-medium text-brand-600">↑ {{ $metrics['impressions_change'] }}%</span>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="text-[10px] text-gray-400 uppercase tracking-wide">Clicks</div>
                <div class="text-2xl font-bold mt-1">{{ number_format($metrics['clicks']) }}</div>
                <span class="text-[10px] text-gray-400">Last 30 days</span>
                <div class="flex items-center gap-1 mt-1">
                    <span class="text-xs font-medium {{ $metrics['clicks_change'] >= 0 ? 'text-brand-600' : 'text-red-500' }}">{{ $metrics['clicks_change'] >= 0 ? '↑' : '↓' }} {{ abs($metrics['clicks_change']) }}%</span>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="text-[10px] text-gray-400 uppercase tracking-wide">Conversions</div>
                <div class="text-2xl font-bold mt-1">{{ number_format($metrics['conversions']) }}</div>
                <span class="text-[10px] text-gray-400">Last 30 days</span>
                <div class="flex items-center gap-1 mt-1">
                    <span class="text-xs font-medium {{ $metrics['conversions_change'] >= 0 ? 'text-brand-600' : 'text-red-500' }}">{{ $metrics['conversions_change'] >= 0 ? '↑' : '↓' }} {{ abs($metrics['conversions_change']) }}%</span>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="text-[10px] text-gray-400 uppercase tracking-wide">CTR</div>
                <div class="text-2xl font-bold mt-1">{{ $metrics['ctr'] }}%</div>
                <span class="text-[10px] text-gray-400">Last 30 days</span>
                <div class="flex items-center gap-1 mt-1">
                    <span class="text-xs font-medium {{ $metrics['ctr_change'] >= 0 ? 'text-brand-600' : 'text-red-500' }}">{{ $metrics['ctr_change'] >= 0 ? '↑' : '↓' }} {{ abs($metrics['ctr_change']) }}%</span>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="text-[10px] text-gray-400 uppercase tracking-wide">Activity per Click</div>
                <div class="text-2xl font-bold mt-1">{{ $metrics['activity_per_click'] }}</div>
                <span class="text-[10px] text-gray-400">Last 30 days</span>
                <div class="flex items-center gap-1 mt-1">
                    <span class="text-xs font-medium {{ $metrics['apc_change'] >= 0 ? 'text-brand-600' : 'text-red-500' }}">{{ $metrics['apc_change'] >= 0 ? '↑' : '↓' }} {{ abs($metrics['apc_change']) }}%</span>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-end justify-center">
                <span class="text-[10px] text-gray-400">Previous 30 days</span>
            </div>
        </div>
    </div>

    {{-- ═══════════ PERFORMANCE CHART ═══════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-700">Performance</h3>
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-1.5 text-xs text-gray-500">
                    <span class="w-3 h-3 rounded-sm bg-gray-300"></span> impressions
                </label>
                <label class="flex items-center gap-1.5 text-xs text-gray-500">
                    <span class="w-3 h-3 rounded-sm bg-brand-500"></span> click rate
                </label>
                <button class="text-xs text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" stroke="currentColor" stroke-width="1.5"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/></svg>
                </button>
            </div>
        </div>

        {{-- Chart area (CSS-only bar chart) --}}
        <div class="relative h-64">
            <div class="absolute inset-0 flex items-end gap-1.5 px-2">
                @foreach($chartData as $day)
                    <div class="flex-1 flex flex-col items-center gap-0.5 group relative">
                        {{-- Impressions bar --}}
                        <div class="w-full rounded-t bg-gray-200 transition-all group-hover:bg-gray-300" style="height: {{ $day['impressions_pct'] }}%"></div>
                        {{-- Clicks bar overlaid --}}
                        <div class="w-full rounded-t bg-brand-400 transition-all group-hover:bg-brand-500 -mt-0.5" style="height: {{ $day['clicks_pct'] }}%"></div>
                        <span class="text-[9px] text-gray-400 mt-1">{{ $day['label'] }}</span>
                    </div>
                @endforeach
            </div>
            {{-- Y-axis labels --}}
            <div class="absolute left-0 top-0 bottom-6 flex flex-col justify-between text-[9px] text-gray-400 -ml-1">
                <span>100%</span>
                <span>75%</span>
                <span>50%</span>
                <span>25%</span>
                <span>0%</span>
            </div>
            {{-- Right Y-axis --}}
            <div class="absolute right-0 top-0 bottom-6 flex flex-col justify-between text-[9px] text-gray-400 -mr-1">
                <span>1,500</span>
                <span>1,000</span>
                <span>500</span>
                <span>250</span>
                <span>0</span>
            </div>
        </div>
        <p class="text-[10px] text-gray-400 mt-4">
            <span class="inline-flex items-center gap-1"><svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg> metrics are reported in real time. Use the summary section above to view data in different periods or time.</span>
        </p>
    </div>

    {{-- ═══════════ CAMPAIGNS TABLE ═══════════ --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">All Campaigns</h3>
            <button class="text-xs text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" stroke="currentColor" stroke-width="1.5"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/></svg>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[10px] uppercase tracking-wider text-gray-400 border-b border-gray-100">
                        <th class="px-6 py-3 font-medium">campaign name</th>
                        <th class="px-4 py-3 font-medium">status</th>
                        <th class="px-4 py-3 font-medium">clicks</th>
                        <th class="px-4 py-3 font-medium">ctr</th>
                        <th class="px-4 py-3 font-medium">impressions</th>
                        <th class="px-4 py-3 font-medium">conversions</th>
                        <th class="px-4 py-3 font-medium">conversion rate</th>
                        <th class="px-4 py-3 font-medium">start-end date</th>
                        <th class="px-4 py-3 font-medium">spent</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($campaigns as $campaign)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-3.5 font-medium text-gray-900 max-w-[200px] truncate">{{ $campaign['name'] }}</td>
                            <td class="px-4 py-3.5">
                                @php
                                    $statusColors = [
                                        'active' => 'bg-green-100 text-green-700',
                                        'running' => 'bg-green-100 text-green-700',
                                        'paused' => 'bg-yellow-100 text-yellow-700',
                                        'draft' => 'bg-gray-100 text-gray-600',
                                        'completed' => 'bg-blue-100 text-blue-700',
                                        'rejected' => 'bg-red-100 text-red-700',
                                        'pending_review' => 'bg-orange-100 text-orange-700',
                                        'disabled' => 'bg-gray-100 text-gray-500',
                                    ];
                                    $color = $statusColors[$campaign['status']] ?? 'bg-gray-100 text-gray-600';
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-semibold {{ $color }}">{{ $campaign['status'] }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-gray-600">{{ $campaign['clicks'] }}</td>
                            <td class="px-4 py-3.5 text-gray-600">{{ $campaign['ctr'] }}%</td>
                            <td class="px-4 py-3.5 text-gray-600">{{ number_format($campaign['impressions']) }}</td>
                            <td class="px-4 py-3.5 text-gray-600">{{ $campaign['conversions'] }}</td>
                            <td class="px-4 py-3.5 text-gray-600">{{ $campaign['conversion_rate'] }}%</td>
                            <td class="px-4 py-3.5 text-gray-500 text-xs">
                                {{ $campaign['start_date'] }}<br>
                                <span class="text-gray-400">{{ $campaign['end_date'] }}</span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="text-xs font-medium text-gray-700">{{ $campaign['spent'] }}</div>
                                <div class="text-[10px] text-gray-400">{{ $campaign['budget_type'] }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-1">
                                    <button class="p-1 rounded hover:bg-gray-100" title="View">
                                        <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke="currentColor" stroke-width="1.5"/></svg>
                                    </button>
                                    <button class="p-1 rounded hover:bg-gray-100" title="Edit">
                                        <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    </button>
                                    <button class="p-1 rounded hover:bg-gray-100" title="Stats">
                                        <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                <p class="text-sm font-medium">No campaigns yet</p>
                                <p class="text-xs mt-1">Create your first campaign to get started.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="flex items-center justify-between px-6 py-3 border-t border-gray-100 text-xs text-gray-400">
            <span>showing {{ count($campaigns) }} of {{ count($campaigns) }} campaigns</span>
            <div class="flex items-center gap-1">
                <button class="px-2 py-1 rounded border border-gray-200 hover:bg-gray-50">&laquo;</button>
                <button class="px-2 py-1 rounded border border-brand-500 bg-brand-50 text-brand-700 font-medium">1</button>
                <button class="px-2 py-1 rounded border border-gray-200 hover:bg-gray-50">&raquo;</button>
            </div>
        </div>
    </div>
@endsection
