@extends('layouts.publisher')

@section('title', 'Direct Link Details')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <a href="{{ route('publisher.direct-links') }}" class="text-sm text-gray-500 hover:text-gray-800">Back to direct links</a>
                <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ $link->name }}</h1>
                <p class="mt-1 text-sm text-gray-500">Direct link code: <span class="font-mono">{{ $link->link_code }}</span></p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ $link->full_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Open Link</a>
                <button type="button" onclick="copyToClipboard('{{ $link->full_url }}')" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Copy URL</button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Clicks</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format((int) $link->click_count) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Views</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format((int) $link->view_count) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ ucfirst($link->status) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Expires</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $link->expires_at?->format('Y-m-d') ?? 'No expiry' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Visibility</p>
            <div class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between gap-4"><span class="text-gray-500">Publisher Scope</span><span class="font-medium text-gray-900">{{ $link->publisher_scope }}</span></div>
                <div class="flex justify-between gap-4"><span class="text-gray-500">AdBlock Scope</span><span class="font-medium text-gray-900">{{ $link->adblock_scope }}</span></div>
                <div class="flex justify-between gap-4"><span class="text-gray-500">Creator</span><span class="font-medium text-gray-900">{{ $link->creator?->email ?? 'System' }}</span></div>
                <div class="flex justify-between gap-4"><span class="text-gray-500">Created</span><span class="font-medium text-gray-900">{{ $link->created_at?->format('Y-m-d H:i') }}</span></div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Destination</p>
            <p class="mt-3 break-all rounded-lg bg-gray-50 p-3 text-sm text-gray-700">{{ $link->destination_url ?: 'No destination URL configured' }}</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Serve URL</p>
            <p class="mt-3 break-all rounded-lg bg-gray-50 p-3 font-mono text-sm text-gray-700">{{ $link->full_url }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-sm font-bold text-gray-900">Assigned Publishers</h2>
            </div>
            <div class="px-5 py-4">
                @if($link->publisher_scope === 'all')
                    <p class="text-sm text-gray-500">All publisher accounts can access this direct link.</p>
                @else
                    <div class="flex flex-wrap gap-2">
                        @forelse($selectedPublisherNames as $publisher)
                            <span class="rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs font-medium text-gray-600">{{ $publisher->email }}</span>
                        @empty
                            <p class="text-sm text-gray-400">No publishers assigned.</p>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-sm font-bold text-gray-900">Assigned AdBlocks</h2>
            </div>
            <div class="px-5 py-4">
                @if($link->adblock_scope === 'all')
                    <p class="text-sm text-gray-500">All active adblocks can serve this direct link.</p>
                @else
                    <div class="space-y-2">
                        @forelse($selectedZones as $zone)
                            <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                                <div class="font-medium text-gray-900">{{ $zone->name }}</div>
                                <div class="text-xs text-gray-500">{{ $zone->site?->name }} {{ $zone->ad_code ? '· ' . $zone->ad_code : '' }}</div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400">No adblocks assigned.</p>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>
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
