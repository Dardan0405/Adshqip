@extends('layouts.advertiser')

@section('title', 'Group Settings')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Group Settings</h1>
            <p class="text-sm text-gray-500 mt-1">Build a grouped performance report with custom metrics, filters, dimensions, and display period.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        @foreach([
            ['label' => 'Requests', 'value' => number_format($summary['requests']), 'class' => 'text-slate-900 bg-slate-50 border-slate-200'],
            ['label' => 'Impressions', 'value' => number_format($summary['impressions']), 'class' => 'text-blue-700 bg-blue-50 border-blue-200'],
            ['label' => 'Clicks', 'value' => number_format($summary['clicks']), 'class' => 'text-indigo-700 bg-indigo-50 border-indigo-200'],
            ['label' => 'Spend', 'value' => '$' . number_format($summary['spend'], 2), 'class' => 'text-emerald-700 bg-emerald-50 border-emerald-200'],
        ] as $card)
            @php [$textClass, $bgClass, $borderClass] = explode(' ', $card['class']); @endphp
            <div class="rounded-xl border {{ $borderClass }} {{ $bgClass }} p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-2">{{ $card['label'] }}</div>
                <div class="text-2xl font-bold {{ $textClass }}">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    <form method="GET" action="{{ route('advertiser.reports.group-settings') }}" class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="p-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Basic Settings</h2>
            <div class="grid md:grid-cols-4 gap-3 mt-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ $filters['start_date'] }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ $filters['end_date'] }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Device Type</label>
                    <select name="device_type" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm">
                        @foreach(['all' => 'All', 'website' => 'Website', 'mobile' => 'Mobile', 'tablet' => 'Tablet'] as $value => $label)
                            <option value="{{ $value }}" @selected($filters['device_type'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Timezone</label>
                    <input type="text" name="timezone" value="{{ $filters['timezone'] }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" placeholder="UTC">
                </div>
            </div>
        </div>

        <div class="grid xl:grid-cols-2 gap-4 p-4 border-b border-gray-100">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Columns</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    @foreach($metricOptions as $key => $label)
                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm">
                            <input type="checkbox" name="metrics[]" value="{{ $key }}" @checked(in_array($key, $filters['metrics'], true)) class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div>
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Group By Dimensions</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach($dimensionOptions as $key => $label)
                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm">
                            <input type="checkbox" name="group_by[]" value="{{ $key }}" @checked(in_array($key, $filters['group_by'], true)) class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="p-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900 mb-3">Filters</h2>
            <div class="grid md:grid-cols-5 gap-3">
                <select name="campaigns[]" multiple class="min-h-28 px-3 py-2 rounded-lg border border-gray-200 text-sm">
                    @foreach($filterOptions['campaigns'] as $campaign)
                        <option value="{{ $campaign->id }}" @selected(in_array($campaign->id, $filters['campaigns']))>{{ $campaign->name }}</option>
                    @endforeach
                </select>
                <select name="creatives[]" multiple class="min-h-28 px-3 py-2 rounded-lg border border-gray-200 text-sm">
                    @foreach($filterOptions['creatives'] as $creative)
                        <option value="{{ $creative->id }}" @selected(in_array($creative->id, $filters['creatives']))>{{ $creative->name }}</option>
                    @endforeach
                </select>
                <select name="countries[]" multiple class="min-h-28 px-3 py-2 rounded-lg border border-gray-200 text-sm">
                    @foreach($filterOptions['countries'] as $country)
                        <option value="{{ $country }}" @selected(in_array($country, $filters['countries'], true))>{{ $country }}</option>
                    @endforeach
                </select>
                <select name="ad_sizes[]" multiple class="min-h-28 px-3 py-2 rounded-lg border border-gray-200 text-sm">
                    @foreach($filterOptions['ad_sizes'] as $size)
                        <option value="{{ $size }}" @selected(in_array($size, $filters['ad_sizes'], true))>{{ $size }}</option>
                    @endforeach
                </select>
                <select name="revenue_types[]" multiple class="min-h-28 px-3 py-2 rounded-lg border border-gray-200 text-sm">
                    @foreach($filterOptions['revenue_types'] as $type)
                        <option value="{{ $type }}" @selected(in_array($type, $filters['revenue_types'], true))>{{ strtoupper($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="text-xs text-gray-400 mt-2">Use Ctrl or Cmd to select multiple filter values.</div>
        </div>

        <div class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Display By</label>
                <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1">
                    @foreach($displayOptions as $key => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="display_by" value="{{ $key }}" @checked($filters['display_by'] === $key) class="sr-only peer">
                            <span class="block px-3 py-1.5 rounded-md text-sm text-gray-600 peer-checked:bg-gray-900 peer-checked:text-white">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('advertiser.reports.group-settings') }}" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-800">Apply Settings</button>
            </div>
        </div>
    </form>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Grouped Data</h2>
            <p class="text-xs text-gray-500 mt-1">{{ $rows->total() }} rows based on selected settings</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        @foreach($filters['group_by'] as $dimension)
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">{{ $dimensionOptions[$dimension] ?? $dimension }}</th>
                        @endforeach
                        @if($filters['display_by'] !== 'cumulative' && !in_array('date', $filters['group_by'], true))
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">{{ $displayOptions[$filters['display_by']] }}</th>
                        @endif
                        @foreach($filters['metrics'] as $metric)
                            <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">{{ $metricOptions[$metric] ?? $metric }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rows as $row)
                        <tr class="hover:bg-gray-50/50">
                            @foreach($filters['group_by'] as $dimension)
                                <td class="px-4 py-3 text-gray-700 whitespace-nowrap">{{ $row->{$dimension} ?? 'N/A' }}</td>
                            @endforeach
                            @if($filters['display_by'] !== 'cumulative' && !in_array('date', $filters['group_by'], true))
                                <td class="px-4 py-3 text-gray-700 whitespace-nowrap">{{ $row->date ?? 'N/A' }}</td>
                            @endif
                            @foreach($filters['metrics'] as $metric)
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @if(in_array($metric, ['spend', 'ecpm'], true))
                                        ${{ number_format((float) ($row->{$metric} ?? 0), 2) }}
                                    @elseif(in_array($metric, ['ctr', 'fill_rate'], true))
                                        {{ number_format((float) ($row->{$metric} ?? 0), 2) }}%
                                    @else
                                        {{ number_format((float) ($row->{$metric} ?? 0)) }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($filters['group_by']) + count($filters['metrics']) + 1 }}" class="px-4 py-12 text-center text-sm text-gray-500">No grouped report data found for the selected settings.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($rows->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $rows->links() }}</div>
        @endif
    </div>
@endsection
