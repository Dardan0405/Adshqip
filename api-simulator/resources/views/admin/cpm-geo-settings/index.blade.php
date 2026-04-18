@extends('layouts.admin')
@section('title', 'CPM GEO Settings')
@section('content')

    @if(session('success'))
        <div class="mb-4 flex items-center gap-2 rounded-xl border border-emerald-300 bg-emerald-50 p-3 text-sm text-emerald-700">
            <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">CPM GEO Settings</h1>
            <p class="mt-1 text-sm text-gray-500">Manage global CPM overrides by country. Matching countries affect live CPM revenue calculations for regular and direct campaign traffic.</p>
        </div>
        <button type="button" onclick="toggleCreateForm()" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-brand-700">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            Create CPM GEO
        </button>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-blue-500">Total Rows</div>
            <div class="mt-2 text-2xl font-bold text-blue-700">{{ number_format($stats['total_rows']) }}</div>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-emerald-500">Average CPM</div>
            <div class="mt-2 text-2xl font-bold text-emerald-700">{{ number_format($stats['average_cpm'], 2) }}</div>
        </div>
        <div class="rounded-xl border border-purple-200 bg-purple-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-purple-500">Highest CPM</div>
            <div class="mt-2 text-2xl font-bold text-purple-700">{{ number_format($stats['highest_cpm'], 2) }}</div>
        </div>
    </div>

    <div id="createCpmGeoForm" class="mb-6 rounded-xl border border-gray-200 bg-white {{ $errors->any() ? '' : 'hidden' }}">
        <form method="POST" action="{{ route('admin.cpm-geo-settings.store') }}" class="space-y-4 p-6">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Country Name</label>
                    <select name="country_code" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" required>
                        <option value="">Select country</option>
                        @foreach($countries as $code => $name)
                            <option value="{{ $code }}" {{ old('country_code') === $code ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('country_code') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">CPM Value</label>
                    <input type="number" name="cpm_value" value="{{ old('cpm_value') }}" step="0.0001" min="0" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="2.5000" required>
                    @error('cpm_value') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex items-center justify-end gap-3">
                <button type="button" onclick="toggleCreateForm(false)" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Save CPM GEO</button>
            </div>
        </form>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/70">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">ID</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">CPM Value</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Country Name</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($settings as $setting)
                        <tr class="transition-colors hover:bg-gray-50/60">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $setting->id }}</td>
                            <td class="px-4 py-3">
                                <span class="font-semibold text-emerald-700">{{ number_format((float) $setting->cpm_value, 4) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $setting->country_name }}</div>
                                <div class="text-xs text-gray-400">{{ $setting->country_code }}</div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.cpm-geo-settings.destroy', $setting) }}" class="inline-flex" onsubmit="return confirm('Delete this CPM GEO setting?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 transition-colors hover:bg-rose-50">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="h-12 w-12 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M12 3v18M3 12h18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    <p class="text-sm text-gray-500">No CPM GEO settings created yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($settings->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $settings->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
function toggleCreateForm(forceOpen = null) {
    const form = document.getElementById('createCpmGeoForm');
    if (!form) return;
    if (forceOpen === true) {
        form.classList.remove('hidden');
        return;
    }
    if (forceOpen === false) {
        form.classList.add('hidden');
        return;
    }
    form.classList.toggle('hidden');
}
</script>
@endpush
