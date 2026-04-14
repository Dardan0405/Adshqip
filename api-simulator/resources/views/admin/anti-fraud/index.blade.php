@extends('layouts.admin')

@section('title', 'Anti-fraud Clicks')

@section('content')
<div class="space-y-6" x-data="antiFraudPage()">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Anti-fraud Clicks</h1>
            <p class="text-sm text-gray-500 mt-1">Monitor and analyze click fraud activity across your network</p>
        </div>
        <div class="flex items-center gap-3">
            <a :href="exportUrl" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Total Clicks</p>
                    <p class="text-xl font-bold text-gray-900">{{ number_format($summary['total_clicks']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-red-100 rounded-lg">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Fraud Clicks</p>
                    <p class="text-xl font-bold text-red-600">{{ number_format($summary['fraud_clicks']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Valid Clicks</p>
                    <p class="text-xl font-bold text-green-600">{{ number_format($summary['valid_clicks']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Fraud Rate</p>
                    <p class="text-xl font-bold text-yellow-600">{{ $summary['fraud_rate'] }}%</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-orange-100 rounded-lg">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Flagged Publishers</p>
                    <p class="text-xl font-bold text-orange-600">{{ $summary['publishers_flagged'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Total Penalty Points</p>
                    <p class="text-xl font-bold text-purple-600">{{ number_format($summary['total_penalty_points']) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" action="{{ route('admin.anti-fraud') }}" class="flex flex-wrap items-end gap-4">
            <input type="hidden" name="tab" :value="activeTab">

            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by publisher, IP, URL..." class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date', $defaults['start_date']) }}" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date', $defaults['end_date']) }}" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="min-w-[180px]">
                <label class="block text-xs font-medium text-gray-600 mb-1">Publisher</label>
                <select name="publisher_id" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Publishers</option>
                    @foreach($publishers as $id => $name)
                        <option value="{{ $id }}" {{ request('publisher_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                    Apply Filters
                </button>
                <a href="{{ route('admin.anti-fraud') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Tabs --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        {{-- Tab Navigation --}}
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button @click="activeTab = 'statistics'" :class="activeTab === 'statistics' ? 'border-blue-500 text-blue-600 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="flex-1 sm:flex-none px-6 py-4 text-sm font-medium border-b-2 transition">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Anti-fraud Statistics
                    </div>
                </button>
                <button @click="activeTab = 'valid'" :class="activeTab === 'valid' ? 'border-blue-500 text-blue-600 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="flex-1 sm:flex-none px-6 py-4 text-sm font-medium border-b-2 transition">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Valid Clicks
                    </div>
                </button>
                <button @click="activeTab = 'penalty'" :class="activeTab === 'penalty' ? 'border-blue-500 text-blue-600 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="flex-1 sm:flex-none px-6 py-4 text-sm font-medium border-b-2 transition">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Penalty Points
                    </div>
                </button>
            </nav>
        </div>

        {{-- Tab Content --}}
        <div class="p-4">
            {{-- Anti-fraud Statistics Tab --}}
            <div x-show="activeTab === 'statistics'" x-transition>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Date</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Publisher Name</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-600">Fraud Clicks</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">IP Address</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">URL</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-600">AdBlock</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($statisticsData as $row)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-gray-900">{{ $row->date }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-medium text-gray-900">{{ $row->publisher_name }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ number_format($row->fraud_clicks) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $row->ip_address }}</td>
                                <td class="px-4 py-3 text-gray-600 max-w-xs truncate" title="{{ $row->url }}">{{ $row->url }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($row->adblock)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Yes</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">No</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    No fraud statistics found for the selected filters.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Valid Clicks Tab --}}
            <div x-show="activeTab === 'valid'" x-transition x-cloak>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Date</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Publisher Name</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-600">Valid Clicks</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">IP Address</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">URL</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-600">AdBlock</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($validClicksData as $row)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-gray-900">{{ $row->date }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-medium text-gray-900">{{ $row->publisher_name }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ number_format($row->fraud_clicks) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $row->ip_address }}</td>
                                <td class="px-4 py-3 text-gray-600 max-w-xs truncate" title="{{ $row->url }}">{{ $row->url }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($row->adblock)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Yes</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">No</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    No valid clicks data found for the selected filters.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Penalty Points Tab --}}
            <div x-show="activeTab === 'penalty'" x-transition x-cloak>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Date</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Publisher</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-600">Penalty Points</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($penaltyPointsData as $row)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-gray-900">{{ $row->date }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-medium text-gray-900">{{ $row->publisher_name }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $points = $row->penalty_points;
                                        $colorClass = $points > 50 ? 'bg-red-100 text-red-800' : ($points > 20 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800');
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                        {{ number_format($points) }} pts
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-gray-500">
                                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    No penalty points data found for the selected filters.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>

<script>
function antiFraudPage() {
    return {
        activeTab: '{{ $activeTab }}',
        get exportUrl() {
            const params = new URLSearchParams(window.location.search);
            params.set('tab', this.activeTab);
            return '{{ route('admin.anti-fraud.export') }}?' + params.toString();
        }
    }
}
</script>
@endsection
