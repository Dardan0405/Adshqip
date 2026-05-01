@extends('layouts.advertiser')

@section('title', 'Dashboard')

@section('content')
@php
    $money = function (float $amount) use ($currencyCode) {
        $symbols = ['USD' => '$', 'EUR' => 'EUR ', 'ALL' => 'L ', 'GBP' => 'GBP '];
        return ($symbols[$currencyCode] ?? $currencyCode . ' ') . number_format($amount, 2);
    };
    $changeClass = fn ($value) => $value >= 0 ? 'text-brand-600' : 'text-red-500';
    $changeArrow = fn ($value) => $value >= 0 ? '+' : '-';
@endphp

<div class="space-y-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <div class="text-xs text-gray-400"><span class="text-brand-600">Home</span> / dashboard</div>
            <h1 class="mt-1 text-2xl font-bold text-gray-900">Overview</h1>
            <p class="text-sm text-gray-500">Welcome back, {{ Auth::user()->email }}</p>
        </div>

        @if($announcement)
            <div class="max-w-xl rounded-lg border border-brand-200 bg-brand-50 px-3 py-2 text-xs text-brand-700">
                <span class="font-semibold">{{ $announcement->title }}</span>
                <span class="ml-1">{{ $announcement->summary }}</span>
                @if($announcement->cta_url && $announcement->cta_label)
                    <a href="{{ $announcement->cta_url }}" class="ml-2 font-semibold underline">{{ $announcement->cta_label }}</a>
                @endif
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <div class="text-xs font-medium text-gray-400">Total Campaigns</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($stats['total_campaigns']) }}</div>
            <a href="{{ route('advertiser.campaigns') }}" class="mt-1 inline-block text-xs text-brand-600 hover:underline">View all</a>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <div class="text-xs font-medium text-gray-400">Active Campaigns</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($stats['active_campaigns']) }}</div>
            <a href="{{ route('advertiser.campaigns', ['status' => 'active']) }}" class="mt-1 inline-block text-xs text-brand-600 hover:underline">View active</a>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <div class="text-xs font-medium text-gray-400">Current Balance</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $money((float) $stats['balance']) }}</div>
            <a href="{{ route('advertiser.payments.add-funds') }}" class="mt-1 inline-block text-xs text-brand-600 hover:underline">Manage</a>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <div class="text-xs font-medium text-gray-400">Spendings</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $money((float) $stats['spendings']) }}</div>
            <span class="text-[10px] text-gray-400">Last 7 days</span>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <div class="text-xs font-medium text-gray-400">ROI</div>
            <div class="mt-2 text-3xl font-bold text-brand-600">{{ number_format((float) $stats['roi'], 1) }}%</div>
            <span class="text-[10px] text-gray-400">Based on conversion revenue, last 7 days</span>
        </div>
    </div>

    <div>
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900">All Campaigns</h2>
            <div class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs text-gray-400">Last 30 days</div>
        </div>

        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
            @foreach([
                ['label' => 'Impressions', 'value' => number_format($metrics['impressions']), 'change' => $metrics['impressions_change']],
                ['label' => 'Clicks', 'value' => number_format($metrics['clicks']), 'change' => $metrics['clicks_change']],
                ['label' => 'Conversions', 'value' => number_format($metrics['conversions']), 'change' => $metrics['conversions_change']],
                ['label' => 'CTR', 'value' => number_format((float) $metrics['ctr'], 2) . '%', 'change' => $metrics['ctr_change']],
                ['label' => 'Activity per Click', 'value' => number_format((float) $metrics['activity_per_click'], 3), 'change' => $metrics['apc_change']],
            ] as $metric)
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <div class="text-[10px] uppercase tracking-wide text-gray-400">{{ $metric['label'] }}</div>
                    <div class="mt-1 text-2xl font-bold">{{ $metric['value'] }}</div>
                    <span class="text-[10px] text-gray-400">Last 30 days</span>
                    <div class="mt-1 text-xs font-medium {{ $changeClass($metric['change']) }}">
                        {{ $changeArrow($metric['change']) }}{{ number_format(abs((float) $metric['change']), 1) }}%
                    </div>
                </div>
            @endforeach
            <div class="flex items-end justify-center rounded-xl border border-gray-200 bg-white p-4">
                <span class="text-[10px] text-gray-400">Compared with previous 30 days</span>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700">Performance</h3>
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1.5 text-xs text-gray-500"><span class="h-3 w-3 rounded-sm bg-gray-300"></span> impressions</span>
                <span class="flex items-center gap-1.5 text-xs text-gray-500"><span class="h-3 w-3 rounded-sm bg-brand-500"></span> clicks</span>
            </div>
        </div>

        <div class="relative h-64 pl-8 pr-8">
            <div class="absolute bottom-6 left-0 top-0 flex flex-col justify-between text-[9px] text-gray-400">
                @foreach($chartAxis['labels'] as $label)
                    <span>{{ $label }}</span>
                @endforeach
            </div>
            <div class="absolute bottom-6 left-8 right-8 top-0 flex items-end gap-1.5">
                @foreach($chartData as $day)
                    <div class="group relative flex flex-1 flex-col items-center gap-0.5">
                        <div class="w-full rounded-t bg-gray-200 transition-all group-hover:bg-gray-300" style="height: {{ $day['impressions_pct'] }}%"></div>
                        <div class="-mt-0.5 w-full rounded-t bg-brand-400 transition-all group-hover:bg-brand-500" style="height: {{ $day['clicks_pct'] }}%"></div>
                        <span class="mt-1 text-[9px] text-gray-400">{{ $day['label'] }}</span>
                        <div class="pointer-events-none absolute bottom-full mb-2 hidden min-w-[120px] rounded-lg border border-gray-200 bg-white px-2 py-1 text-[10px] text-gray-600 shadow-sm group-hover:block">
                            <div class="font-semibold text-gray-900">{{ $day['label'] }}</div>
                            <div>{{ number_format($day['impressions']) }} impressions</div>
                            <div>{{ number_format($day['clicks']) }} clicks</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <h3 class="text-sm font-semibold text-gray-700">Campaigns</h3>
            <a href="{{ route('advertiser.campaigns.create') }}" class="rounded-lg bg-brand-600 px-3 py-2 text-xs font-semibold text-white hover:bg-brand-700">Create Campaign</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-left text-[10px] uppercase tracking-wider text-gray-400">
                        <th class="px-6 py-3 font-medium">Campaign</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Clicks</th>
                        <th class="px-4 py-3 font-medium">CTR</th>
                        <th class="px-4 py-3 font-medium">Impressions</th>
                        <th class="px-4 py-3 font-medium">Conversions</th>
                        <th class="px-4 py-3 font-medium">Conversion Rate</th>
                        <th class="px-4 py-3 font-medium">Dates</th>
                        <th class="px-4 py-3 font-medium">Spent</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($campaigns as $campaign)
                        @php
                            $statusColors = [
                                'active' => 'bg-green-100 text-green-700',
                                'running' => 'bg-green-100 text-green-700',
                                'approved' => 'bg-green-100 text-green-700',
                                'paused' => 'bg-yellow-100 text-yellow-700',
                                'draft' => 'bg-gray-100 text-gray-600',
                                'completed' => 'bg-blue-100 text-blue-700',
                                'rejected' => 'bg-red-100 text-red-700',
                                'pending_review' => 'bg-orange-100 text-orange-700',
                                'disabled' => 'bg-gray-100 text-gray-500',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50/50">
                            <td class="max-w-[220px] px-6 py-3.5">
                                <div class="truncate font-medium text-gray-900">{{ $campaign['name'] }}</div>
                                <div class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">{{ $campaign['type'] }} #{{ $campaign['id'] }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex rounded px-2 py-0.5 text-[10px] font-semibold {{ $statusColors[$campaign['status']] ?? 'bg-gray-100 text-gray-600' }}">{{ $campaign['status'] }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-gray-600">{{ number_format($campaign['clicks']) }}</td>
                            <td class="px-4 py-3.5 text-gray-600">{{ number_format((float) $campaign['ctr'], 2) }}%</td>
                            <td class="px-4 py-3.5 text-gray-600">{{ number_format($campaign['impressions']) }}</td>
                            <td class="px-4 py-3.5 text-gray-600">{{ number_format($campaign['conversions']) }}</td>
                            <td class="px-4 py-3.5 text-gray-600">{{ number_format((float) $campaign['conversion_rate'], 2) }}%</td>
                            <td class="px-4 py-3.5 text-xs text-gray-500">{{ $campaign['start_date'] }}<br><span class="text-gray-400">{{ $campaign['end_date'] }}</span></td>
                            <td class="px-4 py-3.5">
                                <div class="text-xs font-medium text-gray-700">{{ $campaign['spent'] }}</div>
                                <div class="text-[10px] text-gray-400">{{ $campaign['budget_type'] }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-1">
                                    <a href="{{ $campaign['show_url'] }}" class="rounded p-1 hover:bg-gray-100" title="View">
                                        <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke="currentColor" stroke-width="1.5"/></svg>
                                    </a>
                                    <a href="{{ $campaign['edit_url'] }}" class="rounded p-1 hover:bg-gray-100" title="Edit">
                                        <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    </a>
                                    <a href="{{ $campaign['stats_url'] }}" class="rounded p-1 hover:bg-gray-100" title="Stats">
                                        <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-gray-400">
                                <p class="text-sm font-medium">No campaigns yet</p>
                                <p class="mt-1 text-xs">Create your first campaign to get started.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between border-t border-gray-100 px-6 py-3 text-xs text-gray-400">
            <span>Showing {{ count($campaigns) }} campaigns</span>
            <a href="{{ route('advertiser.campaigns') }}" class="font-medium text-brand-600 hover:underline">Open campaigns</a>
        </div>
    </div>
</div>
@endsection
