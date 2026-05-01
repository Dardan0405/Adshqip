@extends('layouts.admin')

@section('title', 'Ad Serving Logs')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Ad Serving Logs</h1>
            <p class="mt-1 text-sm text-gray-500">Inspect live ad serving, click, view, conversion, adblock, and zone loader events.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.ad-serving-logs.export', request()->query()) }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Total Logs</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Served</div>
            <div class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($stats['served']) }}</div>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Blocked</div>
            <div class="mt-2 text-2xl font-bold text-red-600">{{ number_format($stats['blocked']) }}</div>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Revenue</div>
            <div class="mt-2 text-2xl font-bold text-blue-600">{{ number_format($stats['revenue'], 4) }}</div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.ad-serving-logs') }}" class="grid gap-3 md:grid-cols-6">
            <div class="md:col-span-2">
                <label class="mb-1 block text-xs font-medium text-gray-600">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Request, viewer, click, IP, URL..." class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Delivery</label>
                <select name="delivery_type" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All</option>
                    @foreach($deliveryTypes as $value => $label)
                        <option value="{{ $value }}" @selected(request('delivery_type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Event</label>
                <select name="event_type" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All</option>
                    @foreach($eventTypes as $value => $label)
                        <option value="{{ $value }}" @selected(request('event_type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Status</label>
                <select name="status" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Zone ID</label>
                <input type="number" name="zone_id" value="{{ request('zone_id') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Campaign ID</label>
                <input type="number" name="campaign_id" value="{{ request('campaign_id') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Direct Campaign ID</label>
                <input type="number" name="direct_campaign_id" value="{{ request('direct_campaign_id') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div class="flex items-end gap-2 md:col-span-2">
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">Filter</button>
                <a href="{{ route('admin.ad-serving-logs') }}" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200">Reset</a>
            </div>
        </form>
    </div>

    <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Time</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Event</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Campaign</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Zone/Site</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Visitor</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Revenue</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                        @php
                            $statusClass = match($log->status) {
                                'served', 'tracked' => 'bg-emerald-100 text-emerald-700',
                                'blocked' => 'bg-red-100 text-red-700',
                                'error' => 'bg-orange-100 text-orange-700',
                                default => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <tr class="align-top transition hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $log->created_at?->format('Y-m-d H:i:s') }}</div>
                                <code class="mt-1 block max-w-[180px] truncate rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-500">{{ $log->request_id }}</code>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-700">{{ $deliveryTypes[$log->delivery_type] ?? $log->delivery_type }}</span>
                                    <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">{{ $eventTypes[$log->event_type] ?? $log->event_type }}</span>
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusClass }}">{{ $statuses[$log->status] ?? $log->status }}</span>
                                </div>
                                <div class="mt-2 text-xs text-gray-500">{{ strtoupper($log->pricing_model ?? 'n/a') }} {{ $log->bid_amount ? 'Bid ' . $log->bid_amount : '' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @if($log->delivery_type === 'direct')
                                    <div class="font-medium text-gray-900">#{{ $log->direct_campaign_id }} {{ $log->directCampaign?->name }}</div>
                                    <div class="text-xs text-gray-500">Creative #{{ $log->direct_creative_id }} {{ $log->directCreative?->variant_label ?? $log->directCreative?->headline }}</div>
                                @else
                                    <div class="font-medium text-gray-900">#{{ $log->campaign_id }} {{ $log->campaign?->name }}</div>
                                    <div class="text-xs text-gray-500">Ad #{{ $log->ad_id }} {{ $log->ad?->name }}</div>
                                @endif
                                @if($log->advertiser)
                                    <div class="mt-1 truncate text-xs text-gray-500">{{ $log->advertiser->email }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">Zone #{{ $log->zone_id ?: '-' }} {{ $log->zone?->name }}</div>
                                <div class="text-xs text-gray-500">Site #{{ $log->site_id ?: '-' }} {{ $log->site?->name }}</div>
                                @if($log->publisher)
                                    <div class="mt-1 truncate text-xs text-gray-500">{{ $log->publisher->email }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-xs text-gray-700">{{ $log->ip_address ?: '-' }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ $log->country_code ?: '??' }} / {{ $log->device_type ?: 'unknown' }}</div>
                                <div class="mt-1 max-w-[220px] truncate text-xs text-gray-400" title="{{ $log->referer }}">{{ $log->referer ?: 'No referrer' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ number_format((float) $log->revenue, 4) }}</div>
                                <div class="text-xs text-gray-500">Pub {{ number_format((float) $log->publisher_earnings, 4) }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-center gap-1">
                                    <details class="relative">
                                        <summary class="cursor-pointer rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700">View</summary>
                                        <div class="absolute right-0 z-20 mt-2 w-96 rounded-xl border border-gray-100 bg-white p-4 text-xs shadow-xl">
                                            <div class="mb-2 font-semibold text-gray-900">Request URL</div>
                                            <div class="break-all text-gray-600">{{ $log->request_url }}</div>
                                            @if($log->destination_url)
                                                <div class="mb-2 mt-4 font-semibold text-gray-900">Destination URL</div>
                                                <div class="break-all text-gray-600">{{ $log->destination_url }}</div>
                                            @endif
                                            <div class="mb-2 mt-4 font-semibold text-gray-900">User Agent</div>
                                            <div class="break-all text-gray-600">{{ $log->user_agent }}</div>
                                        </div>
                                    </details>
                                    <form method="POST" action="{{ route('admin.ad-serving-logs.destroy', $log) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Delete this ad serving log?')" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-500">No ad serving logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $logs->links() }}</div>
        @endif
    </div>

    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <form method="POST" action="{{ route('admin.ad-serving-logs.clear') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            @csrf
            @method('DELETE')
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Delete logs older than days</label>
                <input type="number" name="older_than_days" min="1" max="3650" value="90" class="w-48 rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
            </div>
            <button type="submit" onclick="return confirm('Delete old ad serving logs?')" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700">Clear Old Logs</button>
        </form>
    </div>
</div>
@endsection
