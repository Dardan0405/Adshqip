@extends('layouts.advertiser')

@section('title', 'Direct Campaign — ' . $campaign['name'])

@section('content')
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
                <a href="{{ route('advertiser.direct-campaigns') }}" class="hover:text-gray-600 transition-colors">Direct Campaigns</a>
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                <span class="text-gray-600">#{{ $campaign['id'] }}</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $campaign['name'] }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('advertiser.direct-campaigns') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Back
            </a>
            <a href="{{ route('advertiser.direct-campaigns.edit', $campaign['id']) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm transition-colors">
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
            'archived' => 'bg-purple-100 text-purple-700 border-purple-200',
        ];
        $statusDots = [
            'active' => 'bg-emerald-500', 'paused' => 'bg-amber-500', 'draft' => 'bg-gray-400',
            'completed' => 'bg-blue-500', 'rejected' => 'bg-red-500', 'pending_review' => 'bg-orange-500',
            'archived' => 'bg-purple-500',
        ];
        $pricingColors = [
            'cpm' => 'bg-purple-100 text-purple-700', 'cpc' => 'bg-blue-100 text-blue-700',
            'cpa' => 'bg-emerald-100 text-emerald-700', 'cpv' => 'bg-pink-100 text-pink-700',
            'cpv_ctw' => 'bg-indigo-100 text-indigo-700', 'flat_rate' => 'bg-amber-100 text-amber-700',
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
            <span class="px-2.5 py-1 rounded text-[11px] font-bold uppercase {{ $pricingColors[$campaign['pricing_model']] ?? 'bg-gray-100 text-gray-600' }}">{{ strtoupper(str_replace('_', ' ', $campaign['pricing_model'])) }}</span>
            <div class="h-4 w-px bg-gray-200"></div>
            <span class="text-sm text-gray-500">
                <span class="font-medium text-gray-700">{{ $campaign['start_date'] ? \Carbon\Carbon::parse($campaign['start_date'])->format('M d, Y') : '—' }}</span>
                &mdash;
                <span class="font-medium text-gray-700">{{ $campaign['end_date'] ? \Carbon\Carbon::parse($campaign['end_date'])->format('M d, Y') : 'No end date' }}</span>
            </span>
            <div class="h-4 w-px bg-gray-200"></div>
            <span class="text-sm text-gray-500">Priority: <strong class="text-gray-700">{{ $campaign['priority'] ?? 5 }}</strong></span>
            <span class="text-sm text-gray-500">{{ ucfirst($campaign['delivery_mode'] ?? 'standard') }}</span>
            @if($campaign['campaign_group_name'] ?? null)
                <div class="h-4 w-px bg-gray-200"></div>
                <span class="inline-flex items-center gap-1.5 text-sm text-gray-500">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    {{ $campaign['campaign_group_name'] }}
                </span>
            @endif
        </div>
    </div>

    {{-- Performance Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3 mb-6">
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
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">AdBlock Detected</div>
            <div class="text-xl font-bold text-gray-900 mt-1">{{ number_format($campaign['adblock_detected'] ?? 0) }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left column --}}
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
                            <dd class="text-sm font-medium text-gray-900 mt-1">{{ $campaign['advertiser_email'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Pricing Model</dt>
                            <dd class="text-sm font-medium text-gray-900 mt-1">{{ strtoupper(str_replace('_', ' ', $campaign['pricing_model'])) }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Marketing Objective</dt>
                            <dd class="text-sm font-medium text-gray-900 mt-1">{{ ucwords(str_replace('_', ' ', $campaign['marketing_objective'] ?? 'Traffic')) }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Linked AdBlock</dt>
                            <dd class="text-sm font-medium text-gray-900 mt-1">
                                @if($campaign['zone_id'] ?? null)
                                    #{{ $campaign['zone_id'] }} - {{ $campaign['zone_name'] }}
                                @else
                                    No AdBlock linked
                                @endif
                            </dd>
                            @if($campaign['zone_site_name'] ?? null)
                                <div class="text-xs text-gray-400 mt-1">{{ $campaign['zone_site_name'] }}</div>
                            @endif
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Start Date</dt>
                            <dd class="text-sm font-medium text-gray-900 mt-1">{{ $campaign['start_date'] ? \Carbon\Carbon::parse($campaign['start_date'])->format('F d, Y') : '—' }}</dd>
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

            {{-- Destination & Tracking --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">Destination &amp; Tracking</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Destination URL</dt>
                        <dd class="mt-1.5 bg-gray-50 rounded-lg p-3">
                            <p class="text-[11px] font-mono text-gray-700 break-all">{{ $campaign['destination_url'] ?? '—' }}</p>
                        </dd>
                    </div>
                    @if($campaign['display_url'] ?? null)
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Display URL</dt>
                        <dd class="text-sm font-medium text-gray-900 mt-1">{{ $campaign['display_url'] }}</dd>
                    </div>
                    @endif
                    @if($campaign['tracking_url'] ?? null)
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Tracking URL</dt>
                        <dd class="mt-1.5 bg-blue-50 rounded-lg p-3">
                            <p class="text-[11px] font-mono text-blue-700 break-all">{{ $campaign['tracking_url'] }}</p>
                        </dd>
                    </div>
                    @endif
                    @if($campaign['click_tracking_url'] ?? null)
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Click Tracking URL</dt>
                        <dd class="mt-1.5 bg-blue-50 rounded-lg p-3">
                            <p class="text-[11px] font-mono text-blue-700 break-all">{{ $campaign['click_tracking_url'] }}</p>
                        </dd>
                    </div>
                    @endif
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
                            <dd class="text-sm font-bold text-gray-900 mt-1">&euro;{{ number_format($campaign['total_budget'] ?? 0, 2) }}</dd>
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
                            @php $remaining = ($campaign['total_budget'] ?? 0) - $campaign['spend']; @endphp
                            <dd class="text-sm font-bold {{ $remaining > 0 ? 'text-emerald-600' : 'text-red-600' }} mt-1">&euro;{{ number_format($remaining, 2) }}</dd>
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

            {{-- Ad Content --}}
            @if(($campaign['headline'] ?? null) || ($campaign['body_text'] ?? null) || ($campaign['call_to_action'] ?? null))
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">Ad Content</h2>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5">
                        @if($campaign['headline'] ?? null)
                        <div class="sm:col-span-2">
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Headline</dt>
                            <dd class="text-sm font-medium text-gray-900 mt-1">{{ $campaign['headline'] }}</dd>
                        </div>
                        @endif
                        @if($campaign['body_text'] ?? null)
                        <div class="sm:col-span-2">
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Body Text</dt>
                            <dd class="text-sm text-gray-700 mt-1 leading-relaxed">{{ $campaign['body_text'] }}</dd>
                        </div>
                        @endif
                        @if($campaign['call_to_action'] ?? null)
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Call to Action</dt>
                            <dd class="mt-1"><span class="px-3 py-1 rounded-lg bg-brand-100 text-brand-700 text-xs font-semibold">{{ $campaign['call_to_action'] }}</span></dd>
                        </div>
                        @endif
                        @if($campaign['sponsored_label'] ?? null)
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Sponsored Label</dt>
                            <dd class="text-sm font-medium text-gray-900 mt-1">{{ $campaign['sponsored_label'] }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>
            @endif

            {{-- Brand Settings --}}
            @if($campaign['brand_name'] ?? null)
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">Brand Settings</h2>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5">
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Brand Name</dt>
                            <dd class="text-sm font-medium text-gray-900 mt-1">{{ $campaign['brand_name'] }}</dd>
                        </div>
                        @if($campaign['brand_tagline'] ?? null)
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Tagline</dt>
                            <dd class="text-sm text-gray-700 mt-1">{{ $campaign['brand_tagline'] }}</dd>
                        </div>
                        @endif
                        @if(($campaign['brand_color_primary'] ?? null) || ($campaign['brand_color_secondary'] ?? null))
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Brand Colors</dt>
                            <dd class="mt-1.5 flex items-center gap-2">
                                @if($campaign['brand_color_primary'] ?? null)
                                    <span class="w-8 h-8 rounded-lg border border-gray-200" style="background-color: {{ $campaign['brand_color_primary'] }}" title="Primary: {{ $campaign['brand_color_primary'] }}"></span>
                                @endif
                                @if($campaign['brand_color_secondary'] ?? null)
                                    <span class="w-8 h-8 rounded-lg border border-gray-200" style="background-color: {{ $campaign['brand_color_secondary'] }}" title="Secondary: {{ $campaign['brand_color_secondary'] }}"></span>
                                @endif
                            </dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>
            @endif
        </div>

        {{-- Right column --}}
        <div class="space-y-6">
            {{-- Targeting --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">Targeting</h2>
                </div>
                <div class="p-6 space-y-4">
                    @php
                        $tb = $campaign['targeting_by_type'] ?? [];
                        $countryNames = [
                            'AL'=>'Albania','BA'=>'Bosnia & Herzegovina','BG'=>'Bulgaria','HR'=>'Croatia',
                            'GR'=>'Greece','XK'=>'Kosovo','ME'=>'Montenegro','MK'=>'North Macedonia',
                            'RO'=>'Romania','RS'=>'Serbia','SI'=>'Slovenia','TR'=>'Turkey',
                        ];
                        $langNames = [
                            'sq'=>'Albanian','bs'=>'Bosnian','bg'=>'Bulgarian','hr'=>'Croatian','el'=>'Greek',
                            'mk'=>'Macedonian','me'=>'Montenegrin','ro'=>'Romanian','sr'=>'Serbian','sl'=>'Slovenian',
                            'tr'=>'Turkish','en'=>'English','de'=>'German','fr'=>'French','it'=>'Italian',
                        ];
                    @endphp
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Geo Targeting</dt>
                        <dd class="mt-1.5 flex flex-wrap gap-1.5">
                            @if(!empty($tb['geo_country']))
                                @foreach($tb['geo_country'] as $geo)
                                    <span class="px-2.5 py-1 rounded-lg bg-blue-50 border border-blue-100 text-xs font-semibold text-blue-700">{{ $countryNames[$geo] ?? $geo }}</span>
                                @endforeach
                            @else
                                <span class="text-sm text-gray-400">All countries</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Devices</dt>
                        <dd class="mt-1.5 flex flex-wrap gap-1.5">
                            @if(!empty($tb['device']))
                                @foreach($tb['device'] as $device)
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
                            @if(!empty($tb['os']))
                                @foreach($tb['os'] as $os)
                                    <span class="px-2.5 py-1 rounded-lg bg-indigo-50 border border-indigo-100 text-xs font-semibold text-indigo-700">{{ $os }}</span>
                                @endforeach
                            @else
                                <span class="text-sm text-gray-400">All operating systems</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Browser</dt>
                        <dd class="mt-1.5 flex flex-wrap gap-1.5">
                            @if(!empty($tb['browser']))
                                @foreach($tb['browser'] as $browser)
                                    <span class="px-2.5 py-1 rounded-lg bg-cyan-50 border border-cyan-100 text-xs font-semibold text-cyan-700">{{ $browser }}</span>
                                @endforeach
                            @else
                                <span class="text-sm text-gray-400">All browsers</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Language</dt>
                        <dd class="mt-1.5 flex flex-wrap gap-1.5">
                            @if(!empty($tb['language']))
                                @foreach($tb['language'] as $lang)
                                    <span class="px-2.5 py-1 rounded-lg bg-violet-50 border border-violet-100 text-xs font-semibold text-violet-700">{{ $langNames[$lang] ?? $lang }}</span>
                                @endforeach
                            @else
                                <span class="text-sm text-gray-400">All languages</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Connection Type</dt>
                        <dd class="mt-1.5 flex flex-wrap gap-1.5">
                            @if(!empty($tb['connection_type']))
                                @foreach($tb['connection_type'] as $ct)
                                    <span class="px-2.5 py-1 rounded-lg bg-teal-50 border border-teal-100 text-xs font-semibold text-teal-700">{{ $ct }}</span>
                                @endforeach
                            @else
                                <span class="text-sm text-gray-400">All connection types</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Frequency Cap</dt>
                        <dd class="text-sm font-medium text-gray-900 mt-1">{{ $campaign['frequency_cap'] ?? 'No limit' }} {{ ($campaign['frequency_cap'] ?? null) ? '/ ' . ($campaign['frequency_cap_period'] ?? 'day') : '' }}</dd>
                    </div>
                </div>
            </div>

            {{-- CTW Video Settings --}}
            @if($campaign['ctw_enabled'] ?? false)
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">CTW Video Settings</h2>
                </div>
                <div class="p-6 space-y-3">
                    <div class="flex justify-between text-sm"><span class="text-gray-500">Min Watch</span><span class="font-medium text-gray-900">{{ $campaign['ctw_min_watch_seconds'] ?? 5 }}s</span></div>
                    @if($campaign['ctw_skip_after_seconds'] ?? null)
                    <div class="flex justify-between text-sm"><span class="text-gray-500">Skip After</span><span class="font-medium text-gray-900">{{ $campaign['ctw_skip_after_seconds'] }}s</span></div>
                    @endif
                    <div class="flex justify-between text-sm"><span class="text-gray-500">Autoplay</span><span class="font-medium text-gray-900">{{ ($campaign['ctw_autoplay'] ?? false) ? 'Yes' : 'No' }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-gray-500">Muted Autoplay</span><span class="font-medium text-gray-900">{{ ($campaign['ctw_muted_autoplay'] ?? true) ? 'Yes' : 'No' }}</span></div>
                </div>
            </div>
            @endif

            {{-- End Card Settings --}}
            @if($campaign['end_card_enabled'] ?? false)
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">End Card</h2>
                </div>
                <div class="p-6 space-y-3">
                    <div class="flex justify-between text-sm"><span class="text-gray-500">Type</span><span class="font-medium text-gray-900">{{ ucwords(str_replace('_', ' ', $campaign['end_card_type'] ?? 'cta_button')) }}</span></div>
                    @if($campaign['end_card_headline'] ?? null)
                    <div class="flex justify-between text-sm"><span class="text-gray-500">Headline</span><span class="font-medium text-gray-900">{{ $campaign['end_card_headline'] }}</span></div>
                    @endif
                    @if($campaign['end_card_cta_text'] ?? null)
                    <div class="flex justify-between text-sm"><span class="text-gray-500">CTA</span><span class="font-medium text-gray-900">{{ $campaign['end_card_cta_text'] }}</span></div>
                    @endif
                    <div class="flex justify-between text-sm"><span class="text-gray-500">Display</span><span class="font-medium text-gray-900">{{ $campaign['end_card_display_seconds'] ?? 10 }}s</span></div>
                </div>
            </div>
            @endif

            {{-- Clip Ad Settings --}}
            @if($campaign['clip_enabled'] ?? false)
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">Clip Ad</h2>
                </div>
                <div class="p-6 space-y-3">
                    <div class="flex justify-between text-sm"><span class="text-gray-500">Aspect Ratio</span><span class="font-medium text-gray-900">{{ $campaign['clip_aspect_ratio'] ?? '9:16' }}</span></div>
                    @if($campaign['clip_duration_seconds'] ?? null)
                    <div class="flex justify-between text-sm"><span class="text-gray-500">Duration</span><span class="font-medium text-gray-900">{{ $campaign['clip_duration_seconds'] }}s</span></div>
                    @endif
                    <div class="flex justify-between text-sm"><span class="text-gray-500">Autoplay</span><span class="font-medium text-gray-900">{{ ($campaign['clip_autoplay'] ?? true) ? 'Yes' : 'No' }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-gray-500">Loop</span><span class="font-medium text-gray-900">{{ ($campaign['clip_loop'] ?? true) ? 'Yes' : 'No' }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-gray-500">Swipe Up</span><span class="font-medium text-gray-900">{{ ($campaign['clip_swipe_up_enabled'] ?? true) ? 'Yes' : 'No' }}</span></div>
                    @if($campaign['clip_caption'] ?? null)
                    <div><span class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Caption</span><p class="text-sm text-gray-700 mt-1">{{ $campaign['clip_caption'] }}</p></div>
                    @endif
                </div>
            </div>
            @endif

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
                                    <div class="h-2 rounded-full bg-brand-500" style="width: {{ (($campaign['priority'] ?? 5) / 10) * 100 }}%"></div>
                                </div>
                                <span class="text-sm font-bold text-gray-700">{{ $campaign['priority'] ?? 5 }}/10</span>
                            </div>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Delivery Mode</dt>
                        <dd class="text-sm font-medium text-gray-900 mt-1">{{ ucfirst($campaign['delivery_mode'] ?? 'standard') }}</dd>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">Quick Actions</h2>
                </div>
                <div class="p-4 space-y-2">
                    <a href="{{ route('advertiser.direct-campaigns.edit', $campaign['id']) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-50 transition-colors text-sm text-gray-700">
                        <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        Edit Campaign
                    </a>
                    <form method="POST" action="{{ route('advertiser.direct-campaigns.duplicate', $campaign['id']) }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-50 transition-colors text-sm text-gray-700 w-full text-left">
                            <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            Duplicate Campaign
                        </button>
                    </form>
                    @if($campaign['status'] === 'active')
                        <form method="POST" action="{{ route('advertiser.direct-campaigns.updateStatus', $campaign['id']) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="paused">
                            <button type="submit" class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-amber-50 transition-colors text-sm text-amber-700 w-full text-left">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                Pause Campaign
                            </button>
                        </form>
                    @elseif($campaign['status'] === 'paused')
                        <form method="POST" action="{{ route('advertiser.direct-campaigns.updateStatus', $campaign['id']) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="active">
                            <button type="submit" class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-emerald-50 transition-colors text-sm text-emerald-700 w-full text-left">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" stroke="currentColor" stroke-width="1.5"/><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/></svg>
                                Resume Campaign
                            </button>
                        </form>
                    @endif
                    <div class="border-t border-gray-100 my-1"></div>
                    <form method="POST" action="{{ route('advertiser.direct-campaigns.destroy', $campaign['id']) }}" onsubmit="return confirm('Are you sure you want to delete this campaign?')">
                        @csrf @method('DELETE')
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
