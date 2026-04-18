@extends('layouts.admin')

@section('title', 'Create Campaign')

@section('content')
<style>
    /* Prevent dayparting table from causing layout issues */
    #daypartingBlock {
        position: relative;
        contain: layout style;
    }
    #daypartingBlock table {
        border-collapse: collapse;
        table-layout: fixed;
        width: 100%;
    }
    #daypartingBlock tbody,
    #daypartingBlock tr {
        position: relative;
    }
    #daypartingBlock td,
    #daypartingBlock th {
        min-height: 0;
        height: auto;
        overflow: hidden;
        position: relative;
    }
    #daypartingBlock label {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 !important;
        padding: 0 !important;
        line-height: 1 !important;
        vertical-align: middle !important;
        width: 16px;
        height: 16px;
    }
    #daypartingBlock .daypart-cell {
        position: absolute;
        opacity: 0;
        pointer-events: none;
        width: 1px;
        height: 1px;
    }
    #daypartingBlock .daypart-cell:focus {
        outline: none !important;
        box-shadow: none !important;
    }
    #daypartingBlock .daypart-cell:focus + span {
        outline: none !important;
    }
    /* Prevent page scroll/shift on checkbox click */
    #campaignForm {
        position: relative;
        overflow-anchor: none;
    }
    html {
        scroll-behavior: auto;
        overflow-anchor: none;
    }
</style>
<form method="POST" action="{{ route('admin.campaigns.store') }}" enctype="multipart/form-data" id="campaignForm">
    @csrf

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="mb-4 p-4 rounded-xl border border-red-300 bg-red-50">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none"><path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-red-800 mb-1">Please fix the following errors:</h3>
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-400 mb-1">
                <a href="{{ route('admin.campaigns') }}" class="hover:text-gray-600 transition-colors">Campaigns</a>
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                <span class="text-gray-600">Create Campaign</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Create New Campaign</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.campaigns') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Create Campaign
            </button>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SECTION 1: CAMPAIGN SETTINGS                           --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-brand-100 flex items-center justify-center">
                    <span class="text-sm font-bold text-brand-600">1</span>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Campaign Settings</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Configure the basic campaign parameters</p>
                </div>
            </div>
        </div>
        <div class="p-6 space-y-5">
            {{-- Row: Name + Type --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Campaign Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Summer Brand Awareness — Albania" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Campaign Type <span class="text-red-500">*</span></label>
                    <select name="campaign_type" required class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                        <option value="">Select type...</option>
                        @foreach($campaignTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Marketing Objective <span class="text-red-500">*</span></label>
                    <select name="marketing_objective" required class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                        <option value="">Select objective...</option>
                        @foreach($marketingObjectives as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Row: Show in Admarket + Campaign Group --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="flex items-center gap-3 pt-5">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="show_in_admarket" value="1" class="sr-only peer">
                        <div class="w-10 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-brand-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-600"></div>
                    </label>
                    <span class="text-sm font-medium text-gray-700">Show in Admarket</span>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Campaign Group</label>
                    <div class="flex gap-2">
                        <select name="group_id" class="flex-1 px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                            <option value="">No group</option>
                            @foreach($campaignGroups as $group)
                                <option value="{{ $group['id'] }}">{{ $group['name'] }}</option>
                            @endforeach
                        </select>
                        <button type="button" onclick="document.getElementById('newGroupModal').classList.remove('hidden')" class="px-3 py-2.5 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors whitespace-nowrap">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Row: Pixel Tracker --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Pixel Tracker</label>
                <div class="flex gap-2">
                    <select name="pixel_tracker_id" id="pixelSelect" class="flex-1 px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                        <option value="">No pixel tracker</option>
                        @foreach($pixelTrackers as $pixel)
                            <option value="{{ $pixel['id'] }}">{{ $pixel['name'] }} ({{ $pixel['type'] }}) — {{ $pixel['advertiser'] }}</option>
                        @endforeach
                    </select>
                    <button type="button" onclick="document.getElementById('newPixelModal').classList.remove('hidden')" class="px-3 py-2.5 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Linked AdBlock</label>
                <select name="zone_id" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                    <option value="">No AdBlock selected</option>
                    @foreach($zones as $zone)
                        <option value="{{ $zone['id'] }}" {{ old('zone_id') == $zone['id'] ? 'selected' : '' }}>
                            #{{ $zone['id'] }} - {{ $zone['name'] }} ({{ $zone['site_name'] }} / {{ ucfirst($zone['placement']) }})
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-400">Select the AdBlock where this campaign should run.</p>
            </div>

            {{-- S2S Postback Tracking --}}
            <div class="mt-5 pt-5 border-t border-gray-100">
                <div class="flex items-center gap-3 mb-4">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="s2s_enabled" value="1" id="s2sEnabledToggle" class="sr-only peer" onchange="toggleS2sSection()">
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-brand-500/20 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-500"></div>
                    </label>
                    <div>
                        <span class="text-xs font-semibold text-gray-700 uppercase tracking-wider">S2S (Server-to-Server) Postback</span>
                        <p class="text-[10px] text-gray-400">Cookie-free conversion tracking. iOS 14+ compatible. Industry standard for 2026.</p>
                    </div>
                </div>
                <div id="s2sSection" class="hidden">
                    <div class="bg-gray-50 rounded-lg p-4 mb-3">
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Postback URL (Advertiser)</label>
                        <input type="text" name="s2s_postback_url" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white font-mono" placeholder="https://your-tracker.com/postback?click_id={click_id}&payout={payout}&tx_id={tx_id}">
                        <p class="text-[10px] text-gray-400 mt-1">The URL where we'll fire a postback when a conversion is received. Available macros: <code class="text-[10px] bg-gray-200 px-1 rounded">{click_id}</code> <code class="text-[10px] bg-gray-200 px-1 rounded">{payout}</code> <code class="text-[10px] bg-gray-200 px-1 rounded">{tx_id}</code> <code class="text-[10px] bg-gray-200 px-1 rounded">{goal}</code> <code class="text-[10px] bg-gray-200 px-1 rounded">{campaign_id}</code> <code class="text-[10px] bg-gray-200 px-1 rounded">{ad_id}</code> <code class="text-[10px] bg-gray-200 px-1 rounded">{country}</code> <code class="text-[10px] bg-gray-200 px-1 rounded">{device}</code> <code class="text-[10px] bg-gray-200 px-1 rounded">{timestamp}</code></p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4">
                        <p class="text-xs font-semibold text-blue-700 mb-2">Your S2S Postback Endpoint</p>
                        <p class="text-[11px] text-blue-600 font-mono break-all">{{ url('/track/campaign') }}/&lt;CAMPAIGN_ID&gt;/postback?click_id=<span class="text-blue-800 font-bold">CLICK_ID</span>&payout=<span class="text-blue-800 font-bold">AMOUNT</span>&tx_id=<span class="text-blue-800 font-bold">TX_ID</span></p>
                        <p class="text-[10px] text-blue-500 mt-2">Send a GET or POST request to this URL when a conversion occurs. The <code class="bg-blue-100 px-1 rounded">click_id</code> is automatically appended to your destination URL on each click.</p>
                    </div>
                </div>
            </div>

            {{-- Row: Capping + Priority --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Impression Capping</label>
                    <div class="flex items-center gap-3">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="capping_mode" value="none" checked class="text-brand-600 focus:ring-brand-500" onchange="document.getElementById('cappingInput').classList.add('hidden')">
                            <span class="text-sm text-gray-600">No limit</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="capping_mode" value="custom" class="text-brand-600 focus:ring-brand-500" onchange="document.getElementById('cappingInput').classList.remove('hidden')">
                            <span class="text-sm text-gray-600">Custom</span>
                        </label>
                    </div>
                    <div id="cappingInput" class="hidden mt-2">
                        <div class="flex items-center gap-2">
                            <input type="number" name="frequency_cap" min="1" placeholder="e.g. 10" class="w-32 px-4 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                            <span class="text-sm text-gray-500">impressions per hour</span>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Priority: <span id="priorityValue" class="text-brand-600">5</span></label>
                    <input type="range" name="priority" min="1" max="10" value="5" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-brand-600" oninput="document.getElementById('priorityValue').textContent = this.value">
                    <div class="flex justify-between text-[10px] text-gray-400 mt-1">
                        <span>1 (Low)</span>
                        <span>5 (Normal)</span>
                        <span>10 (High)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SECTION 2: DISPLAY TIME                                --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                    <span class="text-sm font-bold text-blue-600">2</span>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Display Time</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Schedule when your campaign runs</p>
                </div>
            </div>
        </div>
        <div class="p-6 space-y-5">
            {{-- Status + Budget --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Campaign Status</label>
                    <select name="status" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                        <option value="draft">Draft</option>
                        <option value="pending_review">Pending Review</option>
                        <option value="active">Active</option>
                        <option value="paused">Paused</option>
                    </select>
                </div>
            </div>

            {{-- Budget & Bidding --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Bid Amount <span class="text-red-500">*</span></label>
                    <input type="number" name="bid_amount" required step="0.01" min="{{ $campaignSettings['minimum_bid_rate'] }}" placeholder="0.00" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    <p class="mt-1 text-xs text-gray-400">Minimum bid rate: {{ number_format((float) $campaignSettings['minimum_bid_rate'], 4) }}</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Currency <span class="text-red-500">*</span></label>
                    <select name="currency" required class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                        <option value="EUR">EUR (€)</option>
                        <option value="USD">USD ($)</option>
                        <option value="GBP">GBP (£)</option>
                        <option value="ALL">ALL (Lek)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Total Budget</label>
                    <input type="number" name="total_budget" step="0.01" min="{{ $campaignSettings['minimum_budget'] }}" placeholder="0.00" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    <p class="mt-1 text-xs text-gray-400">Minimum total budget: {{ number_format((float) $campaignSettings['minimum_budget'], 2) }}</p>
                </div>
            </div>

            {{-- Schedule --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Campaign Schedule</label>
                <div class="flex items-center gap-4">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="schedule_mode" value="immediately" checked class="text-brand-600 focus:ring-brand-500" onchange="document.getElementById('dateRangeBlock').classList.add('hidden')">
                        <span class="text-sm text-gray-600">Start Immediately</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="schedule_mode" value="period" class="text-brand-600 focus:ring-brand-500" onchange="document.getElementById('dateRangeBlock').classList.remove('hidden')">
                        <span class="text-sm text-gray-600">Set Display Period</span>
                    </label>
                </div>
                <div id="dateRangeBlock" class="hidden mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Start Date</label>
                        <input type="datetime-local" name="start_date" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">End Date</label>
                        <input type="datetime-local" name="end_date" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                </div>
            </div>

            {{-- Weekly Distribution --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Weekly Distribution</label>
                <div class="flex items-center gap-4 mb-3">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="weekly_mode" value="24_7" checked class="text-brand-600 focus:ring-brand-500" onchange="document.getElementById('daypartingBlock').classList.add('hidden')">
                        <span class="text-sm text-gray-600">24/7 (Always On)</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="weekly_mode" value="custom" class="text-brand-600 focus:ring-brand-500" onchange="document.getElementById('daypartingBlock').classList.remove('hidden')">
                        <span class="text-sm text-gray-600">Custom</span>
                    </label>
                </div>
                <div id="daypartingBlock" class="hidden">
                    <label class="block text-xs font-medium text-gray-500 mb-2">Dayparting Preset</label>
                    <div class="flex flex-wrap gap-2 mb-4">
                        @php $daypartOptions = ['All', 'Working Days', 'Weekend', 'Only Night', 'Only Day', 'Only Morning']; @endphp
                        @foreach($daypartOptions as $opt)
                            <button type="button" class="daypart-preset px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-medium text-gray-600 hover:bg-brand-50 hover:border-brand-300 hover:text-brand-600 transition-colors" data-preset="{{ \Illuminate\Support\Str::slug($opt) }}">{{ $opt }}</button>
                        @endforeach
                    </div>

                    {{-- Dayparting grid --}}
                    <div class="overflow-x-auto overflow-y-hidden">
                        <table class="w-full text-[10px] table-fixed">
                            <thead>
                                <tr>
                                    <th class="text-left py-1 px-2 text-gray-500 font-semibold">Day / Hour</th>
                                    @for($h = 0; $h < 24; $h++)
                                        <th class="py-1 px-0.5 text-center text-gray-400 font-medium">{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @php $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday']; @endphp
                                @foreach($days as $day)
                                    <tr>
                                        <td class="py-1 px-2 text-gray-600 font-medium whitespace-nowrap">{{ $day }}</td>
                                        @for($h = 0; $h < 24; $h++)
                                            <td class="py-1 px-0.5 text-center align-middle" style="overflow: visible;">
                                                <label class="inline-flex cursor-pointer items-center justify-center m-0" style="position: relative; display: inline-flex; width: 16px; height: 16px;">
                                                    <input type="checkbox" name="dayparting[{{ strtolower($day) }}][]" value="{{ $h }}" class="daypart-cell peer" data-day="{{ strtolower($day) }}" data-hour="{{ $h }}" style="position: absolute; opacity: 0; pointer-events: none; width: 1px; height: 1px; margin: 0;">
                                                    <span class="inline-block w-4 h-4 rounded border border-gray-200 peer-checked:bg-brand-500 peer-checked:border-brand-500 transition-colors flex-shrink-0" style="pointer-events: all;"></span>
                                                </label>
                                            </td>
                                        @endfor
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SECTION 3: TARGET VISITORS                             --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <span class="text-sm font-bold text-emerald-600">3</span>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Target Visitors</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Define your audience targeting</p>
                </div>
            </div>
        </div>
        <div class="p-6 space-y-5">
            {{-- Device targeting --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Targeting Type (Device)</label>
                    <select name="device_type" id="deviceTypeSelect" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                        <option value="all">All Devices</option>
                        @foreach($deviceTypes as $deviceType)
                            <option value="{{ $deviceType }}">{{ ucfirst($deviceType) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Select Device</label>
                    <select name="target_devices[]" id="deviceListSelect" multiple class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white min-h-[80px]">
                        {{-- Populated via JS --}}
                    </select>
                    <p class="text-[10px] text-gray-400 mt-1">Hold Ctrl/Cmd to select multiple</p>
                </div>
            </div>

            {{-- Country targeting --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Country Targeting</label>
                <select name="targeting_geo[]" multiple class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white min-h-[120px]">
                    @foreach($countries as $country)
                        <option value="{{ $country['code'] }}">{{ $country['name'] }} ({{ $country['code'] }})</option>
                    @endforeach
                </select>
                <p class="text-[10px] text-gray-400 mt-1">Hold Ctrl/Cmd to select multiple countries</p>
            </div>

            {{-- City-Level Targeting --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider">City Targeting</label>
                    <button type="button" id="addCityBtn" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-brand-50 border border-brand-200 text-brand-600 hover:bg-brand-100 transition text-xs font-semibold">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Add City
                    </button>
                </div>
                <div id="cityTargetingList" class="space-y-2">
                    {{-- Dynamic city rows will be added here --}}
                </div>
                <p class="text-[10px] text-gray-400 mt-1">Select a country first, then pick a city. Click + to add more.</p>
            </div>

            {{-- Region --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Region</label>
                <div class="flex flex-wrap gap-3">
                    @php $regions = ['All', 'Australia', 'Asia', 'South America', 'North America', 'Europe', 'Africa', 'Other']; @endphp
                    @foreach($regions as $region)
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="targeting_region[]" value="{{ \Illuminate\Support\Str::slug($region) }}" {{ $region === 'All' ? 'checked' : '' }} class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span class="text-sm text-gray-600">{{ $region }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="border-t border-gray-100"></div>

            {{-- OS Targeting --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider">Operating System Targeting</label>
                    <button type="button" id="addOsBtn" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-brand-50 border border-brand-200 text-brand-600 hover:bg-brand-100 transition text-xs font-semibold">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Add OS
                    </button>
                </div>
                <div id="osTargetingList" class="space-y-2"></div>
                <p class="text-[10px] text-gray-400 mt-1">Select an OS, then optionally pick a version. Click + to add more.</p>
            </div>

            {{-- Browser Targeting --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider">Browser Targeting</label>
                    <button type="button" id="addBrowserBtn" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-brand-50 border border-brand-200 text-brand-600 hover:bg-brand-100 transition text-xs font-semibold">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Add Browser
                    </button>
                </div>
                <div id="browserTargetingList" class="space-y-2"></div>
                <p class="text-[10px] text-gray-400 mt-1">Select a browser, then optionally pick a version. Click + to add more.</p>
            </div>

            {{-- Connection Type Targeting --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Connection Type</label>
                <div class="flex flex-wrap gap-3">
                    @foreach($connectionTypes as $ct)
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" class="connection-type-cb rounded border-gray-300 text-brand-600 focus:ring-brand-500" value="{{ $ct }}">
                            <span class="text-sm text-gray-700">{{ $ct }}</span>
                        </label>
                    @endforeach
                </div>
                <input type="hidden" name="targeting_connection_type" id="targeting_connection_type">
                <p class="text-[10px] text-gray-400 mt-1">Leave unchecked to target all connection types.</p>
            </div>

            {{-- Carrier Targeting --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider">Mobile Carrier Targeting</label>
                    <button type="button" id="addCarrierBtn" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-brand-50 border border-brand-200 text-brand-600 hover:bg-brand-100 transition text-xs font-semibold">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Add Carrier
                    </button>
                </div>
                <div id="carrierTargetingList" class="space-y-2"></div>
                <p class="text-[10px] text-gray-400 mt-1">Select a country, then pick a mobile carrier. Click + to add more.</p>
            </div>

            {{-- Language Targeting --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Language Targeting</label>
                <select id="languageSelect" multiple class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white min-h-[120px]">
                    @foreach($languages as $code => $name)
                        <option value="{{ $code }}">{{ $name }} ({{ $code }})</option>
                    @endforeach
                </select>
                <input type="hidden" name="targeting_language" id="targeting_language">
                <p class="text-[10px] text-gray-400 mt-1">Hold Ctrl/Cmd to select multiple languages. Leave empty to target all languages.</p>
            </div>

            {{-- Traffic Type Targeting --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Traffic Type</label>
                <div class="flex flex-wrap gap-4">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="targeting_traffic_type" value="all" checked class="text-brand-600 focus:ring-brand-500">
                        <span class="text-sm text-gray-700">All Traffic</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="targeting_traffic_type" value="mainstream" class="text-brand-600 focus:ring-brand-500">
                        <span class="text-sm text-gray-700">Mainstream</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="targeting_traffic_type" value="non-mainstream" class="text-brand-600 focus:ring-brand-500">
                        <span class="text-sm text-gray-700">Non-Mainstream (Adult)</span>
                    </label>
                </div>
                <p class="text-[10px] text-gray-400 mt-1">Choose whether the campaign targets mainstream or non-mainstream (adult) traffic.</p>
            </div>

            {{-- IP Targeting --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">IP Include (Whitelist)</label>
                    <textarea name="targeting_ip_include" rows="4" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white font-mono" placeholder="192.168.1.0/24&#10;10.0.0.1&#10;203.0.113.0/28"></textarea>
                    <p class="text-[10px] text-gray-400 mt-1">One IP or CIDR range per line. Only these IPs will see the ad. Leave empty to allow all.</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">IP Exclude (Blacklist)</label>
                    <textarea name="targeting_ip_exclude" rows="4" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white font-mono" placeholder="192.168.1.100&#10;10.0.0.0/8"></textarea>
                    <p class="text-[10px] text-gray-400 mt-1">One IP or CIDR range per line. These IPs will be blocked from seeing the ad.</p>
                </div>
            </div>

            <div class="border-t border-gray-100"></div>

            {{-- Meta Keywords Targeting --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider">Meta Keywords Targeting</label>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-100 text-blue-700">Contextual</span>
                </div>
                <p class="text-xs text-gray-500 mb-3">Target websites based on their HTML meta keywords. When our tracking pixel detects matching keywords on a publisher's page, this campaign becomes eligible to serve.</p>

                @if(!empty($keywords))
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 max-h-64 overflow-y-auto p-3 bg-gray-50 rounded-lg border border-gray-200">
                    @php
                        $keywordsByCategory = collect($keywords)->groupBy('category');
                    @endphp
                    @foreach($keywordsByCategory as $category => $categoryKeywords)
                        <div class="col-span-full">
                            <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">{{ $category ?: 'Uncategorized' }}</span>
                        </div>
                        @foreach($categoryKeywords as $kw)
                            <label class="inline-flex items-center gap-2 cursor-pointer p-1.5 rounded hover:bg-white transition">
                                <input type="checkbox" name="targeting_keywords[]" value="{{ $kw['keyword'] }}" class="keyword-cb rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                <span class="text-sm text-gray-700 truncate" title="{{ $kw['description'] ?? $kw['keyword'] }}">{{ $kw['keyword'] }}</span>
                            </label>
                        @endforeach
                    @endforeach
                </div>
                <div class="flex items-center gap-3 mt-2">
                    <button type="button" onclick="document.querySelectorAll('.keyword-cb').forEach(cb => cb.checked = true)" class="text-xs text-brand-600 hover:text-brand-700 font-medium">Select All</button>
                    <span class="text-gray-300">|</span>
                    <button type="button" onclick="document.querySelectorAll('.keyword-cb').forEach(cb => cb.checked = false)" class="text-xs text-brand-600 hover:text-brand-700 font-medium">Deselect All</button>
                    <span class="text-gray-300">|</span>
                    <span class="text-xs text-gray-400"><span id="selectedKeywordCount">0</span> selected</span>
                </div>
                @else
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 text-center">
                    <p class="text-sm text-gray-500">No keywords available. <a href="{{ route('admin.keywords') }}" class="text-brand-600 hover:underline">Add keywords</a> first.</p>
                </div>
                @endif
                <p class="text-[10px] text-gray-400 mt-2">Leave unchecked to target all websites regardless of their meta keywords.</p>
            </div>

        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SECTION 4: SPECIAL BIDDING OPTIONS                     --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                    <span class="text-sm font-bold text-amber-600">4</span>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Special Bidding Options</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Configure traffic source and country-level bidding</p>
                </div>
            </div>
        </div>
        <div class="p-6 space-y-6">
            {{-- Traffic Source Bidding --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-800 mb-3">Traffic Source Bidding</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Traffic Source</label>
                        <select id="ts_source" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm bg-white">
                            <option value="">Select source...</option>
                            @foreach($trafficSources as $src)
                                <option value="{{ $src['id'] }}" data-name="{{ $src['name'] }}">{{ $src['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Bid Rate</label>
                        <input type="number" id="ts_bid" step="0.01" min="0" placeholder="0.00" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                        <select id="ts_status" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm bg-white">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div>
                        <button type="button" onclick="addTrafficSource()" class="w-full px-4 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 transition-colors">Add</button>
                    </div>
                </div>
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full text-sm" id="trafficSourceTable">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="text-left px-4 py-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Source ID</th>
                                <th class="text-left px-4 py-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Source Name</th>
                                <th class="text-right px-4 py-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Bid Rate</th>
                                <th class="text-center px-4 py-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                                <th class="text-center px-4 py-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tsBody" class="divide-y divide-gray-50">
                            <tr id="tsEmptyRow"><td colspan="5" class="px-4 py-6 text-center text-xs text-gray-400">No traffic sources added yet</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="border-t border-gray-100"></div>

            {{-- Country Bidding --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-800 mb-3">Country Bidding</h3>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Country</label>
                        <select id="cb_country" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm bg-white">
                            <option value="">Select country...</option>
                            @foreach($countries as $country)
                                <option value="{{ $country['code'] }}" data-name="{{ $country['name'] }}">{{ $country['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Pricing</label>
                        <select id="cb_pricing" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm bg-white">
                            @foreach($pricingModels as $pm)
                                <option value="{{ $pm }}">{{ $pm }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Bid</label>
                        <input type="number" id="cb_bid" step="0.01" min="0" placeholder="0.00" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                        <select id="cb_status" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm bg-white">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div>
                        <button type="button" onclick="addCountryBid()" class="w-full px-4 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 transition-colors">Add</button>
                    </div>
                </div>
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full text-sm" id="countryBidTable">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="text-left px-4 py-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Country</th>
                                <th class="text-left px-4 py-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Pricing</th>
                                <th class="text-right px-4 py-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Bid Rate</th>
                                <th class="text-center px-4 py-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                                <th class="text-center px-4 py-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="cbBody" class="divide-y divide-gray-50">
                            <tr id="cbEmptyRow"><td colspan="5" class="px-4 py-6 text-center text-xs text-gray-400">No country bids added yet</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SECTION 5: AD FORMATS                                  --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                    <span class="text-sm font-bold text-purple-600">5</span>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Ad Formats</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Choose ad type, format, and add creatives</p>
                </div>
            </div>
        </div>
        <div class="p-6 space-y-5">
            {{-- Ad Type --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Ad Type</label>
                    <select id="adTypeSelect" name="ad_type" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                        <option value="all">All</option>
                        <option value="display_web" {{ $defaultAdTypeGroup === 'display_web' ? 'selected' : '' }}>Display Web</option>
                        <option value="special_web" {{ $defaultAdTypeGroup === 'special_web' ? 'selected' : '' }}>Special Web</option>
                        <option value="display_video" {{ $defaultAdTypeGroup === 'display_video' ? 'selected' : '' }}>Display Video</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-400">Default group follows App Configurations: {{ $campaignSettings['creative_type'] }}</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Ad Format</label>
                    <select id="adFormatSelect" name="ad_format" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                        <option value="">Select format...</option>
                    </select>
                </div>
            </div>

            {{-- Creative form (shown after format selected) --}}
            <div id="creativeFormBlock" class="hidden border border-gray-200 rounded-xl p-5 bg-gray-50/50">
                <h3 class="text-sm font-semibold text-gray-800 mb-4">Add Creative</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Ad Name <span class="text-red-500">*</span></label>
                        <input type="text" id="cr_name" placeholder="e.g. Banner Summer Sale" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Ad URL <span class="text-red-500">*</span></label>
                        <input type="url" id="cr_url" placeholder="https://example.com/landing" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                </div>

                {{-- Text Ad Fields --}}
                <div id="textAdFields" class="hidden grid grid-cols-1 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Ad Title <span class="text-red-500">*</span></label>
                        <input type="text" id="cr_text_title" placeholder="e.g. Great Summer Sale!" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Description</label>
                        <textarea id="cr_text_description" rows="2" placeholder="Short description of your ad" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Ad Body Text</label>
                        <textarea id="cr_text_body" rows="3" placeholder="Main ad content text" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Button Text</label>
                        <input type="text" id="cr_text_cta" placeholder="e.g. Shop Now, Learn More, Sign Up" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                </div>

                {{-- Image/Banner Ad Fields --}}
                <div id="imageAdFields" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Content Type</label>
                        <select id="cr_content_type" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm bg-white">
                            <option value="image">Image</option>
                            <option value="html">HTML</option>
                            <option value="flash">Flash</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Upload File <span class="text-red-500">*</span></label>
                        <input type="file" id="cr_file" accept="image/*,.html,.swf,.gif" class="w-full px-3 py-1.5 rounded-lg border border-gray-200 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100">
                    </div>
                </div>

                {{-- Social Bar Ad Fields --}}
                <div id="socialBarAdFields" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Title <span class="text-red-500">*</span></label>
                        <input type="text" id="cr_sb_title" placeholder="e.g. Limited Time Offer!" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Description</label>
                        <input type="text" id="cr_sb_description" placeholder="Short message for the bar" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Button Text</label>
                        <input type="text" id="cr_sb_button_text" placeholder="e.g. Get Deal" value="Learn More" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Icon Image (Optional)</label>
                        <input type="file" id="cr_sb_icon" accept="image/*" class="w-full px-3 py-1.5 rounded-lg border border-gray-200 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100">
                    </div>
                </div>

                {{-- Native Ad Fields --}}
                {{-- In-Page Push Ad Fields --}}
                <div id="inPagePushAdFields" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Headline <span class="text-red-500">*</span></label>
                        <input type="text" id="cr_ipp_headline" placeholder="e.g. Don't Miss This Offer!" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Body Text</label>
                        <input type="text" id="cr_ipp_body" placeholder="e.g. Limited time — click to see details" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Icon Image (Optional)</label>
                        <input type="file" id="cr_ipp_icon" accept="image/*" class="w-full px-3 py-1.5 rounded-lg border border-gray-200 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100">
                        <p class="text-[10px] text-gray-400 mt-1">Recommended: 48x48px square image</p>
                    </div>
                </div>

                {{-- Popunder Ad Fields --}}
                <div id="popunderAdFields" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Headline <span class="text-red-500">*</span></label>
                        <input type="text" id="cr_popunder_headline" placeholder="e.g. Check Out Our Latest Deals" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Body Text</label>
                        <input type="text" id="cr_popunder_body" placeholder="Short text shown in the trigger area (optional)" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                </div>

                {{-- Interstitial Ad Fields --}}
                <div id="interstitialAdFields" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Headline <span class="text-red-500">*</span></label>
                        <input type="text" id="cr_interstitial_headline" placeholder="e.g. Special Offer — 50% Off Today" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Button Text</label>
                        <input type="text" id="cr_interstitial_cta" placeholder="e.g. Continue, Shop Now, Learn More" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Body Text</label>
                        <textarea id="cr_interstitial_body" rows="2" placeholder="Short description shown below the headline" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Background Image (Optional)</label>
                        <input type="file" id="cr_interstitial_image" accept="image/*" class="w-full px-3 py-1.5 rounded-lg border border-gray-200 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100">
                    </div>
                </div>

                {{-- Native Ad Fields --}}
                <div id="nativeAdFields" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Headline <span class="text-red-500">*</span></label>
                        <input type="text" id="cr_native_headline" placeholder="e.g. Discover Our New Collection" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Brand Name</label>
                        <input type="text" id="cr_native_brand" placeholder="e.g. Your Company" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Body Text</label>
                        <textarea id="cr_native_body" rows="2" placeholder="Describe what you're promoting" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Button Text</label>
                        <input type="text" id="cr_native_cta" placeholder="e.g. Learn More, Shop Now" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Cover Image (Optional)</label>
                        <input type="file" id="cr_native_image" accept="image/*" class="w-full px-3 py-1.5 rounded-lg border border-gray-200 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100">
                    </div>
                </div>

                {{-- Video Ad Fields --}}
                <div id="videoAdFields" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Video Source Type</label>
                        <select id="cr_video_source" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm bg-white">
                            <option value="url">Video URL</option>
                            <option value="upload">Upload Video</option>
                        </select>
                    </div>
                    <div id="videoUrlField">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Video URL <span class="text-red-500">*</span></label>
                        <input type="url" id="cr_video_url" placeholder="https://example.com/video.mp4" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div id="videoUploadField" class="hidden">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Upload Video <span class="text-red-500">*</span></label>
                        <input type="file" id="cr_video_file" accept="video/*" class="w-full px-3 py-1.5 rounded-lg border border-gray-200 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Headline</label>
                        <input type="text" id="cr_video_headline" placeholder="e.g. Watch Our Latest Ad" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Button Text</label>
                        <input type="text" id="cr_video_cta" placeholder="e.g. Learn More, Shop Now" value="Learn More" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Video Thumbnail (Optional)</label>
                        <input type="file" id="cr_video_thumb" accept="image/*" class="w-full px-3 py-1.5 rounded-lg border border-gray-200 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100">
                    </div>
                    {{-- Reward-specific fields (only shown for rewarded format) --}}
                    <div id="rewardedFields" class="hidden md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 mt-1 p-3 rounded-lg border border-amber-200 bg-amber-50/50">
                        <div class="md:col-span-2">
                            <p class="text-xs font-semibold text-amber-700 flex items-center gap-1.5 mb-2">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                Reward Settings
                            </p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Reward Amount</label>
                            <input type="number" id="cr_reward_amount" placeholder="e.g. 50" min="1" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Reward Type</label>
                            <select id="cr_reward_type" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm bg-white">
                                <option value="Coins">Coins</option>
                                <option value="Gems">Gems</option>
                                <option value="Credits">Credits</option>
                                <option value="Points">Points</option>
                                <option value="Extra Life">Extra Life</option>
                                <option value="Bonus">Bonus</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="button" onclick="addCreative()" class="px-4 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 transition-colors">Add Creative</button>
                </div>
            </div>

            {{-- Creatives table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="creativeTable">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left px-4 py-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Ad Name</th>
                            <th class="text-left px-4 py-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Preview</th>
                            <th class="text-left px-4 py-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Filename</th>
                            <th class="text-left px-4 py-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Dimension</th>
                            <th class="text-left px-4 py-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Format</th>
                            <th class="text-right px-4 py-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Size (KB)</th>
                            <th class="text-left px-4 py-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Ad URL</th>
                            <th class="text-center px-4 py-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="crBody" class="divide-y divide-gray-50">
                        <tr id="crEmptyRow"><td colspan="8" class="px-4 py-6 text-center text-xs text-gray-400">No creatives added yet. Select an ad type and format above.</td></tr>
                    </tbody>
                </table>
            </div>

            {{-- Macro Variables --}}
            <div class="border-t border-gray-100 pt-5">
                <h3 class="text-sm font-semibold text-gray-800 mb-3">Macro Variables</h3>
                <div class="bg-gray-50 rounded-lg p-4 text-xs text-gray-600 font-mono space-y-1.5">
                    <div><span class="inline-block w-24 font-bold text-gray-800">[zone]</span> Zone ID (recommended).</div>
                    <div><span class="inline-block w-24 font-bold text-gray-800">[lang]</span> User main browser language</div>
                    <div><span class="inline-block w-24 font-bold text-gray-800">[clickid]</span> Click ID (for server 2 server)</div>
                    <div><span class="inline-block w-24 font-bold text-gray-800">[time]</span> Time in seconds since Epoch (aka. Timestamp) (cache buster)</div>
                    <div><span class="inline-block w-24 font-bold text-gray-800">[campaign]</span> Current campaign ID in Adgate system</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Submit --}}
    <div class="flex items-center justify-end gap-3 mb-10">
        <a href="{{ route('admin.campaigns') }}" class="px-5 py-2.5 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">Cancel</a>
        <button type="submit" class="px-6 py-2.5 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm transition-colors">Create Campaign</button>
    </div>
</form>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- MODAL: NEW GROUP                                       --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div id="newGroupModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-4">Create Campaign Group</h3>
        <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Group Name <span class="text-red-500">*</span></label>
            <input type="text" id="newGroupName" placeholder="e.g. Performance Q4" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
        </div>
        <div class="flex justify-end gap-2 mt-5">
            <button type="button" onclick="document.getElementById('newGroupModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">Cancel</button>
            <button type="button" onclick="createGroup()" class="px-4 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700">Create</button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- MODAL: NEW PIXEL TRACKER                               --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div id="newPixelModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-4">Create Pixel Tracker</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Advertiser <span class="text-red-500">*</span></label>
                <select id="px_advertiser" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm bg-white">
                    <option value="">Select advertiser...</option>
                    @foreach($advertisers as $adv)
                        <option value="{{ $adv['id'] }}">{{ $adv['name'] }} ({{ $adv['email'] }})</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Pixel Name <span class="text-red-500">*</span></label>
                    <input type="text" id="px_name" placeholder="e.g. Main Website Pixel" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Pixel Type <span class="text-red-500">*</span></label>
                    <select id="px_type" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm bg-white">
                        <option value="conversion">Conversion</option>
                        <option value="pageview">Page View</option>
                        <option value="event">Event</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Category</label>
                    <select id="px_category" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm bg-white">
                        <option value="">Select category...</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Protocol</label>
                    <select id="px_protocol" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm bg-white">
                        <option value="https">HTTPS</option>
                        <option value="http">HTTP</option>
                        <option value="both">Both</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Pixel Goal Value</label>
                <input type="number" id="px_goal" step="0.01" min="0" placeholder="0.00" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Appended Code</label>
                <textarea id="px_code" rows="3" placeholder="Paste pixel code here..." class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></textarea>
            </div>
        </div>
        <div class="flex justify-end gap-2 mt-5">
            <button type="button" onclick="document.getElementById('newPixelModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">Cancel</button>
            <button type="button" onclick="createPixel()" class="px-4 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700">Create Pixel</button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- JAVASCRIPT                                             --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<script>
// ─── S2S Toggle ───
function toggleS2sSection() {
    var enabled = document.getElementById('s2sEnabledToggle').checked;
    document.getElementById('s2sSection').style.display = enabled ? 'block' : 'none';
}

// ─── Keyword Counter ───
function updateKeywordCount() {
    const count = document.querySelectorAll('.keyword-cb:checked').length;
    const countEl = document.getElementById('selectedKeywordCount');
    if (countEl) countEl.textContent = count;
}
document.querySelectorAll('.keyword-cb').forEach(cb => cb.addEventListener('change', updateKeywordCount));
updateKeywordCount();

// ─── Ad Formats Data (from PHP) ───
const adFormats = @json($adFormats);
const defaultAdTypeGroup = @json($defaultAdTypeGroup);
const displayScreens = @json($displayScreens);

// ─── Device list by type ───
const devicesByType = @json($devicesByType);

// ─── Device type change ───
document.getElementById('deviceTypeSelect').addEventListener('change', function() {
    const sel = document.getElementById('deviceListSelect');
    sel.innerHTML = '';
    const allDevices = Object.values(devicesByType).flat();
    const deviceOptions = this.value === 'all' ? allDevices : (devicesByType[this.value] || []);
    deviceOptions.forEach(d => {
        const o = document.createElement('option');
        o.value = d.value;
        o.textContent = d.label;
        sel.appendChild(o);
    });
});
document.getElementById('deviceTypeSelect').dispatchEvent(new Event('change'));

// ─── Ad Type / Format change ───
document.getElementById('adTypeSelect').addEventListener('change', function() {
    const formatSel = document.getElementById('adFormatSelect');
    formatSel.innerHTML = '<option value="">Select format...</option>';
    const type = this.value;
    if (type === 'all') {
        Object.entries(adFormats).forEach(([key, group]) => {
            const optgroup = document.createElement('optgroup');
            optgroup.label = group.label;
            if (key === 'display_web') {
                displayScreens.forEach((screen) => {
                    const o = document.createElement('option');
                    o.value = 'screen:' + screen.id;
                    o.textContent = screen.label;
                    o.dataset.group = key;
                    o.dataset.screenId = screen.id;
                    o.dataset.screenName = screen.screen_name;
                    o.dataset.dimension = screen.dimension;
                    optgroup.appendChild(o);
                });
            } else {
                Object.entries(group.sizes).forEach(([val, label]) => {
                    const o = document.createElement('option');
                    o.value = val;
                    o.textContent = label;
                    o.dataset.group = key;
                    optgroup.appendChild(o);
                });
            }
            formatSel.appendChild(optgroup);
        });
    } else if (adFormats[type]) {
        if (type === 'display_web') {
            displayScreens.forEach((screen) => {
                const o = document.createElement('option');
                o.value = 'screen:' + screen.id;
                o.textContent = screen.label;
                o.dataset.group = type;
                o.dataset.screenId = screen.id;
                o.dataset.screenName = screen.screen_name;
                o.dataset.dimension = screen.dimension;
                formatSel.appendChild(o);
            });
        } else {
            Object.entries(adFormats[type].sizes).forEach(([val, label]) => {
                const o = document.createElement('option');
                o.value = val;
                o.textContent = label;
                o.dataset.group = type;
                formatSel.appendChild(o);
            });
        }
    }
    document.getElementById('creativeFormBlock').classList.add('hidden');
});

document.getElementById('adFormatSelect').addEventListener('change', function() {
    const block = document.getElementById('creativeFormBlock');
    const format = this.value;
    const selectedOption = this.options[this.selectedIndex];
    const adType = document.getElementById('adTypeSelect').value;

    // Hide all field groups first
    document.getElementById('textAdFields').classList.add('hidden');
    document.getElementById('imageAdFields').classList.add('hidden');
    document.getElementById('videoAdFields').classList.add('hidden');
    document.getElementById('socialBarAdFields').classList.add('hidden');
    document.getElementById('nativeAdFields').classList.add('hidden');
    document.getElementById('interstitialAdFields').classList.add('hidden');
    document.getElementById('popunderAdFields').classList.add('hidden');
    document.getElementById('inPagePushAdFields').classList.add('hidden');
    document.getElementById('rewardedFields').classList.add('hidden');

    // URL-only formats (no file upload needed)
    const urlOnlyFormats = ['direct_link'];

    if (format) {
        block.classList.remove('hidden');

        // Show appropriate fields based on ad type/format
        if (format === 'social_bar') {
            document.getElementById('socialBarAdFields').classList.remove('hidden');
        } else if (format === 'popunder') {
            document.getElementById('popunderAdFields').classList.remove('hidden');
        } else if (format === 'interstitial') {
            document.getElementById('interstitialAdFields').classList.remove('hidden');
        } else if (format === 'in_page_push') {
            document.getElementById('inPagePushAdFields').classList.remove('hidden');
        } else if (format === 'native') {
            document.getElementById('nativeAdFields').classList.remove('hidden');
        } else if (format === 'text' || format === 'text_banner') {
            document.getElementById('textAdFields').classList.remove('hidden');
        } else if (adType === 'display_video' || format.includes('video') || format === 'instream' || format === 'outstream' || format === 'rewarded') {
            document.getElementById('videoAdFields').classList.remove('hidden');
            if (format === 'rewarded') {
                document.getElementById('rewardedFields').classList.remove('hidden');
            }
        } else if (urlOnlyFormats.includes(format)) {
            // URL-only ads: just Ad Name + Ad URL, no file upload needed
        } else {
            // Display banner ads — show image/file upload fields
            document.getElementById('imageAdFields').classList.remove('hidden');
        }
    } else {
        block.classList.add('hidden');
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const adTypeSelect = document.getElementById('adTypeSelect');
    if (adTypeSelect && defaultAdTypeGroup && adTypeSelect.value !== defaultAdTypeGroup) {
        adTypeSelect.value = defaultAdTypeGroup;
    }
    adTypeSelect?.dispatchEvent(new Event('change'));
});

// Video source type toggle
document.addEventListener('DOMContentLoaded', function() {
    const videoSourceSelect = document.getElementById('cr_video_source');
    if (videoSourceSelect) {
        videoSourceSelect.addEventListener('change', function() {
            const urlField = document.getElementById('videoUrlField');
            const uploadField = document.getElementById('videoUploadField');
            if (this.value === 'url') {
                urlField.classList.remove('hidden');
                uploadField.classList.add('hidden');
            } else {
                urlField.classList.add('hidden');
                uploadField.classList.remove('hidden');
            }
        });
    }
});

// ─── Traffic Source Bidding ───
let tsCounter = 0;
function addTrafficSource() {
    const srcEl = document.getElementById('ts_source');
    const bid = document.getElementById('ts_bid').value;
    const status = document.getElementById('ts_status').value;
    if (!srcEl.value || !bid) return alert('Please select a source and enter a bid rate.');

    const name = srcEl.options[srcEl.selectedIndex].dataset.name;
    const id = srcEl.value;
    tsCounter++;

    document.getElementById('tsEmptyRow')?.remove();
    const row = document.createElement('tr');
    row.className = 'hover:bg-gray-50/50';
    row.id = 'ts_row_' + tsCounter;
    row.innerHTML = `
        <td class="px-4 py-2.5 text-xs font-mono text-gray-500">#${id}</td>
        <td class="px-4 py-2.5 text-sm text-gray-800">${name}</td>
        <td class="px-4 py-2.5 text-sm text-right font-medium tabular-nums">${parseFloat(bid).toFixed(2)}</td>
        <td class="px-4 py-2.5 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase ${status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600'}">${status}</span></td>
        <td class="px-4 py-2.5 text-center">
            <button type="button" onclick="this.closest('tr').remove()" class="p-1 rounded hover:bg-red-50 text-gray-400 hover:text-red-600 transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            </button>
        </td>
    `;
    // Hidden inputs
    const inputs = `<input type="hidden" name="traffic_sources[${tsCounter}][id]" value="${id}"><input type="hidden" name="traffic_sources[${tsCounter}][name]" value="${name}"><input type="hidden" name="traffic_sources[${tsCounter}][bid]" value="${bid}"><input type="hidden" name="traffic_sources[${tsCounter}][status]" value="${status}">`;
    row.querySelector('td:first-child').insertAdjacentHTML('beforeend', inputs);
    document.getElementById('tsBody').appendChild(row);

    srcEl.value = '';
    document.getElementById('ts_bid').value = '';
}

// ─── Country Bidding ───
let cbCounter = 0;
function addCountryBid() {
    const countryEl = document.getElementById('cb_country');
    const pricing = document.getElementById('cb_pricing').value;
    const bid = document.getElementById('cb_bid').value;
    const status = document.getElementById('cb_status').value;
    if (!countryEl.value || !bid) return alert('Please select a country and enter a bid.');

    const name = countryEl.options[countryEl.selectedIndex].dataset.name;
    const code = countryEl.value;
    cbCounter++;

    document.getElementById('cbEmptyRow')?.remove();
    const row = document.createElement('tr');
    row.className = 'hover:bg-gray-50/50';
    row.innerHTML = `
        <td class="px-4 py-2.5 text-sm text-gray-800">${name} (${code})</td>
        <td class="px-4 py-2.5"><span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-100 text-blue-700">${pricing}</span></td>
        <td class="px-4 py-2.5 text-sm text-right font-medium tabular-nums">${parseFloat(bid).toFixed(2)}</td>
        <td class="px-4 py-2.5 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase ${status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600'}">${status}</span></td>
        <td class="px-4 py-2.5 text-center">
            <button type="button" onclick="this.closest('tr').remove()" class="p-1 rounded hover:bg-red-50 text-gray-400 hover:text-red-600 transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            </button>
        </td>
    `;
    const inputs = `<input type="hidden" name="country_bids[${cbCounter}][code]" value="${code}"><input type="hidden" name="country_bids[${cbCounter}][name]" value="${name}"><input type="hidden" name="country_bids[${cbCounter}][pricing]" value="${pricing}"><input type="hidden" name="country_bids[${cbCounter}][bid]" value="${bid}"><input type="hidden" name="country_bids[${cbCounter}][status]" value="${status}">`;
    row.querySelector('td:first-child').insertAdjacentHTML('beforeend', inputs);
    document.getElementById('cbBody').appendChild(row);

    countryEl.value = '';
    document.getElementById('cb_bid').value = '';
}

// ─── Creatives ───
const urlOnlyCreativeFormats = ['direct_link'];
const videoFormats = ['instream', 'outstream', 'rewarded'];
const textFormats = ['text', 'text_banner'];
let crCounter = 0;
function addCreative() {
    const name = document.getElementById('cr_name').value;
    const url = document.getElementById('cr_url').value;
    const formatSelect = document.getElementById('adFormatSelect');
    const selectedFormatOption = formatSelect.options[formatSelect.selectedIndex];
    const rawFormat = formatSelect.value;
    const format = selectedFormatOption?.dataset.screenId ? 'display_web' : rawFormat;
    const adType = document.getElementById('adTypeSelect').value;
    const isUrlOnly = urlOnlyCreativeFormats.includes(format);
    const isVideo = videoFormats.includes(format) || adType === 'display_video';
    const isText = textFormats.includes(format);
    const isSocialBar = format === 'social_bar';
    const isNative = format === 'native';
    const isInterstitial = format === 'interstitial';
    const isPopunder = format === 'popunder';
    const isInPagePush = format === 'in_page_push';
    const isRewarded = format === 'rewarded';

    // Determine content type
    let contentType = 'image';
    if (isSocialBar) contentType = 'text';
    else if (isPopunder) contentType = 'popunder';
    else if (isInterstitial) contentType = 'interstitial';
    else if (isInPagePush) contentType = 'in_page_push';
    else if (isNative) contentType = 'native';
    else if (isUrlOnly) contentType = 'url';
    else if (isVideo) contentType = 'video';
    else if (isText) contentType = 'text';
    else contentType = document.getElementById('cr_content_type')?.value || 'image';

    // Determine which file input to use
    let fileInput = null;
    if (!isUrlOnly && !isVideo && !isText && !isSocialBar && !isNative && !isInterstitial && !isPopunder && !isInPagePush) {
        fileInput = document.getElementById('cr_file');
    }

    if (!name || !url) return alert('Please fill in Ad Name and Ad URL.');

    // Gather video-specific fields
    let videoUrl = '';
    let videoFileInput = null;
    let videoThumbInput = null;
    let videoHeadline = '';
    let videoCta = '';
    if (isVideo) {
        const videoSourceType = document.getElementById('cr_video_source')?.value || 'url';
        if (videoSourceType === 'url') {
            videoUrl = document.getElementById('cr_video_url')?.value || '';
            if (!videoUrl) return alert('Please enter a Video URL.');
        } else {
            videoFileInput = document.getElementById('cr_video_file');
            if (!videoFileInput?.files[0]) return alert('Please select a video file to upload.');
        }
        videoThumbInput = document.getElementById('cr_video_thumb');
        videoHeadline = document.getElementById('cr_video_headline')?.value || '';
        videoCta = document.getElementById('cr_video_cta')?.value || '';
    }

    // Gather rewarded video fields
    let rewardAmount = '';
    let rewardType = '';
    if (isRewarded) {
        rewardAmount = document.getElementById('cr_reward_amount')?.value || '';
        rewardType = document.getElementById('cr_reward_type')?.value || 'Coins';
    }

    // Gather text ad fields
    let textTitle = '';
    let textDescription = '';
    let textBody = '';
    let textCta = '';
    if (isText) {
        textTitle = document.getElementById('cr_text_title')?.value || '';
        textDescription = document.getElementById('cr_text_description')?.value || '';
        textBody = document.getElementById('cr_text_body')?.value || '';
        textCta = document.getElementById('cr_text_cta')?.value || '';
        if (!textTitle) return alert('Please enter an Ad Title for the text ad.');
    }

    // Gather social bar fields
    let sbTitle = '';
    let sbDescription = '';
    let sbButtonText = '';
    let sbIconInput = null;
    if (isSocialBar) {
        sbTitle = document.getElementById('cr_sb_title')?.value || '';
        sbDescription = document.getElementById('cr_sb_description')?.value || '';
        sbButtonText = document.getElementById('cr_sb_button_text')?.value || 'Learn More';
        sbIconInput = document.getElementById('cr_sb_icon');
        if (!sbTitle) return alert('Please enter a Title for the Social Bar ad.');
    }

    // Gather native ad fields
    let nativeHeadline = '';
    let nativeBrand = '';
    let nativeBody = '';
    let nativeCta = '';
    let nativeImageInput = null;
    if (isNative) {
        nativeHeadline = document.getElementById('cr_native_headline')?.value || '';
        nativeBrand = document.getElementById('cr_native_brand')?.value || '';
        nativeBody = document.getElementById('cr_native_body')?.value || '';
        nativeCta = document.getElementById('cr_native_cta')?.value || '';
        nativeImageInput = document.getElementById('cr_native_image');
        if (!nativeHeadline) return alert('Please enter a Headline for the Native ad.');
    }

    // Gather interstitial ad fields
    let interstitialHeadline = '';
    let interstitialBody = '';
    let interstitialCta = '';
    let interstitialImageInput = null;
    if (isInterstitial) {
        interstitialHeadline = document.getElementById('cr_interstitial_headline')?.value || '';
        interstitialBody = document.getElementById('cr_interstitial_body')?.value || '';
        interstitialCta = document.getElementById('cr_interstitial_cta')?.value || '';
        interstitialImageInput = document.getElementById('cr_interstitial_image');
        if (!interstitialHeadline) return alert('Please enter a Headline for the Interstitial ad.');
    }

    // Gather popunder ad fields
    let popunderHeadline = '';
    let popunderBody = '';
    if (isPopunder) {
        popunderHeadline = document.getElementById('cr_popunder_headline')?.value || '';
        popunderBody = document.getElementById('cr_popunder_body')?.value || '';
        if (!popunderHeadline) return alert('Please enter a Headline for the Popunder ad.');
    }

    // Gather in-page push ad fields
    let ippHeadline = '';
    let ippBody = '';
    let ippIconInput = null;
    if (isInPagePush) {
        ippHeadline = document.getElementById('cr_ipp_headline')?.value || '';
        ippBody = document.getElementById('cr_ipp_body')?.value || '';
        ippIconInput = document.getElementById('cr_ipp_icon');
        if (!ippHeadline) return alert('Please enter a Headline for the In-Page Push ad.');
    }

    crCounter++;
    const file = fileInput ? fileInput.files[0] : (videoFileInput ? videoFileInput.files[0] : null);
    const sbIconFile = sbIconInput ? sbIconInput.files[0] : null;
    const nativeImageFile = nativeImageInput ? nativeImageInput.files[0] : null;
    const interstitialImageFile = interstitialImageInput ? interstitialImageInput.files[0] : null;
    const ippIconFile = ippIconInput ? ippIconInput.files[0] : null;
    const filename = file ? file.name : (isInPagePush ? (ippIconFile ? ippIconFile.name : 'In-Page Push') : (isPopunder ? 'Popunder' : (isInterstitial ? (interstitialImageFile ? interstitialImageFile.name : 'Interstitial') : (isNative ? (nativeImageFile ? nativeImageFile.name : 'Native Ad') : (isSocialBar ? 'Social Bar' : (isUrlOnly ? 'URL-only' : (isVideo && videoUrl ? 'Video URL' : (isText ? 'Text Ad' : '—'))))))));
    const fileSize = file ? (file.size / 1024).toFixed(1) : '—';
    const displayScreenId = selectedFormatOption?.dataset.screenId || '';
    const displayScreenName = selectedFormatOption?.dataset.screenName || '';
    const dimension = selectedFormatOption?.dataset.dimension || (rawFormat.includes('x') ? rawFormat : '—');
    const formatLabel = selectedFormatOption?.textContent || '—';

    // Preview column content
    let previewHtml = contentType.toUpperCase();
    if (contentType === 'image' && file) {
        previewHtml = '<img src="" class="w-8 h-8 rounded object-cover cr-preview-' + crCounter + '" />';
    } else if (isRewarded) {
        previewHtml = '<svg class="w-5 h-5 text-amber-500" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
    } else if (isVideo) {
        previewHtml = '<svg class="w-5 h-5 text-purple-500" viewBox="0 0 24 24" fill="none"><path d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" fill="currentColor"/><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/></svg>';
    } else if (isPopunder) {
        previewHtml = '<svg class="w-5 h-5 text-pink-500" viewBox="0 0 24 24" fill="none"><path d="M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M17 17l3 3m-3 0l3-3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';
    } else if (isInterstitial) {
        previewHtml = '<svg class="w-5 h-5 text-red-500" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M8 12h8M12 8v8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';
    } else if (isInPagePush) {
        previewHtml = '<svg class="w-5 h-5 text-teal-500" viewBox="0 0 24 24" fill="none"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M13.73 21a2 2 0 01-3.46 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';
    } else if (isNative) {
        previewHtml = '<svg class="w-5 h-5 text-indigo-500" viewBox="0 0 24 24" fill="none"><path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2z" stroke="currentColor" stroke-width="1.5"/><path d="M3 9h18M9 21V9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';
    } else if (isSocialBar) {
        previewHtml = '<svg class="w-5 h-5 text-orange-500" viewBox="0 0 24 24" fill="none"><rect x="2" y="17" width="20" height="5" rx="1" stroke="currentColor" stroke-width="1.5"/><path d="M8 19.5h8M6 19.5h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';
    } else if (isText) {
        previewHtml = '<svg class="w-5 h-5 text-blue-500" viewBox="0 0 24 24" fill="none"><path d="M4 6h16M4 12h16M4 18h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';
    }

    document.getElementById('crEmptyRow')?.remove();
    const row = document.createElement('tr');
    row.className = 'hover:bg-gray-50/50';
    row.innerHTML = `
        <td class="px-4 py-2.5 text-sm font-medium text-gray-800">${name}</td>
        <td class="px-4 py-2.5"><span class="inline-block w-8 h-8 rounded bg-gray-100 flex items-center justify-center text-[10px] text-gray-400">${previewHtml}</span></td>
        <td class="px-4 py-2.5 text-xs text-gray-500 font-mono max-w-[120px] truncate">${filename}</td>
        <td class="px-4 py-2.5 text-xs text-gray-600">${dimension}</td>
        <td class="px-4 py-2.5"><span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-purple-100 text-purple-700">${formatLabel}</span></td>
        <td class="px-4 py-2.5 text-right text-xs tabular-nums text-gray-600">${fileSize}</td>
        <td class="px-4 py-2.5 text-xs text-gray-500 max-w-[150px] truncate">${url}</td>
        <td class="px-4 py-2.5 text-center">
            <button type="button" onclick="this.closest('tr').remove()" class="p-1 rounded hover:bg-red-50 text-gray-400 hover:text-red-600 transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            </button>
        </td>
    `;
    // Add hidden inputs so ad_formats data gets submitted
    let hiddenInputs = `
        <input type="hidden" name="ad_formats[${crCounter}][ad_name]" value="${name.replace(/"/g, '&quot;')}">
        <input type="hidden" name="ad_formats[${crCounter}][ad_url]" value="${url.replace(/"/g, '&quot;')}">
        <input type="hidden" name="ad_formats[${crCounter}][ad_type]" value="${adType}">
        <input type="hidden" name="ad_formats[${crCounter}][format]" value="${format}">
        <input type="hidden" name="ad_formats[${crCounter}][format_label]" value="${formatLabel.replace(/"/g, '&quot;')}">
        <input type="hidden" name="ad_formats[${crCounter}][content_type]" value="${contentType}">
        <input type="hidden" name="ad_formats[${crCounter}][filename]" value="${filename}">
        <input type="hidden" name="ad_formats[${crCounter}][dimension]" value="${dimension}">
        <input type="hidden" name="ad_formats[${crCounter}][display_screen_id]" value="${displayScreenId}">
        <input type="hidden" name="ad_formats[${crCounter}][display_screen_name]" value="${displayScreenName.replace(/"/g, '&quot;')}">
        <input type="hidden" name="ad_formats[${crCounter}][file_path]" value="">
        <input type="hidden" name="ad_formats[${crCounter}][file_size]" value="${file ? file.size : 0}">
    `;

    // Video-specific hidden inputs
    if (isVideo) {
        hiddenInputs += `<input type="hidden" name="ad_formats[${crCounter}][video_url]" value="${videoUrl.replace(/"/g, '&quot;')}">`;
        hiddenInputs += `<input type="hidden" name="ad_formats[${crCounter}][video_headline]" value="${videoHeadline.replace(/"/g, '&quot;')}">`;
        hiddenInputs += `<input type="hidden" name="ad_formats[${crCounter}][video_cta]" value="${videoCta.replace(/"/g, '&quot;')}">`;
    }

    // Rewarded video hidden inputs
    if (isRewarded) {
        hiddenInputs += `<input type="hidden" name="ad_formats[${crCounter}][reward_amount]" value="${rewardAmount}">`;
        hiddenInputs += `<input type="hidden" name="ad_formats[${crCounter}][reward_type]" value="${rewardType.replace(/"/g, '&quot;')}">`;
    }

    // Text ad hidden inputs
    if (isText) {
        hiddenInputs += `
            <input type="hidden" name="ad_formats[${crCounter}][text_title]" value="${textTitle.replace(/"/g, '&quot;')}">
            <input type="hidden" name="ad_formats[${crCounter}][text_description]" value="${textDescription.replace(/"/g, '&quot;')}">
            <input type="hidden" name="ad_formats[${crCounter}][text_body]" value="${textBody.replace(/"/g, '&quot;')}">
            <input type="hidden" name="ad_formats[${crCounter}][text_cta]" value="${textCta.replace(/"/g, '&quot;')}">
        `;
    }

    // Social Bar hidden inputs
    if (isSocialBar) {
        hiddenInputs += `
            <input type="hidden" name="ad_formats[${crCounter}][text_title]" value="${sbTitle.replace(/"/g, '&quot;')}">
            <input type="hidden" name="ad_formats[${crCounter}][text_description]" value="${sbDescription.replace(/"/g, '&quot;')}">
            <input type="hidden" name="ad_formats[${crCounter}][text_body]" value="${sbButtonText.replace(/"/g, '&quot;')}">
        `;
    }

    // Native ad hidden inputs
    if (isNative) {
        hiddenInputs += `
            <input type="hidden" name="ad_formats[${crCounter}][native_headline]" value="${nativeHeadline.replace(/"/g, '&quot;')}">
            <input type="hidden" name="ad_formats[${crCounter}][native_brand]" value="${nativeBrand.replace(/"/g, '&quot;')}">
            <input type="hidden" name="ad_formats[${crCounter}][native_body]" value="${nativeBody.replace(/"/g, '&quot;')}">
            <input type="hidden" name="ad_formats[${crCounter}][native_cta]" value="${nativeCta.replace(/"/g, '&quot;')}">
        `;
    }

    // Interstitial ad hidden inputs
    if (isInterstitial) {
        hiddenInputs += `
            <input type="hidden" name="ad_formats[${crCounter}][interstitial_headline]" value="${interstitialHeadline.replace(/"/g, '&quot;')}">
            <input type="hidden" name="ad_formats[${crCounter}][interstitial_body]" value="${interstitialBody.replace(/"/g, '&quot;')}">
            <input type="hidden" name="ad_formats[${crCounter}][interstitial_cta]" value="${interstitialCta.replace(/"/g, '&quot;')}">
        `;
    }

    // Popunder ad hidden inputs
    if (isPopunder) {
        hiddenInputs += `
            <input type="hidden" name="ad_formats[${crCounter}][popunder_headline]" value="${popunderHeadline.replace(/"/g, '&quot;')}">
            <input type="hidden" name="ad_formats[${crCounter}][popunder_body]" value="${popunderBody.replace(/"/g, '&quot;')}">
        `;
    }

    // In-Page Push ad hidden inputs
    if (isInPagePush) {
        hiddenInputs += `
            <input type="hidden" name="ad_formats[${crCounter}][ipp_headline]" value="${ippHeadline.replace(/"/g, '&quot;')}">
            <input type="hidden" name="ad_formats[${crCounter}][ipp_body]" value="${ippBody.replace(/"/g, '&quot;')}">
        `;
    }

    row.querySelector('td:first-child').insertAdjacentHTML('beforeend', hiddenInputs);

    // Move the actual file input into the row so it gets submitted with the form
    if (file && fileInput) {
        const originalParent = fileInput.parentNode;
        fileInput.name = `ad_files[${crCounter}]`;
        fileInput.style.display = 'none';
        row.appendChild(fileInput);

        const newFileInput = document.createElement('input');
        newFileInput.type = 'file';
        newFileInput.id = 'cr_file';
        newFileInput.accept = 'image/*,.html,.swf,.gif';
        newFileInput.className = 'w-full px-3 py-1.5 rounded-lg border border-gray-200 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100';
        originalParent.appendChild(newFileInput);
    }

    // Move video file input into the row
    if (videoFileInput && videoFileInput.files[0]) {
        const originalParent = videoFileInput.parentNode;
        videoFileInput.name = `ad_files[${crCounter}]`;
        videoFileInput.style.display = 'none';
        row.appendChild(videoFileInput);

        const newVideoInput = document.createElement('input');
        newVideoInput.type = 'file';
        newVideoInput.id = 'cr_video_file';
        newVideoInput.accept = 'video/*';
        newVideoInput.className = 'w-full px-3 py-1.5 rounded-lg border border-gray-200 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100';
        originalParent.appendChild(newVideoInput);
    }

    // Move social bar icon file input into the row
    if (sbIconInput && sbIconFile) {
        const originalParent = sbIconInput.parentNode;
        sbIconInput.name = `ad_files[${crCounter}]`;
        sbIconInput.style.display = 'none';
        row.appendChild(sbIconInput);

        const newSbIconInput = document.createElement('input');
        newSbIconInput.type = 'file';
        newSbIconInput.id = 'cr_sb_icon';
        newSbIconInput.accept = 'image/*';
        newSbIconInput.className = 'w-full px-3 py-1.5 rounded-lg border border-gray-200 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100';
        originalParent.appendChild(newSbIconInput);
    }

    // Move native ad image file input into the row
    if (nativeImageInput && nativeImageFile) {
        const originalParent = nativeImageInput.parentNode;
        nativeImageInput.name = `ad_files[${crCounter}]`;
        nativeImageInput.style.display = 'none';
        row.appendChild(nativeImageInput);

        const newNativeImageInput = document.createElement('input');
        newNativeImageInput.type = 'file';
        newNativeImageInput.id = 'cr_native_image';
        newNativeImageInput.accept = 'image/*';
        newNativeImageInput.className = 'w-full px-3 py-1.5 rounded-lg border border-gray-200 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100';
        originalParent.appendChild(newNativeImageInput);
    }

    // Move interstitial background image file input into the row
    if (interstitialImageInput && interstitialImageFile) {
        const originalParent = interstitialImageInput.parentNode;
        interstitialImageInput.name = `ad_files[${crCounter}]`;
        interstitialImageInput.style.display = 'none';
        row.appendChild(interstitialImageInput);

        const newInterstitialImageInput = document.createElement('input');
        newInterstitialImageInput.type = 'file';
        newInterstitialImageInput.id = 'cr_interstitial_image';
        newInterstitialImageInput.accept = 'image/*';
        newInterstitialImageInput.className = 'w-full px-3 py-1.5 rounded-lg border border-gray-200 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100';
        originalParent.appendChild(newInterstitialImageInput);
    }

    // Move in-page push icon file input into the row
    if (ippIconInput && ippIconFile) {
        const originalParent = ippIconInput.parentNode;
        ippIconInput.name = `ad_files[${crCounter}]`;
        ippIconInput.style.display = 'none';
        row.appendChild(ippIconInput);

        const newIppIconInput = document.createElement('input');
        newIppIconInput.type = 'file';
        newIppIconInput.id = 'cr_ipp_icon';
        newIppIconInput.accept = 'image/*';
        newIppIconInput.className = 'w-full px-3 py-1.5 rounded-lg border border-gray-200 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100';
        originalParent.appendChild(newIppIconInput);
    }

    // Move video thumbnail input into the row
    if (videoThumbInput && videoThumbInput.files[0]) {
        const originalParent = videoThumbInput.parentNode;
        videoThumbInput.name = `ad_thumbs[${crCounter}]`;
        videoThumbInput.style.display = 'none';
        row.appendChild(videoThumbInput);

        const newThumbInput = document.createElement('input');
        newThumbInput.type = 'file';
        newThumbInput.id = 'cr_video_thumb';
        newThumbInput.accept = 'image/*';
        newThumbInput.className = 'w-full px-3 py-1.5 rounded-lg border border-gray-200 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100';
        originalParent.appendChild(newThumbInput);
    }

    document.getElementById('crBody').appendChild(row);

    // Preview image
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = e => {
            const img = row.querySelector('.cr-preview-' + crCounter);
            if (img) img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    // Reset fields
    document.getElementById('cr_name').value = '';
    document.getElementById('cr_url').value = '';
    if (document.getElementById('cr_video_url')) document.getElementById('cr_video_url').value = '';
    if (document.getElementById('cr_video_headline')) document.getElementById('cr_video_headline').value = '';
    if (document.getElementById('cr_video_cta')) { document.getElementById('cr_video_cta').value = 'Learn More'; }
    if (document.getElementById('cr_text_title')) document.getElementById('cr_text_title').value = '';
    if (document.getElementById('cr_text_description')) document.getElementById('cr_text_description').value = '';
    if (document.getElementById('cr_text_body')) document.getElementById('cr_text_body').value = '';
    if (document.getElementById('cr_text_cta')) document.getElementById('cr_text_cta').value = '';
    if (document.getElementById('cr_sb_title')) document.getElementById('cr_sb_title').value = '';
    if (document.getElementById('cr_sb_description')) document.getElementById('cr_sb_description').value = '';
    if (document.getElementById('cr_sb_button_text')) document.getElementById('cr_sb_button_text').value = 'Learn More';
    if (document.getElementById('cr_native_headline')) document.getElementById('cr_native_headline').value = '';
    if (document.getElementById('cr_native_brand')) document.getElementById('cr_native_brand').value = '';
    if (document.getElementById('cr_native_body')) document.getElementById('cr_native_body').value = '';
    if (document.getElementById('cr_native_cta')) document.getElementById('cr_native_cta').value = '';
    if (document.getElementById('cr_interstitial_headline')) document.getElementById('cr_interstitial_headline').value = '';
    if (document.getElementById('cr_interstitial_body')) document.getElementById('cr_interstitial_body').value = '';
    if (document.getElementById('cr_interstitial_cta')) document.getElementById('cr_interstitial_cta').value = '';
    if (document.getElementById('cr_popunder_headline')) document.getElementById('cr_popunder_headline').value = '';
    if (document.getElementById('cr_popunder_body')) document.getElementById('cr_popunder_body').value = '';
    if (document.getElementById('cr_ipp_headline')) document.getElementById('cr_ipp_headline').value = '';
    if (document.getElementById('cr_ipp_body')) document.getElementById('cr_ipp_body').value = '';
    if (document.getElementById('cr_reward_amount')) document.getElementById('cr_reward_amount').value = '';
    if (document.getElementById('cr_reward_type')) document.getElementById('cr_reward_type').value = 'Coins';
}

// ─── Create Group (AJAX) ───
function createGroup() {
    const name = document.getElementById('newGroupName').value.trim();
    if (!name) return alert('Please enter a group name.');

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value;

    // Send AJAX request
    fetch('{{ route("admin.campaigns.groups.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ name: name })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const sel = document.querySelector('[name="group_id"]');
            const opt = document.createElement('option');
            opt.value = data.group.id;
            opt.textContent = data.group.name;
            opt.selected = true;
            sel.appendChild(opt);
            document.getElementById('newGroupName').value = '';
            document.getElementById('newGroupModal').classList.add('hidden');
            // Show success message
            showNotification('Campaign group created successfully!', 'success');
        } else {
            alert('Error creating group: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to create campaign group. Please try again.');
    });
}

// ─── Show notification helper ───
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-4 py-3 rounded-lg shadow-lg z-50 ${type === 'success' ? 'bg-emerald-500' : 'bg-red-500'} text-white text-sm font-medium`;
    notification.textContent = message;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

// ─── Create Pixel (AJAX) ───
function createPixel() {
    const name = document.getElementById('px_name').value.trim();
    const type = document.getElementById('px_type').value;
    const adv = document.getElementById('px_advertiser');
    const categoryId = document.getElementById('px_category').value;
    const protocol = document.getElementById('px_protocol').value;
    const goalValue = document.getElementById('px_goal').value;
    const code = document.getElementById('px_code').value.trim();

    if (!name || !adv.value) return alert('Please fill in Advertiser and Pixel Name.');

    const advText = adv.options[adv.selectedIndex].textContent;

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value;

    // Send AJAX request
    fetch('{{ route("admin.campaigns.pixels.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            name: name,
            type: type,
            advertiser_id: adv.value,
            advertiser_name: advText,
            category_id: categoryId,
            protocol: protocol,
            goal_value: goalValue,
            code: code
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const sel = document.getElementById('pixelSelect');
            const opt = document.createElement('option');
            opt.value = data.pixel.id;
            opt.textContent = `${data.pixel.name} (${data.pixel.type}) — ${data.pixel.advertiser}`;
            opt.selected = true;
            sel.appendChild(opt);
            // Reset modal
            document.getElementById('px_name').value = '';
            document.getElementById('px_goal').value = '';
            document.getElementById('px_code').value = '';
            document.getElementById('newPixelModal').classList.add('hidden');
            // Show success message
            showNotification('Pixel tracker created successfully!', 'success');
        } else {
            alert('Error creating pixel: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to create pixel tracker. Please try again.');
    });
}

// ─── Dayparting presets ───
document.querySelectorAll('.daypart-preset').forEach(btn => {
    btn.addEventListener('click', function() {
        const preset = this.dataset.preset;
        const cells = document.querySelectorAll('.daypart-cell');

        // Clear all first
        cells.forEach(c => c.checked = false);

        if (preset === 'all') {
            cells.forEach(c => c.checked = true);
        } else if (preset === 'working-days') {
            cells.forEach(c => { if (!['saturday','sunday'].includes(c.dataset.day)) c.checked = true; });
        } else if (preset === 'weekend') {
            cells.forEach(c => { if (['saturday','sunday'].includes(c.dataset.day)) c.checked = true; });
        } else if (preset === 'only-night') {
            cells.forEach(c => { const h = parseInt(c.dataset.hour); if (h >= 22 || h < 6) c.checked = true; });
        } else if (preset === 'only-day') {
            cells.forEach(c => { const h = parseInt(c.dataset.hour); if (h >= 8 && h < 20) c.checked = true; });
        } else if (preset === 'only-morning') {
            cells.forEach(c => { const h = parseInt(c.dataset.hour); if (h >= 6 && h < 12) c.checked = true; });
        }

        // Highlight active preset
        document.querySelectorAll('.daypart-preset').forEach(b => b.classList.remove('bg-brand-50','border-brand-300','text-brand-600'));
        this.classList.add('bg-brand-50','border-brand-300','text-brand-600');
    });
});

// ─── City Targeting: dynamic add/remove rows ───
const citiesByCountry = @json($cities);
let cityRowIndex = 0;

function createCityRow(selectedCountry = '', selectedCity = '') {
    const row = document.createElement('div');
    row.className = 'flex items-center gap-2 city-row';
    row.dataset.index = cityRowIndex;

    // Country select
    const countrySelect = document.createElement('select');
    countrySelect.className = 'w-1/3 px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white city-country-select';
    countrySelect.innerHTML = '<option value="">Select Country</option>';
    Object.keys(citiesByCountry).forEach(code => {
        const opt = document.createElement('option');
        opt.value = code;
        const countryNames = {AL:'Albania',BA:'Bosnia and Herzegovina',BG:'Bulgaria',HR:'Croatia',GR:'Greece',XK:'Kosovo',ME:'Montenegro',MK:'North Macedonia',RO:'Romania',RS:'Serbia',SI:'Slovenia',TR:'Turkey'};
        opt.textContent = (countryNames[code] || code) + ' (' + code + ')';
        if (code === selectedCountry) opt.selected = true;
        countrySelect.appendChild(opt);
    });

    // City select
    const citySelect = document.createElement('select');
    citySelect.className = 'flex-1 px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white city-city-select';
    citySelect.innerHTML = '<option value="">Select City</option>';
    citySelect.disabled = !selectedCountry;

    if (selectedCountry && citiesByCountry[selectedCountry]) {
        citiesByCountry[selectedCountry].forEach(city => {
            const opt = document.createElement('option');
            opt.value = city;
            opt.textContent = city;
            if (city === selectedCity) opt.selected = true;
            citySelect.appendChild(opt);
        });
    }

    // When country changes, repopulate city dropdown
    countrySelect.addEventListener('change', function() {
        const code = this.value;
        citySelect.innerHTML = '<option value="">Select City</option>';
        citySelect.disabled = !code;
        if (code && citiesByCountry[code]) {
            citiesByCountry[code].forEach(city => {
                const opt = document.createElement('option');
                opt.value = city;
                opt.textContent = city;
                citySelect.appendChild(opt);
            });
        }
    });

    // Remove button
    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'p-2 rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50 transition';
    removeBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';
    removeBtn.addEventListener('click', function() {
        row.remove();
    });

    row.appendChild(countrySelect);
    row.appendChild(citySelect);
    row.appendChild(removeBtn);

    document.getElementById('cityTargetingList').appendChild(row);
    cityRowIndex++;
}

document.getElementById('addCityBtn').addEventListener('click', function() {
    createCityRow();
});

// ─── OS Targeting: dynamic add/remove rows ───
const osList = @json($operatingSystems);

function createOsRow(selectedOs = '', selectedVersion = '') {
    const row = document.createElement('div');
    row.className = 'flex items-center gap-2 os-row';

    const osSelect = document.createElement('select');
    osSelect.className = 'w-1/3 px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white os-name-select';
    osSelect.innerHTML = '<option value="">Select OS</option>';
    Object.keys(osList).forEach(os => {
        const opt = document.createElement('option');
        opt.value = os;
        opt.textContent = os;
        if (os === selectedOs) opt.selected = true;
        osSelect.appendChild(opt);
    });

    const versionSelect = document.createElement('select');
    versionSelect.className = 'flex-1 px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white os-version-select';
    versionSelect.innerHTML = '<option value="">All Versions</option>';
    versionSelect.disabled = !selectedOs;

    if (selectedOs && osList[selectedOs]) {
        osList[selectedOs].forEach(ver => {
            const opt = document.createElement('option');
            opt.value = ver;
            opt.textContent = ver;
            if (ver === selectedVersion) opt.selected = true;
            versionSelect.appendChild(opt);
        });
    }

    osSelect.addEventListener('change', function() {
        const os = this.value;
        versionSelect.innerHTML = '<option value="">All Versions</option>';
        versionSelect.disabled = !os;
        if (os && osList[os]) {
            osList[os].forEach(ver => {
                const opt = document.createElement('option');
                opt.value = ver;
                opt.textContent = ver;
                versionSelect.appendChild(opt);
            });
        }
    });

    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'p-2 rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50 transition';
    removeBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';
    removeBtn.addEventListener('click', () => row.remove());

    row.appendChild(osSelect);
    row.appendChild(versionSelect);
    row.appendChild(removeBtn);
    document.getElementById('osTargetingList').appendChild(row);
}

document.getElementById('addOsBtn').addEventListener('click', () => createOsRow());

// ─── Browser Targeting: dynamic add/remove rows ───
const browserList = @json($browsers);

function createBrowserRow(selectedBrowser = '', selectedVersion = '') {
    const row = document.createElement('div');
    row.className = 'flex items-center gap-2 browser-row';

    const browserSelect = document.createElement('select');
    browserSelect.className = 'w-1/3 px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white browser-name-select';
    browserSelect.innerHTML = '<option value="">Select Browser</option>';
    Object.keys(browserList).forEach(br => {
        const opt = document.createElement('option');
        opt.value = br;
        opt.textContent = br;
        if (br === selectedBrowser) opt.selected = true;
        browserSelect.appendChild(opt);
    });

    const versionSelect = document.createElement('select');
    versionSelect.className = 'flex-1 px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white browser-version-select';
    versionSelect.innerHTML = '<option value="">All Versions</option>';
    versionSelect.disabled = !selectedBrowser;

    if (selectedBrowser && browserList[selectedBrowser]) {
        browserList[selectedBrowser].forEach(ver => {
            const opt = document.createElement('option');
            opt.value = ver;
            opt.textContent = ver;
            if (ver === selectedVersion) opt.selected = true;
            versionSelect.appendChild(opt);
        });
    }

    browserSelect.addEventListener('change', function() {
        const br = this.value;
        versionSelect.innerHTML = '<option value="">All Versions</option>';
        versionSelect.disabled = !br;
        if (br && browserList[br]) {
            browserList[br].forEach(ver => {
                const opt = document.createElement('option');
                opt.value = ver;
                opt.textContent = ver;
                versionSelect.appendChild(opt);
            });
        }
    });

    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'p-2 rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50 transition';
    removeBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';
    removeBtn.addEventListener('click', () => row.remove());

    row.appendChild(browserSelect);
    row.appendChild(versionSelect);
    row.appendChild(removeBtn);
    document.getElementById('browserTargetingList').appendChild(row);
}

document.getElementById('addBrowserBtn').addEventListener('click', () => createBrowserRow());

// ─── Carrier Targeting: dynamic add/remove rows ───
const carriersByCountry = @json($mobileCarriers);

function createCarrierRow(selectedCountry = '', selectedCarrier = '') {
    const row = document.createElement('div');
    row.className = 'carrier-row flex items-center gap-2';

    const countrySelect = document.createElement('select');
    countrySelect.className = 'carrier-country-select flex-1 px-3 py-2 rounded-lg border border-gray-200 text-sm bg-white';
    countrySelect.innerHTML = '<option value="">Select Country...</option>';
    Object.keys(carriersByCountry).forEach(code => {
        const opt = document.createElement('option');
        opt.value = code;
        opt.textContent = code;
        if (code === selectedCountry) opt.selected = true;
        countrySelect.appendChild(opt);
    });

    const carrierSelect = document.createElement('select');
    carrierSelect.className = 'carrier-name-select flex-1 px-3 py-2 rounded-lg border border-gray-200 text-sm bg-white';
    carrierSelect.disabled = !selectedCountry;
    carrierSelect.innerHTML = '<option value="">Select Carrier...</option>';
    if (selectedCountry && carriersByCountry[selectedCountry]) {
        carriersByCountry[selectedCountry].forEach(c => {
            const opt = document.createElement('option');
            opt.value = c;
            opt.textContent = c;
            if (c === selectedCarrier) opt.selected = true;
            carrierSelect.appendChild(opt);
        });
    }

    countrySelect.addEventListener('change', function() {
        const code = this.value;
        carrierSelect.innerHTML = '<option value="">Select Carrier...</option>';
        carrierSelect.disabled = !code;
        if (code && carriersByCountry[code]) {
            carriersByCountry[code].forEach(c => {
                const opt = document.createElement('option');
                opt.value = c;
                opt.textContent = c;
                carrierSelect.appendChild(opt);
            });
        }
    });

    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'p-2 rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50 transition';
    removeBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';
    removeBtn.addEventListener('click', () => row.remove());

    row.appendChild(countrySelect);
    row.appendChild(carrierSelect);
    row.appendChild(removeBtn);
    document.getElementById('carrierTargetingList').appendChild(row);
}

document.getElementById('addCarrierBtn').addEventListener('click', () => createCarrierRow());

// ─── Form submission: Convert multi-select to JSON ───
document.getElementById('campaignForm').addEventListener('submit', function(e) {
    // Convert targeting_geo multi-select to JSON
    const geoSelect = document.querySelector('select[name="targeting_geo[]"]');
    if (geoSelect) {
        const selectedGeo = Array.from(geoSelect.selectedOptions).map(opt => opt.value);
        geoSelect.name = '';
        if (selectedGeo.length > 0) {
            const hiddenGeo = document.createElement('input');
            hiddenGeo.type = 'hidden';
            hiddenGeo.name = 'targeting_geo';
            hiddenGeo.value = JSON.stringify(selectedGeo);
            this.appendChild(hiddenGeo);
        }
    }

    // Convert targeting_device multi-select to JSON
    const deviceSelect = document.querySelector('select[name="target_devices[]"]');
    if (deviceSelect) {
        const selectedDevices = Array.from(deviceSelect.selectedOptions).map(opt => opt.value);
        if (selectedDevices.length > 0) {
            const hiddenDevice = document.createElement('input');
            hiddenDevice.type = 'hidden';
            hiddenDevice.name = 'targeting_device';
            hiddenDevice.value = JSON.stringify(selectedDevices);
            this.appendChild(hiddenDevice);
        }
    }

    // Collect city targeting rows into JSON
    const cityRows = document.querySelectorAll('.city-row');
    const cityData = [];
    cityRows.forEach(row => {
        const country = row.querySelector('.city-country-select')?.value;
        const city = row.querySelector('.city-city-select')?.value;
        if (country && city) {
            cityData.push({ country: country, city: city });
        }
    });
    if (cityData.length > 0) {
        const hiddenCity = document.createElement('input');
        hiddenCity.type = 'hidden';
        hiddenCity.name = 'targeting_city';
        hiddenCity.value = JSON.stringify(cityData);
        this.appendChild(hiddenCity);
    }

    // Collect OS targeting rows
    const osRows = document.querySelectorAll('.os-row');
    const osData = [];
    const osVersionData = [];
    osRows.forEach(row => {
        const os = row.querySelector('.os-name-select')?.value;
        const version = row.querySelector('.os-version-select')?.value;
        if (os) {
            if (!osData.includes(os)) osData.push(os);
            if (version) osVersionData.push({ os: os, version: version });
        }
    });
    if (osData.length > 0) {
        const h = document.createElement('input');
        h.type = 'hidden'; h.name = 'targeting_os'; h.value = JSON.stringify(osData);
        this.appendChild(h);
    }
    if (osVersionData.length > 0) {
        const h = document.createElement('input');
        h.type = 'hidden'; h.name = 'targeting_os_version'; h.value = JSON.stringify(osVersionData);
        this.appendChild(h);
    }

    // Collect Browser targeting rows
    const browserRows = document.querySelectorAll('.browser-row');
    const browserData = [];
    const browserVersionData = [];
    browserRows.forEach(row => {
        const br = row.querySelector('.browser-name-select')?.value;
        const version = row.querySelector('.browser-version-select')?.value;
        if (br) {
            if (!browserData.includes(br)) browserData.push(br);
            if (version) browserVersionData.push({ browser: br, version: version });
        }
    });
    if (browserData.length > 0) {
        const h = document.createElement('input');
        h.type = 'hidden'; h.name = 'targeting_browser'; h.value = JSON.stringify(browserData);
        this.appendChild(h);
    }
    if (browserVersionData.length > 0) {
        const h = document.createElement('input');
        h.type = 'hidden'; h.name = 'targeting_browser_version'; h.value = JSON.stringify(browserVersionData);
        this.appendChild(h);
    }

    // Collect Connection Type checkboxes
    const connChecked = Array.from(document.querySelectorAll('.connection-type-cb:checked')).map(cb => cb.value);
    if (connChecked.length > 0) {
        document.getElementById('targeting_connection_type').value = JSON.stringify(connChecked);
    }

    // Collect Carrier targeting rows
    const carrierRows = document.querySelectorAll('.carrier-row');
    const carrierData = [];
    carrierRows.forEach(row => {
        const country = row.querySelector('.carrier-country-select')?.value;
        const carrier = row.querySelector('.carrier-name-select')?.value;
        if (country && carrier) {
            carrierData.push({ country: country, carrier: carrier });
        }
    });
    if (carrierData.length > 0) {
        const h = document.createElement('input');
        h.type = 'hidden'; h.name = 'targeting_carrier'; h.value = JSON.stringify(carrierData);
        this.appendChild(h);
    }

    // Collect Language targeting
    const langSelect = document.getElementById('languageSelect');
    if (langSelect) {
        const selectedLangs = Array.from(langSelect.selectedOptions).map(opt => opt.value);
        if (selectedLangs.length > 0) {
            document.getElementById('targeting_language').value = JSON.stringify(selectedLangs);
        }
    }
});
</script>
@endsection
