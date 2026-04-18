@extends('layouts.admin')
@section('title', 'Display Screen')
@section('content')

    @if(session('success'))
        <div class="mb-4 flex items-center gap-2 rounded-xl border border-emerald-300 bg-emerald-50 p-3 text-sm text-emerald-700">
            <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Display Screen</h1>
            <p class="mt-1 text-sm text-gray-500">Manage reusable screen dimensions for admin-side display configuration.</p>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-blue-500">Total Screens</div>
            <div class="mt-2 text-2xl font-bold text-blue-700">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-emerald-500">Active</div>
            <div class="mt-2 text-2xl font-bold text-emerald-700">{{ number_format($stats['active']) }}</div>
        </div>
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-rose-500">Blocked</div>
            <div class="mt-2 text-2xl font-bold text-rose-700">{{ number_format($stats['blocked']) }}</div>
        </div>
        <div class="rounded-xl border border-purple-200 bg-purple-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-purple-500">Largest</div>
            <div class="mt-2 text-lg font-bold text-purple-700">{{ $stats['largest']?->dimensions ?? '—' }}</div>
        </div>
    </div>

    <div class="mb-6 rounded-xl border border-gray-200 bg-white">
        <form method="POST" action="{{ route('admin.display-screens.store') }}" class="grid gap-4 p-6 md:grid-cols-4">
            @csrf
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Screen Name</label>
                <input type="text" name="screen_name" value="{{ old('screen_name') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="Desktop Large" required>
                @error('screen_name') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Value</label>
                <input type="text" name="value" value="{{ old('value') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="desktop_large" required>
                @error('value') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Width</label>
                <input type="number" name="width" value="{{ old('width') }}" min="1" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="1920" required>
                @error('width') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Height</label>
                <div class="flex gap-2">
                    <input type="number" name="height" value="{{ old('height') }}" min="1" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="1080" required>
                    <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-brand-700">Save</button>
                </div>
                @error('height') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>
        </form>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/70">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">ID</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Screen Name</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Width</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Height</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($screens as $screen)
                        <tr class="transition-colors hover:bg-gray-50/60">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $screen->id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $screen->screen_name }}</div>
                                <div class="text-xs text-gray-400">{{ $screen->value }}</div>
                                <span class="mt-1 inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase {{ $screen->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                    {{ $screen->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ number_format($screen->width) }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ number_format($screen->height) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <button type="button"
    class="edit-screen-btn rounded-lg border border-blue-200 px-3 py-1.5 text-xs font-semibold text-blue-600 transition-colors hover:bg-blue-50"
    data-id="{{ $screen->id }}"
    data-screen-name="{{ $screen->screen_name }}"
    data-value="{{ $screen->value }}"
    data-width="{{ $screen->width }}"
    data-height="{{ $screen->height }}">Edit</button>
                                    @if($screen->status === 'active')
                                        <form method="POST" action="{{ route('admin.display-screens.block', $screen) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg border border-amber-200 px-3 py-1.5 text-xs font-semibold text-amber-600 transition-colors hover:bg-amber-50">Block</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.display-screens.unblock', $screen) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg border border-emerald-200 px-3 py-1.5 text-xs font-semibold text-emerald-600 transition-colors hover:bg-emerald-50">Unblock</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="h-12 w-12 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M4 6h16M7 4v4M17 4v4M5 10h14a2 2 0 0 1 2 2v5a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3v-5a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    <p class="text-sm text-gray-500">No display screens found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($screens->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $screens->links() }}</div>
        @endif
    </div>

    <div id="editScreenModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/40 px-4">
        <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Edit Display Screen</h3>
                    <p class="text-sm text-gray-500">Update the saved screen dimensions.</p>
                </div>
                <button type="button" onclick="closeEditScreen()" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
            </div>
            <form id="editScreenForm" method="POST" class="grid gap-4 p-6 md:grid-cols-2">
                @csrf
                @method('PUT')
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Screen Name</label>
                    <input id="edit_screen_name" type="text" name="screen_name" value="{{ old('screen_name') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 @error('screen_name') border-rose-300 @enderror" required>
                    @error('screen_name') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Value</label>
                    <input id="edit_value" type="text" name="value" value="{{ old('value') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 @error('value') border-rose-300 @enderror" required>
                    @error('value') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Width</label>
                    <input id="edit_width" type="number" name="width" value="{{ old('width') }}" min="1" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 @error('width') border-rose-300 @enderror" required>
                    @error('width') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Height</label>
                    <input id="edit_height" type="number" name="height" value="{{ old('height') }}" min="1" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 @error('height') border-rose-300 @enderror" required>
                    @error('height') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2 flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeEditScreen()" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Update</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('editScreenModal');
    var form = document.getElementById('editScreenForm');

    // Attach click handlers to all edit buttons
    document.querySelectorAll('.edit-screen-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            var screenName = this.getAttribute('data-screen-name');
            var value = this.getAttribute('data-value');
            var width = this.getAttribute('data-width');
            var height = this.getAttribute('data-height');

            form.action = '{{ url("admin/display-screens") }}/' + id;
            document.getElementById('edit_screen_name').value = screenName;
            document.getElementById('edit_value').value = value;
            document.getElementById('edit_width').value = width;
            document.getElementById('edit_height').value = height;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });

    // Auto-open modal if there are edit validation errors
    @if($errors->any() && old('_method') === 'PUT')
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    @endif
});

function closeEditScreen() {
    var modal = document.getElementById('editScreenModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endpush
