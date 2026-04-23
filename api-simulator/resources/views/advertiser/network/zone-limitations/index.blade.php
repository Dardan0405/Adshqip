@extends('layouts.advertiser')

@section('title', 'Zone Limitation')

@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Zone Limitation</h1>
            <p class="text-sm text-gray-500 mt-1">Create whitelist and blacklist zone lists for your account.</p>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-3 mb-6">
        @foreach([
            ['Total Lists', number_format($totalLimitations), 'text-gray-900'],
            ['Whitelist', number_format($whitelistCount), 'text-emerald-700'],
            ['Blacklist', number_format($blacklistCount), 'text-rose-700'],
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
                <form method="GET" action="{{ route('advertiser.network.zone-limitations') }}" class="flex flex-wrap items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name..." class="px-3 py-2 rounded-lg border border-gray-200 text-sm">
                    <select name="type" class="px-3 py-2 rounded-lg border border-gray-200 text-sm">
                        <option value="">All Types</option>
                        <option value="adblock_whitelist" @selected(request('type') === 'adblock_whitelist')>Whitelist</option>
                        <option value="adblock_blacklist" @selected(request('type') === 'adblock_blacklist')>Blacklist</option>
                    </select>
                    <button class="px-4 py-2 rounded-lg bg-gray-900 text-sm font-medium text-white">Filter</button>
                    <a href="{{ route('advertiser.network.zone-limitations') }}" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600">Reset</a>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/70 border-b border-gray-100">
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Name</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Type</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Zone ID's</th>
                            <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($limitations as $limitation)
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs text-gray-500">#{{ $limitation->id }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $limitation->name }}</td>
                                <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $limitation->type === 'adblock_whitelist' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">{{ $limitation->type === 'adblock_whitelist' ? 'Whitelist' : 'Blacklist' }}</span></td>
                                <td class="px-4 py-3 text-gray-700">{{ implode(', ', $limitation->zone_ids ?? []) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <details class="inline-block text-left">
                                        <summary class="cursor-pointer rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600">Edit</summary>
                                        <form method="POST" action="{{ route('advertiser.network.zone-limitations.update', $limitation) }}" class="absolute z-10 mt-2 w-72 rounded-xl border border-gray-200 bg-white p-3 shadow-lg">
                                            @csrf
                                            @method('PUT')
                                            <input name="name" value="{{ $limitation->name }}" class="mb-2 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" required>
                                            <select name="type" class="mb-2 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" required>
                                                <option value="adblock_whitelist" @selected($limitation->type === 'adblock_whitelist')>Whitelist</option>
                                                <option value="adblock_blacklist" @selected($limitation->type === 'adblock_blacklist')>Blacklist</option>
                                            </select>
                                            <input name="zone_ids[]" value="{{ implode(',', $limitation->zone_ids ?? []) }}" class="mb-2 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Zone IDs comma separated" required>
                                            <button class="w-full rounded-lg bg-gray-900 px-3 py-2 text-xs font-semibold text-white">Save</button>
                                        </form>
                                    </details>
                                    <form method="POST" action="{{ route('advertiser.network.zone-limitations.destroy', $limitation) }}" onsubmit="return confirm('Delete this zone limitation?')" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-12 text-center text-sm text-gray-500">No zone limitations found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($limitations->hasPages())
                <div class="border-t border-gray-100 px-4 py-3">{{ $limitations->links() }}</div>
            @endif
        </div>

        <form method="POST" action="{{ route('advertiser.network.zone-limitations.store') }}" class="bg-white rounded-xl border border-gray-200 p-4 h-max">
            @csrf
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Add Zone Limitation List</h2>
            <div class="space-y-3">
                <input name="name" placeholder="List Name" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" required>
                <select name="type" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" required>
                    <option value="adblock_whitelist">Whitelist</option>
                    <option value="adblock_blacklist">Blacklist</option>
                </select>
                <input name="zone_ids[]" placeholder="Zone ID" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" required>
                <button class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Add List</button>
            </div>
        </form>
    </div>
@endsection
