@extends('layouts.advertiser')

@section('title', 'Traffic Source')

@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Traffic Source</h1>
            <p class="text-sm text-gray-500 mt-1">Manage campaign traffic source bid rates.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        @foreach([
            ['Total Sources', number_format($totalSources), 'text-gray-900'],
            ['Active', number_format($activeSources), 'text-emerald-700'],
            ['Paused', number_format($pausedSources), 'text-amber-700'],
            ['Average Bid', '$' . number_format($avgBidRate, 2), 'text-blue-700'],
        ] as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card[0] }}</div>
                <div class="mt-2 text-2xl font-bold {{ $card[2] }}">{{ $card[1] }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr,360px]">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <form method="GET" action="{{ route('advertiser.network.traffic-sources') }}" class="flex flex-wrap items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search campaign..." class="px-3 py-2 rounded-lg border border-gray-200 text-sm">
                    <select name="status" class="px-3 py-2 rounded-lg border border-gray-200 text-sm">
                        <option value="">All Statuses</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="paused" @selected(request('status') === 'paused')>Paused</option>
                    </select>
                    <button class="px-4 py-2 rounded-lg bg-gray-900 text-sm font-medium text-white">Filter</button>
                    <a href="{{ route('advertiser.network.traffic-sources') }}" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600">Reset</a>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/70 border-b border-gray-100">
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Source ID</th>
                            <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Bid Rate</th>
                            <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Campaign</th>
                            <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($trafficSources as $ts)
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs text-gray-500">#{{ $ts->id }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-emerald-700">${{ number_format((float) $ts->bid_rate, 2) }}</td>
                                <td class="px-4 py-3 text-center"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $ts->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ ucfirst($ts->status) }}</span></td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $ts->campaign_type === 'direct' ? ($ts->directCampaign->name ?? 'N/A') . ' (Direct)' : ($ts->campaign->name ?? 'N/A') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <form method="POST" action="{{ route('advertiser.network.traffic-sources.destroy', $ts) }}" onsubmit="return confirm('Delete this traffic source?')" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-12 text-center text-sm text-gray-500">No traffic sources found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($trafficSources->hasPages())
                <div class="border-t border-gray-100 px-4 py-3">{{ $trafficSources->links() }}</div>
            @endif
        </div>

        <form method="POST" action="{{ route('advertiser.network.traffic-sources.store') }}" class="bg-white rounded-xl border border-gray-200 p-4 h-max">
            @csrf
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Add Traffic Source</h2>
            <div class="space-y-3">
                <select name="campaign_id" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" required>
                    <option value="">Select Campaign</option>
                    @foreach($campaigns as $campaign)
                        <option value="{{ $campaign['type'] }}:{{ $campaign['id'] }}">{{ $campaign['label'] }}</option>
                    @endforeach
                </select>
                <select name="traffic_source_id" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" required>
                    <option value="">Select Traffic Source</option>
                    @foreach($sourceLookups as $source)
                        <option value="{{ $source->id }}">{{ $source->name }}</option>
                    @endforeach
                </select>
                <input type="number" step="0.01" min="0" name="bid_rate" placeholder="Bid Rate" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" required>
                <select name="status" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" required>
                    <option value="active">Active</option>
                    <option value="paused">Paused</option>
                </select>
                <button class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Add Source</button>
            </div>
        </form>
    </div>
@endsection
