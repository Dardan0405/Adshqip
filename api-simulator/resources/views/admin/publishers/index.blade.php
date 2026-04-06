@extends('layouts.admin')

@section('title', 'Publishers')

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
            <h1 class="text-2xl font-bold text-gray-900">Publishers</h1>
            <p class="text-sm text-gray-500 mt-1">Manage all publisher accounts across the platform.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('addPublisherModal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Add Publisher
            </button>
        </div>
    </div>

    {{-- ═══════════ STAT CARDS ═══════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        @php
            $statCards = [
                ['label' => 'Total Publishers', 'value' => number_format($totalPublishers), 'color' => 'text-gray-900', 'bg' => 'bg-gray-50', 'border' => 'border-gray-200', 'icon' => '<path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Active', 'value' => number_format($activePublishers), 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'icon' => '<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Blocked', 'value' => number_format($blockedPublishers), 'color' => 'text-red-700', 'bg' => 'bg-red-50', 'border' => 'border-red-200', 'icon' => '<path d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Total Sites', 'value' => number_format($totalSites), 'color' => 'text-blue-700', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'icon' => '<path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Total Adblocks', 'value' => number_format($totalAdblocks), 'color' => 'text-purple-700', 'bg' => 'bg-purple-50', 'border' => 'border-purple-200', 'icon' => '<path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Total Earnings', 'value' => '€' . number_format($totalEarnings, 2), 'color' => 'text-amber-700', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'icon' => '<path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/>'],
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
            {{-- Search --}}
            <form method="GET" action="{{ route('admin.publishers') }}" class="flex items-center gap-2 flex-1">
                <div class="relative flex-1 max-w-md">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, or company..."
                           class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Blocked</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="pending_verification" {{ request('status') === 'pending_verification' ? 'selected' : '' }}>Pending</option>
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
                    <a href="{{ route('admin.publishers.export', ['format' => 'csv']) }}" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">CSV</a>
                    <button onclick="exportExcel()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Excel</button>
                    <button onclick="exportPDF()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">PDF</button>
                    <button onclick="window.print()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Print</button>
                </div>
            </div>
        </div>

        {{-- ═══════════ TABLE ═══════════ --}}
        <div class="overflow-x-auto">
            <table id="publishersTable" class="w-full text-sm">
                <thead>
                    <tr class="border-t border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Publisher Name</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Number of Sites</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Total Earnings</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Created</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($publishers as $pub)
                        @php
                            $name = trim(($pub->profile->first_name ?? '') . ' ' . ($pub->profile->last_name ?? ''));
                            if (!$name) $name = $pub->email;
                            $company = $pub->profile->company_name ?? '';
                            $sites = $siteCounts[$pub->id] ?? 0;
                            $earnings = $earningsPerPublisher[$pub->id] ?? 0;
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">#{{ $pub->id }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-semibold text-xs flex-shrink-0">
                                        {{ strtoupper(substr($pub->email, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $name }}</div>
                                        @if($company)
                                            <div class="text-xs text-gray-400">{{ $company }}</div>
                                        @else
                                            <div class="text-xs text-gray-400">{{ $pub->email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $statusColors = [
                                        'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'suspended' => 'bg-red-50 text-red-700 border-red-200',
                                        'inactive' => 'bg-gray-100 text-gray-600 border-gray-200',
                                        'pending_verification' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'closed' => 'bg-gray-100 text-gray-500 border-gray-200',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $statusColors[$pub->status] ?? 'bg-gray-100 text-gray-600 border-gray-200' }}">
                                    {{ ucfirst(str_replace('_', ' ', $pub->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ number_format($sites) }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">&euro;{{ number_format($earnings, 2) }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $pub->created_at?->format('M d, Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    {{-- Show / Details --}}
                                    <button onclick="showPublisher({{ $pub->id }})" class="p-1.5 rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-600 transition-colors" title="View Details">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke="currentColor" stroke-width="1.5"/></svg>
                                    </button>
                                    {{-- Edit --}}
                                    <button onclick="editPublisher({{ $pub->id }})" class="p-1.5 rounded-lg hover:bg-amber-50 text-gray-400 hover:text-amber-600 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    </button>
                                    {{-- Login As --}}
                                    <a href="{{ route('admin.publishers.loginAs', $pub->id) }}" class="p-1.5 rounded-lg hover:bg-indigo-50 text-gray-400 hover:text-indigo-600 transition-colors" title="Login as User"
                                       onclick="return confirm('Login as {{ $pub->email }}? You will be redirected to their dashboard.')">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </a>
                                    {{-- Notification --}}
                                    <button onclick="sendNotification({{ $pub->id }}, '{{ addslashes($pub->email) }}')" class="p-1.5 rounded-lg hover:bg-purple-50 text-gray-400 hover:text-purple-600 transition-colors" title="Send Notification">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    </button>
                                    {{-- Sites Icon --}}
                                    <a href="{{ route('admin.sites', ['publisher_id' => $pub->id]) }}" class="p-1.5 rounded-lg hover:bg-emerald-50 text-gray-400 hover:text-emerald-600 transition-colors" title="View Sites">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    </a>
                                    {{-- Block / Unblock --}}
                                    @if($pub->status === 'suspended')
                                        <button onclick="unblockPublisher({{ $pub->id }})" class="p-1.5 rounded-lg hover:bg-emerald-50 text-gray-400 hover:text-emerald-600 transition-colors" title="Unblock">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                        </button>
                                    @else
                                        <button onclick="blockPublisher({{ $pub->id }})" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600 transition-colors" title="Block">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                        </button>
                                    @endif
                                    {{-- Delete --}}
                                    <button onclick="deletePublisher({{ $pub->id }})" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors" title="Delete">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    <p class="text-sm text-gray-500">No publishers found.</p>
                                    <button onclick="document.getElementById('addPublisherModal').classList.remove('hidden')" class="text-sm text-brand-600 hover:text-brand-700 font-medium">Add your first publisher</button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($publishers->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $publishers->links() }}
            </div>
        @endif
    </div>

    {{-- ═══════════ ADD PUBLISHER MODAL ═══════════ --}}
    <div id="addPublisherModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Add New Publisher</h3>
                <button onclick="document.getElementById('addPublisherModal').classList.add('hidden')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.publishers.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">First Name</label>
                        <input type="text" name="first_name" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Last Name</label>
                        <input type="text" name="last_name" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required minlength="6" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Company Name</label>
                    <input type="text" name="company_name" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                        <input type="text" name="phone" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Country</label>
                        <select name="country_code" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                            <option value="AL" selected>Albania (AL)</option>
                            <option value="XK">Kosovo (XK)</option>
                            <option value="MK">North Macedonia (MK)</option>
                            <option value="ME">Montenegro (ME)</option>
                            <option value="RS">Serbia (RS)</option>
                            <option value="BA">Bosnia & Herzegovina (BA)</option>
                            <option value="HR">Croatia (HR)</option>
                            <option value="SI">Slovenia (SI)</option>
                            <option value="BG">Bulgaria (BG)</option>
                            <option value="RO">Romania (RO)</option>
                            <option value="GR">Greece (GR)</option>
                            <option value="TR">Turkey (TR)</option>
                            <option value="US">United States (US)</option>
                            <option value="GB">United Kingdom (GB)</option>
                            <option value="DE">Germany (DE)</option>
                            <option value="IT">Italy (IT)</option>
                            <option value="CH">Switzerland (CH)</option>
                            <option value="AT">Austria (AT)</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Website URL</label>
                    <input type="url" name="website_url" placeholder="https://" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('addPublisherModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm">Create Publisher</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══════════ VIEW DETAILS MODAL ═══════════ --}}
    <div id="viewModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Publisher Details</h3>
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

    {{-- ═══════════ EDIT MODAL ═══════════ --}}
    <div id="editModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Edit Publisher</h3>
                <button onclick="document.getElementById('editModal').classList.add('hidden')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
            </div>
            <form id="editForm" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">First Name</label>
                        <input type="text" name="first_name" id="edit_first_name" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Last Name</label>
                        <input type="text" name="last_name" id="edit_last_name" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="edit_email" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">New Password <span class="text-xs text-gray-400">(leave blank to keep current)</span></label>
                    <input type="password" name="password" minlength="6" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Company Name</label>
                    <input type="text" name="company_name" id="edit_company_name" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                        <input type="text" name="phone" id="edit_phone" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Country</label>
                        <select name="country_code" id="edit_country_code" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                            <option value="AL">Albania (AL)</option>
                            <option value="XK">Kosovo (XK)</option>
                            <option value="MK">North Macedonia (MK)</option>
                            <option value="ME">Montenegro (ME)</option>
                            <option value="RS">Serbia (RS)</option>
                            <option value="BA">Bosnia & Herzegovina (BA)</option>
                            <option value="HR">Croatia (HR)</option>
                            <option value="SI">Slovenia (SI)</option>
                            <option value="BG">Bulgaria (BG)</option>
                            <option value="RO">Romania (RO)</option>
                            <option value="GR">Greece (GR)</option>
                            <option value="TR">Turkey (TR)</option>
                            <option value="US">United States (US)</option>
                            <option value="GB">United Kingdom (GB)</option>
                            <option value="DE">Germany (DE)</option>
                            <option value="IT">Italy (IT)</option>
                            <option value="CH">Switzerland (CH)</option>
                            <option value="AT">Austria (AT)</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Website URL</label>
                    <input type="url" name="website_url" id="edit_website_url" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    {{-- ═══════════ NOTIFICATION MODAL ═══════════ --}}
    <div id="notificationModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Send Notification</h3>
                <button onclick="document.getElementById('notificationModal').classList.add('hidden')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-sm text-gray-500">Send a notification to <strong id="notif_email"></strong></p>
                <input type="hidden" id="notif_user_id">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                    <select id="notif_type" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        <option value="info">Info</option>
                        <option value="success">Success</option>
                        <option value="warning">Warning</option>
                        <option value="error">Error</option>
                        <option value="payment">Payment</option>
                        <option value="campaign">Campaign</option>
                        <option value="system">System</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Subject <span class="text-red-500">*</span></label>
                    <input type="text" id="notif_subject" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Message <span class="text-red-500">*</span></label>
                    <textarea id="notif_message" rows="4" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></textarea>
                </div>
                <div id="notif_result" class="hidden text-sm rounded-lg p-3"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('notificationModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="button" id="notif_send_btn" onclick="submitNotification()" class="px-5 py-2 rounded-lg bg-purple-600 text-sm font-semibold text-white hover:bg-purple-700 shadow-sm">Send Notification</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function showPublisher(id) {
        document.getElementById('viewModal').classList.remove('hidden');
        document.getElementById('viewModalContent').innerHTML = `
            <div class="flex items-center justify-center py-8">
                <svg class="w-6 h-6 animate-spin text-brand-600" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            </div>
        `;

        fetch('/admin/publishers-manage/' + id)
            .then(r => r.json())
            .then(data => {
                const name = (data.first_name || '') + ' ' + (data.last_name || '');
                document.getElementById('viewModalContent').innerHTML = `
                    <div class="space-y-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-semibold text-lg">
                                ${data.email.substring(0, 2).toUpperCase()}
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">${name.trim() || data.email}</div>
                                <div class="text-sm text-gray-500">${data.email}</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="text-xs text-gray-400 mb-1">Status</div>
                                <div class="font-medium capitalize">${data.status}</div>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="text-xs text-gray-400 mb-1">Company</div>
                                <div class="font-medium">${data.company_name || '-'}</div>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="text-xs text-gray-400 mb-1">Sites</div>
                                <div class="font-medium">${data.site_count}</div>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="text-xs text-gray-400 mb-1">Adblocks</div>
                                <div class="font-medium">${data.adblock_count}</div>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="text-xs text-gray-400 mb-1">Total Earnings</div>
                                <div class="font-medium">&euro;${parseFloat(data.total_earnings).toFixed(2)}</div>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="text-xs text-gray-400 mb-1">Balance</div>
                                <div class="font-medium">&euro;${parseFloat(data.balance).toFixed(2)}</div>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="text-xs text-gray-400 mb-1">Phone</div>
                                <div class="font-medium">${data.phone || '-'}</div>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="text-xs text-gray-400 mb-1">Country</div>
                                <div class="font-medium">${data.country_code || '-'}</div>
                            </div>
                        </div>
                        <div class="border-t pt-4 text-sm text-gray-500">
                            <p>Created: ${data.created_at}</p>
                            <p>Last Login: ${data.last_login_at || 'Never'}</p>
                            <p>IP: ${data.last_login_ip || '-'}</p>
                        </div>
                    </div>
                `;
            })
            .catch(() => {
                document.getElementById('viewModalContent').innerHTML = `
                    <div class="text-center text-red-500 py-4">Failed to load publisher details.</div>
                `;
            });
    }

    function editPublisher(id) {
        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('editForm').action = '/admin/publishers-manage/' + id;

        fetch('/admin/publishers-manage/' + id)
            .then(r => r.json())
            .then(data => {
                document.getElementById('edit_first_name').value = data.first_name || '';
                document.getElementById('edit_last_name').value = data.last_name || '';
                document.getElementById('edit_email').value = data.email || '';
                document.getElementById('edit_company_name').value = data.company_name || '';
                document.getElementById('edit_phone').value = data.phone || '';
                document.getElementById('edit_country_code').value = data.country_code || 'AL';
                document.getElementById('edit_website_url').value = data.website_url || '';
            });
    }

    function blockPublisher(id) {
        if (!confirm('Block this publisher?')) return;
        fetch('/admin/publishers-manage/' + id + '/block', {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
            else alert(data.message || 'Failed to block publisher');
        });
    }

    function unblockPublisher(id) {
        if (!confirm('Unblock this publisher?')) return;
        fetch('/admin/publishers-manage/' + id + '/unblock', {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
            else alert(data.message || 'Failed to unblock publisher');
        });
    }

    function deletePublisher(id) {
        if (!confirm('Delete this publisher? This action cannot be undone.')) return;
        fetch('/admin/publishers-manage/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
            else alert(data.message || 'Failed to delete publisher');
        });
    }

    function sendNotification(id, email) {
        document.getElementById('notif_user_id').value = id;
        document.getElementById('notif_email').textContent = email;
        document.getElementById('notif_type').value = 'info';
        document.getElementById('notif_subject').value = '';
        document.getElementById('notif_message').value = '';
        document.getElementById('notif_result').classList.add('hidden');
        document.getElementById('notif_send_btn').disabled = false;
        document.getElementById('notificationModal').classList.remove('hidden');
    }

    function submitNotification() {
        const userId = document.getElementById('notif_user_id').value;
        const title = document.getElementById('notif_subject').value.trim();
        const message = document.getElementById('notif_message').value.trim();
        const type = document.getElementById('notif_type').value;
        const resultEl = document.getElementById('notif_result');
        const btn = document.getElementById('notif_send_btn');

        if (!title || !message) {
            resultEl.className = 'text-sm rounded-lg p-3 bg-red-50 text-red-700 border border-red-200';
            resultEl.textContent = 'Subject and message are required.';
            resultEl.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Sending...';

        fetch('/admin/publishers-manage/' + userId + '/notify', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ title, message, type })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                resultEl.className = 'text-sm rounded-lg p-3 bg-emerald-50 text-emerald-700 border border-emerald-200';
                resultEl.textContent = data.message;
                resultEl.classList.remove('hidden');
                btn.textContent = 'Sent!';
                setTimeout(() => {
                    document.getElementById('notificationModal').classList.add('hidden');
                }, 1500);
            } else {
                resultEl.className = 'text-sm rounded-lg p-3 bg-red-50 text-red-700 border border-red-200';
                resultEl.textContent = data.message || 'Failed to send notification.';
                resultEl.classList.remove('hidden');
                btn.disabled = false;
                btn.textContent = 'Send Notification';
            }
        })
        .catch(() => {
            resultEl.className = 'text-sm rounded-lg p-3 bg-red-50 text-red-700 border border-red-200';
            resultEl.textContent = 'Network error. Please try again.';
            resultEl.classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = 'Send Notification';
        });
    }

    function copyTable() {
        const table = document.getElementById('publishersTable');
        let text = '';
        for (const row of table.rows) {
            text += Array.from(row.cells).map(c => c.innerText).join('\t') + '\n';
        }
        navigator.clipboard.writeText(text).then(() => alert('Table copied to clipboard'));
    }

    function exportExcel() {
        const table = document.getElementById('publishersTable');
        let html = '<table>';
        for (const row of table.rows) {
            html += '<tr>' + Array.from(row.cells).map(c => '<td>' + c.innerText + '</td>').join('') + '</tr>';
        }
        html += '</table>';

        const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'publishers.xls';
        a.click();
    }

    function exportPDF() {
        window.print();
    }
</script>
@endpush
