@extends('layouts.publisher')

@section('title', 'Websites')

@section('content')
    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-700 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-3 rounded-xl border border-red-300 bg-red-50 text-red-700 text-sm">
            <ul class="space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Websites</h1>
            <p class="text-sm text-gray-500 mt-1">Manage your websites and monitor live monetization performance.</p>
        </div>
        <button type="button" onclick="document.getElementById('addSiteModal').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm transition-colors">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            Add Website
        </button>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="p-4 border-b border-gray-100">
            <form method="GET" action="{{ route('publisher.sites') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1 max-w-md">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search by site, URL, or category..."
                           class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium text-gray-600 hover:bg-gray-200 transition-colors">Search</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-t border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Id</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Site</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Site URL</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Impression</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Clicks</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">CTR %</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">ECP</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Revenue</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sites as $site)
                        @php
                            $stat = $stats[$site->id] ?? null;
                            $impressions = (int) ($stat->total_impressions ?? 0);
                            $clicks = (int) ($stat->total_clicks ?? 0);
                            $ctr = (float) ($stat->ctr ?? 0);
                            $ecp = (float) ($stat->ecp ?? 0);
                            $revenue = (float) ($stat->total_revenue ?? 0);
                            $siteUrl = preg_match('#^https?://#i', $site->domain) ? $site->domain : 'https://' . $site->domain;
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">#{{ $site->id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $site->name }}</div>
                                <div class="text-xs text-gray-400">
                                    {{ $site->categories->pluck('name')->join(', ') ?: 'Uncategorized' }} • {{ ucfirst(str_replace('_', ' ', $site->status)) }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ $siteUrl }}" target="_blank" class="text-sm text-brand-600 hover:text-brand-700 truncate max-w-[220px] inline-block" title="{{ $siteUrl }}">
                                    {{ $siteUrl }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format($impressions) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format($clicks) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format($ctr, 2) }}%</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">&euro;{{ number_format($ecp, 2) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">&euro;{{ number_format($revenue, 2) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <button onclick="openAdblockWizard({{ $site->id }})" class="p-1.5 rounded-lg hover:bg-emerald-50 text-gray-400 hover:text-emerald-600 transition-colors" title="Add Adblock">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 5v14m7-7H5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    </button>
                                    <button onclick="editSite({{ $site->id }})" class="p-1.5 rounded-lg hover:bg-amber-50 text-gray-400 hover:text-amber-600 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    </button>
                                    <button onclick="deleteSite({{ $site->id }})" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors" title="Delete">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    <p class="text-sm text-gray-500">No websites found.</p>
                                    <button onclick="document.getElementById('addSiteModal').classList.remove('hidden')" class="text-sm text-brand-600 hover:text-brand-700 font-medium">Add your first website</button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sites->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $sites->links() }}
            </div>
        @endif
    </div>

    <div id="addSiteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Add Website</h3>
                <button onclick="document.getElementById('addSiteModal').classList.add('hidden')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('publisher.sites.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Site Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Url <span class="text-red-500">*</span></label>
                    <input type="text" name="url" required placeholder="example.com or https://example.com" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Categories</label>
                    <select name="category_ids[]" multiple size="6" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">You can select more than one category.</p>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('addSiteModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm">Create Website</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Edit Website</h3>
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
                    <label class="block text-xs font-medium text-gray-600 mb-1">Url <span class="text-red-500">*</span></label>
                    <input type="text" name="url" id="edit_url" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Categories</label>
                    <select name="category_ids[]" id="edit_category_ids" multiple size="6" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">You can select more than one category.</p>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div id="adblockWizardModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4" onclick="if(event.target===this) closeAdblockWizard()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[92vh] overflow-hidden flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Add Adblock</h3>
                    <p class="text-xs text-gray-500 mt-1">Create publisher adblocks in three quick steps.</p>
                </div>
                <button onclick="closeAdblockWizard()" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
            </div>

            <div class="px-6 pt-4 border-b border-gray-100">
                <div class="flex flex-wrap gap-2 text-xs font-semibold">
                    <button type="button" id="wizardStepIndicator1" class="px-3 py-1.5 rounded-full bg-brand-600 text-white">1. Adblock Type</button>
                    <button type="button" id="wizardStepIndicator2" class="px-3 py-1.5 rounded-full bg-gray-100 text-gray-500">2. Adblock Input</button>
                    <button type="button" id="wizardStepIndicator3" class="px-3 py-1.5 rounded-full bg-gray-100 text-gray-500">3. Get Code</button>
                </div>
                <div id="adblockWizardError" class="hidden mt-3 mb-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700"></div>
            </div>

            <div class="flex-1 overflow-y-auto px-6 py-5">
                <div id="firstStep" class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Select Web/App</label>
                            <select id="choose_type" name="choose_type" onchange="toggleWizardTargetType()" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                                <option value="web">Web</option>
                                <option value="app">App</option>
                            </select>
                        </div>
                        <div id="siteListWrapper">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Site URL</label>
                            <select id="site_list_id" name="site_list_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                                <option value="">Select site</option>
                                @foreach($allSites as $site)
                                    <option value="{{ $site->id }}">{{ $site->name }} - {{ preg_match('#^https?://#i', $site->domain) ? $site->domain : 'https://' . $site->domain }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="mobileAppWrapper" class="hidden">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Mobile App</label>
                            <select id="mobileapp_list" name="mobileapp_list" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                                <option value="">Select app</option>
                                @foreach($mobileApps as $app)
                                    <option value="{{ $app->id }}">{{ $app->app_name }} - {{ $app->app_url }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Zone Type</label>
                        <select id="adTypeSelect" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            <option value="display_web">Display Web</option>
                            <option value="special_web">Special Web</option>
                            <option value="display_video">Display Video</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Sizes</label>
                        <select id="adFormatSelect" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            <option value="">Select size...</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-2">Resolved Zone Type</label>
                        <div id="zoneTypePills" class="flex flex-wrap gap-2 text-xs"></div>
                    </div>
                </div>

                <div id="secondStep" class="hidden space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Adblock Name</label>
                            <input type="text" id="adblock_name" name="adblock_name" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Floor Price</label>
                            <input type="text" id="floor_price" name="floor_price" value="0.00" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Pass Back Tags</label>
                        <textarea id="passback" name="passback" rows="3" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"></textarea>
                    </div>

                    <div class="rounded-xl border border-gray-200">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <h4 class="text-sm font-semibold text-gray-900">Native Design Fields</h4>
                        </div>
                        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Native Image Width</label>
                                <input type="text" id="image_width" name="image_width" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Native Image Height</label>
                                <input type="text" id="image_height" name="image_height" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">HTML Template</label>
                                <textarea id="html_template" name="html_template" rows="3" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"></textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Custom CSS</label>
                                <textarea id="custom_css" name="custom_css" rows="3" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Background Color</label>
                                <input type="color" id="bg_color" name="bg_color" value="#ffffff" class="h-10 w-full rounded-lg border border-gray-200 bg-white px-2 py-1">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Sponsored Prefix</label>
                                <input type="text" id="sponsored_prefix" name="sponsored_prefix" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">CSS Path</label>
                                <input type="text" id="css_path" name="css_path" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Inline Video</label>
                                <button type="button" id="inline_video" data-enabled="0" onclick="toggleInlineVideo()" class="inline-flex h-10 min-w-24 items-center justify-center rounded-lg border border-gray-200 px-4 text-sm font-medium text-gray-600 hover:bg-gray-50">
                                    OFF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="thirdStep" class="hidden space-y-4">
                    <div class="flex flex-wrap gap-2">
                        @foreach(['js' => 'JS', 'iframe' => 'IFRAME', 'inline' => 'Inline', 'real' => 'Video Real', 'small' => 'Video Small', 'box' => 'Video Box', 'head' => 'Video Head', 'overlay' => 'Overlay', 'curl' => 'PHP'] as $tabId => $tabLabel)
                            <button type="button" id="tab-{{ $tabId }}" onclick="switchCodeTab('{{ $tabId }}')" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50">
                                {{ $tabLabel }}
                            </button>
                        @endforeach
                    </div>

                    @foreach(['js', 'iframe', 'inline', 'real', 'small', 'box', 'head', 'overlay', 'curl'] as $tabId)
                        <textarea id="{{ $tabId }}" rows="10" class="hidden w-full rounded-xl border border-gray-200 bg-gray-950 px-4 py-3 font-mono text-xs text-emerald-200 focus:outline-none"></textarea>
                    @endforeach

                    <div class="flex justify-end">
                        <button type="button" onclick="copyActiveCodeTab()" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                            Copy Code
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-gray-100 px-6 py-4">
                <button type="button" id="wizardBackButton" onclick="goToWizardStep(1)" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 hidden">
                    Back
                </button>
                <div class="ml-auto flex items-center gap-3">
                    <button type="button" onclick="closeAdblockWizard()" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                        Close
                    </button>
                    <button type="button" id="wizardNextButton" onclick="goToWizardStep(2)" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                        Continue
                    </button>
                    <button type="button" id="wizardCreateButton" onclick="submitAdblockWizard()" class="hidden rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                        Generate Code
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const adFormats = @json($adFormats);
    const displayScreens = @json($displayScreens);
    let activeCodeTab = 'js';

    function resolvedZoneType(rawFormat, adType) {
        if (adType === 'display_video' || ['instream', 'outstream', 'rewarded'].includes(rawFormat)) {
            return 'video';
        }
        if (rawFormat === 'native') {
            return 'native';
        }
        if (rawFormat === 'popunder') {
            return 'popup';
        }
        if (rawFormat === 'interstitial') {
            return 'interstitial';
        }
        if (rawFormat === 'social_bar') {
            return 'bannerbox';
        }
        if (rawFormat === 'in_page_push') {
            return 'overlay';
        }
        return 'banner';
    }

    function updateZoneTypePills() {
        const adType = document.getElementById('adTypeSelect').value;
        const formatSelect = document.getElementById('adFormatSelect');
        const option = formatSelect.options[formatSelect.selectedIndex];
        const rawFormat = formatSelect.value;
        const zoneType = rawFormat ? resolvedZoneType(rawFormat, adType) : 'banner';
        const label = option && option.textContent ? option.textContent : 'Select size to preview';

        document.getElementById('zoneTypePills').innerHTML = `
            <span class="rounded-full bg-brand-50 px-3 py-1 font-semibold text-brand-700">${adFormats[adType]?.label || 'Ad Type'}</span>
            <span class="rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-700">${label}</span>
            <span class="rounded-full bg-emerald-50 px-3 py-1 font-semibold text-emerald-700">${zoneType}</span>
        `;
    }

    function populatePublisherAdFormats() {
        const adType = document.getElementById('adTypeSelect').value;
        const formatSelect = document.getElementById('adFormatSelect');
        formatSelect.innerHTML = '<option value="">Select size...</option>';

        if (!adFormats[adType]) {
            updateZoneTypePills();
            return;
        }

        if (adType === 'display_web') {
            if (displayScreens.length) {
                displayScreens.forEach((screen) => {
                    const option = document.createElement('option');
                    option.value = screen.dimension;
                    option.textContent = screen.label;
                    option.dataset.group = adType;
                    option.dataset.screenId = screen.id;
                    formatSelect.appendChild(option);
                });
            } else {
                Object.entries(adFormats[adType].sizes).forEach(([value, label]) => {
                    const option = document.createElement('option');
                    option.value = value;
                    option.textContent = label;
                    option.dataset.group = adType;
                    formatSelect.appendChild(option);
                });
            }
        } else {
            Object.entries(adFormats[adType].sizes).forEach(([value, label]) => {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = label;
                option.dataset.group = adType;
                formatSelect.appendChild(option);
            });
        }

        if (formatSelect.options.length > 1) {
            formatSelect.selectedIndex = 1;
        }

        updateZoneTypePills();
    }

    function editSite(id) {
        fetch('/publisher/sites/' + id)
            .then(response => response.json())
            .then(data => {
                document.getElementById('edit_name').value = data.name || '';
                document.getElementById('edit_url').value = data.url || '';
                const categorySelect = document.getElementById('edit_category_ids');
                Array.from(categorySelect.options).forEach(option => {
                    option.selected = Array.isArray(data.category_ids) && data.category_ids.includes(Number(option.value));
                });
                document.getElementById('editForm').action = '/publisher/sites/' + id;
                document.getElementById('editModal').classList.remove('hidden');
            });
    }

    function deleteSite(id) {
        if (!confirm('Delete this website?')) {
            return;
        }

        fetch('/publisher/sites/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json'
            }
        }).then(() => window.location.reload());
    }

    function resetAdblockWizard() {
        document.getElementById('choose_type').value = 'web';
        document.getElementById('site_list_id').value = '';
        document.getElementById('mobileapp_list').value = '';
        document.getElementById('adTypeSelect').value = 'display_web';
        document.getElementById('adblock_name').value = '';
        document.getElementById('floor_price').value = '0.00';
        document.getElementById('passback').value = '';
        document.getElementById('image_width').value = '';
        document.getElementById('image_height').value = '';
        document.getElementById('html_template').value = '';
        document.getElementById('custom_css').value = '';
        document.getElementById('bg_color').value = '#ffffff';
        document.getElementById('sponsored_prefix').value = '';
        document.getElementById('css_path').value = '';
        document.getElementById('inline_video').dataset.enabled = '0';
        document.getElementById('inline_video').textContent = 'OFF';
        ['js', 'iframe', 'inline', 'real', 'small', 'box', 'head', 'overlay', 'curl'].forEach(id => {
            document.getElementById(id).value = '';
        });
        clearWizardError();
        toggleWizardTargetType();
        populatePublisherAdFormats();
        goToWizardStep(1);
    }

    function openAdblockWizard(siteId = null) {
        resetAdblockWizard();
        if (siteId) {
            document.getElementById('choose_type').value = 'web';
            document.getElementById('site_list_id').value = String(siteId);
            toggleWizardTargetType();
        }
        document.getElementById('adblockWizardModal').classList.remove('hidden');
    }

    function closeAdblockWizard() {
        document.getElementById('adblockWizardModal').classList.add('hidden');
    }

    function toggleWizardTargetType() {
        const isWeb = document.getElementById('choose_type').value === 'web';
        document.getElementById('siteListWrapper').classList.toggle('hidden', !isWeb);
        document.getElementById('mobileAppWrapper').classList.toggle('hidden', isWeb);
    }

    function toggleInlineVideo() {
        const button = document.getElementById('inline_video');
        const enabled = button.dataset.enabled === '1';
        button.dataset.enabled = enabled ? '0' : '1';
        button.textContent = enabled ? 'OFF' : 'ON';
        button.classList.toggle('bg-brand-600', !enabled);
        button.classList.toggle('text-white', !enabled);
        button.classList.toggle('border-brand-600', !enabled);
    }

    function showWizardError(message) {
        const errorBox = document.getElementById('adblockWizardError');
        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
    }

    function clearWizardError() {
        const errorBox = document.getElementById('adblockWizardError');
        errorBox.textContent = '';
        errorBox.classList.add('hidden');
    }

    function goToWizardStep(step) {
        clearWizardError();

        if (step === 2) {
            const chooseType = document.getElementById('choose_type').value;
            if (chooseType === 'web' && !document.getElementById('site_list_id').value) {
                showWizardError('Please select a site URL before continuing.');
                return;
            }
            if (chooseType === 'app' && !document.getElementById('mobileapp_list').value) {
                showWizardError('Please select a mobile app before continuing.');
                return;
            }
        }

        document.getElementById('firstStep').classList.toggle('hidden', step !== 1);
        document.getElementById('secondStep').classList.toggle('hidden', step !== 2);
        document.getElementById('thirdStep').classList.toggle('hidden', step !== 3);

        document.getElementById('wizardStepIndicator1').className = 'px-3 py-1.5 rounded-full ' + (step === 1 ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-500');
        document.getElementById('wizardStepIndicator2').className = 'px-3 py-1.5 rounded-full ' + (step === 2 ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-500');
        document.getElementById('wizardStepIndicator3').className = 'px-3 py-1.5 rounded-full ' + (step === 3 ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-500');

        document.getElementById('wizardBackButton').classList.toggle('hidden', step === 1);
        document.getElementById('wizardNextButton').classList.toggle('hidden', step !== 1);
        document.getElementById('wizardCreateButton').classList.toggle('hidden', step !== 2);

        if (step === 3) {
            switchCodeTab(activeCodeTab);
        }
    }

    function switchCodeTab(tabId) {
        activeCodeTab = tabId;
        ['js', 'iframe', 'inline', 'real', 'small', 'box', 'head', 'overlay', 'curl'].forEach(id => {
            document.getElementById(id).classList.toggle('hidden', id !== tabId);
            const tabButton = document.getElementById('tab-' + id);
            tabButton.className = 'rounded-lg border px-3 py-1.5 text-xs font-semibold ' + (id === tabId
                ? 'border-brand-600 bg-brand-600 text-white'
                : 'border-gray-200 text-gray-600 hover:bg-gray-50');
        });
    }

    function copyActiveCodeTab() {
        const textarea = document.getElementById(activeCodeTab);
        textarea.select();
        document.execCommand('copy');
    }

    function submitAdblockWizard() {
        clearWizardError();

        const name = document.getElementById('adblock_name').value.trim();
        if (!name) {
            showWizardError('Please enter an adblock name.');
            return;
        }

        const formatId = document.getElementById('adTypeSelect').value;
        const sizeId = document.getElementById('adFormatSelect').value;
        if (!sizeId) {
            showWizardError('Please select a size before generating the code.');
            return;
        }
        const zoneType = resolvedZoneType(sizeId, formatId);
        const placement = ['overlay', 'popup', 'interstitial'].includes(zoneType) ? zoneType : 'content';

        fetch('{{ route('publisher.adblocks.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                choose_type: document.getElementById('choose_type').value,
                site_id: document.getElementById('site_list_id').value || null,
                mobile_app_id: document.getElementById('mobileapp_list').value || null,
                name: name,
                format_id: formatId,
                size_id: sizeId,
                zone_type: zoneType,
                placement: placement,
                floor_price: document.getElementById('floor_price').value || 0,
                passback: document.getElementById('passback').value || null,
                image_width: document.getElementById('image_width').value || null,
                image_height: document.getElementById('image_height').value || null,
                html_template: document.getElementById('html_template').value || null,
                custom_css: document.getElementById('custom_css').value || null,
                bg_color: document.getElementById('bg_color').value || null,
                sponsored_prefix: document.getElementById('sponsored_prefix').value || null,
                css_path: document.getElementById('css_path').value || null,
                inline_video: document.getElementById('inline_video').dataset.enabled === '1',
                status: 'active'
            })
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Unable to create adblock.');
            }
            return data;
        })
        .then(data => {
            Object.entries(data.codes || {}).forEach(([id, value]) => {
                const element = document.getElementById(id);
                if (element) {
                    element.value = value || '';
                }
            });
            switchCodeTab('js');
            goToWizardStep(3);
        })
        .catch(error => {
            showWizardError(error.message);
        });
    }

    document.getElementById('adTypeSelect').addEventListener('change', populatePublisherAdFormats);
    document.getElementById('adFormatSelect').addEventListener('change', updateZoneTypePills);
    populatePublisherAdFormats();
</script>
@endpush
