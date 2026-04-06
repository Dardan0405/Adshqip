@extends('layouts.admin')

@section('title', 'Sites')

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
            <h1 class="text-2xl font-bold text-gray-900">Sites</h1>
            <p class="text-sm text-gray-500 mt-1">Manage all publisher sites across the platform.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('addSiteModal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Add Site
            </button>
        </div>
    </div>

    {{-- Search & Filter Bar --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4">
            {{-- Search --}}
            <form method="GET" action="{{ route('admin.sites') }}" class="flex items-center gap-2 flex-1">
                <div class="relative flex-1 max-w-md">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by site name or domain..."
                           class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="pending_review" {{ request('status') === 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
                @if(request('publisher_id'))
                    <input type="hidden" name="publisher_id" value="{{ request('publisher_id') }}">
                @endif
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium text-gray-600 hover:bg-gray-200 transition-colors">Search</button>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table id="sitesTable" class="w-full text-sm">
                <thead>
                    <tr class="border-t border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Site Name</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Publisher</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Site URL</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Impressions</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Clicks</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">CTR%</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Revenue</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sites as $site)
                        @php
                            $stat = $stats[$site->id] ?? null;
                            $impressions = $stat->total_impressions ?? 0;
                            $clicks = $stat->total_clicks ?? 0;
                            $ctr = $stat->ctr ?? 0;
                            $revenue = $stat->total_revenue ?? 0;
                            $publisher = $site->publisher;
                            $publisherName = $publisher ? 
                                trim(($publisher->profile->first_name ?? '') . ' ' . ($publisher->profile->last_name ?? '')) ?: $publisher->email 
                                : 'Unknown';
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">#{{ $site->id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $site->name }}</div>
                                <div class="text-xs text-gray-400">{{ ucfirst(str_replace('_', ' ', $site->status)) }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-semibold text-[10px]">
                                        {{ strtoupper(substr($publisher->email ?? 'U', 0, 1)) }}
                                    </div>
                                    <span class="text-sm text-gray-700">{{ $publisherName }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <a href="https://{{ $site->domain }}" target="_blank" class="text-sm text-brand-600 hover:text-brand-700 truncate max-w-[200px] inline-block" title="{{ $site->domain }}">
                                    {{ $site->domain }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format($impressions) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format($clicks) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format($ctr, 2) }}%</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">&euro;{{ number_format($revenue, 2) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    {{-- View --}}
                                    <button onclick="showSite({{ $site->id }})" class="p-1.5 rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-600 transition-colors" title="View Details">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke="currentColor" stroke-width="1.5"/></svg>
                                    </button>
                                    {{-- Edit --}}
                                    <button onclick="editSite({{ $site->id }})" class="p-1.5 rounded-lg hover:bg-amber-50 text-gray-400 hover:text-amber-600 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    </button>
                                    {{-- Delete --}}
                                    <button onclick="deleteSite({{ $site->id }})" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors" title="Delete">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                    {{-- Reports (placeholder) --}}
                                    <button class="p-1.5 rounded-lg hover:bg-purple-50 text-gray-400 hover:text-purple-600 transition-colors" title="Reports (Coming Soon)">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    </button>
                                    {{-- Adblock (placeholder) --}}
                                    <button class="p-1.5 rounded-lg hover:bg-emerald-50 text-gray-400 hover:text-emerald-600 transition-colors" title="Adblock (Coming Soon)">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    <p class="text-sm text-gray-500">No sites found.</p>
                                    <button onclick="document.getElementById('addSiteModal').classList.remove('hidden')" class="text-sm text-brand-600 hover:text-brand-700 font-medium">Add your first site</button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($sites->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $sites->links() }}
            </div>
        @endif
    </div>

    {{-- Add Site Modal --}}
    <div id="addSiteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Add New Site</h3>
                <button onclick="document.getElementById('addSiteModal').classList.add('hidden')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.sites.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Site Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Domain (without https://) <span class="text-red-500">*</span></label>
                    <input type="text" name="domain" required placeholder="example.com" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Publisher <span class="text-red-500">*</span></label>
                    <select name="publisher_id" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        <option value="">Select Publisher</option>
                        @foreach($publishers as $pub)
                            @php
                                $pubName = trim(($pub->profile->first_name ?? '') . ' ' . ($pub->profile->last_name ?? '')) ?: $pub->email;
                            @endphp
                            <option value="{{ $pub->id }}">{{ $pubName }} ({{ $pub->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
                        <input type="text" name="category" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Language</label>
                        <select name="language" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                            <option value="sq">Albanian (sq)</option>
                            <option value="en">English (en)</option>
                            <option value="de">German (de)</option>
                            <option value="it">Italian (it)</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        <option value="pending_review">Pending Review</option>
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('addSiteModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm">Create Site</button>
                </div>
            </form>
        </div>
    </div>

    {{-- View Site Modal --}}
    <div id="viewModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Site Details</h3>
                <button onclick="document.getElementById('viewModal').classList.add('hidden')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
            </div>
            <div id="viewModalContent" class="p-6">
                <div class="flex items-center justify-center py-8">
                    <svg class="w-6 h-6 animate-spin text-brand-600" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Site Modal --}}
    <div id="editModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Edit Site</h3>
                <button onclick="document.getElementById('editModal').classList.add('hidden')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
            </div>
            <form id="editForm" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Site Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="edit_name" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Domain (Website URL) <span class="text-red-500">*</span></label>
                    <input type="text" name="domain" id="edit_domain" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm bg-gray-50 cursor-not-allowed" readonly title="Website URL cannot be changed">
                    <p class="text-xs text-gray-400 mt-1">Website URL cannot be changed after creation.</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Publisher <span class="text-red-500">*</span></label>
                    <select name="publisher_id" id="edit_publisher_id" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        @foreach($publishers as $pub)
                            @php
                                $pubName = trim(($pub->profile->first_name ?? '') . ' ' . ($pub->profile->last_name ?? '')) ?: $pub->email;
                            @endphp
                            <option value="{{ $pub->id }}">{{ $pubName }} ({{ $pub->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                    <textarea name="description" id="edit_description" rows="2" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
                        <input type="text" name="category" id="edit_category" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Language</label>
                        <select name="language" id="edit_language" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                            <option value="sq">Albanian (sq)</option>
                            <option value="en">English (en)</option>
                            <option value="de">German (de)</option>
                            <option value="it">Italian (it)</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                    <select name="status" id="edit_status" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        <option value="pending_review">Pending Review</option>
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function showSite(id) {
        document.getElementById('viewModal').classList.remove('hidden');
        document.getElementById('viewModalContent').innerHTML = `
            <div class="flex items-center justify-center py-8">
                <svg class="w-6 h-6 animate-spin text-brand-600" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            </div>
        `;

        fetch('/admin/sites/' + id)
            .then(r => r.json())
            .then(data => {
                document.getElementById('viewModalContent').innerHTML = `
                    <div class="space-y-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-semibold text-lg">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none"><path d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">${data.name}</div>
                                <div class="text-sm text-gray-500">${data.domain}</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="text-xs text-gray-400 mb-1">Status</div>
                                <div class="font-medium capitalize">${data.status.replace('_', ' ')}</div>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="text-xs text-gray-400 mb-1">Publisher</div>
                                <div class="font-medium">${data.publisher_name}</div>
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
                                <div class="text-xs text-gray-400 mb-1">Revenue</div>
                                <div class="font-medium">&euro;${parseFloat(data.revenue).toFixed(2)}</div>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="text-xs text-gray-400 mb-1">Category</div>
                                <div class="font-medium">${data.category || '-'}</div>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="text-xs text-gray-400 mb-1">Language</div>
                                <div class="font-medium">${data.language}</div>
                            </div>
                        </div>
                        <div class="border-t pt-4 text-sm text-gray-500">
                            <p>Created: ${data.created_at}</p>
                            <p>Description: ${data.description || '-'}</p>
                        </div>
                    </div>
                `;
            })
            .catch(() => {
                document.getElementById('viewModalContent').innerHTML = `
                    <div class="text-center text-red-500 py-4">Failed to load site details.</div>
                `;
            });
    }

    function editSite(id) {
        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('editForm').action = '/admin/sites/' + id;

        fetch('/admin/sites/' + id)
            .then(r => r.json())
            .then(data => {
                document.getElementById('edit_name').value = data.name || '';
                document.getElementById('edit_domain').value = data.domain || '';
                document.getElementById('edit_publisher_id').value = data.publisher_id || '';
                document.getElementById('edit_description').value = data.description || '';
                document.getElementById('edit_category').value = data.category || '';
                document.getElementById('edit_language').value = data.language || 'sq';
                document.getElementById('edit_status').value = data.status || 'pending_review';
            });
    }

    function deleteSite(id) {
        if (!confirm('Delete this site? This action cannot be undone.')) return;
        fetch('/admin/sites/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
            else alert(data.message || 'Failed to delete site');
        });
    }
</script>
@endpush
