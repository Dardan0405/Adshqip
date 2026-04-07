@extends('layouts.admin')

@section('title', 'Country-wise Bidding')

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
            <h1 class="text-2xl font-bold text-gray-900">Country-wise Bidding</h1>
            <p class="text-sm text-gray-500 mt-1">Manage country-specific bidding rules for advertisers and campaigns.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('addBiddingModal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Add Bidding Rule
            </button>
        </div>
    </div>

    {{-- ═══════════ STAT CARDS ═══════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        @php
            $statCards = [
                ['label' => 'Total Rules', 'value' => number_format($totalBiddings), 'color' => 'text-gray-900', 'bg' => 'bg-gray-50', 'border' => 'border-gray-200', 'icon' => '<path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Advertisers', 'value' => number_format($totalAdvertisers), 'color' => 'text-blue-700', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'icon' => '<path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Avg Bid Value', 'value' => '$' . number_format($avgBidValue, 2), 'color' => 'text-amber-700', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'icon' => '<path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/>'],
                ['label' => 'CPC Rules', 'value' => number_format($cpcCount), 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'icon' => '<path d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'CPM Rules', 'value' => number_format($cpmCount), 'color' => 'text-purple-700', 'bg' => 'bg-purple-50', 'border' => 'border-purple-200', 'icon' => '<path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke="currentColor" stroke-width="1.5"/>'],
                ['label' => 'CPA Rules', 'value' => number_format($cpaCount), 'color' => 'text-indigo-700', 'bg' => 'bg-indigo-50', 'border' => 'border-indigo-200', 'icon' => '<path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
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
            <form method="GET" action="{{ route('admin.country-wise-bidding') }}" class="flex items-center gap-2 flex-1 flex-wrap">
                <div class="relative flex-1 max-w-md">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by advertiser, campaign, country..."
                           class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <select name="type" onchange="this.form.submit()" class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Types</option>
                    <option value="CPC" {{ request('type') === 'CPC' ? 'selected' : '' }}>CPC</option>
                    <option value="CPM" {{ request('type') === 'CPM' ? 'selected' : '' }}>CPM</option>
                    <option value="CPA" {{ request('type') === 'CPA' ? 'selected' : '' }}>CPA</option>
                    <option value="CPV" {{ request('type') === 'CPV' ? 'selected' : '' }}>CPV</option>
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
                    <a href="{{ route('admin.country-wise-bidding.export', request()->all()) }}" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">CSV</a>
                    <button onclick="exportExcel()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Excel</button>
                    <button onclick="window.print()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Print</button>
                </div>
            </div>
        </div>

        {{-- ═══════════ TABLE ═══════════ --}}
        <div class="overflow-x-auto">
            <table id="biddingTable" class="w-full text-sm">
                <thead>
                    <tr class="border-t border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Advertiser</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Campaign</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Type</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Country</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Bid Value</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($biddings as $bidding)
                        @php
                            $advertiserName = trim(($bidding->advertiser->profile->first_name ?? '') . ' ' . ($bidding->advertiser->profile->last_name ?? ''));
                            if (!$advertiserName) $advertiserName = $bidding->advertiser->email;
                            $company = $bidding->advertiser->profile->company_name ?? '';
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">#{{ $bidding->id }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-semibold text-xs flex-shrink-0">
                                        {{ strtoupper(substr($bidding->advertiser->email, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $advertiserName }}</div>
                                        @if($company)
                                            <div class="text-xs text-gray-400">{{ $company }}</div>
                                        @else
                                            <div class="text-xs text-gray-400">{{ $bidding->advertiser->email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($bidding->campaign_id)
                                    <div class="font-medium text-gray-900">
                                        @if($bidding->campaign_type === 'direct' && $bidding->directCampaign)
                                            {{ $bidding->directCampaign->name }}
                                        @elseif($bidding->campaign)
                                            {{ $bidding->campaign->name }}
                                        @else
                                            Campaign #{{ $bidding->campaign_id }}
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        #{{ $bidding->campaign_id }}{{ ($bidding->campaign_type === 'direct') ? ' • Direct' : '' }}
                                    </div>
                                @else
                                    <span class="text-sm text-gray-600">All Campaigns</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm font-medium text-gray-900">{{ $bidding->type }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-semibold text-gray-900">{{ $bidding->country_code }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-gray-900">${{ number_format($bidding->bid_value, 2) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    {{-- Edit --}}
                                    <button onclick="editBidding({{ $bidding->id }})" class="p-1.5 rounded-lg hover:bg-amber-50 text-gray-400 hover:text-amber-600 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    </button>
                                    {{-- Delete --}}
                                    <button onclick="deleteBidding({{ $bidding->id }})" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors" title="Delete">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    <p class="text-sm text-gray-500">No bidding rules found.</p>
                                    <button onclick="document.getElementById('addBiddingModal').classList.remove('hidden')" class="text-sm text-brand-600 hover:text-brand-700 font-medium">Add your first bidding rule</button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($biddings->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $biddings->links() }}
            </div>
        @endif
    </div>

    {{-- ═══════════ ADD BIDDING MODAL ═══════════ --}}
    <div id="addBiddingModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Add Country-wise Bidding Rule</h3>
                <button onclick="document.getElementById('addBiddingModal').classList.add('hidden')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.country-wise-bidding.store') }}" class="p-6 space-y-4">
                @csrf

                {{-- Display validation errors --}}
                @if($errors->any())
                    <div class="p-3 rounded-lg border border-red-300 bg-red-50 text-red-700 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Advertiser <span class="text-red-500">*</span></label>
                    <select name="advertiser_id" id="add_advertiser_id" required onchange="loadCampaigns(this.value, 'add')" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
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
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Campaign <span class="text-xs text-gray-400">(leave blank for all campaigns)</span></label>
                    <select name="campaign_id" id="add_campaign_id" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        <option value="">All Campaigns</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Type <span class="text-red-500">*</span></label>
                        <select name="type" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                            <option value="CPC">CPC (Cost Per Click)</option>
                            <option value="CPM">CPM (Cost Per Mille)</option>
                            <option value="CPA">CPA (Cost Per Action)</option>
                            <option value="CPV">CPV (Cost Per View)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Country Code <span class="text-red-500">*</span></label>
                        <select name="country_code" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
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
                    <label class="block text-xs font-medium text-gray-600 mb-1">Bid Value ($) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">$</span>
                        <input type="number" name="bid_value" required min="0" step="0.01" placeholder="0.00" class="w-full pl-8 pr-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('addBiddingModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm">Create Rule</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══════════ EDIT MODAL ═══════════ --}}
    <div id="editModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Edit Bidding Rule</h3>
                <button onclick="document.getElementById('editModal').classList.add('hidden')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
            </div>
            <form id="editForm" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Advertiser <span class="text-red-500">*</span></label>
                    <select name="advertiser_id" id="edit_advertiser_id" required onchange="loadCampaigns(this.value, 'edit')" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
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
                    <label class="block text-xs font-medium text-gray-600 mb-1">Campaign <span class="text-xs text-gray-400">(leave blank for all campaigns)</span></label>
                    <select name="campaign_id" id="edit_campaign_id" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        <option value="">All Campaigns</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Type <span class="text-red-500">*</span></label>
                        <select name="type" id="edit_type" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                            <option value="CPC">CPC (Cost Per Click)</option>
                            <option value="CPM">CPM (Cost Per Mille)</option>
                            <option value="CPA">CPA (Cost Per Action)</option>
                            <option value="CPV">CPV (Cost Per View)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Country Code <span class="text-red-500">*</span></label>
                        <select name="country_code" id="edit_country_code" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
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
                    <label class="block text-xs font-medium text-gray-600 mb-1">Bid Value ($) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">$</span>
                        <input type="number" name="bid_value" id="edit_bid_value" required min="0" step="0.01" placeholder="0.00" class="w-full pl-8 pr-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
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
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Load campaigns for advertiser
    function loadCampaigns(advertiserId, context) {
        const campaignSelect = document.getElementById(context + '_campaign_id');
        campaignSelect.innerHTML = '<option value="">All Campaigns</option>';

        if (!advertiserId) return;

        fetch(`/admin/country-wise-bidding/campaigns/${advertiserId}`)
            .then(r => {
                if (!r.ok) throw new Error(`HTTP error! status: ${r.status}`);
                return r.json();
            })
            .then(campaigns => {
                console.log('Loaded campaigns:', campaigns);
                if (campaigns && campaigns.length > 0) {
                    campaigns.forEach(campaign => {
                        const option = document.createElement('option');
                        option.value = `${campaign.type}:${campaign.id}`;
                        option.textContent = campaign.label || campaign.name;
                        campaignSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading campaigns:', error);
                campaignSelect.innerHTML += '<option value="" disabled>Error loading campaigns</option>';
            });
    }

    // Edit bidding rule
    function editBidding(id) {
        fetch(`/admin/country-wise-bidding/${id}`, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                document.getElementById('edit_advertiser_id').value = data.advertiser_id;
                document.getElementById('edit_type').value = data.type;
                document.getElementById('edit_country_code').value = data.country_code;
                document.getElementById('edit_bid_value').value = data.bid_value;

                // Load campaigns and set selected
                const campaignSelect = document.getElementById('edit_campaign_id');
                campaignSelect.innerHTML = '<option value="">All Campaigns</option>';
                data.campaigns.forEach(campaign => {
                    const option = document.createElement('option');
                    option.value = `${campaign.type}:${campaign.id}`;
                    option.textContent = campaign.label || campaign.name;
                    if (campaign.id === data.campaign_id && campaign.type === (data.campaign_type || 'network')) option.selected = true;
                    campaignSelect.appendChild(option);
                });

                document.getElementById('editForm').action = `/admin/country-wise-bidding/${id}`;
                document.getElementById('editModal').classList.remove('hidden');
            });
    }

    // Delete bidding rule
    function deleteBidding(id) {
        if (!confirm('Are you sure you want to delete this bidding rule?')) return;

        fetch(`/admin/country-wise-bidding/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => {
            if (data.success) location.reload();
            else alert(data.message || 'Error deleting bidding rule.');
        });
    }

    // Copy table data
    function copyTable() {
        const table = document.getElementById('biddingTable');
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

    // Excel export
    function exportExcel() {
        window.location.href = '{{ route("admin.country-wise-bidding.export", request()->all()) }}';
    }

    // Reopen modal if there are validation errors
    @if($errors->any())
        document.getElementById('addBiddingModal').classList.remove('hidden');
        // Reload campaigns if advertiser was selected
        @if(old('advertiser_id'))
            loadCampaigns('{{ old('advertiser_id') }}', 'add');
        @endif
    @endif
</script>
@endpush
