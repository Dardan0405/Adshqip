@extends('layouts.advertiser')

@section('title', 'Create Direct Campaign')

@section('content')
<style>
    #daypartingBlock table { border-collapse: collapse; table-layout: fixed; width: 100%; }
    #daypartingBlock td, #daypartingBlock th { min-height: 0; height: auto; overflow: hidden; position: relative; }
    #daypartingBlock label { display: inline-flex !important; align-items: center !important; justify-content: center !important; margin: 0 !important; padding: 0 !important; line-height: 1 !important; width: 16px; height: 16px; }
    #daypartingBlock .daypart-cell { position: absolute; opacity: 0; pointer-events: none; width: 1px; height: 1px; }
    #directCampaignForm { position: relative; overflow-anchor: none; }
    html { scroll-behavior: auto; overflow-anchor: none; }
</style>
<form method="POST" action="{{ route('advertiser.direct-campaigns.store') }}" enctype="multipart/form-data" id="directCampaignForm">
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
                <a href="{{ route('advertiser.direct-campaigns') }}" class="hover:text-gray-600 transition-colors">Direct Campaigns</a>
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                <span class="text-gray-600">Create Direct Campaign</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Create New Direct Campaign</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('advertiser.direct-campaigns') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">Cancel</a>
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Campaign Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required value="{{ old('name') }}" placeholder="e.g. Premium Direct — Albania Q4" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Pricing Model <span class="text-red-500">*</span></label>
                    <select name="pricing_model" required class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                        <option value="">Select pricing model...</option>
                        @foreach($pricingModels as $key => $label)
                            <option value="{{ $key }}" {{ old('pricing_model') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Marketing Objective <span class="text-red-500">*</span></label>
                    <select name="marketing_objective" required class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                        <option value="">Select objective...</option>
                        @foreach($marketingObjectives as $key => $label)
                            <option value="{{ $key }}" {{ old('marketing_objective') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Campaign Group Name</label>
                    <input type="text" name="campaign_group_name" value="{{ old('campaign_group_name') }}" placeholder="e.g. Performance Q4" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Linked AdBlock</label>
                    <select name="zone_id" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                        <option value="">No AdBlock linked</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone['id'] }}" {{ old('zone_id') == $zone['id'] ? 'selected' : '' }}>
                                #{{ $zone['id'] }} - {{ $zone['name'] }} @if(!empty($zone['site_name'])) ({{ $zone['site_name'] }}) @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Choose the AdBlock where this direct display, video, or clip ad should be served.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Campaign Status</label>
                    <select name="status" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending_review" {{ old('status') === 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="paused" {{ old('status') === 'paused' ? 'selected' : '' }}>Paused</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Description</label>
                    <textarea name="description" rows="2" placeholder="Campaign description..." class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">{{ old('description') }}</textarea>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Notes</label>
                <textarea name="notes" rows="2" placeholder="Internal notes..." class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">{{ old('notes') }}</textarea>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SECTION 2: DESTINATION & TRACKING                      --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                    <span class="text-sm font-bold text-blue-600">2</span>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Destination & Tracking</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Set up destination and tracking URLs</p>
                </div>
            </div>
        </div>
        <div class="p-6 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Destination URL <span class="text-red-500">*</span></label>
                    <input type="text" name="destination_url" required value="{{ old('destination_url') }}" placeholder="https://example.com/landing" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Display URL</label>
                    <input type="text" name="display_url" value="{{ old('display_url') }}" placeholder="example.com" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Tracking URL</label>
                    <input type="text" name="tracking_url" value="{{ old('tracking_url') }}" placeholder="https://tracker.example.com/..." class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Click Tracking URL</label>
                    <input type="text" name="click_tracking_url" value="{{ old('click_tracking_url') }}" placeholder="https://click.example.com/..." class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SECTION 3: SCHEDULE & DELIVERY                         --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <span class="text-sm font-bold text-emerald-600">3</span>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Schedule & Delivery</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Configure schedule, delivery, and frequency settings</p>
                </div>
            </div>
        </div>
        <div class="p-6 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Start Date</label>
                    <input type="datetime-local" name="start_date" value="{{ old('start_date') }}" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">End Date</label>
                    <input type="datetime-local" name="end_date" value="{{ old('end_date') }}" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Schedule Timezone</label>
                    <select name="schedule_timezone" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                        @php $timezones = ['UTC'=>'UTC', 'Europe/Tirane'=>'Europe/Tirane (CET)', 'Europe/Belgrade'=>'Europe/Belgrade (CET)', 'Europe/Bucharest'=>'Europe/Bucharest (EET)', 'Europe/Athens'=>'Europe/Athens (EET)', 'Europe/Istanbul'=>'Europe/Istanbul (TRT)', 'Europe/London'=>'Europe/London (GMT)', 'Europe/Berlin'=>'Europe/Berlin (CET)', 'America/New_York'=>'America/New York (EST)', 'America/Los_Angeles'=>'America/Los Angeles (PST)']; @endphp
                        @foreach($timezones as $tz => $label)
                            <option value="{{ $tz }}" {{ old('schedule_timezone') === $tz ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Delivery Mode</label>
                    <select name="delivery_mode" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                        <option value="standard" {{ old('delivery_mode') === 'standard' ? 'selected' : '' }}>Standard</option>
                        <option value="accelerated" {{ old('delivery_mode') === 'accelerated' ? 'selected' : '' }}>Accelerated</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Priority: <span id="priorityValue" class="text-brand-600">5</span></label>
                    <input type="range" name="priority" min="1" max="10" value="{{ old('priority', 5) }}" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-brand-600" oninput="document.getElementById('priorityValue').textContent = this.value">
                    <div class="flex justify-between text-[10px] text-gray-400 mt-1">
                        <span>1 (Low)</span><span>5 (Normal)</span><span>10 (High)</span>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Weight: <span id="weightValue" class="text-brand-600">5</span></label>
                    <input type="range" name="weight" min="1" max="10" value="{{ old('weight', 5) }}" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-brand-600" oninput="document.getElementById('weightValue').textContent = this.value">
                    <div class="flex justify-between text-[10px] text-gray-400 mt-1">
                        <span>1 (Low)</span><span>5 (Normal)</span><span>10 (High)</span>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Frequency Cap</label>
                    <input type="number" name="frequency_cap" min="1" value="{{ old('frequency_cap') }}" placeholder="e.g. 3" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Frequency Cap Period</label>
                    <select name="frequency_cap_period" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                        <option value="">Select period...</option>
                        @foreach(['hour'=>'Per Hour','day'=>'Per Day','week'=>'Per Week','month'=>'Per Month','lifetime'=>'Lifetime'] as $k => $v)
                            <option value="{{ $k }}" {{ old('frequency_cap_period') === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Dayparting --}}
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
                                            <td class="py-1 px-0.5 text-center align-middle">
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
    {{-- SECTION 4: BUDGET & BIDDING                            --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                    <span class="text-sm font-bold text-amber-600">4</span>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Budget & Bidding</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Set your budget and bid amounts</p>
                </div>
            </div>
        </div>
        <div class="p-6 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Bid Amount <span class="text-red-500">*</span></label>
                    <input type="number" name="bid_amount" required step="0.01" min="0" value="{{ old('bid_amount') }}" placeholder="0.00" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Daily Budget</label>
                    <input type="number" name="daily_budget" step="0.01" min="0" value="{{ old('daily_budget') }}" placeholder="0.00" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Total Budget</label>
                    <input type="number" name="total_budget" step="0.01" min="0" value="{{ old('total_budget') }}" placeholder="0.00" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Currency <span class="text-red-500">*</span></label>
                    <select name="currency" required class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                        <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR (&euro;)</option>
                        <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD ($)</option>
                        <option value="GBP" {{ old('currency') === 'GBP' ? 'selected' : '' }}>GBP (&pound;)</option>
                        <option value="ALL" {{ old('currency') === 'ALL' ? 'selected' : '' }}>ALL (Lek)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SECTION 5: AD CONTENT                                  --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                    <span class="text-sm font-bold text-purple-600">5</span>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Ad Content</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Define the ad copy and messaging</p>
                </div>
            </div>
        </div>
        <div class="p-6 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Headline</label>
                    <input type="text" name="headline" value="{{ old('headline') }}" placeholder="Ad headline text" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Call to Action</label>
                    <input type="text" name="call_to_action" value="{{ old('call_to_action') }}" placeholder="e.g. Learn More, Shop Now" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Sponsored Label</label>
                    <input type="text" name="sponsored_label" value="{{ old('sponsored_label') }}" placeholder="e.g. Sponsored, Ad, Promoted" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Body Text</label>
                <textarea name="body_text" rows="3" placeholder="Ad body text..." class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">{{ old('body_text') }}</textarea>
            </div>

            {{-- DKI --}}
            <div class="border-t border-gray-100 pt-5">
                <div class="flex items-center gap-3 mb-4 cursor-pointer" onclick="document.getElementById('dkiSection').classList.toggle('hidden')">
                    <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <span class="text-xs font-semibold text-gray-700 uppercase tracking-wider">Dynamic Keyword Insertion (DKI)</span>
                </div>
                <div id="dkiSection" class="hidden space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Headline DKI</label>
                            <input type="text" name="headline_dki" value="{{ old('headline_dki') }}" placeholder="Headline with {keyword}" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Headline DKI Default</label>
                            <input type="text" name="headline_dki_default" value="{{ old('headline_dki_default') }}" placeholder="Default fallback headline" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Body Text DKI</label>
                        <textarea name="body_text_dki" rows="2" placeholder="Body text with {keyword}" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">{{ old('body_text_dki') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SECTION 6: BRAND SETTINGS                              --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-pink-100 flex items-center justify-center">
                    <span class="text-sm font-bold text-pink-600">6</span>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Brand Settings</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Configure brand identity for the campaign</p>
                </div>
            </div>
        </div>
        <div class="p-6 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Brand Name</label>
                    <input type="text" name="brand_name" value="{{ old('brand_name') }}" placeholder="Your brand name" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Brand Tagline</label>
                    <input type="text" name="brand_tagline" value="{{ old('brand_tagline') }}" placeholder="Your brand tagline" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Brand Logo URL</label>
                <input type="text" name="brand_logo_url" value="{{ old('brand_logo_url') }}" placeholder="https://example.com/logo.png" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Primary Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="brand_color_primary" value="{{ old('brand_color_primary', '#6366f1') }}" class="w-10 h-10 rounded-lg border border-gray-200 cursor-pointer p-0.5">
                        <input type="text" id="brandColorPrimaryText" value="{{ old('brand_color_primary', '#6366f1') }}" placeholder="#6366f1" class="flex-1 px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 font-mono" oninput="document.querySelector('[name=brand_color_primary]').value = this.value">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Secondary Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="brand_color_secondary" value="{{ old('brand_color_secondary', '#8b5cf6') }}" class="w-10 h-10 rounded-lg border border-gray-200 cursor-pointer p-0.5">
                        <input type="text" id="brandColorSecondaryText" value="{{ old('brand_color_secondary', '#8b5cf6') }}" placeholder="#8b5cf6" class="flex-1 px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 font-mono" oninput="document.querySelector('[name=brand_color_secondary]').value = this.value">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SECTION 7: CTW VIDEO SETTINGS                          --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-cyan-100 flex items-center justify-center">
                    <span class="text-sm font-bold text-cyan-600">7</span>
                </div>
                <div class="flex-1">
                    <h2 class="text-base font-semibold text-gray-900">CTW Video Settings</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Click-to-Watch video ad configuration</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="ctw_enabled" value="0">
                    <input type="checkbox" name="ctw_enabled" value="1" id="ctwToggle" class="sr-only peer" onchange="document.getElementById('ctwSection').classList.toggle('hidden', !this.checked)">
                    <div class="w-10 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-brand-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-600"></div>
                </label>
            </div>
        </div>
        <div id="ctwSection" class="hidden p-6 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">CTW Thumbnail URL</label>
                    <input type="text" name="ctw_thumbnail_url" value="{{ old('ctw_thumbnail_url') }}" placeholder="https://example.com/thumbnail.jpg" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Min Watch Seconds</label>
                    <input type="number" name="ctw_min_watch_seconds" min="1" value="{{ old('ctw_min_watch_seconds') }}" placeholder="e.g. 5" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Skip After Seconds</label>
                    <input type="number" name="ctw_skip_after_seconds" min="1" value="{{ old('ctw_skip_after_seconds') }}" placeholder="e.g. 15" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>
            <div class="flex items-center gap-8">
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="ctw_autoplay" value="0">
                        <input type="checkbox" name="ctw_autoplay" value="1" class="sr-only peer" {{ old('ctw_autoplay') ? 'checked' : '' }}>
                        <div class="w-10 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-brand-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-600"></div>
                    </label>
                    <span class="text-sm font-medium text-gray-700">Autoplay</span>
                </div>
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="ctw_muted_autoplay" value="0">
                        <input type="checkbox" name="ctw_muted_autoplay" value="1" class="sr-only peer" {{ old('ctw_muted_autoplay') ? 'checked' : '' }}>
                        <div class="w-10 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-brand-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-600"></div>
                    </label>
                    <span class="text-sm font-medium text-gray-700">Muted Autoplay</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SECTION 8: END CARD SETTINGS                           --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center">
                    <span class="text-sm font-bold text-orange-600">8</span>
                </div>
                <div class="flex-1">
                    <h2 class="text-base font-semibold text-gray-900">End Card Settings</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Configure the end card shown after video</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="end_card_enabled" value="0">
                    <input type="checkbox" name="end_card_enabled" value="1" id="endCardToggle" class="sr-only peer" onchange="document.getElementById('endCardSection').classList.toggle('hidden', !this.checked)">
                    <div class="w-10 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-brand-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-600"></div>
                </label>
            </div>
        </div>
        <div id="endCardSection" class="hidden p-6 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">End Card Type</label>
                    <select name="end_card_type" id="endCardType" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white" onchange="document.getElementById('endCardHtmlRow').classList.toggle('hidden', this.value !== 'html')">
                        @foreach(['static_image'=>'Static Image','html'=>'HTML','cta_button'=>'CTA Button','product_feed'=>'Product Feed','custom'=>'Custom'] as $k => $v)
                            <option value="{{ $k }}" {{ old('end_card_type') === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Display Seconds</label>
                    <input type="number" name="end_card_display_seconds" min="1" value="{{ old('end_card_display_seconds') }}" placeholder="e.g. 10" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">End Card Headline</label>
                    <input type="text" name="end_card_headline" value="{{ old('end_card_headline') }}" placeholder="End card headline" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">CTA Text</label>
                    <input type="text" name="end_card_cta_text" value="{{ old('end_card_cta_text') }}" placeholder="e.g. Visit Now" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">End Card Body</label>
                <textarea name="end_card_body" rows="2" placeholder="End card body text..." class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">{{ old('end_card_body') }}</textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Image URL</label>
                    <input type="text" name="end_card_image_url" value="{{ old('end_card_image_url') }}" placeholder="https://example.com/endcard.jpg" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">CTA URL</label>
                    <input type="text" name="end_card_cta_url" value="{{ old('end_card_cta_url') }}" placeholder="https://example.com/action" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">CTA Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="end_card_cta_color" value="{{ old('end_card_cta_color', '#6366f1') }}" class="w-10 h-10 rounded-lg border border-gray-200 cursor-pointer p-0.5">
                        <input type="text" value="{{ old('end_card_cta_color', '#6366f1') }}" placeholder="#6366f1" class="flex-1 px-4 py-2.5 rounded-lg border border-gray-200 text-sm font-mono" oninput="document.querySelector('[name=end_card_cta_color]').value = this.value">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Logo URL</label>
                    <input type="text" name="end_card_logo_url" value="{{ old('end_card_logo_url') }}" placeholder="https://example.com/logo.png" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>
            <div id="endCardHtmlRow" class="hidden">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">End Card HTML</label>
                <textarea name="end_card_html" rows="4" placeholder="Custom HTML code..." class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 font-mono">{{ old('end_card_html') }}</textarea>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SECTION 9: CLIP AD SETTINGS                            --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center">
                    <span class="text-sm font-bold text-violet-600">9</span>
                </div>
                <div class="flex-1">
                    <h2 class="text-base font-semibold text-gray-900">Clip Ad Settings</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Short-form vertical video ad configuration</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="clip_enabled" value="0">
                    <input type="checkbox" name="clip_enabled" value="1" id="clipToggle" class="sr-only peer" onchange="document.getElementById('clipSection').classList.toggle('hidden', !this.checked)">
                    <div class="w-10 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-brand-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-600"></div>
                </label>
            </div>
        </div>
        <div id="clipSection" class="hidden p-6 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Clip Video URL</label>
                    <input type="text" name="clip_video_url" value="{{ old('clip_video_url') }}" placeholder="https://example.com/clip.mp4" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Clip Thumbnail URL</label>
                    <input type="text" name="clip_thumbnail_url" value="{{ old('clip_thumbnail_url') }}" placeholder="https://example.com/thumb.jpg" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Duration (seconds)</label>
                    <input type="number" name="clip_duration_seconds" min="1" value="{{ old('clip_duration_seconds') }}" placeholder="e.g. 15" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Aspect Ratio</label>
                    <select name="clip_aspect_ratio" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                        <option value="9:16" {{ old('clip_aspect_ratio') === '9:16' ? 'selected' : '' }}>9:16 (Vertical)</option>
                        <option value="4:5" {{ old('clip_aspect_ratio') === '4:5' ? 'selected' : '' }}>4:5 (Portrait)</option>
                        <option value="1:1" {{ old('clip_aspect_ratio') === '1:1' ? 'selected' : '' }}>1:1 (Square)</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-8">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Sound Default</label>
                    <select name="clip_sound_default" class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                        <option value="off" {{ old('clip_sound_default') === 'off' ? 'selected' : '' }}>Off</option>
                        <option value="on" {{ old('clip_sound_default') === 'on' ? 'selected' : '' }}>On</option>
                    </select>
                </div>
                <div class="flex items-center gap-3 pt-5">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="clip_autoplay" value="0">
                        <input type="checkbox" name="clip_autoplay" value="1" class="sr-only peer" {{ old('clip_autoplay') ? 'checked' : '' }}>
                        <div class="w-10 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-brand-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-600"></div>
                    </label>
                    <span class="text-sm font-medium text-gray-700">Autoplay</span>
                </div>
                <div class="flex items-center gap-3 pt-5">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="clip_loop" value="0">
                        <input type="checkbox" name="clip_loop" value="1" class="sr-only peer" {{ old('clip_loop') ? 'checked' : '' }}>
                        <div class="w-10 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-brand-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-600"></div>
                    </label>
                    <span class="text-sm font-medium text-gray-700">Loop</span>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Caption</label>
                <textarea name="clip_caption" rows="2" placeholder="Clip caption text..." class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">{{ old('clip_caption') }}</textarea>
            </div>

            {{-- Swipe Up --}}
            <div class="border-t border-gray-100 pt-5">
                <div class="flex items-center gap-3 mb-4">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="clip_swipe_up_enabled" value="0">
                        <input type="checkbox" name="clip_swipe_up_enabled" value="1" class="sr-only peer" {{ old('clip_swipe_up_enabled') ? 'checked' : '' }} onchange="document.getElementById('swipeUpFields').classList.toggle('hidden', !this.checked)">
                        <div class="w-10 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-brand-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-600"></div>
                    </label>
                    <span class="text-sm font-medium text-gray-700">Enable Swipe Up</span>
                </div>
                <div id="swipeUpFields" class="hidden grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Swipe Up Text</label>
                        <input type="text" name="clip_swipe_up_text" value="{{ old('clip_swipe_up_text') }}" placeholder="Swipe up to learn more" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Swipe Up URL</label>
                        <input type="text" name="clip_swipe_up_url" value="{{ old('clip_swipe_up_url') }}" placeholder="https://example.com/landing" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SECTION 10: TARGETING                                  --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center">
                    <span class="text-sm font-bold text-teal-600">10</span>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Targeting</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Define your audience targeting rules</p>
                </div>
            </div>
        </div>
        <div class="p-6 space-y-5">
            {{-- Country Targeting --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Country Targeting</label>
                <select id="geoSelect" multiple class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white min-h-[120px]">
                    @foreach($countries as $country)
                        <option value="{{ $country['code'] }}">{{ $country['name'] }} ({{ $country['code'] }})</option>
                    @endforeach
                </select>
                <input type="hidden" name="targeting_geo" id="targeting_geo">
                <p class="text-[10px] text-gray-400 mt-1">Hold Ctrl/Cmd to select multiple countries</p>
            </div>

            {{-- Device Targeting --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Device Targeting</label>
                <div class="flex flex-wrap gap-4">
                    @foreach(['desktop'=>'Desktop', 'mobile'=>'Mobile', 'tablet'=>'Tablet'] as $val => $label)
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" class="device-cb rounded border-gray-300 text-brand-600 focus:ring-brand-500" value="{{ $val }}">
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <input type="hidden" name="targeting_device" id="targeting_device">
            </div>

            <div class="border-t border-gray-100"></div>

            {{-- OS Targeting --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Operating System Targeting</label>
                <select id="osSelect" multiple class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white min-h-[120px]">
                    @foreach($operatingSystems as $family => $versions)
                        <optgroup label="{{ $family }}">
                            @foreach($versions as $version)
                                <option value="{{ $version }}">{{ $version }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <input type="hidden" name="targeting_os" id="targeting_os">
                <p class="text-[10px] text-gray-400 mt-1">Hold Ctrl/Cmd to select multiple</p>
            </div>

            {{-- Browser Targeting --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Browser Targeting</label>
                <select id="browserSelect" multiple class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white min-h-[120px]">
                    @foreach($browsers as $browser => $versions)
                        <optgroup label="{{ $browser }}">
                            @foreach($versions as $version)
                                <option value="{{ $version }}">{{ $version }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <input type="hidden" name="targeting_browser" id="targeting_browser">
                <p class="text-[10px] text-gray-400 mt-1">Hold Ctrl/Cmd to select multiple</p>
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
                <p class="text-[10px] text-gray-400 mt-1">Hold Ctrl/Cmd to select multiple languages</p>
            </div>

            {{-- Connection Type --}}
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
            </div>

            {{-- Carrier Targeting --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Mobile Carrier Targeting</label>
                <select id="carrierSelect" multiple class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white min-h-[120px]">
                    @foreach($mobileCarriers as $countryCode => $carriers)
                        <optgroup label="{{ $countryCode }}">
                            @foreach($carriers as $carrier)
                                <option value="{{ $carrier }}">{{ $carrier }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <input type="hidden" name="targeting_carrier" id="targeting_carrier">
                <p class="text-[10px] text-gray-400 mt-1">Hold Ctrl/Cmd to select multiple carriers</p>
            </div>
        </div>
    </div>

    {{-- Submit --}}
    <div class="flex items-center justify-end gap-3 mb-10">
        <a href="{{ route('advertiser.direct-campaigns') }}" class="px-5 py-2.5 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">Cancel</a>
        <button type="submit" class="px-6 py-2.5 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm transition-colors">Create Campaign</button>
    </div>
</form>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- JAVASCRIPT                                             --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<script>
// ─── Dayparting Presets ───
document.querySelectorAll('.daypart-preset').forEach(btn => {
    btn.addEventListener('click', function() {
        const preset = this.dataset.preset;
        const checkboxes = document.querySelectorAll('.daypart-cell');
        checkboxes.forEach(cb => cb.checked = false);

        if (preset === 'all') {
            checkboxes.forEach(cb => cb.checked = true);
        } else if (preset === 'working-days') {
            checkboxes.forEach(cb => { if (!['saturday','sunday'].includes(cb.dataset.day)) cb.checked = true; });
        } else if (preset === 'weekend') {
            checkboxes.forEach(cb => { if (['saturday','sunday'].includes(cb.dataset.day)) cb.checked = true; });
        } else if (preset === 'only-night') {
            checkboxes.forEach(cb => { const h = parseInt(cb.dataset.hour); if (h >= 22 || h < 6) cb.checked = true; });
        } else if (preset === 'only-day') {
            checkboxes.forEach(cb => { const h = parseInt(cb.dataset.hour); if (h >= 6 && h < 22) cb.checked = true; });
        } else if (preset === 'only-morning') {
            checkboxes.forEach(cb => { const h = parseInt(cb.dataset.hour); if (h >= 6 && h < 12) cb.checked = true; });
        }
    });
});

// ─── Color input sync ───
document.querySelector('[name=brand_color_primary]')?.addEventListener('input', function() {
    document.getElementById('brandColorPrimaryText').value = this.value;
});
document.querySelector('[name=brand_color_secondary]')?.addEventListener('input', function() {
    document.getElementById('brandColorSecondaryText').value = this.value;
});

// ─── Serialize targeting on form submit ───
function getSelectedValues(selectId) {
    const el = document.getElementById(selectId);
    if (!el) return [];
    return Array.from(el.selectedOptions).map(o => o.value);
}

function getCheckedValues(className) {
    return Array.from(document.querySelectorAll('.' + className + ':checked')).map(cb => cb.value);
}

document.getElementById('directCampaignForm').addEventListener('submit', function() {
    document.getElementById('targeting_geo').value = JSON.stringify(getSelectedValues('geoSelect'));
    document.getElementById('targeting_device').value = JSON.stringify(getCheckedValues('device-cb'));
    document.getElementById('targeting_os').value = JSON.stringify(getSelectedValues('osSelect'));
    document.getElementById('targeting_browser').value = JSON.stringify(getSelectedValues('browserSelect'));
    document.getElementById('targeting_language').value = JSON.stringify(getSelectedValues('languageSelect'));
    document.getElementById('targeting_connection_type').value = JSON.stringify(getCheckedValues('connection-type-cb'));
    document.getElementById('targeting_carrier').value = JSON.stringify(getSelectedValues('carrierSelect'));
});
</script>
@endsection
