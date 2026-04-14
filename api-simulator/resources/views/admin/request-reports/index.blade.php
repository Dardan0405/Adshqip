@extends('layouts.admin')

@section('title', 'Request Report')

@section('content')
    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-700 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="mb-4 p-3 rounded-xl border border-amber-300 bg-amber-50 text-amber-700 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            {{ session('warning') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Request Report</h1>
            <p class="text-sm text-gray-500 mt-1">Request builder based on direct campaign stats, with one-time export and saved recurring requests.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 xl:grid-cols-6 gap-3 mb-6">
        @php
            $summaryCards = [
                ['label' => 'Request Rows', 'value' => number_format($summary['requests']), 'color' => 'text-slate-900', 'bg' => 'bg-slate-50', 'border' => 'border-slate-200'],
                ['label' => 'Impressions', 'value' => number_format($summary['impressions']), 'color' => 'text-blue-700', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200'],
                ['label' => 'Clicks', 'value' => number_format($summary['clicks']), 'color' => 'text-amber-700', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200'],
                ['label' => 'Conversions', 'value' => number_format($summary['conversions']), 'color' => 'text-violet-700', 'bg' => 'bg-violet-50', 'border' => 'border-violet-200'],
                ['label' => 'Spend', 'value' => 'EUR ' . number_format($summary['spend'], 2), 'color' => 'text-rose-700', 'bg' => 'bg-rose-50', 'border' => 'border-rose-200'],
                ['label' => 'Profit', 'value' => 'EUR ' . number_format($summary['profit'], 2), 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200'],
            ];
        @endphp
        @foreach($summaryCards as $card)
            <div class="rounded-xl border {{ $card['border'] }} {{ $card['bg'] }} p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                <div class="mt-2 text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.reports.requests') }}" class="bg-white rounded-xl border border-gray-200 mb-6">
        @csrf
        <div class="p-4 border-b border-gray-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">Request Configuration</h2>
                <p class="text-xs text-gray-500 mt-1">The legacy documentation mentions hour-level grouping and email delivery. This Laravel implementation uses the current daily direct-campaign schema, so `Hour` previews as daily data and exports download directly.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-800">Preview</button>
                <button type="submit" formaction="{{ route('admin.reports.requests.export') }}" formmethod="GET" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Download</button>
                <button type="submit" formaction="{{ route('admin.reports.requests.store') }}" formmethod="POST" class="px-4 py-2 rounded-lg border border-emerald-200 bg-emerald-50 text-sm font-medium text-emerald-700 hover:bg-emerald-100">Save Recurring Request</button>
                <a href="{{ route('admin.reports.requests') }}" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</a>
            </div>
        </div>

        <div class="p-4 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-3">
                <div class="xl:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Request Name</label>
                    <input type="text" name="name" value="{{ request('name', 'Request Report') }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Campaign, advertiser, site..." class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from', now()->subDays(30)->toDateString()) }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to', now()->toDateString()) }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Device</label>
                    <select name="environment" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        @foreach($availableEnvironments as $value => $label)
                            <option value="{{ $value }}" {{ request('environment', 'all') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Frequency</label>
                    <select name="frequency" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        @foreach($availableFrequencies as $value => $label)
                            <option value="{{ $value }}" {{ request('frequency', 'one') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-gray-400 mt-1">Only `Daily`, `Weekly`, and `Monthly` are stored in the Saved Requests table.</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Type</label>
                    <select name="report_type" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        @foreach($availableReportTypes as $value => $label)
                            <option value="{{ $value }}" {{ request('report_type', 'summarized') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">File Format</label>
                    <select name="file_format" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        @foreach($availableFormats as $value => $label)
                            <option value="{{ $value }}" {{ request('file_format', 'csv') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Displayed By</label>
                    <select name="display_by" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        @foreach($availableDisplayBy as $value => $label)
                            <option value="{{ $value }}" {{ $selectedDisplayBy === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Advertisers</label>
                    <select name="advertiser_ids[]" multiple class="w-full min-h-32 px-3 py-2 rounded-lg border border-gray-200 text-sm">
                        @foreach($advertisers as $advertiser)
                            @php $advertiserName = trim(($advertiser->userProfile->first_name ?? '') . ' ' . ($advertiser->userProfile->last_name ?? '')) ?: ($advertiser->userProfile->company_name ?? $advertiser->email); @endphp
                            <option value="{{ $advertiser->id }}" {{ collect(request('advertiser_ids', []))->contains((string) $advertiser->id) || collect(request('advertiser_ids', []))->contains($advertiser->id) ? 'selected' : '' }}>{{ $advertiserName }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Campaigns</label>
                    <select name="campaign_ids[]" multiple class="w-full min-h-32 px-3 py-2 rounded-lg border border-gray-200 text-sm">
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}" {{ collect(request('campaign_ids', []))->contains((string) $campaign->id) || collect(request('campaign_ids', []))->contains($campaign->id) ? 'selected' : '' }}>{{ $campaign->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Publishers</label>
                    <select name="publisher_ids[]" multiple class="w-full min-h-32 px-3 py-2 rounded-lg border border-gray-200 text-sm">
                        @foreach($publishers as $publisher)
                            @php $publisherName = trim(($publisher->userProfile->first_name ?? '') . ' ' . ($publisher->userProfile->last_name ?? '')) ?: ($publisher->userProfile->company_name ?? $publisher->email); @endphp
                            <option value="{{ $publisher->id }}" {{ collect(request('publisher_ids', []))->contains((string) $publisher->id) || collect(request('publisher_ids', []))->contains($publisher->id) ? 'selected' : '' }}>{{ $publisherName }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Sites</label>
                    <select name="site_ids[]" multiple class="w-full min-h-32 px-3 py-2 rounded-lg border border-gray-200 text-sm">
                        @foreach($sites as $site)
                            <option value="{{ $site->id }}" {{ collect(request('site_ids', []))->contains((string) $site->id) || collect(request('site_ids', []))->contains($site->id) ? 'selected' : '' }}>{{ $site->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">AdBlocks</label>
                    <select name="adblock_ids[]" multiple class="w-full min-h-32 px-3 py-2 rounded-lg border border-gray-200 text-sm">
                        @foreach($adblocks as $adblock)
                            <option value="{{ $adblock->id }}" {{ collect(request('adblock_ids', []))->contains((string) $adblock->id) || collect(request('adblock_ids', []))->contains($adblock->id) ? 'selected' : '' }}>{{ $adblock->name }}{{ $adblock->format_key ? ' (' . $adblock->format_key . ')' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Countries</label>
                    <select name="country_codes[]" multiple class="w-full min-h-32 px-3 py-2 rounded-lg border border-gray-200 text-sm">
                        @foreach($countries as $country)
                            <option value="{{ $country['code'] }}" {{ collect(request('country_codes', []))->contains($country['code']) ? 'selected' : '' }}>{{ $country['name'] }} ({{ $country['code'] }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Ad Sizes</label>
                    <select name="ad_sizes[]" multiple class="w-full min-h-32 px-3 py-2 rounded-lg border border-gray-200 text-sm">
                        @foreach($adSizes as $group)
                            <optgroup label="{{ $group['label'] }}">
                                @foreach($group['sizes'] as $value => $label)
                                    <option value="{{ $value }}" {{ collect(request('ad_sizes', []))->contains($value) ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Revenue Types</label>
                    <select name="revenue_types[]" multiple class="w-full min-h-32 px-3 py-2 rounded-lg border border-gray-200 text-sm">
                        @foreach($revenueTypes as $value => $label)
                            <option value="{{ $value }}" {{ collect(request('revenue_types', []))->contains($value) ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                <div class="rounded-xl border border-gray-200 p-4">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <h3 class="text-sm font-semibold text-gray-900">Columns</h3>
                        <span class="text-xs text-gray-400">Metrics included in the report</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($availableMetrics as $metricKey => $metric)
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="metrics[]" value="{{ $metricKey }}" {{ in_array($metricKey, $selectedMetrics, true) ? 'checked' : '' }} class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                <span>{{ $metric['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <h3 class="text-sm font-semibold text-gray-900">Group Dimensions</h3>
                        <span class="text-xs text-gray-400">Controls the grouping in preview and export</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($availableDimensions as $dimensionKey => $dimension)
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="dimensions[]" value="{{ $dimensionKey }}" {{ in_array($dimensionKey, $selectedDimensions, true) ? 'checked' : '' }} class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                <span>{{ $dimension['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-4 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Preview Results</h2>
            <p class="text-xs text-gray-500 mt-1">{{ count($tableColumns) }} columns selected from the current configuration. Preview uses data from <code class="bg-gray-100 px-1 rounded">aq_direct_campaign_stats</code>.</p>
        </div>
        <div class="overflow-x-auto">
            <table id="requestReportsTable" class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/70">
                        @foreach($tableColumns as $column)
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">{{ $column['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($reports as $row)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            @foreach($tableColumns as $column)
                                @php
                                    $value = $row->{$column['key']} ?? null;
                                    $display = match ($column['type']) {
                                        'currency' => 'EUR ' . number_format((float) $value, 2),
                                        'percent' => number_format((float) $value, 2) . '%',
                                        'number' => number_format((float) $value),
                                        default => ($column['key'] === 'report_period' && $value && $selectedDisplayBy === 'month') ? \Illuminate\Support\Carbon::createFromFormat('Y-m', $value)->format('F Y')
                                            : (($column['key'] === 'report_period' && $value && $selectedDisplayBy === 'year') ? $value : ($value ?? '—')),
                                    };
                                @endphp
                                <td class="px-4 py-3 whitespace-nowrap {{ $column['type'] !== 'text' ? 'text-right font-medium' : 'text-left text-gray-700' }}">{{ $display }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ max(count($tableColumns), 1) }}" class="px-4 py-12 text-center text-sm text-gray-500">
                                No preview rows matched the current configuration.
                                This usually means there are no matching records yet in <code class="bg-gray-100 px-1 rounded">aq_direct_campaign_stats</code>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reports->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $reports->links() }}
            </div>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-4 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Saved Requests</h2>
            <p class="text-xs text-gray-500 mt-1">Recurring request definitions stored from the current builder.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/70">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Name</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Columns</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Device</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Display By</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Details</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($savedRequests as $savedRequest)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $savedRequest->name }}</div>
                                <div class="text-xs text-gray-400">{{ strtoupper($savedRequest->file_format) }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ implode(', ', $savedRequest->metrics ?? []) }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ strtoupper($savedRequest->environment) }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ strtoupper($savedRequest->display_by) }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ ucfirst($savedRequest->frequency) }} / {{ ucfirst($savedRequest->report_type) }} / {{ strtoupper($savedRequest->file_format) }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-xs font-medium {{ $savedRequest->status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                    {{ ucfirst($savedRequest->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.reports.requests.download', $savedRequest) }}" class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-medium text-gray-600 hover:bg-gray-50">Download</a>
                                    <form method="POST" action="{{ route('admin.reports.requests.status', $savedRequest) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $savedRequest->status === 'active' ? 'paused' : 'active' }}">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-medium text-gray-600 hover:bg-gray-50">
                                            {{ $savedRequest->status === 'active' ? 'Pause' : 'Activate' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.reports.requests.destroy', $savedRequest) }}" onsubmit="return confirm('Delete this saved request?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 rounded-lg border border-red-200 bg-red-50 text-xs font-medium text-red-700 hover:bg-red-100">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-sm text-gray-500">No recurring request reports saved yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($savedRequests->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $savedRequests->links() }}
            </div>
        @endif
    </div>
@endsection
