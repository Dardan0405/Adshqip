@extends('layouts.admin')
@section('title', 'Add Direct Link')
@section('content')

    @if(session('success'))
        <div class="mb-4 flex items-center gap-2 rounded-xl border border-emerald-300 bg-emerald-50 p-3 text-sm text-emerald-700">
            <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 flex items-center gap-2 rounded-xl border border-rose-300 bg-rose-50 p-3 text-sm text-rose-700">
            <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Add Direct Link</h1>
            <p class="mt-1 text-sm text-gray-500">Create direct links for publishers and adblocks.</p>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-blue-500">Total Publishers</div>
            <div class="mt-2 text-2xl font-bold text-blue-700">{{ number_format($stats['totalPublishers']) }}</div>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-emerald-500">Total AdBlocks</div>
            <div class="mt-2 text-2xl font-bold text-emerald-700">{{ number_format($stats['totalAdblocks']) }}</div>
        </div>
        <div class="rounded-xl border border-purple-200 bg-purple-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-purple-500">Total Links</div>
            <div class="mt-2 text-2xl font-bold text-purple-700">{{ number_format($stats['totalLinks']) }}</div>
        </div>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-amber-500">Active Links</div>
            <div class="mt-2 text-2xl font-bold text-amber-700">{{ number_format($stats['activeLinks']) }}</div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-100 px-6 py-4">
            <h3 class="text-lg font-semibold text-gray-900">Create Direct Link</h3>
            <p class="text-sm text-gray-500">Select publishers and adblocks to generate a direct link.</p>
        </div>
        <form method="POST" action="{{ route('admin.add-direct-link.store') }}" class="p-6" id="directLinkForm">
            @csrf

            <div class="mb-4">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Link Name</label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="Enter a name for this link..." required>
                @error('name') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Publisher</label>
                    <select name="publisher_scope" id="publisherScope" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" required>
                        <option value="all" {{ old('publisher_scope', 'all') === 'all' ? 'selected' : '' }}>All Publishers</option>
                        <option value="selected" {{ old('publisher_scope') === 'selected' ? 'selected' : '' }}>Select Publisher</option>
                    </select>
                    @error('publisher_scope') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">AdBlock</label>
                    <select name="adblock_scope" id="adblockScope" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" required>
                        <option value="all" {{ old('adblock_scope', 'all') === 'all' ? 'selected' : '' }}>All AdBlocks</option>
                        <option value="selected" {{ old('adblock_scope') === 'selected' ? 'selected' : '' }}>Select AdBlock</option>
                    </select>
                    @error('adblock_scope') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div id="publisherSelection" class="mt-4 hidden">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Select Publishers</label>
                <div class="rounded-lg border border-gray-200 p-3 max-h-48 overflow-y-auto">
                    <div class="mb-2 flex items-center gap-2">
                        <input type="checkbox" id="selectAllPublishers" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <label for="selectAllPublishers" class="text-sm font-medium text-gray-700">Select All Publishers</label>
                    </div>
                    <div class="border-t border-gray-100 pt-2 space-y-1">
                        @foreach($publishers as $publisher)
                            <label class="flex items-center gap-2 rounded p-1 hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" name="selected_publishers[]" value="{{ $publisher->id }}" class="publisher-checkbox h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" {{ in_array($publisher->id, old('selected_publishers', [])) ? 'checked' : '' }}>
                                <span class="text-sm text-gray-700">{{ $publisher->email }}</span>
                                @if($publisher->profile?->company_name)
                                    <span class="text-xs text-gray-400">({{ $publisher->profile->company_name }})</span>
                                @endif
                                <span class="text-xs text-blue-500 ml-auto">{{ $publisher->sites->sum(fn($s) => $s->zones->count()) }} zones</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                @error('selected_publishers') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div id="adblockSelection" class="mt-4 hidden">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Select AdBlocks</label>
                <div class="rounded-lg border border-gray-200 p-3 max-h-64 overflow-y-auto" id="adblockList">
                    <div class="mb-2 flex items-center gap-2">
                        <input type="checkbox" id="selectAllAdblocks" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <label for="selectAllAdblocks" class="text-sm font-medium text-gray-700">Select All AdBlocks</label>
                    </div>
                    <div class="border-t border-gray-100 pt-2 space-y-1" id="adblockCheckboxes">
                        @foreach($adblocks as $adblock)
                            <label class="adblock-item flex items-center gap-2 rounded p-1 hover:bg-gray-50 cursor-pointer" data-publisher-id="{{ $adblock->site?->publisher_id }}">
                                <input type="checkbox" name="selected_adblocks[]" value="{{ $adblock->id }}" class="adblock-checkbox h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" {{ in_array($adblock->id, old('selected_adblocks', [])) ? 'checked' : '' }}>
                                <span class="text-sm text-gray-700">{{ $adblock->name }}</span>
                                <span class="text-xs text-gray-400">({{ $adblock->site?->name }})</span>
                                @if($adblock->format)
                                    <span class="text-xs bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded">{{ $adblock->format->name }}</span>
                                @endif
                                @if($adblock->size_key)
                                    <span class="text-xs bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">{{ $adblock->size_key }}</span>
                                @endif
                                <span class="text-xs text-emerald-500 ml-auto">{{ $adblock->site?->publisher?->email }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                @error('selected_adblocks') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 md:grid-cols-2 mt-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Destination URL (Optional)</label>
                    <input type="url" name="destination_url" value="{{ old('destination_url') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="https://example.com">
                    @error('destination_url') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Expires At (Optional)</label>
                    <input type="date" name="expires_at" value="{{ old('expires_at') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    @error('expires_at') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6 flex items-center justify-between border-t border-gray-100 pt-4">
                <div id="selectionCount" class="text-sm text-gray-500">
                    Select publisher and adblock options
                </div>
                <button type="submit" class="rounded-lg bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-brand-700 flex items-center gap-2">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Create Direct Link
                </button>
            </div>
        </form>
    </div>

    @if($directLinks->isNotEmpty())
        <div class="mt-6 rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-100 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900">Direct Links</h3>
                <p class="text-sm text-gray-500">All created direct links.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/70">
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Name</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Publisher</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">AdBlock</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Link Code</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Clicks</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Views</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Created</th>
                            <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($directLinks as $link)
                            <tr class="transition-colors hover:bg-gray-50/60">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $link->id }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $link->name }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase {{ $link->publisher_scope === 'all' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                        {{ $link->publisher_scope }}
                                    </span>
                                    @if($link->publisher_scope === 'selected')
                                        <span class="text-xs text-gray-400">({{ count($link->publisher_ids ?? []) }})</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase {{ $link->adblock_scope === 'all' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $link->adblock_scope }}
                                    </span>
                                    @if($link->adblock_scope === 'selected')
                                        <span class="text-xs text-gray-400">({{ count($link->zone_ids ?? []) }})</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <code class="text-xs bg-gray-100 px-2 py-1 rounded font-mono">{{ $link->link_code }}</code>
                                    <button type="button" onclick="copyToClipboard('{{ $link->full_url }}')" class="ml-1 text-blue-500 hover:text-blue-700 text-xs">Copy</button>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ number_format($link->click_count) }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ number_format($link->view_count) }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase
                                        {{ $link->status === 'active' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                        {{ $link->status === 'paused' ? 'bg-amber-100 text-amber-700' : '' }}
                                        {{ $link->status === 'expired' ? 'bg-rose-100 text-rose-700' : '' }}
                                    ">
                                        {{ $link->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $link->created_at->format('M d, Y') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        @if($link->status === 'active')
                                            <form method="POST" action="{{ route('admin.add-direct-link.status', $link) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="paused">
                                                <button type="submit" class="rounded-lg border border-amber-200 px-3 py-1.5 text-xs font-semibold text-amber-600 transition-colors hover:bg-amber-50">Pause</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.add-direct-link.status', $link) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="active">
                                                <button type="submit" class="rounded-lg border border-emerald-200 px-3 py-1.5 text-xs font-semibold text-emerald-600 transition-colors hover:bg-emerald-50">Activate</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.add-direct-link.destroy', $link) }}" onsubmit="return confirm('Are you sure you want to delete this direct link?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 transition-colors hover:bg-rose-50">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($directLinks->hasPages())
                <div class="border-t border-gray-100 px-4 py-3">{{ $directLinks->links() }}</div>
            @endif
        </div>
    @endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var publisherScope = document.getElementById('publisherScope');
    var adblockScope = document.getElementById('adblockScope');
    var publisherSelection = document.getElementById('publisherSelection');
    var adblockSelection = document.getElementById('adblockSelection');
    var selectAllPublishers = document.getElementById('selectAllPublishers');
    var selectAllAdblocks = document.getElementById('selectAllAdblocks');
    var selectionCount = document.getElementById('selectionCount');

    var totalPublishers = {{ $stats['totalPublishers'] }};
    var totalAdblocks = {{ $stats['totalAdblocks'] }};

    function updateSelectionVisibility() {
        publisherSelection.classList.toggle('hidden', publisherScope.value !== 'selected');
        adblockSelection.classList.toggle('hidden', adblockScope.value !== 'selected');
        filterAdblocksByPublisher();
        updateSelectionCount();
    }

    function filterAdblocksByPublisher() {
        var selectedPublisherIds = [];
        if (publisherScope.value === 'selected') {
            document.querySelectorAll('.publisher-checkbox:checked').forEach(function(cb) {
                selectedPublisherIds.push(cb.value);
            });
        }

        document.querySelectorAll('.adblock-item').forEach(function(item) {
            var publisherId = item.getAttribute('data-publisher-id');
            if (publisherScope.value === 'all' || selectedPublisherIds.length === 0 || selectedPublisherIds.includes(publisherId)) {
                item.classList.remove('hidden');
            } else {
                item.classList.add('hidden');
                item.querySelector('.adblock-checkbox').checked = false;
            }
        });

        updateSelectionCount();
    }

    function updateSelectionCount() {
        var pubCount = 0;
        var adCount = 0;

        if (publisherScope.value === 'all') {
            pubCount = totalPublishers;
        } else {
            pubCount = document.querySelectorAll('.publisher-checkbox:checked').length;
        }

        if (adblockScope.value === 'all') {
            if (publisherScope.value === 'all') {
                adCount = totalAdblocks;
            } else {
                adCount = document.querySelectorAll('.adblock-item:not(.hidden)').length;
            }
        } else {
            adCount = document.querySelectorAll('.adblock-checkbox:checked').length;
        }

        selectionCount.textContent = 'Publishers: ' + pubCount + ' | AdBlocks: ' + adCount;
    }

    publisherScope.addEventListener('change', updateSelectionVisibility);
    adblockScope.addEventListener('change', updateSelectionVisibility);

    selectAllPublishers.addEventListener('change', function() {
        document.querySelectorAll('.publisher-checkbox').forEach(function(cb) {
            cb.checked = selectAllPublishers.checked;
        });
        filterAdblocksByPublisher();
    });

    selectAllAdblocks.addEventListener('change', function() {
        document.querySelectorAll('.adblock-item:not(.hidden) .adblock-checkbox').forEach(function(cb) {
            cb.checked = selectAllAdblocks.checked;
        });
        updateSelectionCount();
    });

    document.querySelectorAll('.publisher-checkbox').forEach(function(cb) {
        cb.addEventListener('change', filterAdblocksByPublisher);
    });

    document.querySelectorAll('.adblock-checkbox').forEach(function(cb) {
        cb.addEventListener('change', updateSelectionCount);
    });

    // Initialize on page load
    updateSelectionVisibility();
});

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Link copied to clipboard!');
    });
}
</script>
@endpush
