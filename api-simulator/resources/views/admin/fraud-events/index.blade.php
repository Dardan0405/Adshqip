@extends('layouts.admin')

@section('title', 'Fraud Events')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Fraud Events</h1>
            <p class="mt-1 text-sm text-gray-500">Review raw anti-fraud events recorded by rules and click-cap checks.</p>
        </div>
        <a href="{{ route('admin.fraud-events.export', request()->query()) }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Export CSV</a>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"><div class="text-xs uppercase tracking-wide text-gray-500">Events</div><div class="mt-2 text-2xl font-bold">{{ number_format($stats['total']) }}</div></div>
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"><div class="text-xs uppercase tracking-wide text-gray-500">Blocked</div><div class="mt-2 text-2xl font-bold text-red-600">{{ number_format($stats['blocked']) }}</div></div>
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"><div class="text-xs uppercase tracking-wide text-gray-500">Flagged</div><div class="mt-2 text-2xl font-bold text-orange-600">{{ number_format($stats['flagged']) }}</div></div>
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"><div class="text-xs uppercase tracking-wide text-gray-500">Critical</div><div class="mt-2 text-2xl font-bold text-purple-600">{{ number_format($stats['critical']) }}</div></div>
    </div>

    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.fraud-events') }}" class="grid gap-3 md:grid-cols-6">
            <div class="md:col-span-2">
                <label class="mb-1 block text-xs font-medium text-gray-600">Search</label>
                <input name="search" value="{{ request('search') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="IP, viewer, fingerprint, user-agent">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Event</label>
                <select name="event_type" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All</option>
                    @foreach($eventTypes as $value => $label)<option value="{{ $value }}" @selected(request('event_type') === $value)>{{ $label }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Reason</label>
                <select name="fraud_reason" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All</option>
                    @foreach($reasons as $value => $label)<option value="{{ $value }}" @selected(request('fraud_reason') === $value)>{{ $label }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Severity</label>
                <select name="severity" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All</option>
                    @foreach($severities as $value => $label)<option value="{{ $value }}" @selected(request('severity') === $value)>{{ $label }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Result</label>
                <select name="blocked" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All</option>
                    <option value="1" @selected(request('blocked') === '1')>Blocked</option>
                    <option value="0" @selected(request('blocked') === '0')>Flagged/Allowed</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Zone ID</label>
                <input type="number" name="zone_id" value="{{ request('zone_id') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Ad ID</label>
                <input type="number" name="ad_id" value="{{ request('ad_id') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <div class="flex items-end gap-2 md:col-span-2">
                <button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white">Filter</button>
                <a href="{{ route('admin.fraud-events') }}" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700">Reset</a>
            </div>
        </form>
    </div>

    @if($publisherRecords->isNotEmpty())
        <div class="rounded-xl border border-orange-100 bg-orange-50 p-4">
            <div class="mb-3 text-sm font-semibold text-orange-900">Open Publisher Fraud Records</div>
            <div class="grid gap-3 md:grid-cols-2">
                @foreach($publisherRecords as $record)
                    <div class="rounded-lg bg-white p-3 text-sm shadow-sm">
                        <div class="font-medium text-gray-900">{{ $record->publisher?->email ?? 'Publisher #' . $record->publisher_id }}</div>
                        <div class="mt-1 text-xs text-gray-500">{{ $record->reason }} - {{ number_format($record->flagged_clicks) }} clicks</div>
                        <form method="POST" action="{{ route('admin.fraud-events.records.resolve', $record) }}" class="mt-2">
                            @csrf @method('PATCH')
                            <button class="text-xs font-medium text-brand-600">Resolve</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b bg-gray-50">
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Time</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Event</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Ad / Zone</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Visitor</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">User Agent</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Action</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($events as $event)
                        @php
                            $severityClass = ['low'=>'bg-gray-100 text-gray-700','medium'=>'bg-blue-100 text-blue-700','high'=>'bg-orange-100 text-orange-700','critical'=>'bg-red-100 text-red-700'][$event->severity] ?? 'bg-gray-100 text-gray-700';
                        @endphp
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $event->created_at?->format('Y-m-d H:i:s') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-700">{{ $eventTypes[$event->event_type] ?? $event->event_type }}</span>
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $severityClass }}">{{ $severities[$event->severity] ?? $event->severity }}</span>
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $event->blocked ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $event->blocked ? 'Blocked' : 'Flagged' }}</span>
                                </div>
                                <div class="mt-1 text-xs text-gray-500">{{ $reasons[$event->fraud_reason] ?? $event->fraud_reason }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">Ad #{{ $event->ad_id ?: '-' }} {{ $event->ad?->name }}</div>
                                <div class="text-xs text-gray-500">Zone #{{ $event->zone_id ?: '-' }} {{ $event->zone?->name }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-xs text-gray-900">{{ $event->ip_address }}</div>
                                <div class="mt-1 max-w-[180px] truncate text-xs text-gray-500" title="{{ $event->viewer_id }}">{{ $event->viewer_id }}</div>
                            </td>
                            <td class="px-4 py-3"><div class="max-w-sm truncate text-xs text-gray-500" title="{{ $event->user_agent }}">{{ $event->user_agent }}</div></td>
                            <td class="px-4 py-3 text-center">
                                <form method="POST" action="{{ route('admin.fraud-events.destroy', $event) }}">
                                    @csrf @method('DELETE')
                                    <button onclick="return confirm('Delete this fraud event?')" class="rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-gray-500">No fraud events found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($events->hasPages())<div class="border-t px-4 py-3">{{ $events->links() }}</div>@endif
    </div>

    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <form method="POST" action="{{ route('admin.fraud-events.clear') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            @csrf @method('DELETE')
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Delete events older than days</label>
                <input type="number" name="older_than_days" min="1" max="3650" value="90" class="w-48 rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <button onclick="return confirm('Delete old fraud events?')" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white">Clear Old Events</button>
        </form>
    </div>
</div>
@endsection
