@extends('layouts.advertiser')

@section('title', 'Event Log')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Event Log</h1>
            <p class="mt-1 text-sm text-gray-500">Monitor ad delivery, URL tracking, conversions, postbacks, and pixel fire activity.</p>
        </div>
        <a href="{{ route('advertiser.tracking.event-log.export', request()->query()) }}" class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
            Export CSV
        </a>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach([
            ['Events', number_format($stats['total']), 'text-gray-900'],
            ['Conversions', number_format($stats['conversions']), 'text-brand-700'],
            ['Clicks', number_format($stats['clicks']), 'text-blue-700'],
            ['Pixel Fires', number_format($stats['pixel_fires']), 'text-emerald-700'],
        ] as $card)
            <div class="rounded-lg border border-gray-200 bg-white p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card[0] }}</div>
                <div class="mt-2 text-2xl font-bold {{ $card[2] }}">{{ $card[1] }}</div>
            </div>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <div class="border-b border-gray-100 p-4">
            <form method="GET" action="{{ route('advertiser.tracking.event-log') }}" class="grid gap-2 md:grid-cols-2 xl:grid-cols-7">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search IDs, URLs, IP, campaign, tracker" class="rounded-lg border border-gray-200 px-3 py-2 text-sm xl:col-span-2">
                <select name="source" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All Sources</option>
                    @foreach($sources as $value => $label)
                        <option value="{{ $value }}" @selected(request('source') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="event_type" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All Events</option>
                    @foreach($eventTypes as $value => $label)
                        <option value="{{ $value }}" @selected(request('event_type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="status" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All Statuses</option>
                    @foreach(['served' => 'Served', 'tracked' => 'Tracked', 'active' => 'Active', 'blocked' => 'Blocked', 'error' => 'Error'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="date" name="date_from" value="{{ request('date_from', $defaults['date_from']) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <input type="date" name="date_to" value="{{ request('date_to', $defaults['date_to']) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <div class="flex gap-2 md:col-span-2 xl:col-span-7">
                    <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">Filter</button>
                    <a href="{{ route('advertiser.tracking.event-log') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600">Reset</a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/70">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Time</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Event</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Campaign / Tracker</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Context</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">URL</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Meta</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($events as $event)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="font-medium text-gray-900">{{ optional($event['created_at'])->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-400">{{ optional($event['created_at'])->format('H:i:s') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">{{ $event['event_label'] }}</div>
                                <div class="mt-1 flex flex-wrap gap-1">
                                    <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-600">{{ $event['source_label'] }}</span>
                                    <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $event['status_class'] }}">{{ Str::headline($event['status']) }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $event['campaign_name'] }}</div>
                                @if($event['tracker_name'] !== '-')
                                    <div class="text-xs text-gray-500">{{ $event['tracker_name'] }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-xs text-gray-500">Device: {{ $event['device_type'] }}</div>
                                <div class="text-xs text-gray-500">Country: {{ $event['country_code'] }}</div>
                                <div class="font-mono text-xs text-gray-400">IP: {{ $event['ip_address'] }}</div>
                            </td>
                            <td class="max-w-xs px-4 py-3">
                                <div class="truncate font-mono text-xs text-gray-600" title="{{ $event['request_url'] }}">{{ $event['request_url'] ?: '-' }}</div>
                                @if($event['destination_url'])
                                    <div class="mt-1 truncate font-mono text-xs text-gray-400" title="{{ $event['destination_url'] }}">{{ $event['destination_url'] }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($event['meta'])
                                    <div class="space-y-1">
                                        @foreach($event['meta'] as $key => $value)
                                            <div class="text-xs text-gray-500">
                                                <span class="font-semibold text-gray-700">{{ Str::headline($key) }}:</span>
                                                <span class="font-mono">{{ is_float($value) ? number_format($value, 4) : $value }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500">No events found for the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($events->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $events->links() }}</div>
        @endif
    </div>
@endsection
