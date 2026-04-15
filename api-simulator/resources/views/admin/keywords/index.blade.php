@extends('layouts.admin')

@section('title', 'Meta Keywords')

@section('content')
<div class="space-y-6" x-data="keywordsPage()">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Meta Keywords</h1>
            <p class="mt-1 text-sm text-gray-500">Manage keywords for contextual campaign targeting based on website meta tags.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="showBulkImportModal = true" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Bulk Import
            </button>
            <a href="{{ route('admin.keywords.export') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export
            </a>
            <button @click="showCreateModal = true" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Keyword
            </button>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-50">
                    <svg class="h-5 w-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalKeywords) }}</p>
                    <p class="text-xs text-gray-500">Total Keywords</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50">
                    <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($activeKeywords) }}</p>
                    <p class="text-xs text-gray-500">Active Keywords</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalMatches) }}</p>
                    <p class="text-xs text-gray-500">Total Matches</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.keywords') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1 max-w-xs">
                <label class="mb-1 block text-xs font-medium text-gray-600">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search keywords..." class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div class="w-40">
                <label class="mb-1 block text-xs font-medium text-gray-600">Status</label>
                <select name="status" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 bg-white">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            @if($categories->isNotEmpty())
            <div class="w-40">
                <label class="mb-1 block text-xs font-medium text-gray-600">Category</label>
                <select name="category" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 bg-white">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="flex gap-2">
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">Search</button>
                <a href="{{ route('admin.keywords') }}" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200">Reset</a>
            </div>
        </form>
    </div>

    {{-- Keywords Table --}}
    <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">ID</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">KEYWORD</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">CATEGORY</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">MATCHES</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">LAST MATCHED</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($keywords as $keyword)
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $keyword->id }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-gray-900">{{ $keyword->keyword }}</span>
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $keyword->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-orange-100 text-orange-700' }}">
                                            {{ $keyword->status === 'active' ? 'Active' : 'Blocked' }}
                                        </span>
                                    </div>
                                    @if($keyword->description)
                                        <span class="text-xs text-gray-400 truncate max-w-xs" title="{{ $keyword->description }}">{{ $keyword->description }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($keyword->category)
                                    <span class="inline-block rounded-lg bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">{{ $keyword->category }}</span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ number_format($keyword->match_count) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                {{ $keyword->last_matched_at ? $keyword->last_matched_at->diffForHumans() : '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <button
                                        type="button"
                                        data-item="{{ json_encode(['id' => $keyword->id, 'keyword' => $keyword->keyword, 'category' => $keyword->category, 'description' => $keyword->description]) }}"
                                        @click="openEditModal(JSON.parse($event.currentTarget.dataset.item))"
                                        class="rounded-lg p-1.5 text-gray-400 transition hover:bg-blue-50 hover:text-blue-600"
                                        title="Edit"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    @if($keyword->status === 'active')
                                        <form method="POST" action="{{ route('admin.keywords.block', $keyword->id) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-orange-50 hover:text-orange-600" title="Block" onclick="return confirm('Are you sure you want to block this keyword?')">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.keywords.unblock', $keyword->id) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-emerald-50 hover:text-emerald-600" title="Unblock">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('admin.keywords.destroy', $keyword->id) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Delete" onclick="return confirm('Are you sure you want to delete this keyword?')">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-500">
                                <svg class="mx-auto mb-4 h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                <p class="font-medium">No keywords found</p>
                                <p class="text-sm">Add keywords to enable contextual targeting for campaigns.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($keywords->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $keywords->links() }}
            </div>
        @endif
    </div>

    {{-- Create Modal --}}
    <div x-show="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex min-h-screen items-center justify-center px-4">
            <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false"></div>
            <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl" @click.stop>
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Add Keyword</h3>
                    <button @click="showCreateModal = false" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.keywords.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Keyword <span class="text-red-500">*</span></label>
                        <input type="text" name="keyword" value="{{ old('keyword') }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20" placeholder="e.g. sports betting">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Category</label>
                        <input type="text" name="category" value="{{ old('category') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20" placeholder="e.g. Gambling">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="2" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20" placeholder="Optional description...">{{ old('description') }}</textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showCreateModal = false" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200">Cancel</button>
                        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-700">Add Keyword</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex min-h-screen items-center justify-center px-4">
            <div class="fixed inset-0 bg-black/50" @click="showEditModal = false"></div>
            <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl" @click.stop>
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Edit Keyword</h3>
                    <button @click="showEditModal = false" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="'/admin/keywords/' + editItem.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Keyword <span class="text-red-500">*</span></label>
                        <input type="text" name="keyword" x-model="editItem.keyword" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Category</label>
                        <input type="text" name="category" x-model="editItem.category" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="2" x-model="editItem.description" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showEditModal = false" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200">Cancel</button>
                        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-700">Update Keyword</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Bulk Import Modal --}}
    <div x-show="showBulkImportModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex min-h-screen items-center justify-center px-4">
            <div class="fixed inset-0 bg-black/50" @click="showBulkImportModal = false"></div>
            <div class="relative w-full max-w-lg rounded-xl bg-white p-6 shadow-xl" @click.stop>
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Bulk Import Keywords</h3>
                    <button @click="showBulkImportModal = false" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.keywords.bulk-import') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Keywords <span class="text-red-500">*</span></label>
                        <textarea name="keywords_text" rows="8" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm font-mono focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20" placeholder="sports betting&#10;online casino&#10;gambling&#10;poker"></textarea>
                        <p class="mt-1 text-xs text-gray-400">Enter one keyword per line. Duplicates will be skipped.</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Category (Optional)</label>
                        <input type="text" name="category" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20" placeholder="e.g. Gambling">
                        <p class="mt-1 text-xs text-gray-400">All imported keywords will be assigned to this category.</p>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showBulkImportModal = false" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200">Cancel</button>
                        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-700">Import Keywords</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function keywordsPage() {
    return {
        showCreateModal: false,
        showEditModal: false,
        showBulkImportModal: false,
        editItem: {
            id: null,
            keyword: '',
            category: '',
            description: '',
        },
        openEditModal(item) {
            this.editItem = {
                id: item.id,
                keyword: item.keyword,
                category: item.category || '',
                description: item.description || '',
            };
            this.showEditModal = true;
        },
    };
}
</script>
@endsection
