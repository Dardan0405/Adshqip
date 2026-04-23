@extends('layouts.advertiser')

@section('title', 'Edit Creative — ' . $ad['name'])

@section('content')
<form method="POST" action="{{ route('advertiser.adformats.update', $ad['id']) }}" id="editAdForm">
    @csrf
    @method('PUT')

    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-400 mb-1">
                <a href="{{ route('advertiser.adformats') }}" class="hover:text-gray-600 transition-colors">Ad Formats</a>
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                <span class="text-gray-600">#{{ $ad['id'] }} — Edit</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Creative</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('advertiser.adformats') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Save Changes
            </button>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl border border-red-300 bg-red-50">
            <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SECTION 1: CREATIVE INFO                               --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-brand-100 flex items-center justify-center">
                    <span class="text-sm font-bold text-brand-600">1</span>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Creative Information</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Basic details about this creative</p>
                </div>
            </div>
        </div>
        <div class="p-6 space-y-5">
            {{-- Row: Name + Campaign --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Creative Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required value="{{ old('name', $ad['name']) }}" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Campaign <span class="text-red-500">*</span></label>
                    <select name="campaign_id" required class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign['id'] }}" {{ (int) old('campaign_id', $ad['campaign_id']) === $campaign['id'] ? 'selected' : '' }}>{{ $campaign['name'] }} — {{ $campaign['advertiser_email'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Row: Ad Type + Status --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Creative Type <span class="text-red-500">*</span></label>
                    <select name="ad_type" required class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                        @foreach($adTypes as $key => $label)
                            <option value="{{ $key }}" {{ old('ad_type', $ad['ad_type']) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Status <span class="text-red-500">*</span></label>
                    <select name="status" required class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" {{ old('status', $ad['status']) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Row: Destination URL + Display URL --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Destination URL <span class="text-red-500">*</span></label>
                    <input type="url" name="destination_url" required value="{{ old('destination_url', $ad['destination_url']) }}" placeholder="https://example.com/landing" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Display URL</label>
                    <input type="text" name="display_url" value="{{ old('display_url', $ad['display_url']) }}" placeholder="example.com" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SECTION 2: AD CONTENT (format-specific fields)         --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    @php $fmt = $ad['ad_format'] ?? null; @endphp
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                    <span class="text-sm font-bold text-purple-600">2</span>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Ad Content</h2>
                    <p class="text-xs text-gray-400 mt-0.5">
                        @if($fmt === 'social_bar') Social Bar ad copy & icon
                        @elseif($fmt === 'native') Native ad copy & branding
                        @elseif($fmt === 'interstitial') Interstitial overlay content
                        @elseif($fmt === 'popunder') Popunder content
                        @elseif($fmt === 'in_page_push') In-Page Push notification content
                        @elseif(in_array($fmt, ['instream', 'outstream'])) Video ad headline & CTA
                        @elseif($fmt === 'rewarded') Rewarded video content & reward settings
                        @elseif($ad['ad_type'] === 'text') Text ad copy
                        @elseif($fmt === 'direct_link') Direct link — no content fields needed
                        @else Ad copy and branding
                        @endif
                    </p>
                </div>
                @if($fmt)
                    <span class="ml-auto px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase
                        @if($fmt === 'social_bar') bg-orange-100 text-orange-700
                        @elseif($fmt === 'native') bg-indigo-100 text-indigo-700
                        @elseif($fmt === 'interstitial') bg-red-100 text-red-700
                        @elseif($fmt === 'popunder') bg-pink-100 text-pink-700
                        @elseif($fmt === 'in_page_push') bg-teal-100 text-teal-700
                        @elseif(in_array($fmt, ['instream', 'outstream'])) bg-purple-100 text-purple-700
                        @elseif($fmt === 'rewarded') bg-amber-100 text-amber-700
                        @elseif($fmt === 'direct_link') bg-brand-100 text-brand-700
                        @else bg-gray-100 text-gray-700
                        @endif
                    ">{{ ucfirst(str_replace('_', ' ', $fmt)) }}</span>
                @endif
            </div>
        </div>
        <div class="p-6 space-y-5">

            @if($fmt === 'social_bar')
            {{-- ═══ SOCIAL BAR FIELDS ═══ --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="headline" value="{{ old('headline', $ad['headline']) }}" placeholder="e.g. Don't Miss This Deal!" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Button Text</label>
                    <input type="text" name="call_to_action" value="{{ old('call_to_action', $ad['call_to_action']) }}" placeholder="e.g. Learn More" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Description</label>
                <input type="text" name="body_text" value="{{ old('body_text', $ad['body_text']) }}" placeholder="Short description text" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>

            @elseif($fmt === 'native')
            {{-- ═══ NATIVE AD FIELDS ═══ --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Headline <span class="text-red-500">*</span></label>
                    <input type="text" name="headline" value="{{ old('headline', $ad['headline']) }}" placeholder="e.g. Discover Our New Product" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Brand Name</label>
                    <input type="text" name="brand_name" value="{{ old('brand_name', $ad['brand_name']) }}" placeholder="e.g. Acme Inc." class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Body Text</label>
                <textarea name="body_text" rows="3" placeholder="Describe your product or offer..." class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none">{{ old('body_text', $ad['body_text']) }}</textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Call to Action</label>
                    <input type="text" name="call_to_action" value="{{ old('call_to_action', $ad['call_to_action']) }}" placeholder="e.g. Learn More, Shop Now" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Alt Text</label>
                    <input type="text" name="alt_text" value="{{ old('alt_text', $ad['alt_text']) }}" placeholder="Image description for accessibility" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>

            @elseif($fmt === 'interstitial')
            {{-- ═══ INTERSTITIAL FIELDS ═══ --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Headline <span class="text-red-500">*</span></label>
                    <input type="text" name="headline" value="{{ old('headline', $ad['headline']) }}" placeholder="e.g. Special Offer!" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">CTA Button Text</label>
                    <input type="text" name="call_to_action" value="{{ old('call_to_action', $ad['call_to_action']) }}" placeholder="e.g. Continue, Get Started" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Body Text</label>
                <textarea name="body_text" rows="2" placeholder="e.g. You will be redirected shortly..." class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none">{{ old('body_text', $ad['body_text']) }}</textarea>
            </div>

            @elseif($fmt === 'popunder')
            {{-- ═══ POPUNDER FIELDS ═══ --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Headline</label>
                <input type="text" name="headline" value="{{ old('headline', $ad['headline']) }}" placeholder="e.g. Check This Out" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Body Text</label>
                <textarea name="body_text" rows="2" placeholder="Optional description..." class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none">{{ old('body_text', $ad['body_text']) }}</textarea>
            </div>

            @elseif($fmt === 'in_page_push')
            {{-- ═══ IN-PAGE PUSH FIELDS ═══ --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Headline <span class="text-red-500">*</span></label>
                    <input type="text" name="headline" value="{{ old('headline', $ad['headline']) }}" placeholder="e.g. Don't Miss This Offer!" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Body Text</label>
                    <input type="text" name="body_text" value="{{ old('body_text', $ad['body_text']) }}" placeholder="e.g. Limited time — click to see details" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>

            @elseif(in_array($fmt, ['instream', 'outstream']))
            {{-- ═══ IN-STREAM / OUT-STREAM VIDEO FIELDS ═══ --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Headline</label>
                    <input type="text" name="headline" value="{{ old('headline', $ad['headline']) }}" placeholder="e.g. Watch Our Latest Ad" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Button Text</label>
                    <input type="text" name="call_to_action" value="{{ old('call_to_action', $ad['call_to_action']) }}" placeholder="e.g. Learn More, Shop Now" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>
            @if($ad['video_url'])
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Video URL</label>
                    <div class="flex items-center gap-2 bg-gray-50 rounded-lg px-4 py-2.5 border border-gray-100">
                        <svg class="w-4 h-4 text-purple-500 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" fill="currentColor"/><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/></svg>
                        <span class="text-xs text-gray-600 font-mono truncate">{{ $ad['video_url'] }}</span>
                    </div>
                </div>
            @endif

            @elseif($fmt === 'rewarded')
            {{-- ═══ REWARDED VIDEO FIELDS ═══ --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Headline</label>
                    <input type="text" name="headline" value="{{ old('headline', $ad['headline']) }}" placeholder="e.g. Watch to Earn Coins!" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">CTA Button Text</label>
                    <input type="text" name="call_to_action" value="{{ old('call_to_action', $ad['call_to_action']) }}" placeholder="e.g. Claim Reward" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>
            @if($ad['video_url'])
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Video URL</label>
                    <div class="flex items-center gap-2 bg-gray-50 rounded-lg px-4 py-2.5 border border-gray-100">
                        <svg class="w-4 h-4 text-purple-500 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" fill="currentColor"/><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/></svg>
                        <span class="text-xs text-gray-600 font-mono truncate">{{ $ad['video_url'] }}</span>
                    </div>
                </div>
            @endif
            {{-- Reward Settings --}}
            <div class="p-4 rounded-lg border border-amber-200 bg-amber-50/50">
                <p class="text-xs font-semibold text-amber-700 flex items-center gap-1.5 mb-3">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    Reward Settings
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Reward Amount</label>
                        <input type="number" name="body_text" value="{{ old('body_text', $ad['body_text']) }}" placeholder="e.g. 50" min="1" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Reward Type</label>
                        @php $currentRewardType = old('sponsored_label', $ad['sponsored_label'] ?? 'Coins'); @endphp
                        <select name="sponsored_label" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 bg-white">
                            @foreach(['Coins', 'Gems', 'Credits', 'Points', 'Extra Life', 'Bonus'] as $rt)
                                <option value="{{ $rt }}" {{ $currentRewardType === $rt ? 'selected' : '' }}>{{ $rt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            @elseif($ad['ad_type'] === 'text')
            {{-- ═══ TEXT AD FIELDS ═══ --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Headline <span class="text-red-500">*</span></label>
                    <input type="text" name="headline" value="{{ old('headline', $ad['headline']) }}" placeholder="e.g. Great Summer Sale!" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Display URL</label>
                    <input type="text" name="display_url" value="{{ old('display_url', $ad['display_url']) }}" placeholder="e.g. example.com/sale" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Body Text</label>
                <textarea name="body_text" rows="3" placeholder="Main ad description text..." class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none">{{ old('body_text', $ad['body_text']) }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Call to Action</label>
                <input type="text" name="call_to_action" value="{{ old('call_to_action', $ad['call_to_action']) }}" placeholder="e.g. Learn More, Shop Now" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>

            @elseif($fmt === 'direct_link')
            {{-- ═══ DIRECT LINK — minimal ═══ --}}
            <div class="flex items-center gap-3 p-4 rounded-lg bg-gray-50 border border-gray-100">
                <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                <p class="text-sm text-gray-500">Direct link ads redirect instantly — no ad content fields are needed.</p>
            </div>

            @else
            {{-- ═══ DEFAULT / IMAGE / DISPLAY FIELDS ═══ --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Headline</label>
                    <input type="text" name="headline" value="{{ old('headline', $ad['headline']) }}" placeholder="e.g. Great Summer Sale!" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Brand Name</label>
                    <input type="text" name="brand_name" value="{{ old('brand_name', $ad['brand_name']) }}" placeholder="e.g. Acme Inc." class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Body Text</label>
                <textarea name="body_text" rows="3" placeholder="Main ad content text..." class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none">{{ old('body_text', $ad['body_text']) }}</textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Call to Action</label>
                    <input type="text" name="call_to_action" value="{{ old('call_to_action', $ad['call_to_action']) }}" placeholder="e.g. Learn More, Shop Now" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Alt Text</label>
                    <input type="text" name="alt_text" value="{{ old('alt_text', $ad['alt_text']) }}" placeholder="Descriptive text for accessibility" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>
            @endif

        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SECTION 3: DELIVERY                                    --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <span class="text-sm font-bold text-emerald-600">3</span>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Delivery</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Priority weight for ad serving</p>
                </div>
            </div>
        </div>
        <div class="p-6 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Priority Weight: <span id="weightValue" class="text-brand-600">{{ old('weight', $ad['weight']) }}</span></label>
                <input type="range" name="weight" min="1" max="10" value="{{ old('weight', $ad['weight']) }}" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-brand-600" oninput="document.getElementById('weightValue').textContent = this.value">
                <div class="flex justify-between text-[10px] text-gray-400 mt-1">
                    <span>1 (Low)</span>
                    <span>5 (Normal)</span>
                    <span>10 (High)</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SECTION 4: CREATIVE ASSET & DIMENSIONS                 --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                    <span class="text-sm font-bold text-blue-600">4</span>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Creative Asset &amp; Dimensions</h2>
                    <p class="text-xs text-gray-400 mt-0.5">File details and dimension settings</p>
                </div>
            </div>
        </div>
        <div class="p-6 space-y-5">
            {{-- Dimension Selector --}}
            @php
                $currentDimension = ($ad['width'] && $ad['height']) ? $ad['width'] . 'x' . $ad['height'] : '';
                $selectedScreenId = old('display_screen_id', $ad['display_screen_id']);
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Display Screen</label>
                    <select id="dimensionSelect" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                        <option value="" {{ $currentDimension === '' ? 'selected' : '' }}>No dimensions</option>
                        @foreach($displayScreens as $screen)
                            <option
                                value="{{ $screen['id'] }}"
                                data-width="{{ $screen['width'] }}"
                                data-height="{{ $screen['height'] }}"
                                data-dimension="{{ $screen['dimension'] }}"
                                {{ (string) $selectedScreenId === (string) $screen['id'] ? 'selected' : '' }}
                            >
                                {{ $screen['screen_name'] }} ({{ $screen['dimension'] }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Selected Size</label>
                    <div class="flex items-center gap-2 h-[42px]">
                        <span class="px-3 py-1.5 rounded-lg bg-purple-50 border border-purple-100 text-xs font-semibold text-purple-700" id="dimensionPreview">{{ $currentDimension ? $currentDimension . ' px' : '—' }}</span>
                    </div>
                </div>
            </div>

            {{-- Hidden inputs that actually get submitted --}}
            <input type="hidden" name="display_screen_id" id="hiddenDisplayScreenId" value="{{ $selectedScreenId }}">
            <input type="hidden" name="width" id="hiddenWidth" value="{{ old('width', $ad['width']) }}">
            <input type="hidden" name="height" id="hiddenHeight" value="{{ old('height', $ad['height']) }}">

            {{-- File info (read-only) --}}
            @if($ad['file_path'] || $ad['file_type'])
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Current File</h4>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">File Type</p>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-100 text-blue-700">{{ $ad['file_type'] ?? '—' }}</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">File Path</p>
                        <p class="text-xs text-gray-500 truncate max-w-[200px]" title="{{ $ad['file_path'] }}">{{ $ad['file_path'] ?? '—' }}</p>
                    </div>
                </div>
                @if($ad['file_path'] && in_array($ad['file_type'], ['image', 'gif']))
                    <div class="flex items-center justify-center" id="imagePreviewContainer">
                        <img src="{{ asset($ad['file_path']) }}" alt="{{ $ad['name'] }}" class="rounded-lg shadow-sm object-contain" id="imagePreview"
                            @if($ad['width'] && $ad['height'])
                                style="width: {{ min($ad['width'], 600) }}px; height: {{ min($ad['height'], 400) }}px; max-width: 100%;"
                            @else
                                style="max-width: 100%; max-height: 16rem;"
                            @endif
                        >
                    </div>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- Bottom action footer --}}
    <div class="sticky bottom-0 z-20 -mx-6 mt-8 border-t border-gray-200 bg-gray-50/95 px-6 py-4 backdrop-blur">
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
            <a href="{{ route('advertiser.adformats') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Cancel
            </a>
            <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Save Changes
            </button>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dimensionSelect = document.getElementById('dimensionSelect');
    const hiddenDisplayScreenId = document.getElementById('hiddenDisplayScreenId');
    const hiddenWidth = document.getElementById('hiddenWidth');
    const hiddenHeight = document.getElementById('hiddenHeight');
    const preview = document.getElementById('dimensionPreview');
    const imagePreview = document.getElementById('imagePreview');

    function updateImagePreview(w, h) {
        if (!imagePreview) return;
        if (w && h) {
            imagePreview.style.width = Math.min(parseInt(w), 600) + 'px';
            imagePreview.style.height = Math.min(parseInt(h), 400) + 'px';
            imagePreview.style.maxWidth = '100%';
            imagePreview.style.maxHeight = '';
        } else {
            imagePreview.style.width = '';
            imagePreview.style.height = '';
            imagePreview.style.maxWidth = '100%';
            imagePreview.style.maxHeight = '16rem';
        }
    }

    dimensionSelect.addEventListener('change', function() {
        const val = dimensionSelect.value;
        const selectedOption = dimensionSelect.options[dimensionSelect.selectedIndex];
        if (val === '') {
            hiddenDisplayScreenId.value = '';
            hiddenWidth.value = '';
            hiddenHeight.value = '';
            preview.textContent = '—';
            updateImagePreview(null, null);
        } else {
            hiddenDisplayScreenId.value = val;
            hiddenWidth.value = selectedOption.dataset.width;
            hiddenHeight.value = selectedOption.dataset.height;
            preview.textContent = selectedOption.dataset.dimension + ' px';
            updateImagePreview(selectedOption.dataset.width, selectedOption.dataset.height);
        }
    });
});
</script>
@endsection

