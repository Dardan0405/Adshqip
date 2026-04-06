@extends('layouts.admin')

@section('title', 'AdBlocks')

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
            <h1 class="text-2xl font-bold text-gray-900">AdBlocks</h1>
            <p class="text-sm text-gray-500 mt-1">Manage ad zones and placements across all sites.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openCreateWizard()"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Add AdBlock
            </button>
        </div>
    </div>

    @if($errors->has('serve_domains') || $errors->has('active_serve_domain') || $errors->has('serve_path'))
        <div class="mb-4 p-3 rounded-xl border border-red-300 bg-red-50 text-red-700 text-sm">
            {{ $errors->first('serve_domains') ?: ($errors->first('active_serve_domain') ?: $errors->first('serve_path')) }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-4">
            <div>
                <h2 class="text-base font-semibold text-gray-900">Anti-Block Serve Settings</h2>
                <p class="text-sm text-gray-500 mt-1">Choose which domain and obfuscated path AdBlock tags should use when serving ads.</p>
            </div>
            <div class="text-xs text-gray-500 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                Current serve URL base: <span class="font-medium text-gray-700">{{ $activeServeDomain }}{{ $servePath }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.adblocks.serveSettings') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Available Domains</label>
                    <textarea name="serve_domains" rows="4" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="https://ads.example.com&#10;https://delivery.example.net">{{ old('serve_domains', implode("\n", $serveDomains)) }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Add one domain per line. These are the domains admins can rotate between for anti-block serving.</p>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Active Domain</label>
                        <input type="text" name="active_serve_domain" value="{{ old('active_serve_domain', $activeServeDomain) }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="https://ads.example.com">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Serve Path</label>
                        <input type="text" name="serve_path" value="{{ old('serve_path', ltrim($servePath, '/')) }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="d">
                        <p class="text-xs text-gray-500 mt-1">Example: `d`, `cdn`, `assets/js`</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
                <div class="text-xs font-medium text-gray-600 mb-1">Example Script URL</div>
                <code class="text-xs text-gray-700 break-all">{{ $activeServeDomain }}{{ $servePath }}/TOKEN.js</code>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-800">Save Serve Settings</button>
            </div>
        </form>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="AdBlock name, site name..."
                       class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>
            <div class="w-40">
                <label class="block text-xs font-medium text-gray-600 mb-1">Site</label>
                <select name="site_id" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    <option value="">All Sites</option>
                    @foreach($sites as $site)
                        <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-40">
                <label class="block text-xs font-medium text-gray-600 mb-1">Publisher</label>
                <select name="publisher_id" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    <option value="">All Publishers</option>
                    @foreach($publishers as $pub)
                        @php
                            $pubName = trim(($pub->profile->first_name ?? '') . ' ' . ($pub->profile->last_name ?? '')) ?: $pub->email;
                        @endphp
                        <option value="{{ $pub->id }}" {{ request('publisher_id') == $pub->id ? 'selected' : '' }}>{{ $pubName }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-40">
                <label class="block text-xs font-medium text-gray-600 mb-1">Format</label>
                <select name="format_id" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    <option value="">All Formats</option>
                    @foreach($formatCategories as $cat)
                        <option value="{{ $cat->format_key }}" {{ request('format_id') == $cat->format_key ? 'selected' : '' }}>{{ $cat->label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-36">
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="paused" {{ request('status') == 'paused' ? 'selected' : '' }}>Paused</option>
                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-800">Filter</button>
                <a href="{{ route('admin.adblocks') }}" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">ID</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">Name</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">Site</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">Type</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">Size</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-700">Impressions</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-700">Clicks</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-700">CTR%</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-700">eCPM</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-700">Revenue</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($zones as $zone)
                        @php
                            $zoneStats = $stats[$zone->site_id] ?? null;
                            $impressions = $zoneStats->total_impressions ?? 0;
                            $clicks = $zoneStats->total_clicks ?? 0;
                            $ctr = $zoneStats->ctr ?? 0;
                            $ecpm = $zoneStats->avg_ecpm ?? 0;
                            $revenue = $zoneStats->total_revenue ?? 0;
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 text-gray-600">#{{ $zone->id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $zone->name }}</div>
                                <div class="text-xs text-gray-500 capitalize">{{ $zone->placement }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-gray-900">{{ $zone->site->name ?? 'Unknown' }}</div>
                                <div class="text-xs text-gray-500">{{ $zone->site->domain ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $zone->format_key ? str_replace('_', ' ', ucwords($zone->format_key, '_')) : 'Unknown' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $zone->size_key ? str_replace('_', ' ', ucwords($zone->size_key, '_')) : '-' }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">{{ number_format($impressions) }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">{{ number_format($clicks) }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">{{ number_format($ctr, 2) }}%</td>
                            <td class="px-4 py-3 text-right text-gray-700">&euro;{{ number_format($ecpm, 2) }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">&euro;{{ number_format($revenue, 2) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <button onclick="showZone({{ $zone->id }})" class="p-1.5 rounded-lg hover:bg-blue-50 text-gray-500 hover:text-blue-600" title="View">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke="currentColor" stroke-width="1.5"/></svg>
                                    </button>
                                    <button onclick="getTag({{ $zone->id }})" class="p-1.5 rounded-lg hover:bg-purple-50 text-gray-500 hover:text-purple-600" title="Get Tag">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                    <button onclick="profileTargeting({{ $zone->id }})" class="p-1.5 rounded-lg hover:bg-orange-50 text-gray-500 hover:text-orange-600" title="Profile Targeting">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    </button>
                                    <button onclick="editZone({{ $zone->id }})" class="p-1.5 rounded-lg hover:bg-amber-50 text-gray-500 hover:text-amber-600" title="Edit">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    </button>
                                    <button onclick="deleteZone({{ $zone->id }})" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-500 hover:text-red-600" title="Delete">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    </button>
                                    <a href="{{ route('admin.adblocks.reports', $zone->id) }}" class="p-1.5 rounded-lg hover:bg-purple-50 text-gray-500 hover:text-purple-600 transition-colors" title="View Reports">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-4 py-8 text-center text-gray-500">
                                No AdBlocks found. <button onclick="openCreateWizard()" class="text-brand-600 hover:underline">Create one</button>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($zones->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $zones->links() }}
            </div>
        @endif
    </div>

    {{-- View Modal --}}
    <div id="viewModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="document.getElementById('viewModal').classList.add('hidden')"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg relative">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">AdBlock Details</h3>
                    <button onclick="document.getElementById('viewModal').classList.add('hidden')" class="p-1 rounded-lg hover:bg-gray-100 text-gray-400">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                </div>
                <div id="viewModalContent" class="p-6 max-h-[70vh] overflow-y-auto">
                    {{-- Content loaded via JS --}}
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div id="editModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="document.getElementById('editModal').classList.add('hidden')"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg relative">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Edit AdBlock</h3>
                    <button onclick="document.getElementById('editModal').classList.add('hidden')" class="p-1 rounded-lg hover:bg-gray-100 text-gray-400">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                </div>
                <form id="editForm" onsubmit="submitEditForm(event)" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Name</label>
                        <input type="text" name="name" id="edit_name" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Site</label>
                        <select name="site_id" id="edit_site_id" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                            @foreach($sites as $site)
                                <option value="{{ $site->id }}">{{ $site->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Format</label>
                            <select name="format_id" id="edit_format_id" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                @foreach($formatCategories as $cat)
                                    <option value="{{ $cat->format_key }}">{{ $cat->label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Size</label>
                            <select name="size_id" id="edit_size_id" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                <option value="">Auto</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Placement</label>
                            <select name="placement" id="edit_placement" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                <option value="header">Header</option>
                                <option value="sidebar">Sidebar</option>
                                <option value="content">Content</option>
                                <option value="footer">Footer</option>
                                <option value="overlay">Overlay</option>
                                <option value="interstitial">Interstitial</option>
                                <option value="push">Push</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Floor Price (&euro;)</label>
                            <input type="number" name="floor_price" id="edit_floor_price" step="0.0001" min="0" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                        <select name="status" id="edit_status" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                            <option value="active">Active</option>
                            <option value="paused">Paused</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <div class="border-t pt-4">
                        <div class="text-xs font-medium text-gray-600 mb-2">Targeting Settings</div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Age Range</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="target_age_min" id="edit_target_age_min" min="0" max="120" class="w-full px-2 py-1.5 rounded border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="Min">
                                    <span class="text-gray-400">-</span>
                                    <input type="number" name="target_age_max" id="edit_target_age_max" min="0" max="120" class="w-full px-2 py-1.5 rounded border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="Max">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Gender</label>
                                <select name="target_gender" id="edit_target_gender" class="w-full px-2 py-1.5 rounded border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                    <option value="">All</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="both">Both</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Target Color</label>
                                <input type="text" name="target_color" id="edit_target_color" class="w-full px-2 py-1.5 rounded border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="e.g., Blue, Red">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Frequency (views/sec)</label>
                                <input type="number" name="frequency_views" id="edit_frequency_views" min="1" class="w-full px-2 py-1.5 rounded border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="e.g., 5">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Height Range (cm)</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="target_height_min" id="edit_target_height_min" min="0" class="w-full px-2 py-1.5 rounded border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="Min">
                                    <span class="text-gray-400">-</span>
                                    <input type="number" name="target_height_max" id="edit_target_height_max" min="0" class="w-full px-2 py-1.5 rounded border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="Max">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Weight Range (kg)</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="target_weight_min" id="edit_target_weight_min" min="0" class="w-full px-2 py-1.5 rounded border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="Min">
                                    <span class="text-gray-400">-</span>
                                    <input type="number" name="target_weight_max" id="edit_target_weight_max" min="0" class="w-full px-2 py-1.5 rounded border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="Max">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Auto Reload</label>
                                <select name="auto_reload" id="edit_auto_reload" class="w-full px-2 py-1.5 rounded border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Reload Time (sec)</label>
                                <input type="number" name="reload_time" id="edit_reload_time" min="1" class="w-full px-2 py-1.5 rounded border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="e.g., 30">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Get Tag Modal --}}
    <div id="tagModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="document.getElementById('tagModal').classList.add('hidden')"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl relative">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Get Ad Tag</h3>
                    <button onclick="document.getElementById('tagModal').classList.add('hidden')" class="p-1 rounded-lg hover:bg-gray-100 text-gray-400">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <p class="text-sm text-gray-600">Copy and paste this code into your website where you want the ad to appear:</p>
                    <div class="relative">
                        <textarea id="tagCode" readonly rows="8" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm font-mono bg-gray-50 focus:outline-none"></textarea>
                        <button id="tagCopyBtn" onclick="copyTag()" class="absolute top-2 right-2 px-3 py-1 rounded-lg bg-brand-600 text-white text-xs font-medium hover:bg-brand-700 transition-colors">
                            Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Profile Targeting Modal --}}
    <div id="targetingModal" class="fixed inset-0 z-50 hidden" data-zone-id="">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="document.getElementById('targetingModal').classList.add('hidden')"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl relative max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white rounded-t-xl">
                    <h3 class="font-semibold text-gray-900">Profile Targeting</h3>
                    <button onclick="document.getElementById('targetingModal').classList.add('hidden')" class="p-1 rounded-lg hover:bg-gray-100 text-gray-400">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <p class="text-sm text-gray-600">Configure targeting settings for this AdBlock:</p>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Age Range</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" id="targeting_age_min" min="0" max="120" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="Min">
                                    <span class="text-gray-500">-</span>
                                    <input type="number" id="targeting_age_max" min="0" max="120" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="Max">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Gender</label>
                                <select id="targeting_gender" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                    <option value="">All</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="both">Both</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Target Color</label>
                                <input type="text" id="targeting_color" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="e.g., Blue, Red">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Frequency (views/sec)</label>
                                <input type="number" id="targeting_frequency" min="1" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="e.g., 5">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Height Range (cm)</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" id="targeting_height_min" min="0" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="Min">
                                    <span class="text-gray-500">-</span>
                                    <input type="number" id="targeting_height_max" min="0" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="Max">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Weight Range (kg)</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" id="targeting_weight_min" min="0" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="Min">
                                    <span class="text-gray-500">-</span>
                                    <input type="number" id="targeting_weight_max" min="0" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="Max">
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Auto Reload</label>
                                <select id="targeting_auto_reload" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Reload Time (seconds)</label>
                                <input type="number" id="targeting_reload_time" min="1" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="e.g., 30">
                            </div>
                        </div>
                        <div class="border-t pt-4 mt-4">
                            <label class="block text-xs font-medium text-gray-600 mb-2">Additional Targeting</label>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Device Types</label>
                                    <div class="flex gap-2 flex-wrap">
                                        <label class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50">
                                            <input type="checkbox" id="targeting_device_desktop" value="desktop" checked class="rounded text-brand-600">
                                            <span class="text-sm">Desktop</span>
                                        </label>
                                        <label class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50">
                                            <input type="checkbox" id="targeting_device_mobile" value="mobile" checked class="rounded text-brand-600">
                                            <span class="text-sm">Mobile</span>
                                        </label>
                                        <label class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50">
                                            <input type="checkbox" id="targeting_device_tablet" value="tablet" checked class="rounded text-brand-600">
                                            <span class="text-sm">Tablet</span>
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Countries <span class="text-gray-400">(Ctrl+click for multiple)</span></label>
                                    <select id="targeting_countries" multiple size="6" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                        <option value="AL">Albania</option>
                                        <option value="XK">Kosovo</option>
                                        <option value="MK">North Macedonia</option>
                                        <option value="ME">Montenegro</option>
                                        <option value="RS">Serbia</option>
                                        <option value="BA">Bosnia & Herzegovina</option>
                                        <option value="HR">Croatia</option>
                                        <option value="SI">Slovenia</option>
                                        <option value="IT">Italy</option>
                                        <option value="DE">Germany</option>
                                        <option value="AT">Austria</option>
                                        <option value="CH">Switzerland</option>
                                        <option value="FR">France</option>
                                        <option value="GB">United Kingdom</option>
                                        <option value="US">United States</option>
                                        <option value="CA">Canada</option>
                                        <option value="TR">Turkey</option>
                                        <option value="GR">Greece</option>
                                        <option value="BG">Bulgaria</option>
                                        <option value="RO">Romania</option>
                                    </select>
                                    <p class="text-xs text-gray-400 mt-1">Leave empty for all countries</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-2 sticky bottom-0 bg-white pb-2">
                        <button type="button" onclick="document.getElementById('targetingModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</button>
                        <button type="button" onclick="saveTargeting()" class="px-5 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm">Save Settings</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Wizard Modal --}}
    <div id="createWizardModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeCreateWizard()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl relative">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Create New AdBlock</h3>
                    <button onclick="closeCreateWizard()" class="p-1 rounded-lg hover:bg-gray-100 text-gray-400">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                </div>

                {{-- Step Indicator --}}
                <div class="flex items-center justify-center gap-2 px-6 py-4 bg-gray-50 border-b border-gray-100">
                    <div id="step1Indicator" class="flex items-center gap-2 px-3 py-1 rounded-full bg-brand-600 text-white text-xs font-medium">
                        <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-xs">1</span>
                        Select Ad
                    </div>
                    <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <div id="step2Indicator" class="flex items-center gap-2 px-3 py-1 rounded-full bg-gray-200 text-gray-600 text-xs font-medium">
                        <span class="w-5 h-5 rounded-full bg-gray-300 flex items-center justify-center text-xs">2</span>
                        AdBlock Input
                    </div>
                    <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <div id="step3Indicator" class="flex items-center gap-2 px-3 py-1 rounded-full bg-gray-200 text-gray-600 text-xs font-medium">
                        <span class="w-5 h-5 rounded-full bg-gray-300 flex items-center justify-center text-xs">3</span>
                        Get Code
                    </div>
                </div>

                <div class="p-6">
                    {{-- Step 1: Select Ad (Format) --}}
                    <div id="step1" class="space-y-4">
                        <h4 class="font-medium text-gray-900">Step 1: Select Ad Format</h4>
                        <p class="text-sm text-gray-600">Choose the type of ad format for your AdBlock:</p>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 max-h-64 overflow-y-auto">
                            @forelse($formatCategories as $cat)
                                @php
                                    $catSizes = $formats->where('format_key', $cat->format_key);
                                @endphp
                                <label class="cursor-pointer">
                                    <input type="radio" name="wizard_format" value="{{ $cat->format_key }}" class="peer hidden" onchange="selectFormat('{{ $cat->format_key }}')">
                                    <div class="p-3 rounded-lg border border-gray-200 peer-checked:border-brand-500 peer-checked:bg-brand-50 hover:bg-gray-50 transition-all">
                                        <div class="font-medium text-sm text-gray-900">{{ $cat->label }}</div>
                                        <div class="text-xs text-gray-500 mt-1">{{ $catSizes->count() }} sizes available</div>
                                    </div>
                                </label>
                            @empty
                                <div class="col-span-3 p-4 text-center text-gray-500 bg-gray-50 rounded-lg">
                                    No ad formats found. Please add ad formats first in the <a href="{{ route('admin.adformats') }}" class="text-brand-600 hover:underline">Ad Formats</a> section.
                                </div>
                            @endforelse
                        </div>
                        <div class="flex justify-end pt-4">
                            <button type="button" onclick="goToStep2()" id="step1Next" class="px-5 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" disabled>Next Step</button>
                        </div>
                    </div>

                    {{-- Step 2: AdBlock Input --}}
                    <div id="step2" class="space-y-4 hidden">
                        <h4 class="font-medium text-gray-900">Step 2: AdBlock Details</h4>
                        <p class="text-sm text-gray-600">Configure your AdBlock settings:</p>
                        <div id="wizard_error" class="hidden rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"></div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Name <span class="text-red-500">*</span></label>
                                <input type="text" id="wizard_name" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="e.g., Homepage Banner">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Site <span class="text-red-500">*</span></label>
                                <select id="wizard_site" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                    <option value="">Select Site</option>
                                    @foreach($sites as $site)
                                        <option value="{{ $site->id }}">{{ $site->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Size</label>
                                    <select id="wizard_size" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                        <option value="">Auto</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Placement <span class="text-red-500">*</span></label>
                                    <select id="wizard_placement" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                        <option value="content">Content</option>
                                        <option value="header">Header</option>
                                        <option value="sidebar">Sidebar</option>
                                        <option value="footer">Footer</option>
                                        <option value="overlay">Overlay</option>
                                        <option value="interstitial">Interstitial</option>
                                        <option value="push">Push</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Floor Price (&euro;)</label>
                                <input type="number" id="wizard_floor_price" step="0.0001" min="0" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="0.00">
                            </div>
                        </div>
                        <div class="flex justify-between pt-4">
                            <button type="button" onclick="goToStep1()" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Back</button>
                            <button type="button" onclick="createAndGoToStep3()" class="px-5 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm">Create & Get Code</button>
                        </div>
                    </div>

                    {{-- Step 3: Get Code --}}
                    <div id="step3" class="space-y-4 hidden">
                        <h4 class="font-medium text-gray-900">Step 3: Your Ad Code</h4>
                        <p class="text-sm text-gray-600">Your AdBlock has been created! Copy this code and paste it into your website:</p>
                        <div class="relative">
                            <textarea id="wizard_code" readonly rows="8" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm font-mono bg-gray-50 focus:outline-none"></textarea>
                            <button id="wizardCopyBtn" onclick="copyWizardCode()" class="absolute top-2 right-2 px-3 py-1 rounded-lg bg-brand-600 text-white text-xs font-medium hover:bg-brand-700 transition-colors">
                                Copy
                            </button>
                        </div>
                        <div class="flex justify-between pt-4">
                            <button type="button" onclick="goToStep2()" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Back</button>
                            <button type="button" onclick="finishWizard()" class="px-5 py-2 rounded-lg bg-emerald-600 text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm">Done</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let selectedFormatKey = null;
    let createdZoneId = null;
    let editingZoneId = null;

    function submitEditForm(event) {
        event.preventDefault();
        if (!editingZoneId) {
            alert('No zone selected for editing');
            return;
        }

        const form = document.getElementById('editForm');
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => { data[key] = value; });

        fetch('/admin/adblocks/' + editingZoneId, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                document.getElementById('editModal').classList.add('hidden');
                location.reload();
            } else {
                alert(response.message || 'Failed to update AdBlock');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error updating AdBlock');
        });
    }

    function showZone(id) {
        document.getElementById('viewModal').classList.remove('hidden');
        document.getElementById('viewModalContent').innerHTML = `
            <div class="flex items-center justify-center py-8">
                <svg class="w-6 h-6 animate-spin text-brand-600" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            </div>
        `;

        fetch('/admin/adblocks/' + id)
            .then(r => r.json())
            .then(data => {
                const formatKey = data.format_key || '';
                const sizeKey = data.size_key || '';
                const sizeMatch = sizeKey.match(/^(\d+)x(\d+)$/i);
                const styleAttr = sizeMatch
                    ? ` style="min-width:${sizeMatch[1]}px;min-height:${sizeMatch[2]}px;"`
                    : '';
                const embedCode = `<div id="adshqip-zone-${data.id}" data-zone-id="${data.id}"${formatKey ? ` data-format="${formatKey}"` : ''}${sizeKey ? ` data-size="${sizeKey}"` : ''}${styleAttr}></div>\n<script async src="${data.serve_url || ''}"><\/script>`;

                document.getElementById('viewModalContent').innerHTML = `
                    <div class="space-y-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-semibold text-lg">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">${data.name}</div>
                                <div class="text-sm text-gray-500">${data.site_name}</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="text-xs text-gray-400 mb-1">Status</div>
                                <div class="font-medium capitalize">${data.status}</div>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="text-xs text-gray-400 mb-1">Format</div>
                                <div class="font-medium">${data.format_name}</div>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="text-xs text-gray-400 mb-1">Size</div>
                                <div class="font-medium">${data.size_name || 'Auto'}</div>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="text-xs text-gray-400 mb-1">Placement</div>
                                <div class="font-medium capitalize">${data.placement}</div>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="text-xs text-gray-400 mb-1">Impressions</div>
                                <div class="font-medium">${parseInt(data.impressions).toLocaleString()}</div>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="text-xs text-gray-400 mb-1">Clicks</div>
                                <div class="font-medium">${parseInt(data.clicks).toLocaleString()}</div>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="text-xs text-gray-400 mb-1">CTR%</div>
                                <div class="font-medium">${data.ctr}%</div>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="text-xs text-gray-400 mb-1">eCPM</div>
                                <div class="font-medium">&euro;${data.ecpm}</div>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="text-xs text-gray-400 mb-1">Revenue</div>
                                <div class="font-medium">&euro;${parseFloat(data.revenue).toFixed(2)}</div>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="text-xs text-gray-400 mb-1">Floor Price</div>
                                <div class="font-medium">&euro;${parseFloat(data.floor_price || 0).toFixed(4)}</div>
                            </div>
                        </div>
                        <div class="border-t pt-4">
                            <div class="text-xs font-medium text-gray-600 mb-2">Targeting Settings</div>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                ${data.target_age_min || data.target_age_max ? `
                                <div class="p-2 bg-gray-50 rounded">
                                    <div class="text-xs text-gray-400">Age Range</div>
                                    <div class="font-medium">${data.target_age_min || '0'} - ${data.target_age_max || '120'}</div>
                                </div>` : ''}
                                ${data.target_gender ? `
                                <div class="p-2 bg-gray-50 rounded">
                                    <div class="text-xs text-gray-400">Gender</div>
                                    <div class="font-medium capitalize">${data.target_gender}</div>
                                </div>` : ''}
                                ${data.target_color ? `
                                <div class="p-2 bg-gray-50 rounded">
                                    <div class="text-xs text-gray-400">Target Color</div>
                                    <div class="font-medium">${data.target_color}</div>
                                </div>` : ''}
                                ${data.frequency_views ? `
                                <div class="p-2 bg-gray-50 rounded">
                                    <div class="text-xs text-gray-400">Frequency</div>
                                    <div class="font-medium">${data.frequency_views} views/sec</div>
                                </div>` : ''}
                                ${data.target_height_min || data.target_height_max ? `
                                <div class="p-2 bg-gray-50 rounded">
                                    <div class="text-xs text-gray-400">Height Range</div>
                                    <div class="font-medium">${data.target_height_min || '0'} - ${data.target_height_max || '300'} cm</div>
                                </div>` : ''}
                                ${data.target_weight_min || data.target_weight_max ? `
                                <div class="p-2 bg-gray-50 rounded">
                                    <div class="text-xs text-gray-400">Weight Range</div>
                                    <div class="font-medium">${data.target_weight_min || '0'} - ${data.target_weight_max || '200'} kg</div>
                                </div>` : ''}
                                ${data.auto_reload ? `
                                <div class="p-2 bg-gray-50 rounded">
                                    <div class="text-xs text-gray-400">Auto Reload</div>
                                    <div class="font-medium">Yes${data.reload_time ? ' (' + data.reload_time + 's)' : ''}</div>
                                </div>` : ''}
                                ${data.target_countries && data.target_countries.length ? `
                                <div class="p-2 bg-gray-50 rounded">
                                    <div class="text-xs text-gray-400">Countries</div>
                                    <div class="font-medium">${data.target_countries.map(c => c.toUpperCase()).join(', ')}</div>
                                </div>` : ''}
                                ${data.target_devices && data.target_devices.length ? `
                                <div class="p-2 bg-gray-50 rounded">
                                    <div class="text-xs text-gray-400">Devices</div>
                                    <div class="font-medium capitalize">${data.target_devices.join(', ')}</div>
                                </div>` : ''}
                            </div>
                            ${!data.target_age_min && !data.target_gender && !data.target_color && !data.frequency_views && !data.auto_reload && !(data.target_countries && data.target_countries.length) && !(data.target_devices && data.target_devices.length) ? '<div class="text-sm text-gray-500 italic">No targeting configured</div>' : ''}
                        </div>
                        <div class="border-t pt-4">
                            <div class="text-xs font-medium text-gray-600 mb-2">Embed Code</div>
                            <pre class="text-xs bg-gray-900 text-gray-100 p-3 rounded-lg overflow-x-auto"><code>${embedCode.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</code></pre>
                        </div>
                    </div>
                `;

            })
            .catch(() => {
                document.getElementById('viewModalContent').innerHTML = `
                    <div class="text-center text-red-500 py-4">Failed to load AdBlock details.</div>
                `;
            });
    }

    function editZone(id) {
        editingZoneId = id;
        document.getElementById('editModal').classList.remove('hidden');

        fetch('/admin/adblocks/' + id)
            .then(r => r.json())
            .then(data => {
                document.getElementById('edit_name').value = data.name || '';
                document.getElementById('edit_site_id').value = data.site_id || '';
                document.getElementById('edit_format_id').value = data.format_key || '';
                document.getElementById('edit_placement').value = data.placement || 'content';
                document.getElementById('edit_floor_price').value = data.floor_price || '';
                document.getElementById('edit_status').value = data.status || 'active';

                // Populate targeting fields
                document.getElementById('edit_target_age_min').value = data.target_age_min || '';
                document.getElementById('edit_target_age_max').value = data.target_age_max || '';
                document.getElementById('edit_target_gender').value = data.target_gender || '';
                document.getElementById('edit_target_color').value = data.target_color || '';
                document.getElementById('edit_frequency_views').value = data.frequency_views || '';
                document.getElementById('edit_target_height_min').value = data.target_height_min || '';
                document.getElementById('edit_target_height_max').value = data.target_height_max || '';
                document.getElementById('edit_target_weight_min').value = data.target_weight_min || '';
                document.getElementById('edit_target_weight_max').value = data.target_weight_max || '';
                document.getElementById('edit_auto_reload').value = data.auto_reload ? '1' : '0';
                document.getElementById('edit_reload_time').value = data.reload_time || '';

                // Load sizes for selected format
                if (data.format_key) {
                    loadSizesForEdit(data.format_key, data.size_key);
                }
            });
    }

    function loadSizesForEdit(formatId, selectedSizeId) {
        fetch('/admin/adblocks/sizes-by-format/' + formatId)
            .then(r => r.json())
            .then(sizes => {
                const select = document.getElementById('edit_size_id');
                select.innerHTML = '<option value="">Auto</option>';
                sizes.forEach(size => {
                    const option = document.createElement('option');
                    option.value = size.id;
                    option.textContent = size.width && size.height ? size.name : size.name;
                    if (size.id == selectedSizeId) option.selected = true;
                    select.appendChild(option);
                });
            });
    }

    document.getElementById('edit_format_id')?.addEventListener('change', function() {
        loadSizesForEdit(this.value, null);
    });

    function deleteZone(id) {
        if (!confirm('Delete this AdBlock? This action cannot be undone.')) return;

        fetch('/admin/adblocks/' + id, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                _method: 'DELETE'
            })
        })
        .then(async r => {
            const data = await r.json().catch(() => ({}));
            if (!r.ok) {
                throw new Error(data.message || 'Failed to delete AdBlock');
            }

            return data;
        })
        .then(data => {
            if (data.success) {
                location.reload();
                return;
            }

            alert(data.message || 'Failed to delete AdBlock');
        })
        .catch(err => {
            console.error(err);
            alert(err.message || 'Error deleting AdBlock');
        });
    }

    function getTag(id) {
        fetch('/admin/adblocks/' + id + '/tag')
            .then(r => r.json())
            .then(data => {
                document.getElementById('tagCode').value = data.ad_code || 'No code available';
                document.getElementById('tagModal').classList.remove('hidden');
            });
    }

    function copyTag() {
        const textarea = document.getElementById('tagCode');
        const text = textarea.value;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(() => {
                showCopyFeedback('tagCopyBtn');
            }).catch(() => {
                fallbackCopy(textarea);
            });
        } else {
            fallbackCopy(textarea);
        }
    }

    function fallbackCopy(textarea) {
        textarea.select();
        document.execCommand('copy');
        showCopyFeedback('tagCopyBtn');
    }

    function showCopyFeedback(btnId) {
        const btn = document.getElementById(btnId);
        if (!btn) return;
        const original = btn.textContent;
        btn.textContent = 'Copied!';
        btn.classList.add('bg-emerald-600');
        btn.classList.remove('bg-brand-600');
        setTimeout(() => {
            btn.textContent = original;
            btn.classList.remove('bg-emerald-600');
            btn.classList.add('bg-brand-600');
        }, 2000);
    }

    function profileTargeting(id) {
        document.getElementById('targetingModal').setAttribute('data-zone-id', id);
        document.getElementById('targetingModal').classList.remove('hidden');

        // Load current targeting settings
        fetch('/admin/adblocks/' + id)
            .then(r => r.json())
            .then(data => {
                document.getElementById('targeting_age_min').value = data.target_age_min || '';
                document.getElementById('targeting_age_max').value = data.target_age_max || '';
                document.getElementById('targeting_gender').value = data.target_gender || '';
                document.getElementById('targeting_color').value = data.target_color || '';
                document.getElementById('targeting_height_min').value = data.target_height_min || '';
                document.getElementById('targeting_height_max').value = data.target_height_max || '';
                document.getElementById('targeting_weight_min').value = data.target_weight_min || '';
                document.getElementById('targeting_weight_max').value = data.target_weight_max || '';
                document.getElementById('targeting_frequency').value = data.frequency_views || '';
                document.getElementById('targeting_auto_reload').value = data.auto_reload ? '1' : '0';
                document.getElementById('targeting_reload_time').value = data.reload_time || '';

                // Load device targeting
                const devices = data.target_devices || ['desktop', 'mobile', 'tablet'];
                document.getElementById('targeting_device_desktop').checked = devices.includes('desktop');
                document.getElementById('targeting_device_mobile').checked = devices.includes('mobile');
                document.getElementById('targeting_device_tablet').checked = devices.includes('tablet');

                // Load country targeting
                const countrySel = document.getElementById('targeting_countries');
                const countries = data.target_countries || [];
                Array.from(countrySel.options).forEach(opt => {
                    opt.selected = countries.includes(opt.value);
                });
            })
            .catch(() => {
                console.error('Failed to load targeting settings');
            });
    }

    function saveTargeting() {
        const zoneId = document.getElementById('targetingModal').getAttribute('data-zone-id');
        if (!zoneId) {
            alert('No zone selected');
            return;
        }

        // Collect selected devices
        const devices = [];
        if (document.getElementById('targeting_device_desktop').checked) devices.push('desktop');
        if (document.getElementById('targeting_device_mobile').checked) devices.push('mobile');
        if (document.getElementById('targeting_device_tablet').checked) devices.push('tablet');

        // Collect selected countries
        const countrySel = document.getElementById('targeting_countries');
        const countries = Array.from(countrySel.selectedOptions).map(opt => opt.value);

        const data = {
            target_age_min: document.getElementById('targeting_age_min').value || null,
            target_age_max: document.getElementById('targeting_age_max').value || null,
            target_gender: document.getElementById('targeting_gender').value || null,
            target_color: document.getElementById('targeting_color').value || null,
            target_height_min: document.getElementById('targeting_height_min').value || null,
            target_height_max: document.getElementById('targeting_height_max').value || null,
            target_weight_min: document.getElementById('targeting_weight_min').value || null,
            target_weight_max: document.getElementById('targeting_weight_max').value || null,
            frequency_views: document.getElementById('targeting_frequency').value || null,
            auto_reload: document.getElementById('targeting_auto_reload').value === '1',
            reload_time: document.getElementById('targeting_reload_time').value || null,
            target_countries: countries.length > 0 ? countries : null,
            target_devices: devices.length > 0 ? devices : null,
        };

        fetch('/admin/adblocks/' + zoneId + '/targeting', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                alert('Targeting settings saved successfully!');
                document.getElementById('targetingModal').classList.add('hidden');
                location.reload();
            } else {
                alert(response.message || 'Failed to save targeting settings');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error saving targeting settings');
        });
    }

    // Wizard functions
    function openCreateWizard() {
        document.getElementById('createWizardModal').classList.remove('hidden');
        goToStep1();
    }

    function closeCreateWizard() {
        document.getElementById('createWizardModal').classList.add('hidden');
        resetWizard();
    }

    function resetWizard() {
        selectedFormatKey = null;
        createdZoneId = null;
        document.querySelectorAll('input[name="wizard_format"]').forEach(el => el.checked = false);
        document.getElementById('wizard_name').value = '';
        document.getElementById('wizard_site').value = '';
        document.getElementById('wizard_size').innerHTML = '<option value="">Auto</option>';
        document.getElementById('wizard_placement').value = 'content';
        document.getElementById('wizard_floor_price').value = '';
        document.getElementById('step1Next').disabled = true;
        clearWizardValidation();
    }

    function setWizardFieldInvalid(fieldId, isInvalid) {
        const field = document.getElementById(fieldId);
        if (!field) return;

        field.classList.toggle('border-red-400', isInvalid);
        field.classList.toggle('ring-2', isInvalid);
        field.classList.toggle('ring-red-500/20', isInvalid);
    }

    function clearWizardValidation() {
        ['wizard_name', 'wizard_site'].forEach(fieldId => setWizardFieldInvalid(fieldId, false));

        const errorBox = document.getElementById('wizard_error');
        if (errorBox) {
            errorBox.textContent = '';
            errorBox.classList.add('hidden');
        }
    }

    function showWizardError(message) {
        const errorBox = document.getElementById('wizard_error');
        if (!errorBox) {
            alert(message);
            return;
        }

        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
    }

    function selectFormat(formatKey) {
        selectedFormatKey = formatKey;
        document.getElementById('step1Next').disabled = false;

        // Load sizes for selected format
        fetch('/admin/adblocks/sizes-by-format/' + formatKey)
            .then(r => r.json())
            .then(sizes => {
                const select = document.getElementById('wizard_size');
                select.innerHTML = '<option value="">Auto</option>';
                sizes.forEach(size => {
                    const option = document.createElement('option');
                    option.value = size.id;
                    option.textContent = size.name;
                    select.appendChild(option);
                });
            });
    }

    function goToStep1() {
        document.getElementById('step1').classList.remove('hidden');
        document.getElementById('step2').classList.add('hidden');
        document.getElementById('step3').classList.add('hidden');
        updateStepIndicators(1);
    }

    function goToStep2() {
        if (!selectedFormatKey) return;
        document.getElementById('step1').classList.add('hidden');
        document.getElementById('step2').classList.remove('hidden');
        document.getElementById('step3').classList.add('hidden');
        updateStepIndicators(2);
    }

    function goToStep3() {
        document.getElementById('step1').classList.add('hidden');
        document.getElementById('step2').classList.add('hidden');
        document.getElementById('step3').classList.remove('hidden');
        updateStepIndicators(3);
    }

    function updateStepIndicators(step) {
        const indicators = [
            document.getElementById('step1Indicator'),
            document.getElementById('step2Indicator'),
            document.getElementById('step3Indicator')
        ];

        indicators.forEach((ind, i) => {
            if (i + 1 === step) {
                ind.className = 'flex items-center gap-2 px-3 py-1 rounded-full bg-brand-600 text-white text-xs font-medium';
                ind.querySelector('span').className = 'w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-xs';
            } else if (i + 1 < step) {
                ind.className = 'flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-medium';
                ind.querySelector('span').className = 'w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs';
                ind.querySelector('span').innerHTML = '&#10003;';
            } else {
                ind.className = 'flex items-center gap-2 px-3 py-1 rounded-full bg-gray-200 text-gray-600 text-xs font-medium';
                ind.querySelector('span').className = 'w-5 h-5 rounded-full bg-gray-300 flex items-center justify-center text-xs';
                ind.querySelector('span').innerHTML = (i + 1).toString();
            }
        });
    }

    function createAndGoToStep3() {
        clearWizardValidation();

        const name = document.getElementById('wizard_name').value.trim();
        const siteId = document.getElementById('wizard_site').value;
        const sizeId = document.getElementById('wizard_size').value;
        const placement = document.getElementById('wizard_placement').value;
        const floorPrice = document.getElementById('wizard_floor_price').value;

        if (!selectedFormatKey) {
            showWizardError('Please select an ad format in Step 1.');
            return;
        }

        const missingFields = [];

        if (!name) {
            setWizardFieldInvalid('wizard_name', true);
            missingFields.push('AdBlock name');
        }

        if (!siteId) {
            setWizardFieldInvalid('wizard_site', true);
            missingFields.push('Site');
        }

        if (missingFields.length) {
            showWizardError('Please complete: ' + missingFields.join(', ') + '.');
            return;
        }

        document.getElementById('wizard_name').value = name;

        fetch('/admin/adblocks', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                name: name,
                site_id: siteId,
                format_id: selectedFormatKey,
                size_id: sizeId || null,
                placement: placement,
                floor_price: floorPrice || 0,
                status: 'active'
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                createdZoneId = data.zone_id;
                document.getElementById('wizard_code').value = data.ad_code;
                goToStep3();
            } else {
                showWizardError(data.message || 'Failed to create AdBlock');
            }
        })
        .catch(err => {
            showWizardError('Error creating AdBlock');
        });
    }

    function copyWizardCode() {
        const textarea = document.getElementById('wizard_code');
        const text = textarea.value;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(() => {
                showCopyFeedback('wizardCopyBtn');
            }).catch(() => {
                textarea.select();
                document.execCommand('copy');
                showCopyFeedback('wizardCopyBtn');
            });
        } else {
            textarea.select();
            document.execCommand('copy');
            showCopyFeedback('wizardCopyBtn');
        }
    }

    function finishWizard() {
        closeCreateWizard();
        location.reload();
    }
</script>
@endpush
