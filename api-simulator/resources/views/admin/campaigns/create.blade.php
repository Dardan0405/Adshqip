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
                    <input type="number" name="bid_amount" required step="0.01" min="0" placeholder="0.00" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
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
                    <input type="number" name="total_budget" step="0.01" min="0" placeholder="0.00" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
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
                        <option value="desktop">Desktop</option>
                        <option value="mobile">Mobile</option>
                        <option value="tablet">Tablet</option>
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
                        <option value="display_web">Display Web</option>
                        <option value="special_web">Special Web</option>
                        <option value="display_video">Display Video</option>
                    </select>
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
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Video Thumbnail (Optional)</label>
                        <input type="file" id="cr_video_thumb" accept="image/*" class="w-full px-3 py-1.5 rounded-lg border border-gray-200 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100">
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
// ─── Ad Formats Data (from PHP) ───
const adFormats = @json($adFormats);

// ─── Device list by type ───
const devicesByType = {
    all: ['Windows PC','MacBook','iMac','Linux Desktop','iPhone','Samsung Galaxy','Google Pixel','iPad','Android Tablet','Huawei','Xiaomi','OnePlus','Oppo'],
    desktop: ['Windows PC','MacBook','iMac','Linux Desktop','Chromebook'],
    mobile: ['iPhone','Samsung Galaxy','Google Pixel','Huawei','Xiaomi','OnePlus','Oppo','Vivo','Realme','Nokia'],
    tablet: ['iPad','iPad Pro','Samsung Tab','Amazon Fire','Lenovo Tab','Microsoft Surface'],
};

// ─── Device type change ───
document.getElementById('deviceTypeSelect').addEventListener('change', function() {
    const sel = document.getElementById('deviceListSelect');
    sel.innerHTML = '';
    (devicesByType[this.value] || []).forEach(d => {
        const o = document.createElement('option');
        o.value = d.toLowerCase().replace(/\s+/g, '_');
        o.textContent = d;
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
            Object.entries(group.sizes).forEach(([val, label]) => {
                const o = document.createElement('option');
                o.value = val;
                o.textContent = label;
                o.dataset.group = key;
                optgroup.appendChild(o);
            });
            formatSel.appendChild(optgroup);
        });
    } else if (adFormats[type]) {
        Object.entries(adFormats[type].sizes).forEach(([val, label]) => {
            const o = document.createElement('option');
            o.value = val;
            o.textContent = label;
            o.dataset.group = type;
            formatSel.appendChild(o);
        });
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

    // URL-only formats (no file upload needed)
    const urlOnlyFormats = ['interstitial', 'native', 'popunder', 'direct_link', 'in_page_push'];

    if (format) {
        block.classList.remove('hidden');

        // Show appropriate fields based on ad type/format
        if (format === 'text' || format === 'text_banner') {
            document.getElementById('textAdFields').classList.remove('hidden');
        } else if (adType === 'display_video' || format.includes('video')) {
            document.getElementById('videoAdFields').classList.remove('hidden');
        } else if (urlOnlyFormats.includes(format)) {
            // URL-only ads: just Ad Name + Ad URL, no file upload needed
            // Fields are already visible (cr_name + cr_url), nothing extra to show
        } else {
            // Display banner ads — show image/file upload fields
            document.getElementById('imageAdFields').classList.remove('hidden');
        }
    } else {
        block.classList.add('hidden');
    }
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
    const inputs = `<input type="hidden" name="traffic_sources[${tsCounter}][id]" value="${id}"><input type="hidden" name="traffic_sources[${tsCounter}][bid]" value="${bid}"><input type="hidden" name="traffic_sources[${tsCounter}][status]" value="${status}">`;
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
    const inputs = `<input type="hidden" name="country_bids[${cbCounter}][code]" value="${code}"><input type="hidden" name="country_bids[${cbCounter}][pricing]" value="${pricing}"><input type="hidden" name="country_bids[${cbCounter}][bid]" value="${bid}"><input type="hidden" name="country_bids[${cbCounter}][status]" value="${status}">`;
    row.querySelector('td:first-child').insertAdjacentHTML('beforeend', inputs);
    document.getElementById('cbBody').appendChild(row);

    countryEl.value = '';
    document.getElementById('cb_bid').value = '';
}

// ─── Creatives ───
const urlOnlyCreativeFormats = ['interstitial', 'native', 'popunder', 'direct_link', 'in_page_push'];
let crCounter = 0;
function addCreative() {
    const name = document.getElementById('cr_name').value;
    const url = document.getElementById('cr_url').value;
    const format = document.getElementById('adFormatSelect').value;
    const isUrlOnly = urlOnlyCreativeFormats.includes(format);
    const contentType = isUrlOnly ? 'url' : (document.getElementById('cr_content_type')?.value || 'image');
    const fileInput = isUrlOnly ? null : document.getElementById('cr_file');
    if (!name || !url) return alert('Please fill in Ad Name and Ad URL.');

    crCounter++;
    const file = fileInput ? fileInput.files[0] : null;
    const filename = file ? file.name : (isUrlOnly ? 'URL-only' : '—');
    const fileSize = file ? (file.size / 1024).toFixed(1) : '—';
    const dimension = format.includes('x') ? format : '—';
    const formatLabel = document.getElementById('adFormatSelect').options[document.getElementById('adFormatSelect').selectedIndex].textContent;

    document.getElementById('crEmptyRow')?.remove();
    const row = document.createElement('tr');
    row.className = 'hover:bg-gray-50/50';
    row.innerHTML = `
        <td class="px-4 py-2.5 text-sm font-medium text-gray-800">${name}</td>
        <td class="px-4 py-2.5"><span class="inline-block w-8 h-8 rounded bg-gray-100 flex items-center justify-center text-[10px] text-gray-400">${contentType === 'image' && file ? '<img src="" class="w-8 h-8 rounded object-cover cr-preview-' + crCounter + '" />' : contentType.toUpperCase()}</span></td>
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
    const adType = document.getElementById('adTypeSelect').value;
    const hiddenInputs = `
        <input type="hidden" name="ad_formats[${crCounter}][ad_name]" value="${name.replace(/"/g, '&quot;')}">
        <input type="hidden" name="ad_formats[${crCounter}][ad_url]" value="${url.replace(/"/g, '&quot;')}">
        <input type="hidden" name="ad_formats[${crCounter}][ad_type]" value="${adType}">
        <input type="hidden" name="ad_formats[${crCounter}][format]" value="${format}">
        <input type="hidden" name="ad_formats[${crCounter}][format_label]" value="${formatLabel.replace(/"/g, '&quot;')}">
        <input type="hidden" name="ad_formats[${crCounter}][content_type]" value="${contentType}">
        <input type="hidden" name="ad_formats[${crCounter}][filename]" value="${filename}">
        <input type="hidden" name="ad_formats[${crCounter}][dimension]" value="${dimension}">
        <input type="hidden" name="ad_formats[${crCounter}][file_path]" value="">
        <input type="hidden" name="ad_formats[${crCounter}][file_size]" value="${file ? file.size : 0}">
    `;
    row.querySelector('td:first-child').insertAdjacentHTML('beforeend', hiddenInputs);

    // Move the actual file input into the row so it gets submitted with the form
    if (file) {
        const originalParent = fileInput.parentNode; // Save parent BEFORE moving

        fileInput.name = `ad_files[${crCounter}]`;
        fileInput.style.display = 'none';
        row.appendChild(fileInput);

        // Create a new file input in the ORIGINAL location
        const newFileInput = document.createElement('input');
        newFileInput.type = 'file';
        newFileInput.id = 'cr_file';
        newFileInput.accept = 'image/*,.html,.swf,.gif';
        newFileInput.className = 'w-full px-3 py-1.5 rounded-lg border border-gray-200 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100';
        originalParent.appendChild(newFileInput);
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

    // Reset text fields only (file input was already replaced)
    document.getElementById('cr_name').value = '';
    document.getElementById('cr_url').value = '';
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

// ─── Form submission: Convert multi-select to JSON ───
document.getElementById('campaignForm').addEventListener('submit', function(e) {
    // Convert targeting_geo multi-select to JSON
    const geoSelect = document.querySelector('select[name="targeting_geo[]"]');
    if (geoSelect) {
        const selectedGeo = Array.from(geoSelect.selectedOptions).map(opt => opt.value);
        // Remove the original field name
        geoSelect.name = '';
        // Add hidden input with JSON value
        if (selectedGeo.length > 0) {
            const hiddenGeo = document.createElement('input');
            hiddenGeo.type = 'hidden';
            hiddenGeo.name = 'targeting_geo';
            hiddenGeo.value = JSON.stringify(selectedGeo);
            this.appendChild(hiddenGeo);
        }
    }

    // Convert targeting_device multi-select to JSON (if needed)
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
});
</script>
@endsection
