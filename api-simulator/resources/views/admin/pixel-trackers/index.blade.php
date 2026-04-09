@extends('layouts.admin')

@section('title', 'Pixel Tracking')

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
            <h1 class="text-2xl font-bold text-gray-900">Pixel Tracking</h1>
            <p class="text-sm text-gray-500 mt-1">Manage pixel trackers for advertiser conversion and event tracking.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('addPixelModal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Create Pixel Tracker
            </button>
        </div>
    </div>

    {{-- ═══════════ STAT CARDS ═══════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
        @php
            $statCards = [
                ['label' => 'Total Pixels', 'value' => number_format($totalPixels), 'color' => 'text-gray-900', 'bg' => 'bg-gray-50', 'border' => 'border-gray-200', 'icon' => '<path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'HTML Pixel', 'value' => number_format($htmlCount), 'color' => 'text-blue-700', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'icon' => '<path d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'],
                ['label' => 'S2S Pixel', 'value' => number_format($s2sCount), 'color' => 'text-purple-700', 'bg' => 'bg-purple-50', 'border' => 'border-purple-200', 'icon' => '<path d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Mobile S2S', 'value' => number_format($mobileS2sCount), 'color' => 'text-amber-700', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'icon' => '<path d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Active', 'value' => number_format($activeCount), 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'icon' => '<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
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
            <form method="GET" action="{{ route('admin.pixel-trackers') }}" class="flex items-center gap-2 flex-1 flex-wrap">
                <div class="relative flex-1 max-w-md">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, pixel code, advertiser..."
                           class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <select name="type" onchange="this.form.submit()" class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Types</option>
                    <option value="html_pixel" {{ request('type') === 'html_pixel' ? 'selected' : '' }}>HTML Pixel</option>
                    <option value="s2s_pixel" {{ request('type') === 's2s_pixel' ? 'selected' : '' }}>S2S Pixel</option>
                    <option value="mobile_s2s" {{ request('type') === 'mobile_s2s' ? 'selected' : '' }}>Mobile S2S</option>
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

            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Export
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-44 bg-white rounded-xl border border-gray-200 shadow-lg py-1 z-50" style="display:none;">
                    <button onclick="copyTable()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Copy</button>
                    <a href="{{ route('admin.pixel-trackers.export', request()->all()) }}" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">CSV</a>
                    <button onclick="window.print()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Print</button>
                </div>
            </div>
        </div>

        {{-- ═══════════ TABLE ═══════════ --}}
        <div class="overflow-x-auto">
            <table id="pixelTable" class="w-full text-sm">
                <thead>
                    <tr class="border-t border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Name</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Type</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Advertiser</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Category</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pixels as $pixel)
                        @php
                            $advertiserName = trim(($pixel->advertiser->profile->first_name ?? '') . ' ' . ($pixel->advertiser->profile->last_name ?? ''));
                            if (!$advertiserName) $advertiserName = $pixel->advertiser->email ?? 'N/A';
                            $company = $pixel->advertiser->profile->company_name ?? '';

                            $typeLabels = [
                                'html_pixel' => ['label' => 'HTML Pixel', 'bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200'],
                                's2s_pixel' => ['label' => 'S2S Pixel', 'bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'border' => 'border-purple-200'],
                                'mobile_s2s' => ['label' => 'Mobile S2S', 'bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200'],
                                'conversion' => ['label' => 'Conversion', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200'],
                                'pageview' => ['label' => 'Pageview', 'bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'border' => 'border-gray-200'],
                                'event' => ['label' => 'Event', 'bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200'],
                                'custom' => ['label' => 'Custom', 'bg' => 'bg-pink-50', 'text' => 'text-pink-700', 'border' => 'border-pink-200'],
                            ];
                            $typeInfo = $typeLabels[$pixel->type] ?? ['label' => ucfirst($pixel->type), 'bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'border' => 'border-gray-200'];
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">#{{ $pixel->id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $pixel->name }}</div>
                                <div class="text-xs text-gray-400 font-mono">{{ $pixel->pixel_code }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $typeInfo['bg'] }} {{ $typeInfo['text'] }} border {{ $typeInfo['border'] }}">
                                    {{ $typeInfo['label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-semibold text-xs flex-shrink-0">
                                        {{ strtoupper(substr($pixel->advertiser->email ?? 'N', 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $advertiserName }}</div>
                                        @if($company)
                                            <div class="text-xs text-gray-400">{{ $company }}</div>
                                        @else
                                            <div class="text-xs text-gray-400">{{ $pixel->advertiser->email ?? '' }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($pixel->category)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                        {{ $pixel->category }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    {{-- Edit --}}
                                    <button onclick="openEditModal({{ $pixel->id }})" class="p-1.5 rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-600 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                    {{-- Code --}}
                                    <button onclick="openCodeModal({{ $pixel->id }})" class="p-1.5 rounded-lg hover:bg-purple-50 text-gray-400 hover:text-purple-600 transition-colors" title="View Code">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                    {{-- Link to Campaign --}}
                                    <button onclick="openLinkModal({{ $pixel->id }}, {{ $pixel->advertiser_id }})" class="p-1.5 rounded-lg hover:bg-emerald-50 text-gray-400 hover:text-emerald-600 transition-colors" title="Link to Campaign">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                    {{-- Delete --}}
                                    <button onclick="deletePixel({{ $pixel->id }})" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors" title="Delete">
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
                                    <p class="text-sm text-gray-500">No pixel trackers found.</p>
                                    <button onclick="document.getElementById('addPixelModal').classList.remove('hidden')" class="text-sm text-brand-600 hover:text-brand-700 font-medium">Create your first pixel tracker</button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pixels->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $pixels->links() }}
            </div>
        @endif
    </div>

    {{-- ═══════════ ADD PIXEL MODAL ═══════════ --}}
    <div id="addPixelModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Create Pixel Tracker</h3>
                <button onclick="document.getElementById('addPixelModal').classList.add('hidden')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.pixel-trackers.store') }}" class="p-6 space-y-4">
                @csrf
                @if($errors->any())
                    <div class="p-3 rounded-lg border border-red-300 bg-red-50 text-red-700 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @include('admin.pixel-trackers._form', ['prefix' => 'add', 'isEdit' => false])
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('addPixelModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm">Create Pixel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══════════ EDIT PIXEL MODAL ═══════════ --}}
    <div id="editPixelModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Edit Pixel Tracker</h3>
                <button onclick="document.getElementById('editPixelModal').classList.add('hidden')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
            </div>
            <form id="editPixelForm" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div id="editFormErrors" class="hidden p-3 rounded-lg border border-red-300 bg-red-50 text-red-700 text-sm"></div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Advertiser <span class="text-red-500">*</span></label>
                    <select name="advertiser_id" id="edit_advertiser_id" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        <option value="">Select Advertiser</option>
                        @foreach($advertisers as $adv)
                            @php
                                $name = trim(($adv->profile->first_name ?? '') . ' ' . ($adv->profile->last_name ?? ''));
                                if (!$name) $name = $adv->email;
                            @endphp
                            <option value="{{ $adv->id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Pixel Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="edit_name" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-2">Type <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="flex items-center gap-2 px-3 py-2.5 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 transition-colors">
                            <input type="radio" name="type" value="html_pixel" id="edit_type_html" class="w-4 h-4 text-brand-600 border-gray-300 focus:ring-brand-500">
                            <span class="text-sm font-medium text-gray-700">HTML Pixel</span>
                        </label>
                        <label class="flex items-center gap-2 px-3 py-2.5 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 transition-colors">
                            <input type="radio" name="type" value="s2s_pixel" id="edit_type_s2s" class="w-4 h-4 text-brand-600 border-gray-300 focus:ring-brand-500">
                            <span class="text-sm font-medium text-gray-700">S2S Pixel</span>
                        </label>
                        <label class="flex items-center gap-2 px-3 py-2.5 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 transition-colors">
                            <input type="radio" name="type" value="mobile_s2s" id="edit_type_mobile" class="w-4 h-4 text-brand-600 border-gray-300 focus:ring-brand-500">
                            <span class="text-sm font-medium text-gray-700">Mobile S2S</span>
                        </label>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Pixel Goal</label>
                        <select name="pixel_goal" id="edit_pixel_goal" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                            <option value="">Select Goal</option>
                            <option value="conversion">Conversion</option>
                            <option value="lead">Lead</option>
                            <option value="purchase">Purchase</option>
                            <option value="signup">Sign Up</option>
                            <option value="pageview">Page View</option>
                            <option value="add_to_cart">Add to Cart</option>
                            <option value="install">App Install</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="edit_status" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                            <option value="active">Active</option>
                            <option value="paused">Paused</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
                    <input type="text" name="category" id="edit_category" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Append Code</label>
                    <textarea name="append_code" id="edit_append_code" rows="4" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('editPixelModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══════════ CODE MODAL ═══════════ --}}
    <div id="codeModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Pixel Code</h3>
                <button onclick="document.getElementById('codeModal').classList.add('hidden')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-medium text-gray-600">Pixel Name</label>
                        <span id="code_type_badge" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"></span>
                    </div>
                    <div id="code_pixel_name" class="text-sm font-medium text-gray-900"></div>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-600">Pixel Code</label>
                    <div class="mt-1 px-3 py-2 rounded-lg bg-gray-50 border border-gray-200 font-mono text-sm text-gray-700" id="code_pixel_code"></div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-medium text-gray-600">Tracking URL</label>
                        <button onclick="copyToClipboard(document.getElementById('code_tracking_url').textContent)" class="text-xs text-brand-600 hover:text-brand-700 font-medium">Copy URL</button>
                    </div>
                    <div class="mt-1 px-3 py-2 rounded-lg bg-gray-50 border border-gray-200 font-mono text-xs text-gray-700 break-all" id="code_tracking_url"></div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-medium text-gray-600" id="code_embed_label">Embed Code</label>
                        <button onclick="copyToClipboard(document.getElementById('code_html_code').textContent)" class="text-xs text-brand-600 hover:text-brand-700 font-medium">Copy Code</button>
                    </div>
                    <pre class="mt-1 px-3 py-2 rounded-lg bg-gray-900 text-green-400 font-mono text-xs overflow-x-auto whitespace-pre-wrap" id="code_html_code"></pre>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
                <button onclick="document.getElementById('codeModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Close</button>
            </div>
        </div>
    </div>

    {{-- ═══════════ LINK TO CAMPAIGN MODAL ═══════════ --}}
    <div id="linkModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Link to Campaign</h3>
                <button onclick="document.getElementById('linkModal').classList.add('hidden')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-sm text-gray-500">Select a campaign to attach this pixel tracker to. The pixel will fire for conversions on the selected campaign.</p>
                <div id="linkCampaignsList" class="space-y-2 max-h-64 overflow-y-auto">
                    <div class="text-sm text-gray-400 text-center py-4">Loading campaigns...</div>
                </div>
                <div id="linkSuccessMsg" class="hidden p-3 rounded-lg border border-emerald-300 bg-emerald-50 text-emerald-700 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <span>Pixel linked successfully!</span>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
                <button onclick="document.getElementById('linkModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Close</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ─── EDIT ────────────────────────────────────────────
    function openEditModal(id) {
        fetch(`/admin/pixel-trackers/${id}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            document.getElementById('editPixelForm').action = `/admin/pixel-trackers/${id}`;
            document.getElementById('edit_advertiser_id').value = data.advertiser_id;
            document.getElementById('edit_name').value = data.name;
            document.getElementById('edit_pixel_goal').value = data.pixel_goal || '';
            document.getElementById('edit_status').value = data.status || 'active';
            document.getElementById('edit_category').value = data.category || '';
            document.getElementById('edit_append_code').value = data.append_code || '';

            // Set type radio
            const typeMap = { html_pixel: 'edit_type_html', s2s_pixel: 'edit_type_s2s', mobile_s2s: 'edit_type_mobile' };
            const radioId = typeMap[data.type];
            if (radioId) document.getElementById(radioId).checked = true;

            document.getElementById('editFormErrors').classList.add('hidden');
            document.getElementById('editPixelModal').classList.remove('hidden');
        });
    }

    document.getElementById('editPixelForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: formData
        }).then(r => r.json()).then(data => {
            if (data.success) {
                location.reload();
            } else {
                const errDiv = document.getElementById('editFormErrors');
                errDiv.textContent = data.message || 'Error updating pixel.';
                errDiv.classList.remove('hidden');
            }
        });
    });

    // ─── CODE ────────────────────────────────────────────
    function openCodeModal(id) {
        fetch(`/admin/pixel-trackers/${id}/code`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            document.getElementById('code_pixel_name').textContent = data.name;
            document.getElementById('code_pixel_code').textContent = data.pixel_code;
            document.getElementById('code_tracking_url').textContent = data.tracking_url;
            document.getElementById('code_html_code').textContent = data.html_code;

            const typeColors = {
                html_pixel: { bg: 'bg-blue-50', text: 'text-blue-700', border: 'border-blue-200', label: 'HTML Pixel' },
                s2s_pixel: { bg: 'bg-purple-50', text: 'text-purple-700', border: 'border-purple-200', label: 'S2S Pixel' },
                mobile_s2s: { bg: 'bg-amber-50', text: 'text-amber-700', border: 'border-amber-200', label: 'Mobile S2S' },
            };
            const tc = typeColors[data.type] || { bg: 'bg-gray-50', text: 'text-gray-700', border: 'border-gray-200', label: data.type };
            const badge = document.getElementById('code_type_badge');
            badge.className = `inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold ${tc.bg} ${tc.text} border ${tc.border}`;
            badge.textContent = tc.label;

            const embedLabel = document.getElementById('code_embed_label');
            embedLabel.textContent = data.type === 'html_pixel' ? 'Embed Code (HTML)' : 'Postback URL';

            document.getElementById('codeModal').classList.remove('hidden');
        });
    }

    // ─── LINK TO CAMPAIGN ────────────────────────────────
    let currentLinkPixelId = null;

    function openLinkModal(pixelId, advertiserId) {
        currentLinkPixelId = pixelId;
        const list = document.getElementById('linkCampaignsList');
        const successMsg = document.getElementById('linkSuccessMsg');
        successMsg.classList.add('hidden');
        list.innerHTML = '<div class="text-sm text-gray-400 text-center py-4">Loading campaigns...</div>';
        document.getElementById('linkModal').classList.remove('hidden');

        fetch(`/admin/pixel-trackers/campaigns/${advertiserId}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(campaigns => {
            if (campaigns.length === 0) {
                list.innerHTML = '<div class="text-sm text-gray-400 text-center py-4">No campaigns found for this advertiser.</div>';
                return;
            }
            let html = '';
            campaigns.forEach(c => {
                const linked = c.has_pixel ? '<span class="text-xs text-emerald-600 font-medium ml-auto">Linked</span>' : '';
                html += `<button onclick="linkToCampaign(${c.id}, '${c.type}')" class="flex items-center gap-3 w-full px-4 py-3 rounded-lg border border-gray-200 hover:border-brand-300 hover:bg-brand-50/50 text-left transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-brand-100 flex items-center justify-center text-gray-500 group-hover:text-brand-600 flex-shrink-0">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900 truncate">${c.label}</div>
                        <div class="text-xs text-gray-400">${c.type === 'direct' ? 'Direct Campaign' : 'Network Campaign'}</div>
                    </div>
                    ${linked}
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-brand-500 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>`;
            });
            list.innerHTML = html;
        });
    }

    function linkToCampaign(campaignId, campaignType) {
        fetch(`/admin/pixel-trackers/${currentLinkPixelId}/link`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ campaign_id: campaignId, campaign_type: campaignType })
        }).then(r => r.json()).then(data => {
            if (data.success) {
                document.getElementById('linkSuccessMsg').classList.remove('hidden');
                setTimeout(() => location.reload(), 1200);
            } else {
                alert(data.message || 'Error linking pixel.');
            }
        });
    }

    // ─── DELETE ──────────────────────────────────────────
    function deletePixel(id) {
        if (!confirm('Are you sure you want to delete this pixel tracker?')) return;
        fetch(`/admin/pixel-trackers/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => {
            if (data.success) location.reload();
            else alert(data.message || 'Error deleting pixel tracker.');
        });
    }

    // ─── COPY HELPERS ────────────────────────────────────
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            // Brief visual feedback would go here
            alert('Copied to clipboard!');
        });
    }

    function copyTable() {
        const table = document.getElementById('pixelTable');
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

    @if($errors->any())
        document.getElementById('addPixelModal').classList.remove('hidden');
    @endif
</script>
@endpush
