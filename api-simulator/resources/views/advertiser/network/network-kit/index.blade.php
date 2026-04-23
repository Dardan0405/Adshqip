@extends('layouts.advertiser')

@section('title', 'Network Kit')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Network Kit</h1>
            <p class="text-sm text-gray-500 mt-1">Analyze your network traffic by country, ad size, type, and ECPM.</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <form method="GET" action="{{ route('advertiser.network.network-kit') }}" class="flex flex-wrap items-end gap-3 p-4">
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date', $defaults['start_date']) }}" class="px-3 py-2 rounded-lg border border-gray-200 text-sm">
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date', $defaults['end_date']) }}" class="px-3 py-2 rounded-lg border border-gray-200 text-sm">
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1">Device</label>
                <select name="device" class="px-3 py-2 rounded-lg border border-gray-200 text-sm">
                    <option value="">All Devices</option>
                    @foreach($filterOptions['devices'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('device') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1">Country</label>
                <select name="country" class="px-3 py-2 rounded-lg border border-gray-200 text-sm">
                    <option value="">All Countries</option>
                    @foreach($filterOptions['countries'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('country') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button class="px-4 py-2 rounded-lg bg-gray-900 text-sm font-medium text-white">Apply</button>
            <a href="{{ route('advertiser.network.network-kit') }}" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600">Reset</a>
        </form>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-6">
        @foreach([
            ['Impressions', number_format($summary['impressions']), 'text-gray-900'],
            ['Clicks', number_format($summary['clicks']), 'text-blue-700'],
            ['Revenue', $adminCurrency->format($summary['revenue']), 'text-emerald-700'],
            ['CTR', number_format($summary['ctr'], 2) . '%', 'text-violet-700'],
            ['ECPM', $adminCurrency->format($summary['ecpm']), 'text-rose-700'],
        ] as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card[0] }}</div>
                <div class="mt-2 text-2xl font-bold {{ $card[2] }}">{{ $card[1] }}</div>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Network Performance</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/70 border-b border-gray-100">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Country</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Ad Size</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Type</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Impressions</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Clicks</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">ECPM</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">CTR</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rows as $row)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $countryNames[$row->country_code] ?? $row->country_code }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $row->ad_size }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ Str::headline($row->type) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format($row->impressions) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format($row->clicks) }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-700">{{ $adminCurrency->format($row->ecpm) }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-blue-700">{{ number_format($row->ctr, 2) }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-sm text-gray-500">No network data found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
