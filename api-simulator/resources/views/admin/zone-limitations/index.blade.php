@extends('layouts.admin')

@section('title', 'Zone Limitations')

@section('content')
    {{-- Success/Error flash --}}
    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-700 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Zone Limitations</h1>
            <p class="text-sm text-gray-500 mt-1">Manage adblock whitelist and blacklist rules for advertisers.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('addListModal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Add List
            </button>
        </div>
    </div>

    {{-- ═══════════ STAT CARDS ═══════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        @php
            $statCards = [
                ['label' => 'Total Lists', 'value' => number_format($totalLimitations), 'color' => 'text-gray-900', 'bg' => 'bg-gray-50', 'border' => 'border-gray-200', 'icon' => '<path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Whitelists', 'value' => number_format($whitelistCount), 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'icon' => '<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Blacklists', 'value' => number_format($blacklistCount), 'color' => 'text-red-700', 'bg' => 'bg-red-50', 'border' => 'border-red-200', 'icon' => '<path d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Advertisers', 'value' => number_format($totalAdvertisers), 'color' => 'text-blue-700', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'icon' => '<path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
            ];
        @endphp
        @foreach($statCards as $card)
            <div class="rounded-xl border {{ $card['border'] }} {{ $card['bg'] }} p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                    <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none">{!! $card['icon'] !!}</svg>
                </div>
                <div class="text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- ═══════════ SEARCH & EXPORT BAR ═══════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4">
            {{-- Search & Filters --}}
            <form method="GET" action="{{ route('admin.zone-limitations') }}" class="flex items-center gap-2 flex-1 flex-wrap">
                <div class="relative flex-1 max-w-md">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, advertiser..."
                           class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <select name="type" onchange="this.form.submit()" class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Types</option>
                    <option value="adblock_whitelist" {{ request('type') === 'adblock_whitelist' ? 'selected' : '' }}>Whitelist</option>
                    <option value="adblock_blacklist" {{ request('type') === 'adblock_blacklist' ? 'selected' : '' }}>Blacklist</option>
                </select>
                <select name="advertiser_id" onchange="this.form.submit()" class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Advertisers</option>
                    @foreach($advertisers as $adv)
                        @php
                            $name = trim(($adv->profile->first_name ?? '') . ' ' . ($adv->profile->last_name ?? ''));
                            if (!$name) $name = $adv->email;
                        @endphp
                        <option value="{{ $adv->id }}" {{ request('advertiser_id') == $adv->id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium text-gray-600 hover:bg-gray-200 transition-colors">Search</button>
            </form>

            {{-- Export dropdown --}}
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Export
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-44 bg-white rounded-xl border border-gray-200 shadow-lg py-1 z-50" style="display:none;">
                    <button onclick="copyTable()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Copy</button>
                    <a href="{{ route('admin.zone-limitations.export', request()->all()) }}" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">CSV</a>
                    <button onclick="window.print()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Print</button>
                </div>
            </div>
        </div>

        {{-- ═══════════ TABLE ═══════════ --}}
        <div class="overflow-x-auto">
            <table id="limitationsTable" class="w-full text-sm">
                <thead>
                    <tr class="border-t border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Name</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Type</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Advertiser</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Zone ID's</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($limitations as $limitation)
                        @php
                            $advertiserName = trim(($limitation->advertiser->profile->first_name ?? '') . ' ' . ($limitation->advertiser->profile->last_name ?? ''));
                            if (!$advertiserName) $advertiserName = $limitation->advertiser->email;
                            $company = $limitation->advertiser->profile->company_name ?? '';
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">#{{ $limitation->id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $limitation->name }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @if($limitation->type === 'adblock_whitelist')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        Whitelist
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                        Blacklist
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-semibold text-xs flex-shrink-0">
                                        {{ strtoupper(substr($limitation->advertiser->email, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $advertiserName }}</div>
                                        @if($company)
                                            <div class="text-xs text-gray-400">{{ $company }}</div>
                                        @else
                                            <div class="text-xs text-gray-400">{{ $limitation->advertiser->email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($limitation->zone_ids ?? [] as $zoneId)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-mono font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                            #{{ $zoneId }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center">
                                    <button onclick="deleteLimitation({{ $limitation->id }})" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors" title="Delete">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    <p class="text-sm text-gray-500">No zone limitation lists found.</p>
                                    <button onclick="document.getElementById('addListModal').classList.remove('hidden')" class="text-sm text-brand-600 hover:text-brand-700 font-medium">Add your first list</button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($limitations->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $limitations->links() }}
            </div>
        @endif
    </div>

    {{-- ═══════════ ADD LIST MODAL ═══════════ --}}
    <div id="addListModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Add Zone Limitation List</h3>
                <button onclick="document.getElementById('addListModal').classList.add('hidden')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.zone-limitations.store') }}" class="p-6 space-y-4">
                @csrf

                {{-- Validation errors --}}
                @if($errors->any())
                    <div class="p-3 rounded-lg border border-red-300 bg-red-50 text-red-700 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Advertiser --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Advertiser <span class="text-red-500">*</span></label>
                    <select name="advertiser_id" id="add_advertiser_id" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        <option value="">Select Advertiser</option>
                        @foreach($advertisers as $adv)
                            @php
                                $name = trim(($adv->profile->first_name ?? '') . ' ' . ($adv->profile->last_name ?? ''));
                                if (!$name) $name = $adv->email;
                            @endphp
                            <option value="{{ $adv->id }}" {{ old('advertiser_id') == $adv->id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('advertiser_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Name --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Premium Zones Whitelist"
                           class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    @error('name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- List Type (checkboxes, but only one can be selected) --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-2">List Type <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="type" value="adblock_whitelist" id="type_whitelist"
                                   {{ old('type', 'adblock_whitelist') === 'adblock_whitelist' ? 'checked' : '' }}
                                   onchange="onTypeChange()" class="w-4 h-4 text-brand-600 border-gray-300 focus:ring-brand-500">
                            <span class="text-sm text-gray-700">Adblock Whitelist</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="type" value="adblock_blacklist" id="type_blacklist"
                                   {{ old('type') === 'adblock_blacklist' ? 'checked' : '' }}
                                   onchange="onTypeChange()" class="w-4 h-4 text-brand-600 border-gray-300 focus:ring-brand-500">
                            <span class="text-sm text-gray-700">Adblock Blacklist</span>
                        </label>
                    </div>
                    @error('type')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Zone selection area --}}
                <div id="zoneSelectionArea">
                    <label class="block text-xs font-medium text-gray-600 mb-1" id="zoneDropdownLabel">Select Adblock Whitelist Zones <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        <input type="text" id="zoneSearch" placeholder="Search zones by name or ID..."
                               oninput="searchZones(this.value)"
                               class="w-full pl-10 pr-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div id="zoneDropdown" class="mt-2 max-h-48 overflow-y-auto rounded-lg border border-gray-200 bg-white hidden">
                        {{-- Populated via JS --}}
                    </div>
                    <div id="selectedZones" class="mt-2 flex flex-wrap gap-1">
                        {{-- Selected zone chips --}}
                    </div>
                    @error('zone_ids')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                    @error('zone_ids.*')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('addListModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm">Create List</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let selectedZoneIds = [];
    let allZones = [];
    let searchTimeout = null;

    // Load zones on page load
    loadAllZones();

    function loadAllZones() {
        fetch('{{ route("admin.zone-limitations.zones") }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(zones => {
            allZones = zones;
        })
        .catch(err => console.error('Error loading zones:', err));
    }

    function onTypeChange() {
        const isWhitelist = document.getElementById('type_whitelist').checked;
        const label = document.getElementById('zoneDropdownLabel');
        label.innerHTML = isWhitelist
            ? 'Select Adblock Whitelist Zones <span class="text-red-500">*</span>'
            : 'Select Adblock Blacklist Zones <span class="text-red-500">*</span>';
    }

    function searchZones(query) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const dropdown = document.getElementById('zoneDropdown');

            if (query.length === 0) {
                // Show all zones if no query
                renderZoneDropdown(allZones);
                return;
            }

            // Filter from cached zones
            const filtered = allZones.filter(z =>
                z.label.toLowerCase().includes(query.toLowerCase()) ||
                z.id.toString() === query
            );
            renderZoneDropdown(filtered);
        }, 200);
    }

    function renderZoneDropdown(zones) {
        const dropdown = document.getElementById('zoneDropdown');

        if (zones.length === 0) {
            dropdown.innerHTML = '<div class="px-4 py-3 text-sm text-gray-400 text-center">No zones found</div>';
            dropdown.classList.remove('hidden');
            return;
        }

        let html = '';
        zones.forEach(zone => {
            const isSelected = selectedZoneIds.includes(zone.id);
            html += `<div class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 cursor-pointer transition-colors ${isSelected ? 'bg-brand-50' : ''}"
                          onclick="toggleZone(${zone.id}, '${zone.label.replace(/'/g, "\\'")}')">
                <input type="checkbox" ${isSelected ? 'checked' : ''} class="w-4 h-4 text-brand-600 border-gray-300 rounded focus:ring-brand-500 pointer-events-none">
                <span class="text-sm text-gray-700">${zone.label}</span>
            </div>`;
        });

        dropdown.innerHTML = html;
        dropdown.classList.remove('hidden');
    }

    function toggleZone(zoneId, label) {
        const idx = selectedZoneIds.indexOf(zoneId);
        if (idx > -1) {
            selectedZoneIds.splice(idx, 1);
        } else {
            selectedZoneIds.push(zoneId);
        }
        renderSelectedZones();
        // Re-render dropdown to update checkboxes
        const query = document.getElementById('zoneSearch').value;
        if (query) {
            searchZones(query);
        } else {
            renderZoneDropdown(allZones);
        }
    }

    function removeZone(zoneId) {
        selectedZoneIds = selectedZoneIds.filter(id => id !== zoneId);
        renderSelectedZones();
        // Re-render dropdown if visible
        const dropdown = document.getElementById('zoneDropdown');
        if (!dropdown.classList.contains('hidden')) {
            const query = document.getElementById('zoneSearch').value;
            if (query) {
                searchZones(query);
            } else {
                renderZoneDropdown(allZones);
            }
        }
    }

    function renderSelectedZones() {
        const container = document.getElementById('selectedZones');
        // Remove old hidden inputs
        container.querySelectorAll('input[type="hidden"]').forEach(el => el.remove());

        let html = '';
        selectedZoneIds.forEach(id => {
            const zone = allZones.find(z => z.id === id);
            const label = zone ? zone.label : `#${id}`;
            html += `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-brand-50 text-brand-700 border border-brand-200">
                ${label}
                <button type="button" onclick="removeZone(${id})" class="ml-0.5 hover:text-red-500">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <input type="hidden" name="zone_ids[]" value="${id}">
            </span>`;
        });

        container.innerHTML = html;
    }

    // Show dropdown on search focus
    document.getElementById('zoneSearch').addEventListener('focus', function() {
        if (allZones.length > 0) {
            renderZoneDropdown(allZones);
        }
    });

    // Hide dropdown on outside click
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('zoneDropdown');
        const searchInput = document.getElementById('zoneSearch');
        if (!dropdown.contains(e.target) && e.target !== searchInput) {
            dropdown.classList.add('hidden');
        }
    });

    // Delete limitation
    function deleteLimitation(id) {
        if (!confirm('Are you sure you want to delete this zone limitation list?')) return;

        fetch(`/admin/zone-limitations/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => {
            if (data.success) location.reload();
            else alert(data.message || 'Error deleting zone limitation.');
        });
    }

    // Copy table data
    function copyTable() {
        const table = document.getElementById('limitationsTable');
        const rows = table.querySelectorAll('tr');
        let text = '';
        rows.forEach(row => {
            const cells = row.querySelectorAll('th, td');
            const rowData = [];
            cells.forEach((cell, i) => {
                if (i < cells.length - 1) rowData.push(cell.textContent.trim().replace(/\s+/g, ' '));
            });
            text += rowData.join('\t') + '\n';
        });
        navigator.clipboard.writeText(text).then(() => alert('Table data copied to clipboard!'));
    }

    // Reopen modal if there are validation errors
    @if($errors->any())
        document.getElementById('addListModal').classList.remove('hidden');
    @endif
</script>
@endpush
