@extends('layouts.advertiser')

@section('title', 'Country Wise Bidding')

@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Country Wise Bidding</h1>
            <p class="text-sm text-gray-500 mt-1">Manage country bid rules for your campaigns.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        @foreach([
            ['Total Rules', number_format($totalBiddings), 'text-gray-900'],
            ['Average Bid', '$' . number_format($avgBidValue, 2), 'text-emerald-700'],
            ['CPC Rules', number_format($cpcCount), 'text-blue-700'],
            ['CPM Rules', number_format($cpmCount), 'text-violet-700'],
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
                <form method="GET" action="{{ route('advertiser.network.country-wise-bidding') }}" class="flex flex-wrap items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search campaign or country..." class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <select name="type" class="px-3 py-2 rounded-lg border border-gray-200 text-sm">
                        <option value="">All Types</option>
                        @foreach(['CPC', 'CPM', 'CPA', 'CPV'] as $type)
                            <option value="{{ $type }}" @selected(request('type') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                    <button class="px-4 py-2 rounded-lg bg-gray-900 text-sm font-medium text-white">Filter</button>
                    <a href="{{ route('advertiser.network.country-wise-bidding') }}" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600">Reset</a>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/70 border-b border-gray-100">
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Campaign</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Type</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Country</th>
                            <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Bid Value</th>
                            <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($biddings as $bidding)
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs text-gray-500">#{{ $bidding->id }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $bidding->selected_campaign?->name ?? 'All Campaigns' }}</td>
                                <td class="px-4 py-3"><span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">{{ $bidding->type }}</span></td>
                                <td class="px-4 py-3 text-gray-700">{{ $bidding->country_code }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-emerald-700">${{ number_format((float) $bidding->bid_value, 2) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <form method="POST" action="{{ route('advertiser.network.country-wise-bidding.destroy', $bidding) }}" onsubmit="return confirm('Delete this bidding rule?')" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500">No bidding rules found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($biddings->hasPages())
                <div class="border-t border-gray-100 px-4 py-3">{{ $biddings->links() }}</div>
            @endif
        </div>

        <form method="POST" action="{{ route('advertiser.network.country-wise-bidding.store') }}" class="bg-white rounded-xl border border-gray-200 p-4 h-max">
            @csrf
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Add Bidding Rule</h2>
            <div class="space-y-3">
                <select name="campaign_id" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm">
                    <option value="">All Campaigns</option>
                    @foreach($campaigns as $campaign)
                        <option value="{{ $campaign['type'] }}:{{ $campaign['id'] }}">{{ $campaign['label'] }}</option>
                    @endforeach
                </select>
                <select name="type" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" required>
                    @foreach(['CPC', 'CPM', 'CPA', 'CPV'] as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
                <input name="country_code" maxlength="2" placeholder="Country Code, e.g. US" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm uppercase" required>
                <input type="number" step="0.01" min="0" name="bid_value" placeholder="Bid Value" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" required>
                <button class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Add Rule</button>
            </div>
        </form>
    </div>
@endsection
