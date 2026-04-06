@extends('layouts.admin')

@section('title', 'Campaign Details — ' . $campaign['name'])

@section('content')
    {{-- Success flash --}}
    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-700 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-400 mb-1">
                <a href="{{ route('admin.campaigns') }}" class="hover:text-gray-600 transition-colors">Campaigns</a>
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                <span class="text-gray-600">#{{ $campaign['id'] }}</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $campaign['name'] }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.campaigns') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Back
            </a>
            <a href="{{ route('admin.campaigns.edit', $campaign['id']) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                Edit Campaign
            </a>
        </div>
    </div>

    @php
        $statusStyles = [
            'active' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'paused' => 'bg-amber-100 text-amber-700 border-amber-200',
            'draft' => 'bg-gray-100 text-gray-600 border-gray-200',
            'completed' => 'bg-blue-100 text-blue-700 border-blue-200',
            'rejected' => 'bg-red-100 text-red-700 border-red-200',
            'pending_review' => 'bg-orange-100 text-orange-700 border-orange-200',
        ];
        $statusDots = [
            'active' => 'bg-emerald-500', 'paused' => 'bg-amber-500', 'draft' => 'bg-gray-400',
            'completed' => 'bg-blue-500', 'rejected' => 'bg-red-500', 'pending_review' => 'bg-orange-500',
        ];
        $typeColors = [
            'cpm' => 'bg-purple-100 text-purple-700', 'cpc' => 'bg-blue-100 text-blue-700',
            'cpa' => 'bg-emerald-100 text-emerald-700', 'cpv' => 'bg-pink-100 text-pink-700',
            'cpv_ctw' => 'bg-indigo-100 text-indigo-700',
        ];
        $spendPct = $campaign['budget'] > 0 ? round(($campaign['spend'] / $campaign['budget']) * 100, 1) : 0;
    @endphp

    {{-- Status + Quick Info Bar --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
        <div class="flex flex-wrap items-center gap-4">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border {{ $statusStyles[$campaign['status']] ?? 'bg-gray-100 text-gray-600 border-gray-200' }}">
                <span class="w-2 h-2 rounded-full {{ $statusDots[$campaign['status']] ?? 'bg-gray-400' }}"></span>
                {{ ucwords(str_replace('_', ' ', $campaign['status'])) }}
            </span>
            <span class="px-2.5 py-1 rounded text-[11px] font-bold uppercase {{ $typeColors[$campaign['type']] ?? 'bg-gray-100 text-gray-600' }}">{{ strtoupper($campaign['type']) }}</span>
            <span class="text-sm text-gray-500">{{ $campaign['model'] }}</span>
            <div class="h-4 w-px bg-gray-200"></div>
            <span class="text-sm text-gray-500">
                <span class="font-medium text-gray-700">{{ \Carbon\Carbon::parse($campaign['start_date'])->format('M d, Y') }}</span>
                &mdash;
                <span class="font-medium text-gray-700">{{ $campaign['end_date'] ? \Carbon\Carbon::parse($campaign['end_date'])->format('M d, Y') : 'No end date' }}</span>
            </span>
            @if($campaign['group_name'] ?? null)
                <div class="h-4 w-px bg-gray-200"></div>
                <span class="inline-flex items-center gap-1.5 text-sm text-gray-500">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    {{ $campaign['group_name'] }}
                </span>
            @endif
        </div>
    </div>

    {{-- Performance Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Impressions</div>
            <div class="text-xl font-bold text-gray-900 mt-1">{{ number_format($campaign['impressions']) }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Clicks</div>
            <div class="text-xl font-bold text-gray-900 mt-1">{{ number_format($campaign['clicks']) }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Conversions</div>
            <div class="text-xl font-bold text-gray-900 mt-1">{{ number_format($campaign['conversions']) }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">CTR</div>
            <div class="text-xl font-bold {{ $campaign['ctr'] >= 2.5 ? 'text-emerald-600' : ($campaign['ctr'] >= 1.0 ? 'text-blue-600' : 'text-gray-900') }} mt-1">{{ number_format($campaign['ctr'], 2) }}%</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Budget</div>
            <div class="text-xl font-bold text-gray-900 mt-1">&euro;{{ number_format($campaign['budget'], 2) }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Spend</div>
            <div class="text-xl font-bold text-gray-900 mt-1">&euro;{{ number_format($campaign['spend'], 2) }}</div>
            <div class="flex items-center gap-2 mt-1.5">
                <div class="flex-1 h-1.5 rounded-full bg-gray-100">
                    <div class="h-1.5 rounded-full {{ $spendPct >= 90 ? 'bg-red-500' : ($spendPct >= 70 ? 'bg-amber-500' : 'bg-brand-500') }}" style="width: {{ min($spendPct, 100) }}%"></div>
                </div>
                <span class="text-[10px] font-medium text-gray-400">{{ $spendPct }}%</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left column: Campaign Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Campaign Information --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">Campaign Information</h2>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5">
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Campaign ID</dt>
                            <dd class="text-sm font-medium text-gray-900 mt-1">#{{ $campaign['id'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Advertiser</dt>
                            <dd class="text-sm font-medium text-gray-900 mt-1">{{ $campaign['advertiser'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Campaign Type</dt>
                            <dd class="text-sm font-medium text-gray-900 mt-1">{{ $campaign['model'] }} ({{ strtoupper($campaign['type']) }})</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Marketing Objective</dt>
                            <dd class="text-sm font-medium text-gray-900 mt-1">{{ ucwords(str_replace('_', ' ', $campaign['marketing_objective'] ?? 'Traffic')) }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Linked AdBlock</dt>
                            <dd class="text-sm font-medium text-gray-900 mt-1">
                                @if($campaign['zone_name'])
                                    #{{ $campaign['zone_id'] }} - {{ $campaign['zone_name'] }}
                                    @if($campaign['zone_site_name'])
                                        <span class="text-gray-400">({{ $campaign['zone_site_name'] }})</span>
                                    @endif
                                @else
                                    None linked
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Start Date</dt>
                            <dd class="text-sm font-medium text-gray-900 mt-1">{{ \Carbon\Carbon::parse($campaign['start_date'])->format('F d, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">End Date</dt>
                            <dd class="text-sm font-medium text-gray-900 mt-1">{{ $campaign['end_date'] ? \Carbon\Carbon::parse($campaign['end_date'])->format('F d, Y') : '—' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Description</dt>
                            <dd class="text-sm text-gray-700 mt-1 leading-relaxed">{{ $campaign['description'] ?? 'No description provided.' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Budget & Bidding --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">Budget &amp; Bidding</h2>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-8 gap-y-5">
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Total Budget</dt>
                            <dd class="text-sm font-bold text-gray-900 mt-1">&euro;{{ number_format($campaign['budget'], 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Daily Budget</dt>
                            <dd class="text-sm font-bold text-gray-900 mt-1">&euro;{{ number_format($campaign['daily_budget'] ?? 0, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Bid Amount</dt>
                            <dd class="text-sm font-bold text-gray-900 mt-1">&euro;{{ number_format($campaign['bid_amount'] ?? 0, 4) }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Currency</dt>
                            <dd class="text-sm font-bold text-gray-900 mt-1">{{ $campaign['currency'] ?? 'EUR' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Total Spend</dt>
                            <dd class="text-sm font-bold text-gray-900 mt-1">&euro;{{ number_format($campaign['spend'], 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Remaining Budget</dt>
                            <dd class="text-sm font-bold {{ ($campaign['budget'] - $campaign['spend']) > 0 ? 'text-emerald-600' : 'text-red-600' }} mt-1">&euro;{{ number_format($campaign['budget'] - $campaign['spend'], 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Budget Used</dt>
                            <dd class="mt-1">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-2 rounded-full bg-gray-100">
                                        <div class="h-2 rounded-full {{ $spendPct >= 90 ? 'bg-red-500' : ($spendPct >= 70 ? 'bg-amber-500' : 'bg-brand-500') }}" style="width: {{ min($spendPct, 100) }}%"></div>
                                    </div>
                                    <span class="text-sm font-bold text-gray-700">{{ $spendPct }}%</span>
                                </div>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Performance Metrics --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">Performance Metrics</h2>
                </div>
                <div class="p-6">
                    @php
                        $cpc = $campaign['clicks'] > 0 ? $campaign['spend'] / $campaign['clicks'] : 0;
                        $cpa = $campaign['conversions'] > 0 ? $campaign['spend'] / $campaign['conversions'] : 0;
                        $cpm = $campaign['impressions'] > 0 ? ($campaign['spend'] / $campaign['impressions']) * 1000 : 0;
                        $convRate = $campaign['clicks'] > 0 ? ($campaign['conversions'] / $campaign['clicks']) * 100 : 0;
                    @endphp
                    <dl class="grid grid-cols-2 sm:grid-cols-4 gap-x-8 gap-y-5">
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">eCPM</dt>
                            <dd class="text-sm font-bold text-gray-900 mt-1">&euro;{{ number_format($cpm, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">eCPC</dt>
                            <dd class="text-sm font-bold text-gray-900 mt-1">&euro;{{ number_format($cpc, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">eCPA</dt>
                            <dd class="text-sm font-bold text-gray-900 mt-1">&euro;{{ number_format($cpa, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Conv. Rate</dt>
                            <dd class="text-sm font-bold {{ $convRate >= 5 ? 'text-emerald-600' : ($convRate >= 2 ? 'text-blue-600' : 'text-gray-900') }} mt-1">{{ number_format($convRate, 2) }}%</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        {{-- Right column: Targeting & Settings --}}
        <div class="space-y-6">
            {{-- Targeting --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">Targeting</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Geo Targeting</dt>
                        <dd class="mt-1.5 flex flex-wrap gap-1.5">
                            @if(!empty($campaign['targeting_geo']))
                                @php
                                    $countryNames = [
                                        'AL'=>'Albania','BA'=>'Bosnia & Herzegovina','BG'=>'Bulgaria','HR'=>'Croatia',
                                        'GR'=>'Greece','XK'=>'Kosovo','ME'=>'Montenegro','MK'=>'North Macedonia',
                                        'RO'=>'Romania','RS'=>'Serbia','SI'=>'Slovenia','TR'=>'Turkey',
                                    ];
                                @endphp
                                @foreach($campaign['targeting_geo'] as $geo)
                                    <span class="px-2.5 py-1 rounded-lg bg-blue-50 border border-blue-100 text-xs font-semibold text-blue-700">{{ $countryNames[$geo] ?? $geo }} ({{ $geo }})</span>
                                @endforeach
                            @else
                                <span class="text-sm text-gray-400">All countries</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Devices</dt>
                        <dd class="mt-1.5 flex flex-wrap gap-1.5">
                            @if(!empty($campaign['targeting_device']))
                                @foreach($campaign['targeting_device'] as $device)
                                    <span class="px-2.5 py-1 rounded-lg bg-purple-50 border border-purple-100 text-xs font-semibold text-purple-700">{{ ucfirst($device) }}</span>
                                @endforeach
                            @else
                                <span class="text-sm text-gray-400">All devices</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Operating System</dt>
                        <dd class="mt-1.5 flex flex-wrap gap-1.5">
                            @if(!empty($campaign['targeting_os']))
                                @foreach($campaign['targeting_os'] as $os)
                                    <span class="px-2.5 py-1 rounded-lg bg-indigo-50 border border-indigo-100 text-xs font-semibold text-indigo-700">{{ $os }}</span>
                                @endforeach
                            @else
                                <span class="text-sm text-gray-400">All operating systems</span>
                            @endif
                        </dd>
                    </div>
                    @if(!empty($campaign['targeting_os_version']))
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">OS Versions</dt>
                        <dd class="mt-1.5 flex flex-wrap gap-1.5">
                            @foreach($campaign['targeting_os_version'] as $osVer)
                                @if(is_array($osVer))
                                    <span class="px-2.5 py-1 rounded-lg bg-indigo-50 border border-indigo-100 text-xs font-semibold text-indigo-600">{{ $osVer['version'] ?? '' }}</span>
                                @endif
                            @endforeach
                        </dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Browser</dt>
                        <dd class="mt-1.5 flex flex-wrap gap-1.5">
                            @if(!empty($campaign['targeting_browser']))
                                @foreach($campaign['targeting_browser'] as $browser)
                                    <span class="px-2.5 py-1 rounded-lg bg-cyan-50 border border-cyan-100 text-xs font-semibold text-cyan-700">{{ $browser }}</span>
                                @endforeach
                            @else
                                <span class="text-sm text-gray-400">All browsers</span>
                            @endif
                        </dd>
                    </div>
                    @if(!empty($campaign['targeting_browser_version']))
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Browser Versions</dt>
                        <dd class="mt-1.5 flex flex-wrap gap-1.5">
                            @foreach($campaign['targeting_browser_version'] as $brVer)
                                @if(is_array($brVer))
                                    <span class="px-2.5 py-1 rounded-lg bg-cyan-50 border border-cyan-100 text-xs font-semibold text-cyan-600">{{ $brVer['version'] ?? '' }}</span>
                                @endif
                            @endforeach
                        </dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Frequency Cap</dt>
                        <dd class="text-sm font-medium text-gray-900 mt-1">{{ $campaign['frequency_cap'] ?? 'No limit' }} {{ ($campaign['frequency_cap'] ?? null) ? 'impressions/day' : '' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-2">Weekly Distribution</dt>
                        <dd class="text-sm font-medium text-gray-900 mt-1">
                            @if(!empty($campaign['targeting_schedule']))
                                <div class="overflow-x-auto mt-2">
                                    <table class="w-full text-[9px] table-fixed">
                                        <thead>
                                            <tr>
                                                <th class="text-left py-1 px-1 text-gray-500 font-semibold text-[8px]">Day</th>
                                                @for($h = 0; $h < 24; $h++)
                                                    <th class="py-1 px-0.5 text-center text-gray-400 font-medium text-[8px]">{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}</th>
                                                @endfor
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday']; @endphp
                                            @foreach($days as $day)
                                                <tr>
                                                    <td class="py-1 px-1 text-gray-600 font-medium text-[9px]">{{ ucfirst($day) }}</td>
                                                    @for($h = 0; $h < 24; $h++)
                                                        @php
                                                            $isActive = isset($campaign['targeting_schedule'][$day]) &&
                                                                       is_array($campaign['targeting_schedule'][$day]) &&
                                                                       in_array((string)$h, $campaign['targeting_schedule'][$day]);
                                                        @endphp
                                                        <td class="py-1 px-0.5 text-center">
                                                            <span class="inline-block w-3 h-3 rounded {{ $isActive ? 'bg-brand-500' : 'bg-gray-100' }}"></span>
                                                        </td>
                                                    @endfor
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <span class="text-sm text-gray-600">24/7 (Always On)</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Region Targeting</dt>
                        <dd class="mt-1.5 flex flex-wrap gap-1.5">
                            @if(!empty($campaign['targeting_region']))
                                @foreach($campaign['targeting_region'] as $region)
                                    <span class="px-2.5 py-1 rounded-lg bg-green-50 border border-green-100 text-xs font-semibold text-green-700">{{ ucwords(str_replace('-', ' ', $region)) }}</span>
                                @endforeach
                            @else
                                <span class="text-sm text-gray-400">All regions</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">City Targeting</dt>
                        <dd class="mt-1.5 flex flex-wrap gap-1.5">
                            @if(!empty($campaign['targeting_city']))
                                @foreach($campaign['targeting_city'] as $cityEntry)
                                    @if(is_array($cityEntry) && !empty($cityEntry['city']))
                                        <span class="px-2.5 py-1 rounded-lg bg-orange-50 border border-orange-100 text-xs font-semibold text-orange-700">{{ $cityEntry['city'] }} ({{ $cityEntry['country'] ?? '' }})</span>
                                    @endif
                                @endforeach
                            @else
                                <span class="text-sm text-gray-400">All cities</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Connection Type</dt>
                        <dd class="mt-1.5 flex flex-wrap gap-1.5">
                            @if(!empty($campaign['targeting_connection_type']))
                                @foreach($campaign['targeting_connection_type'] as $connType)
                                    <span class="px-2.5 py-1 rounded-lg bg-teal-50 border border-teal-100 text-xs font-semibold text-teal-700">{{ $connType }}</span>
                                @endforeach
                            @else
                                <span class="text-sm text-gray-400">All connection types</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Mobile Carrier</dt>
                        <dd class="mt-1.5 flex flex-wrap gap-1.5">
                            @if(!empty($campaign['targeting_carrier']))
                                @foreach($campaign['targeting_carrier'] as $carrierEntry)
                                    @if(is_array($carrierEntry) && !empty($carrierEntry['carrier']))
                                        <span class="px-2.5 py-1 rounded-lg bg-pink-50 border border-pink-100 text-xs font-semibold text-pink-700">{{ $carrierEntry['carrier'] }} ({{ $carrierEntry['country'] ?? '' }})</span>
                                    @endif
                                @endforeach
                            @else
                                <span class="text-sm text-gray-400">All carriers</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Language Targeting</dt>
                        <dd class="mt-1.5 flex flex-wrap gap-1.5">
                            @if(!empty($campaign['targeting_language']))
                                @php
                                    $langNames = [
                                        'sq'=>'Albanian','bs'=>'Bosnian','bg'=>'Bulgarian','hr'=>'Croatian','el'=>'Greek',
                                        'mk'=>'Macedonian','me'=>'Montenegrin','ro'=>'Romanian','sr'=>'Serbian','sl'=>'Slovenian',
                                        'tr'=>'Turkish','en'=>'English','de'=>'German','fr'=>'French','it'=>'Italian',
                                        'es'=>'Spanish','pt'=>'Portuguese','ru'=>'Russian','ar'=>'Arabic','zh'=>'Chinese',
                                        'ja'=>'Japanese','ko'=>'Korean','hi'=>'Hindi','nl'=>'Dutch','pl'=>'Polish',
                                        'sv'=>'Swedish','da'=>'Danish','fi'=>'Finnish','no'=>'Norwegian','cs'=>'Czech',
                                        'hu'=>'Hungarian','uk'=>'Ukrainian',
                                    ];
                                @endphp
                                @foreach($campaign['targeting_language'] as $langCode)
                                    <span class="px-2.5 py-1 rounded-lg bg-violet-50 border border-violet-100 text-xs font-semibold text-violet-700">{{ $langNames[$langCode] ?? $langCode }} ({{ $langCode }})</span>
                                @endforeach
                            @else
                                <span class="text-sm text-gray-400">All languages</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Traffic Type</dt>
                        <dd class="mt-1.5">
                            @php $tt = $campaign['targeting_traffic_type'] ?? 'all'; @endphp
                            @if($tt === 'mainstream')
                                <span class="px-2.5 py-1 rounded-lg bg-green-50 border border-green-100 text-xs font-semibold text-green-700">Mainstream</span>
                            @elseif($tt === 'non-mainstream')
                                <span class="px-2.5 py-1 rounded-lg bg-red-50 border border-red-100 text-xs font-semibold text-red-700">Non-Mainstream (Adult)</span>
                            @else
                                <span class="px-2.5 py-1 rounded-lg bg-gray-50 border border-gray-100 text-xs font-semibold text-gray-600">All Traffic</span>
                            @endif
                        </dd>
                    </div>
                    @if(!empty($campaign['targeting_ip_include']))
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">IP Whitelist</dt>
                        <dd class="mt-1.5 flex flex-wrap gap-1.5">
                            @foreach($campaign['targeting_ip_include'] as $ip)
                                <span class="px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-100 text-xs font-mono font-semibold text-emerald-700">{{ $ip }}</span>
                            @endforeach
                        </dd>
                    </div>
                    @endif
                    @if(!empty($campaign['targeting_ip_exclude']))
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">IP Blacklist</dt>
                        <dd class="mt-1.5 flex flex-wrap gap-1.5">
                            @foreach($campaign['targeting_ip_exclude'] as $ip)
                                <span class="px-2.5 py-1 rounded-lg bg-red-50 border border-red-100 text-xs font-mono font-semibold text-red-600">{{ $ip }}</span>
                            @endforeach
                        </dd>
                    </div>
                    @endif

                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Ad Formats</dt>
                        <dd class="mt-1.5">
                            @if(!empty($campaign['ad_formats']))
                                <div class="space-y-2">
                                    @foreach($campaign['ad_formats'] as $format)
                                        @if(is_array($format))
                                            <div class="flex items-center gap-2 flex-wrap">
                                                @if(!empty($format['file_path']) && ($format['content_type'] ?? '') === 'image')
                                                    <img src="{{ asset($format['file_path']) }}" alt="{{ $format['ad_name'] ?? '' }}" class="w-8 h-8 rounded object-cover flex-shrink-0">
                                                @endif
                                                <span class="inline-block px-2.5 py-1 rounded-lg bg-purple-50 border border-purple-100 text-xs font-semibold text-purple-700">{{ $format['format_label'] ?? $format['format'] ?? '—' }}</span>
                                                <span class="text-xs text-gray-600">{{ $format['ad_name'] ?? '' }}</span>
                                                @if(!empty($format['dimension']) && $format['dimension'] !== '—')
                                                    <span class="text-[10px] text-gray-400">({{ $format['dimension'] }})</span>
                                                @endif
                                                @if(!empty($format['file_size']))
                                                    <span class="text-[10px] text-gray-400">{{ number_format($format['file_size'] / 1024, 1) }} KB</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="inline-block px-2.5 py-1 rounded-lg bg-purple-50 border border-purple-100 text-xs font-semibold text-purple-700">{{ $format }}</span>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <span class="text-sm text-gray-400">No specific formats configured</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-2">Traffic Sources</dt>
                        <dd class="mt-1.5">
                            @if(!empty($campaign['traffic_sources']))
                                <div class="overflow-x-auto">
                                    <table class="w-full text-[9px]">
                                        <thead>
                                            <tr class="border-b border-gray-100">
                                                <th class="text-left py-1 px-1.5 text-[8px] font-semibold uppercase text-gray-400">Source</th>
                                                <th class="text-right py-1 px-1.5 text-[8px] font-semibold uppercase text-gray-400">Bid</th>
                                                <th class="text-center py-1 px-1.5 text-[8px] font-semibold uppercase text-gray-400">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-50">
                                            @foreach($campaign['traffic_sources'] as $ts)
                                                <tr>
                                                    <td class="py-1 px-1.5 text-[9px] text-gray-700">{{ $ts['name'] ?? 'Source #' . ($ts['id'] ?? '') }}</td>
                                                    <td class="py-1 px-1.5 text-right text-[9px] font-medium text-gray-900">&euro;{{ number_format($ts['bid'] ?? 0, 2) }}</td>
                                                    <td class="py-1 px-1.5 text-center">
                                                        <span class="px-1.5 py-0.5 rounded text-[8px] font-bold uppercase {{ ($ts['status'] ?? 'active') === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ $ts['status'] ?? 'active' }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <span class="text-sm text-gray-400">No traffic source bidding configured</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-2">Country Bidding</dt>
                        <dd class="mt-1.5">
                            @if(!empty($campaign['country_bids']))
                                <div class="overflow-x-auto">
                                    <table class="w-full text-[9px]">
                                        <thead>
                                            <tr class="border-b border-gray-100">
                                                <th class="text-left py-1 px-1.5 text-[8px] font-semibold uppercase text-gray-400">Country</th>
                                                <th class="text-left py-1 px-1.5 text-[8px] font-semibold uppercase text-gray-400">Model</th>
                                                <th class="text-right py-1 px-1.5 text-[8px] font-semibold uppercase text-gray-400">Bid</th>
                                                <th class="text-center py-1 px-1.5 text-[8px] font-semibold uppercase text-gray-400">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-50">
                                            @foreach($campaign['country_bids'] as $cb)
                                                <tr>
                                                    <td class="py-1 px-1.5 text-[9px] text-gray-700">{{ $cb['name'] ?? $cb['code'] ?? '—' }}</td>
                                                    <td class="py-1 px-1.5">
                                                        <span class="px-1.5 py-0.5 rounded text-[8px] font-bold uppercase bg-blue-100 text-blue-700">{{ strtoupper($cb['pricing'] ?? 'CPM') }}</span>
                                                    </td>
                                                    <td class="py-1 px-1.5 text-right text-[9px] font-medium text-gray-900">&euro;{{ number_format($cb['bid'] ?? 0, 2) }}</td>
                                                    <td class="py-1 px-1.5 text-center">
                                                        <span class="px-1.5 py-0.5 rounded text-[8px] font-bold uppercase {{ ($cb['status'] ?? 'active') === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ $cb['status'] ?? 'active' }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <span class="text-sm text-gray-400">No country-specific bidding configured</span>
                            @endif
                        </dd>
                    </div>
                </div>
            </div>

            {{-- S2S Postback Tracking --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">S2S Postback Tracking</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</dt>
                        <dd class="mt-1.5">
                            @if(!empty($campaign['s2s_enabled']))
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-green-50 border border-green-100 text-xs font-semibold text-green-700">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Enabled
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-50 border border-gray-200 text-xs font-semibold text-gray-500">
                                    Disabled
                                </span>
                            @endif
                        </dd>
                    </div>
                    @if(!empty($campaign['s2s_enabled']))
                        @if(!empty($campaign['s2s_postback_url']))
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Advertiser Postback URL</dt>
                            <dd class="mt-1.5 bg-gray-50 rounded-lg p-3">
                                <p class="text-[11px] font-mono text-gray-700 break-all">{{ $campaign['s2s_postback_url'] }}</p>
                            </dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Your Postback Endpoint</dt>
                            <dd class="mt-1.5 bg-blue-50 rounded-lg p-3">
                                <p class="text-[11px] font-mono text-blue-700 break-all">{{ url('/track/campaign/' . $campaign['id'] . '/postback') }}?click_id={click_id}&payout={payout}&tx_id={tx_id}</p>
                                <p class="text-[10px] text-blue-500 mt-2">Send a GET or POST request to this URL when a conversion occurs.</p>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Available Macros</dt>
                            <dd class="mt-1.5 flex flex-wrap gap-1">
                                @foreach(['{click_id}', '{payout}', '{tx_id}', '{goal}', '{campaign_id}', '{ad_id}', '{country}', '{device}', '{timestamp}'] as $macro)
                                    <span class="px-2 py-0.5 rounded bg-gray-100 border border-gray-200 text-[10px] font-mono text-gray-600">{{ $macro }}</span>
                                @endforeach
                            </dd>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Settings --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">Settings</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Priority Weight</dt>
                        <dd class="mt-1.5">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 rounded-full bg-gray-100">
                                    <div class="h-2 rounded-full bg-brand-500" style="width: {{ (($campaign['weight'] ?? 5) / 10) * 100 }}%"></div>
                                </div>
                                <span class="text-sm font-bold text-gray-700">{{ $campaign['weight'] ?? 5 }}/10</span>
                            </div>
                        </dd>
                    </div>
                    @if($campaign['group_name'] ?? null)
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Campaign Group</dt>
                            <dd class="text-sm font-medium text-gray-900 mt-1">{{ $campaign['group_name'] }}</dd>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">Quick Actions</h2>
                </div>
                <div class="p-4 space-y-2">
                    <a href="{{ route('admin.campaigns.edit', $campaign['id']) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-50 transition-colors text-sm text-gray-700">
                        <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        Edit Campaign
                    </a>
                    <form method="POST" action="{{ route('admin.campaigns.duplicate', $campaign['id']) }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-50 transition-colors text-sm text-gray-700 w-full text-left">
                            <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            Duplicate Campaign
                        </button>
                    </form>
                    @if($campaign['status'] === 'active')
                        <form method="POST" action="{{ route('admin.campaigns.updateStatus', $campaign['id']) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="paused">
                            <button type="submit" class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-amber-50 transition-colors text-sm text-amber-700 w-full text-left">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                Pause Campaign
                            </button>
                        </form>
                    @elseif($campaign['status'] === 'paused')
                        <form method="POST" action="{{ route('admin.campaigns.updateStatus', $campaign['id']) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="active">
                            <button type="submit" class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-emerald-50 transition-colors text-sm text-emerald-700 w-full text-left">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" stroke="currentColor" stroke-width="1.5"/><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/></svg>
                                Resume Campaign
                            </button>
                        </form>
                    @endif
                    <div class="border-t border-gray-100 my-1"></div>
                    <form method="POST" action="{{ route('admin.campaigns.destroy', $campaign['id']) }}" onsubmit="return confirm('Are you sure you want to delete this campaign?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-red-50 transition-colors text-sm text-red-600 w-full text-left">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            Delete Campaign
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
