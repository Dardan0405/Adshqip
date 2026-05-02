@extends('layouts.publisher')

@section('title', 'Direct Links')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Direct Links</h1>
                <p class="mt-1 text-sm text-gray-500">Review the direct links assigned to your publisher account.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('publisher.wallet') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Wallet
                </a>
                <a href="{{ route('publisher.direct-links.export', request()->all()) }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                    CSV Export
                </a>
            </div>
        </div>
    </div>

    @php
        $summaryCards = [
            ['label' => 'Total Links', 'value' => number_format($summary['total']), 'style' => 'border-gray-200 bg-white text-gray-900'],
            ['label' => 'Active', 'value' => number_format($summary['active']), 'style' => 'border-emerald-200 bg-emerald-50 text-emerald-700'],
            ['label' => 'Paused', 'value' => number_format($summary['paused']), 'style' => 'border-amber-200 bg-amber-50 text-amber-700'],
            ['label' => 'Expired', 'value' => number_format($summary['expired']), 'style' => 'border-rose-200 bg-rose-50 text-rose-700'],
            ['label' => 'Clicks', 'value' => number_format($summary['clicks']), 'style' => 'border-blue-200 bg-blue-50 text-blue-700'],
            ['label' => 'Views', 'value' => number_format($summary['views']), 'style' => 'border-purple-200 bg-purple-50 text-purple-700'],
        ];
    @endphp

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-6">
        @foreach($summaryCards as $card)
            <div class="rounded-xl border p-4 {{ $card['style'] }}">
                <p class="text-[10px] font-semibold uppercase tracking-wider opacity-70">{{ $card['label'] }}</p>
                <p class="mt-2 text-xl font-bold">{{ $card['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-100 p-4">
            <form method="GET" action="{{ route('publisher.direct-links') }}" class="flex flex-wrap items-center gap-2">
                <div class="relative min-w-[240px] flex-1">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, code, or destination..." class="w-full rounded-lg border border-gray-200 py-2 pl-10 pr-4 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                </div>

                <select name="status" onchange="this.form.submit()" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="all">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="paused" {{ request('status') === 'paused' ? 'selected' : '' }}>Paused</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                </select>

                <button type="submit" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-200">Search</button>
                <a href="{{ route('publisher.direct-links') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</a>
                <a href="{{ route('publisher.direct-links.export') }}" class="ml-auto rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">CSV</a>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/70 text-[10px] font-semibold uppercase tracking-wider text-gray-400">
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-left">Scopes</th>
                        <th class="px-4 py-3 text-left">Code</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Clicks</th>
                        <th class="px-4 py-3 text-right">Views</th>
                        <th class="px-4 py-3 text-left">Expires</th>
                        <th class="px-4 py-3 text-left">Created</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($links as $link)
                        @php
                            $publisherScope = $link->publisher_scope === 'all' ? 'All publishers' : count($link->publisher_ids ?? []) . ' publishers';
                            $adblockScope = $link->adblock_scope === 'all' ? 'All adblocks' : count($link->zone_ids ?? []) . ' adblocks';
                        @endphp
                        <tr class="hover:bg-gray-50/60">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $link->name }}</div>
                                <div class="mt-0.5 text-xs text-gray-400">{{ $link->destination_url ?: 'No destination URL set' }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                <div>{{ $publisherScope }}</div>
                                <div>{{ $adblockScope }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <code class="rounded bg-gray-100 px-2 py-1 font-mono text-xs text-gray-700">{{ $link->link_code }}</code>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase
                                    {{ $link->status === 'active' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                    {{ $link->status === 'paused' ? 'bg-amber-100 text-amber-700' : '' }}
                                    {{ $link->status === 'expired' ? 'bg-rose-100 text-rose-700' : '' }}
                                ">{{ $link->status }}</span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ number_format((int) $link->click_count) }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ number_format((int) $link->view_count) }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $link->expires_at?->format('Y-m-d H:i') ?? 'No expiry' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $link->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('publisher.direct-links.show', $link->id) }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50">Details</a>
                                    <a href="{{ $link->full_url }}" target="_blank" rel="noopener" class="rounded-lg border border-blue-200 px-3 py-1.5 text-xs font-semibold text-blue-600 hover:bg-blue-50">Open</a>
                                    <button type="button" onclick="copyToClipboard('{{ $link->full_url }}')" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50">Copy</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-14 text-center text-sm text-gray-400">No direct links found for these filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($links->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $links->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text);
    }
}
</script>
@endsection
