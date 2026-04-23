@extends('layouts.advertiser')

@section('title', 'Demo — ' . $ad['name'])

@section('content')
{{-- Page Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-1">
            <a href="{{ route('advertiser.adformats') }}" class="hover:text-gray-600 transition-colors">Ad Formats</a>
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            <span class="text-gray-600">#{{ $ad['id'] }} — Demo</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $ad['name'] }}</h1>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('advertiser.adformats') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Back
        </a>
        <a href="{{ route('advertiser.adformats.edit', $ad['id']) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm transition-colors">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Edit
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- AD PREVIEW (main area)                                 --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-brand-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-brand-600" viewBox="0 0 24 24" fill="none"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Creative Preview</h2>
                        @if($ad['width'] && $ad['height'])
                            <p class="text-xs text-gray-400 mt-0.5">{{ $ad['width'] }}x{{ $ad['height'] }} px</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Ad render area --}}
            <div class="p-6 bg-gray-50 flex items-center justify-center min-h-[200px]">
                @if($ad['ad_type'] === 'image' || $ad['ad_type'] === 'vast')
                    @if($ad['file_path'])
                        <a href="{{ $ad['destination_url'] }}" target="_blank" rel="noopener noreferrer"
                            class="block rounded-lg overflow-hidden border border-gray-200 shadow-sm"
                            @if($ad['width'] && $ad['height'])
                                style="width: {{ $ad['width'] }}px; max-width: 100%; height: {{ $ad['height'] }}px;"
                            @endif
                        >
                            <img src="{{ asset($ad['file_path']) }}" alt="{{ $ad['alt_text'] ?? $ad['name'] }}"
                                class="w-full h-full object-contain"
                            >
                        </a>
                    @else
                        <div class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-white p-8 text-center"
                            @if($ad['width'] && $ad['height'])
                                style="width: {{ $ad['width'] }}px; max-width: 100%; height: {{ $ad['height'] }}px;"
                            @endif
                        >
                            <svg class="w-12 h-12 text-gray-300 mb-3" viewBox="0 0 24 24" fill="none"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <p class="text-sm font-medium text-gray-400">No image file</p>
                            @if($ad['width'] && $ad['height'])
                                <p class="text-xs text-gray-300 mt-1">{{ $ad['width'] }}x{{ $ad['height'] }} px</p>
                            @endif
                        </div>
                    @endif

                @elseif($ad['ad_type'] === 'native')
                    <div class="w-full max-w-md rounded-lg border border-gray-200 bg-white overflow-hidden shadow-sm">
                        @if($ad['file_path'])
                            <img src="{{ asset($ad['file_path']) }}" alt="{{ $ad['alt_text'] ?? $ad['name'] }}" class="w-full h-48 object-cover">
                        @endif
                        <div class="p-4">
                            @if($ad['brand_name'])
                                <div class="flex items-center gap-1.5 mb-2">
                                    <div class="w-5 h-5 rounded-full bg-brand-100 flex items-center justify-center">
                                        <span class="text-[8px] font-bold text-brand-600">{{ strtoupper(substr($ad['brand_name'], 0, 1)) }}</span>
                                    </div>
                                    <span class="text-[10px] font-medium text-gray-500">{{ $ad['brand_name'] }}</span>
                                    <span class="text-[9px] text-gray-400 ml-1">Sponsored</span>
                                </div>
                            @endif
                            @if($ad['headline'])
                                <h4 class="text-sm font-semibold text-gray-900 mb-1">{{ $ad['headline'] }}</h4>
                            @endif
                            @if($ad['body_text'])
                                <p class="text-xs text-gray-500 mb-3">{{ $ad['body_text'] }}</p>
                            @endif
                            @if($ad['call_to_action'])
                                <span class="inline-block px-4 py-1.5 rounded-lg bg-brand-600 text-white text-xs font-semibold">{{ $ad['call_to_action'] }}</span>
                            @endif
                        </div>
                    </div>

                @elseif(($ad['ad_format'] ?? null) === 'social_bar')
                    {{-- Social Bar Preview (compact, top-right style) --}}
                    <div class="relative" style="width:360px;max-width:100%;">
                        <div class="relative flex items-center gap-2.5 px-3.5 py-2.5 text-white rounded-xl" style="background:linear-gradient(135deg,#1a1a2e 0%,#16213e 100%);box-shadow:0 4px 24px rgba(0,0,0,.25);">
                            <span class="absolute bottom-1 right-2.5 text-[8px] uppercase tracking-wider text-white/30">Ad</span>
                            <button class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-white/10 flex items-center justify-center text-white/50 text-[10px] border border-white/10">&times;</button>
                            @if($ad['file_path'])
                                <img src="{{ asset($ad['file_path']) }}" class="w-8 h-8 rounded-md object-cover flex-shrink-0">
                            @else
                                <div class="w-8 h-8 rounded-md bg-blue-500 flex items-center justify-center flex-shrink-0 text-white font-bold text-xs">Ad</div>
                            @endif
                            <div class="flex-1 min-w-0">
                                @if($ad['headline'])
                                    <p class="text-xs font-semibold truncate">{{ $ad['headline'] }}</p>
                                @endif
                                @if($ad['body_text'])
                                    <p class="text-[10px] text-white/60 truncate">{{ $ad['body_text'] }}</p>
                                @endif
                            </div>
                            <span class="px-3.5 py-1.5 bg-blue-500 rounded-md text-[11px] font-semibold flex-shrink-0">{{ $ad['call_to_action'] ?? 'Learn More' }}</span>
                        </div>
                    </div>

                @elseif($ad['ad_type'] === 'text')
                    <div class="w-full max-w-md rounded-lg border border-gray-200 bg-white p-5 shadow-sm hover:border-blue-400 hover:shadow transition-all cursor-pointer">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Sponsored</p>
                        @if($ad['headline'])
                            <p class="text-base font-semibold text-blue-700 mb-1 hover:underline">{{ $ad['headline'] }}</p>
                        @endif
                        @if($ad['display_url'])
                            <p class="text-xs text-emerald-700 mb-2">{{ $ad['display_url'] }}</p>
                        @endif
                        @if($ad['body_text'])
                            <p class="text-sm text-gray-600 leading-relaxed">{!! nl2br(e($ad['body_text'])) !!}</p>
                        @endif
                        @if($ad['call_to_action'])
                            <span class="inline-block mt-3 px-4 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-semibold">{{ $ad['call_to_action'] }}</span>
                        @else
                            <span class="inline-block mt-3 px-4 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-semibold">Learn More</span>
                        @endif
                    </div>

                @elseif(($ad['ad_format'] ?? null) === 'outstream')
                    {{-- Out-Stream Video Preview (inline within content) --}}
                    <div class="w-full max-w-lg">
                        {{-- Simulated article content above --}}
                        <div class="bg-white rounded-t-lg border border-b-0 border-gray-200 p-5">
                            <div class="h-3 bg-gray-200 rounded w-3/4 mb-2.5"></div>
                            <div class="h-2.5 bg-gray-100 rounded w-full mb-1.5"></div>
                            <div class="h-2.5 bg-gray-100 rounded w-5/6 mb-1.5"></div>
                            <div class="h-2.5 bg-gray-100 rounded w-2/3"></div>
                        </div>
                        {{-- Out-stream video player (inline between content) --}}
                        <div class="relative bg-gray-900 border-x border-gray-200">
                            <span class="absolute top-2 left-2 px-2 py-0.5 bg-black/60 text-gray-400 rounded text-[9px] uppercase tracking-wider z-10">Ad</span>
                            <div class="flex flex-col items-center justify-center py-12 relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-indigo-900/30 to-gray-900"></div>
                                <div class="relative flex flex-col items-center">
                                    <div class="w-14 h-14 rounded-full bg-white/10 backdrop-blur flex items-center justify-center mb-3 border border-white/20 cursor-pointer hover:bg-white/20 transition-colors">
                                        <svg class="w-6 h-6 text-white ml-0.5" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                                    </div>
                                    <p class="text-sm font-medium text-white/70">{{ $ad['headline'] ?? $ad['name'] }}</p>
                                    <p class="text-[10px] text-white/40 mt-1">Auto-plays when scrolled into view</p>
                                </div>
                            </div>
                            {{-- CTA button overlay --}}
                            <div class="absolute bottom-3 right-3 z-10">
                                <span class="inline-block px-4 py-1.5 bg-blue-500/90 text-white text-xs font-semibold rounded-md">{{ $ad['call_to_action'] ?? 'Learn More' }}</span>
                            </div>
                            @if($ad['headline'])
                                <div class="absolute bottom-3 left-3 z-10">
                                    <span class="inline-block px-2.5 py-1 bg-black/70 text-white text-xs font-semibold rounded max-w-[60%] truncate">{{ $ad['headline'] }}</span>
                                </div>
                            @endif
                        </div>
                        {{-- Simulated article content below --}}
                        <div class="bg-white rounded-b-lg border border-t-0 border-gray-200 p-5">
                            <div class="h-2.5 bg-gray-100 rounded w-full mb-1.5"></div>
                            <div class="h-2.5 bg-gray-100 rounded w-4/5 mb-1.5"></div>
                            <div class="h-2.5 bg-gray-100 rounded w-5/6"></div>
                        </div>
                        <div class="mt-2 flex items-center gap-1.5 text-[10px] text-gray-400 justify-center">
                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            Plays inline within page content — pauses when scrolled out of view
                        </div>
                    </div>

                @elseif(($ad['ad_format'] ?? null) === 'rewarded')
                    {{-- Rewarded Video Preview --}}
                    <div class="w-full max-w-sm">
                        <div class="rounded-xl overflow-hidden shadow-lg relative bg-gray-900">
                            <div class="absolute inset-0 bg-gradient-to-br from-amber-900/20 to-gray-900"></div>
                            {{-- Ad label --}}
                            <span class="absolute top-2 left-2 px-2 py-0.5 bg-black/60 text-gray-400 rounded text-[9px] uppercase tracking-wider z-10">Rewarded Ad</span>
                            {{-- Reward badge --}}
                            <div class="absolute top-2 right-2 z-10 flex items-center gap-1.5 px-2.5 py-1 bg-amber-500/90 rounded-full">
                                <svg class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                <span class="text-[11px] font-bold text-white">
                                    {{ $ad['body_text'] ? $ad['body_text'] . ' ' : '' }}{{ $ad['sponsored_label'] ?? 'Reward' }}
                                </span>
                            </div>
                            <div class="relative flex flex-col items-center py-16">
                                <div class="w-16 h-16 rounded-full bg-white/10 backdrop-blur flex items-center justify-center mb-3 border border-white/20 cursor-pointer hover:bg-white/20 transition-colors">
                                    <svg class="w-7 h-7 text-white ml-0.5" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                                </div>
                                <p class="text-sm font-medium text-white/70 mb-1">{{ $ad['headline'] ?? $ad['name'] }}</p>
                                <p class="text-[11px] text-amber-400/80">Watch to earn your reward</p>
                            </div>
                            {{-- Progress bar (simulated) --}}
                            <div class="absolute bottom-10 left-4 right-4 z-10">
                                <div class="h-1 bg-white/20 rounded-full overflow-hidden">
                                    <div class="h-full bg-amber-400 rounded-full" style="width:35%"></div>
                                </div>
                                <div class="flex items-center justify-between mt-1">
                                    <span class="text-[9px] text-white/40">0:05</span>
                                    <span class="text-[9px] text-white/40">0:15</span>
                                </div>
                            </div>
                            {{-- CTA (disabled until complete) --}}
                            <div class="absolute bottom-3 right-3 z-10">
                                <span class="inline-block px-4 py-1.5 bg-amber-500/50 text-white/60 text-xs font-semibold rounded-md cursor-not-allowed">{{ $ad['call_to_action'] ?? 'Claim Reward' }}</span>
                            </div>
                        </div>
                        <div class="mt-2 flex items-center gap-1.5 text-[10px] text-gray-400 justify-center">
                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            User must watch full video to unlock reward — no skip
                        </div>
                    </div>

                @elseif($ad['ad_type'] === 'video')
                    {{-- In-Stream Video Preview --}}
                    <div class="rounded-lg overflow-hidden shadow-sm"
                        @if($ad['width'] && $ad['height'])
                            style="width: {{ $ad['width'] }}px; max-width: 100%; height: {{ $ad['height'] }}px;"
                        @else
                            style="width: 480px; max-width: 100%; height: 270px;"
                        @endif
                    >
                        <div class="flex flex-col items-center justify-center w-full h-full bg-gray-900 relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-purple-900/30 to-gray-900"></div>
                            {{-- Ad label --}}
                            <span class="absolute top-2 left-2 px-2 py-0.5 bg-black/60 text-gray-400 rounded text-[9px] uppercase tracking-wider z-10">Ad</span>
                            <div class="relative flex flex-col items-center">
                                <div class="w-16 h-16 rounded-full bg-white/10 backdrop-blur flex items-center justify-center mb-3 border border-white/20 cursor-pointer hover:bg-white/20 transition-colors">
                                    <svg class="w-7 h-7 text-white ml-0.5" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                                </div>
                                <p class="text-sm font-medium text-white/70">{{ $ad['headline'] ?? $ad['name'] }}</p>
                            </div>
                            {{-- CTA button overlay --}}
                            <div class="absolute bottom-3 right-3 z-10">
                                <span class="inline-block px-4 py-1.5 bg-blue-500/90 text-white text-xs font-semibold rounded-md">{{ $ad['call_to_action'] ?? 'Learn More' }}</span>
                            </div>
                            {{-- Headline overlay (bottom-left) --}}
                            @if($ad['headline'])
                                <div class="absolute bottom-3 left-3 z-10">
                                    <span class="inline-block px-2.5 py-1 bg-black/70 text-white text-xs font-semibold rounded max-w-[60%] truncate">{{ $ad['headline'] }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                @elseif(($ad['ad_format'] ?? null) === 'direct_link')
                    {{-- Direct Link Preview --}}
                    <div class="w-full max-w-md">
                        <div class="rounded-xl border border-gray-200 bg-white overflow-hidden shadow-sm">
                            <div class="bg-gradient-to-r from-brand-600 to-brand-700 px-5 py-3 flex items-center gap-2">
                                <svg class="w-4 h-4 text-white/80" viewBox="0 0 24 24" fill="none"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M10.172 13.828a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                <span class="text-xs font-semibold text-white">Direct Link</span>
                            </div>
                            <div class="p-5">
                                <p class="text-sm font-semibold text-gray-900 mb-2">{{ $ad['name'] }}</p>
                                <p class="text-xs text-gray-400 mb-1">Visitors are redirected instantly to:</p>
                                <div class="flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2 border border-gray-100">
                                    <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    <span class="text-xs text-brand-600 font-mono truncate">{{ $ad['destination_url'] }}</span>
                                </div>
                                <div class="mt-3 flex items-center gap-1.5 text-[10px] text-gray-400">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    No visual ad — immediate redirect on page load
                                </div>
                            </div>
                        </div>
                    </div>

                @elseif(($ad['ad_format'] ?? null) === 'popunder')
                    {{-- Popunder Preview --}}
                    <div class="w-full max-w-md">
                        <div class="rounded-xl border border-gray-200 bg-white overflow-hidden shadow-sm">
                            <div class="bg-gradient-to-r from-pink-600 to-rose-600 px-5 py-3 flex items-center gap-2">
                                <svg class="w-4 h-4 text-white/80" viewBox="0 0 24 24" fill="none"><path d="M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M17 17l3 3m-3 0l3-3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                <span class="text-xs font-semibold text-white">Popunder</span>
                            </div>
                            <div class="p-5">
                                <p class="text-sm font-semibold text-gray-900 mb-2">{{ $ad['headline'] ?? $ad['name'] }}</p>
                                @if($ad['body_text'])
                                    <p class="text-xs text-gray-500 mb-3">{!! nl2br(e($ad['body_text'])) !!}</p>
                                @endif
                                <div class="flex items-start gap-3 bg-gray-50 rounded-lg p-3 border border-gray-100">
                                    <div class="w-10 h-10 rounded-lg bg-pink-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg class="w-5 h-5 text-pink-600" viewBox="0 0 24 24" fill="none"><path d="M15 3h6v6M14 10l6.1-6.1M10 14l-6.1 6.1M9 21H3v-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-gray-500 mb-1">Opens in a background tab when the user clicks anywhere on the page:</p>
                                        <span class="text-xs text-brand-600 font-mono truncate block">{{ $ad['destination_url'] }}</span>
                                    </div>
                                </div>
                                <div class="mt-3 flex items-center gap-1.5 text-[10px] text-gray-400">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    Triggered on first click — opens behind the current window
                                </div>
                            </div>
                        </div>
                    </div>

                @elseif(($ad['ad_format'] ?? null) === 'interstitial')
                    {{-- Interstitial Preview --}}
                    <div class="w-full max-w-sm">
                        <div class="rounded-xl overflow-hidden shadow-lg relative" style="{{ $ad['file_path'] ? 'background:url(' . asset($ad['file_path']) . ') center/cover no-repeat' : 'background:rgba(0,0,0,.85)' }}">
                            @if($ad['file_path'])
                                <div class="absolute inset-0 bg-black/60 rounded-xl"></div>
                            @endif
                            <div class="relative p-8 text-center text-white">
                                <p class="text-[10px] uppercase tracking-wider text-white/40 mb-4">Fullscreen Overlay</p>
                                <h3 class="text-lg font-bold mb-2">{{ $ad['headline'] ?? $ad['name'] }}</h3>
                                <p class="text-xs text-white/60 mb-5">{!! nl2br(e($ad['body_text'] ?? 'You will be redirected shortly...')) !!}</p>
                                <span class="inline-block px-6 py-2.5 bg-blue-500 rounded-lg text-sm font-semibold hover:bg-blue-600 transition-colors cursor-pointer">{{ $ad['call_to_action'] ?? 'Continue' }}</span>
                                <div class="mt-3 text-[10px] text-white/30">Closing in <span class="text-white/50">5</span>s</div>
                                <p class="mt-2 text-[10px] text-white/40 underline cursor-pointer">Skip Ad</p>
                            </div>
                        </div>
                    </div>

                @elseif(($ad['ad_format'] ?? null) === 'in_page_push')
                    {{-- In-Page Push Preview --}}
                    <div class="relative" style="width:340px;max-width:100%;">
                        <div class="bg-white rounded-xl shadow-lg p-3.5 flex gap-3 items-start border border-gray-100">
                            <button class="absolute top-2 right-2.5 w-5 h-5 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 text-[10px]">&times;</button>
                            @if($ad['file_path'])
                                <img src="{{ asset($ad['file_path']) }}" class="w-12 h-12 rounded-lg object-cover flex-shrink-0">
                            @else
                                <div class="w-12 h-12 rounded-lg bg-brand-100 flex items-center justify-center flex-shrink-0">
                                    <span class="text-sm font-bold text-brand-600">Ad</span>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-[9px] uppercase tracking-wider text-gray-400 mb-0.5">Sponsored</p>
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $ad['headline'] ?? $ad['name'] }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $ad['body_text'] ?? 'Click to learn more' }}</p>
                            </div>
                        </div>
                        <div class="mt-2 flex items-center gap-1.5 text-[10px] text-gray-400 justify-center">
                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            Slides in from top-right corner
                        </div>
                    </div>

                @elseif($ad['ad_type'] === 'html' || $ad['ad_type'] === 'rich_media')
                    {{-- Generic HTML5 / Rich Media --}}
                    <div class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-amber-300 bg-amber-50 p-8 text-center"
                        @if($ad['width'] && $ad['height'])
                            style="width: {{ $ad['width'] }}px; max-width: 100%; height: {{ $ad['height'] }}px;"
                        @else
                            style="width: 300px; max-width: 100%; height: 250px;"
                        @endif
                    >
                        <div class="w-14 h-14 rounded-xl bg-amber-100 flex items-center justify-center mb-3">
                            <svg class="w-7 h-7 text-amber-600" viewBox="0 0 24 24" fill="none"><path d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <p class="text-sm font-semibold text-gray-700 mb-0.5">{{ $ad['name'] }}</p>
                        <p class="text-xs text-amber-600">HTML5 / Rich Media</p>
                    </div>

                @else
                    <div class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-white p-8 text-center">
                        <svg class="w-12 h-12 text-gray-300 mb-3" viewBox="0 0 24 24" fill="none"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        <p class="text-sm font-medium text-gray-400">{{ $ad['name'] }}</p>
                        <p class="text-xs text-gray-300 mt-0.5">{{ ucfirst(str_replace('_', ' ', $ad['ad_type'])) }} Creative</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- DETAILS SIDEBAR                                        --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="space-y-4">
        {{-- Creative Info --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-3.5 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Details</h3>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Creative Name</p>
                    <p class="text-sm font-medium text-gray-900">{{ $ad['name'] }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Campaign</p>
                    <p class="text-sm text-gray-700">{{ $ad['campaign_name'] }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Creative Type</p>
                    @php
                        $typeColors = [
                            'image' => 'bg-emerald-100 text-emerald-700',
                            'video' => 'bg-purple-100 text-purple-700',
                            'html' => 'bg-amber-100 text-amber-700',
                            'text' => 'bg-blue-100 text-blue-700',
                            'native' => 'bg-indigo-100 text-indigo-700',
                            'rich_media' => 'bg-pink-100 text-pink-700',
                            'vast' => 'bg-cyan-100 text-cyan-700',
                        ];
                        $tColor = $typeColors[$ad['ad_type']] ?? 'bg-gray-100 text-gray-700';
                    @endphp
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $tColor }}">{{ str_replace('_', ' ', $ad['ad_type']) }}</span>
                    @if($ad['ad_format'] ?? null)
                        <span class="text-xs text-gray-400 ml-1">({{ ucfirst(str_replace('_', ' ', $ad['ad_format'])) }})</span>
                    @elseif($ad['file_type'])
                        <span class="text-xs text-gray-400 ml-1">({{ $ad['file_type'] }})</span>
                    @endif
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Dimensions</p>
                    <p class="text-sm font-mono text-gray-700">
                        @if($ad['width'] && $ad['height'])
                            {{ $ad['width'] }}x{{ $ad['height'] }} px
                        @else
                            —
                        @endif
                    </p>
                </div>
                @if($ad['file_size_kb'])
                    <div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">File Size</p>
                        <p class="text-sm text-gray-700">{{ $ad['file_size_kb'] }} KB</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Links --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-3.5 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Links</h3>
            </div>
            <div class="p-5 space-y-3">
                @if($ad['destination_url'])
                    <div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Destination URL</p>
                        <a href="{{ $ad['destination_url'] }}" target="_blank" rel="noopener noreferrer" class="text-xs text-brand-600 hover:text-brand-700 hover:underline break-all">{{ $ad['destination_url'] }}</a>
                    </div>
                @endif
                @if($ad['display_url'])
                    <div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Display URL</p>
                        <p class="text-xs text-gray-600">{{ $ad['display_url'] }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ═══ GET AD CODE ═══ --}}
        <div class="bg-white rounded-xl border border-gray-200" x-data="{ copied: null }">
            <div class="px-5 py-3.5 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-600" viewBox="0 0 24 24" fill="none"><path d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <h3 class="text-sm font-semibold text-gray-900">Get Ad Code</h3>
                </div>
                <p class="text-[10px] text-gray-400 mt-0.5">Copy and paste into your site</p>
            </div>
            <div class="p-5 space-y-4">
                @if(($ad['ad_format'] ?? null) === 'social_bar')
                {{-- ═══ SOCIAL BAR SPECIFIC CODES ═══ --}}

                {{-- Social Bar iframe embed --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Social Bar — iframe Embed</p>
                        <button type="button"
                            @click="navigator.clipboard.writeText($refs.iframeCode.textContent.trim()); copied = 'iframe'; setTimeout(() => copied = null, 2000)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold transition-colors"
                            :class="copied === 'iframe' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                            <svg x-show="copied !== 'iframe'" class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <svg x-show="copied === 'iframe'" class="w-3 h-3" viewBox="0 0 24 24" fill="none" style="display:none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span x-text="copied === 'iframe' ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-3 overflow-x-auto">
                        <code class="text-[11px] text-emerald-400 font-mono whitespace-pre-wrap break-all" x-ref="iframeCode">&lt;iframe src="{{ route('ad.serve', $ad['id']) }}" width="380" height="70" frameborder="0" scrolling="no" style="border:0;overflow:visible;position:fixed;top:12px;right:12px;z-index:99999;background:transparent;"&gt;&lt;/iframe&gt;</code>
                    </div>
                </div>

                {{-- Social Bar JavaScript tag --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Social Bar — JavaScript Tag</p>
                        <button type="button"
                            @click="navigator.clipboard.writeText($refs.jsCode.textContent.trim()); copied = 'js'; setTimeout(() => copied = null, 2000)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold transition-colors"
                            :class="copied === 'js' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                            <svg x-show="copied !== 'js'" class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <svg x-show="copied === 'js'" class="w-3 h-3" viewBox="0 0 24 24" fill="none" style="display:none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span x-text="copied === 'js' ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-3 overflow-x-auto">
                        <code class="text-[11px] text-amber-400 font-mono whitespace-pre-wrap break-all" x-ref="jsCode">&lt;script&gt;
(function(){
  var d=document,i=d.createElement('iframe');
  i.src='{{ route('ad.serve', $ad['id']) }}';
  i.width='380';i.height='70';
  i.frameBorder='0';i.scrolling='no';
  i.style.border='0';i.style.overflow='visible';
  i.style.position='fixed';
  i.style.top='12px';
  i.style.right='12px';
  i.style.zIndex='99999';
  i.style.background='transparent';
  d.currentScript.parentNode.insertBefore(i,d.currentScript);
})();
&lt;/script&gt;</code>
                    </div>
                </div>

                @elseif(($ad['ad_format'] ?? null) === 'popunder')
                {{-- ═══ POPUNDER AD SPECIFIC CODES ═══ --}}

                {{-- Popunder — JavaScript Tag --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Popunder — JavaScript Tag</p>
                        <button type="button"
                            @click="navigator.clipboard.writeText($refs.iframeCode.textContent.trim()); copied = 'iframe'; setTimeout(() => copied = null, 2000)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold transition-colors"
                            :class="copied === 'iframe' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                            <svg x-show="copied !== 'iframe'" class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <svg x-show="copied === 'iframe'" class="w-3 h-3" viewBox="0 0 24 24" fill="none" style="display:none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span x-text="copied === 'iframe' ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-3 overflow-x-auto">
                        <code class="text-[11px] text-emerald-400 font-mono whitespace-pre-wrap break-all" x-ref="iframeCode">&lt;script&gt;
(function(){
  var fired=false;
  document.addEventListener('click',function(){
    if(fired)return;fired=true;
    window.open('{{ route('ad.click', $ad['id']) }}','_blank');
  },{once:true});
})();
&lt;/script&gt;</code>
                    </div>
                </div>

                {{-- Popunder — iframe Embed (for testing) --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Popunder — iframe Embed (Testing)</p>
                        <button type="button"
                            @click="navigator.clipboard.writeText($refs.jsCode.textContent.trim()); copied = 'js'; setTimeout(() => copied = null, 2000)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold transition-colors"
                            :class="copied === 'js' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                            <svg x-show="copied !== 'js'" class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <svg x-show="copied === 'js'" class="w-3 h-3" viewBox="0 0 24 24" fill="none" style="display:none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span x-text="copied === 'js' ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-3 overflow-x-auto">
                        <code class="text-[11px] text-amber-400 font-mono whitespace-pre-wrap break-all" x-ref="jsCode">&lt;iframe src="{{ route('ad.serve', $ad['id']) }}" width="1" height="1" frameborder="0" scrolling="no" style="border:0;overflow:hidden;position:absolute;left:-9999px;"&gt;&lt;/iframe&gt;</code>
                    </div>
                </div>

                @elseif(($ad['ad_format'] ?? null) === 'interstitial')
                {{-- ═══ INTERSTITIAL AD SPECIFIC CODES ═══ --}}

                {{-- Interstitial Ad — JavaScript Overlay --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Interstitial — JavaScript Overlay</p>
                        <button type="button"
                            @click="navigator.clipboard.writeText($refs.iframeCode.textContent.trim()); copied = 'iframe'; setTimeout(() => copied = null, 2000)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold transition-colors"
                            :class="copied === 'iframe' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                            <svg x-show="copied !== 'iframe'" class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <svg x-show="copied === 'iframe'" class="w-3 h-3" viewBox="0 0 24 24" fill="none" style="display:none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span x-text="copied === 'iframe' ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-3 overflow-x-auto">
                        <code class="text-[11px] text-emerald-400 font-mono whitespace-pre-wrap break-all" x-ref="iframeCode">&lt;script&gt;
(function(){
  var overlay=document.createElement('div');
  overlay.style.cssText='position:fixed;top:0;left:0;width:100%;height:100%;z-index:999999;border:0;';
  var iframe=document.createElement('iframe');
  iframe.src='{{ route('ad.serve', $ad['id']) }}';
  iframe.style.cssText='width:100%;height:100%;border:0;';
  iframe.setAttribute('frameborder','0');
  iframe.setAttribute('allowfullscreen','true');
  overlay.appendChild(iframe);
  document.body.appendChild(overlay);
  setTimeout(function(){overlay.remove();},10000);
})();
&lt;/script&gt;</code>
                    </div>
                </div>

                {{-- Interstitial Ad — Direct iframe (for testing) --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Interstitial — iframe Embed (Testing)</p>
                        <button type="button"
                            @click="navigator.clipboard.writeText($refs.jsCode.textContent.trim()); copied = 'js'; setTimeout(() => copied = null, 2000)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold transition-colors"
                            :class="copied === 'js' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                            <svg x-show="copied !== 'js'" class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <svg x-show="copied === 'js'" class="w-3 h-3" viewBox="0 0 24 24" fill="none" style="display:none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span x-text="copied === 'js' ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-3 overflow-x-auto">
                        <code class="text-[11px] text-amber-400 font-mono whitespace-pre-wrap break-all" x-ref="jsCode">&lt;iframe src="{{ route('ad.serve', $ad['id']) }}" width="480" height="360" frameborder="0" scrolling="no" style="border:0;overflow:hidden;max-width:100%;"&gt;&lt;/iframe&gt;</code>
                    </div>
                </div>

                @elseif(($ad['ad_format'] ?? null) === 'in_page_push')
                {{-- ═══ IN-PAGE PUSH AD SPECIFIC CODES ═══ --}}

                {{-- In-Page Push — JavaScript Tag --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">In-Page Push — JavaScript Tag</p>
                        <button type="button"
                            @click="navigator.clipboard.writeText($refs.iframeCode.textContent.trim()); copied = 'iframe'; setTimeout(() => copied = null, 2000)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold transition-colors"
                            :class="copied === 'iframe' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                            <svg x-show="copied !== 'iframe'" class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <svg x-show="copied === 'iframe'" class="w-3 h-3" viewBox="0 0 24 24" fill="none" style="display:none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span x-text="copied === 'iframe' ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-3 overflow-x-auto">
                        <code class="text-[11px] text-emerald-400 font-mono whitespace-pre-wrap break-all" x-ref="iframeCode">&lt;script&gt;
(function(d){
  var i=d.createElement('iframe');
  i.src='{{ route('ad.serve', $ad['id']) }}';
  i.width='340';i.height='90';
  i.style.border='0';i.style.overflow='visible';
  i.style.position='fixed';
  i.style.top='12px';
  i.style.right='12px';
  i.style.zIndex='99999';
  i.style.background='transparent';
  d.currentScript.parentNode.insertBefore(i,d.currentScript);
})(document);
&lt;/script&gt;</code>
                    </div>
                </div>

                {{-- In-Page Push — iframe Embed (for testing) --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">In-Page Push — iframe Embed (Testing)</p>
                        <button type="button"
                            @click="navigator.clipboard.writeText($refs.jsCode.textContent.trim()); copied = 'js'; setTimeout(() => copied = null, 2000)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold transition-colors"
                            :class="copied === 'js' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                            <svg x-show="copied !== 'js'" class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <svg x-show="copied === 'js'" class="w-3 h-3" viewBox="0 0 24 24" fill="none" style="display:none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span x-text="copied === 'js' ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-3 overflow-x-auto">
                        <code class="text-[11px] text-amber-400 font-mono whitespace-pre-wrap break-all" x-ref="jsCode">&lt;iframe src="{{ route('ad.serve', $ad['id']) }}" width="340" height="90" frameborder="0" scrolling="no" style="border:0;overflow:visible;max-width:100%;"&gt;&lt;/iframe&gt;</code>
                    </div>
                </div>

                @elseif($ad['ad_type'] === 'native')
                {{-- ═══ NATIVE AD SPECIFIC CODES ═══ --}}

                {{-- Native Ad iframe embed --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Native Ad — iframe Embed</p>
                        <button type="button"
                            @click="navigator.clipboard.writeText($refs.iframeCode.textContent.trim()); copied = 'iframe'; setTimeout(() => copied = null, 2000)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold transition-colors"
                            :class="copied === 'iframe' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                            <svg x-show="copied !== 'iframe'" class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <svg x-show="copied === 'iframe'" class="w-3 h-3" viewBox="0 0 24 24" fill="none" style="display:none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span x-text="copied === 'iframe' ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-3 overflow-x-auto">
                        <code class="text-[11px] text-emerald-400 font-mono whitespace-pre-wrap break-all" x-ref="iframeCode">&lt;iframe src="{{ route('ad.serve', $ad['id']) }}" width="340" height="{{ $ad['file_path'] ? '320' : '180' }}" frameborder="0" scrolling="no" style="border:0;overflow:hidden;max-width:100%;"&gt;&lt;/iframe&gt;</code>
                    </div>
                </div>

                {{-- Native Ad JavaScript tag --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Native Ad — JavaScript Tag</p>
                        <button type="button"
                            @click="navigator.clipboard.writeText($refs.jsCode.textContent.trim()); copied = 'js'; setTimeout(() => copied = null, 2000)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold transition-colors"
                            :class="copied === 'js' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                            <svg x-show="copied !== 'js'" class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <svg x-show="copied === 'js'" class="w-3 h-3" viewBox="0 0 24 24" fill="none" style="display:none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span x-text="copied === 'js' ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-3 overflow-x-auto">
                        <code class="text-[11px] text-amber-400 font-mono whitespace-pre-wrap break-all" x-ref="jsCode">&lt;script&gt;
(function(){
  var d=document,i=d.createElement('iframe');
  i.src='{{ route('ad.serve', $ad['id']) }}';
  i.width='340';i.height='{{ $ad['file_path'] ? '320' : '180' }}';
  i.frameBorder='0';i.scrolling='no';
  i.style.border='0';i.style.overflow='hidden';
  i.style.maxWidth='100%';
  d.currentScript.parentNode.insertBefore(i,d.currentScript);
})();
&lt;/script&gt;</code>
                    </div>
                </div>

                @elseif(($ad['ad_format'] ?? null) === 'rewarded')
                {{-- ═══ REWARDED VIDEO AD SPECIFIC CODES ═══ --}}

                {{-- Rewarded Video — JavaScript Tag --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Rewarded Video — JavaScript Tag</p>
                        <button type="button"
                            @click="navigator.clipboard.writeText($refs.iframeCode.textContent.trim()); copied = 'iframe'; setTimeout(() => copied = null, 2000)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold transition-colors"
                            :class="copied === 'iframe' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                            <svg x-show="copied !== 'iframe'" class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <svg x-show="copied === 'iframe'" class="w-3 h-3" viewBox="0 0 24 24" fill="none" style="display:none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span x-text="copied === 'iframe' ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-3 overflow-x-auto">
                        <code class="text-[11px] text-emerald-400 font-mono whitespace-pre-wrap break-all" x-ref="iframeCode">&lt;script&gt;
(function(){
  var d=document,overlay=d.createElement('div');
  overlay.style.cssText='position:fixed;top:0;left:0;width:100%;height:100%;z-index:999999;background:rgba(0,0,0,.85);display:flex;align-items:center;justify-content:center;';
  var iframe=d.createElement('iframe');
  iframe.src='{{ route('ad.serve', $ad['id']) }}';
  iframe.width='{{ $ad['width'] ?? 480 }}';iframe.height='{{ $ad['height'] ?? 320 }}';
  iframe.frameBorder='0';iframe.scrolling='no';
  iframe.allow='autoplay; encrypted-media';
  iframe.allowFullscreen=true;
  iframe.style.cssText='border:0;overflow:hidden;max-width:90vw;max-height:80vh;border-radius:8px;';
  overlay.appendChild(iframe);
  d.body.appendChild(overlay);
  window.addEventListener('message',function(e){
    if(e.data==='aq-rewarded-complete'){overlay.remove();}
  });
})();
&lt;/script&gt;</code>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1.5 italic">Displays as a fullscreen overlay. The user must watch the entire video to receive the reward callback.</p>
                </div>

                {{-- Rewarded Video — iframe Embed (Testing) --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Rewarded Video — iframe Embed (Testing)</p>
                        <button type="button"
                            @click="navigator.clipboard.writeText($refs.jsCode.textContent.trim()); copied = 'js'; setTimeout(() => copied = null, 2000)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold transition-colors"
                            :class="copied === 'js' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                            <svg x-show="copied !== 'js'" class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <svg x-show="copied === 'js'" class="w-3 h-3" viewBox="0 0 24 24" fill="none" style="display:none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span x-text="copied === 'js' ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-3 overflow-x-auto">
                        <code class="text-[11px] text-amber-400 font-mono whitespace-pre-wrap break-all" x-ref="jsCode">&lt;iframe src="{{ route('ad.serve', $ad['id']) }}" width="{{ $ad['width'] ?? 480 }}" height="{{ $ad['height'] ?? 320 }}" frameborder="0" scrolling="no" allow="autoplay; encrypted-media" allowfullscreen style="border:0;overflow:hidden;max-width:100%;"&gt;&lt;/iframe&gt;</code>
                    </div>
                </div>

                @elseif(($ad['ad_format'] ?? null) === 'outstream')
                {{-- ═══ OUT-STREAM VIDEO AD SPECIFIC CODES ═══ --}}

                {{-- Out-Stream — Inline JavaScript Tag --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Out-Stream — Inline JS Tag</p>
                        <button type="button"
                            @click="navigator.clipboard.writeText($refs.iframeCode.textContent.trim()); copied = 'iframe'; setTimeout(() => copied = null, 2000)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold transition-colors"
                            :class="copied === 'iframe' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                            <svg x-show="copied !== 'iframe'" class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <svg x-show="copied === 'iframe'" class="w-3 h-3" viewBox="0 0 24 24" fill="none" style="display:none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span x-text="copied === 'iframe' ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-3 overflow-x-auto">
                        <code class="text-[11px] text-emerald-400 font-mono whitespace-pre-wrap break-all" x-ref="iframeCode">&lt;!-- Place between content paragraphs --&gt;
&lt;div id="aq-outstream-{{ $ad['id'] }}" style="margin:20px 0;text-align:center;"&gt;
  &lt;iframe src="{{ route('ad.serve', $ad['id']) }}" width="{{ $ad['width'] ?? 640 }}" height="{{ $ad['height'] ?? 360 }}" frameborder="0" scrolling="no" allow="autoplay; encrypted-media" allowfullscreen style="border:0;overflow:hidden;max-width:100%;display:block;margin:0 auto;"&gt;&lt;/iframe&gt;
&lt;/div&gt;
&lt;script&gt;
(function(){
  var wrap=document.getElementById('aq-outstream-{{ $ad['id'] }}');
  var iframe=wrap.querySelector('iframe');
  var observer=new IntersectionObserver(function(entries){
    entries.forEach(function(e){
      iframe.contentWindow.postMessage(e.isIntersecting?'aq-play':'aq-pause','*');
    });
  },{threshold:0.5});
  observer.observe(wrap);
})();
&lt;/script&gt;</code>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1.5 italic">Place this code between content paragraphs. Video auto-plays when 50% visible and pauses when scrolled away.</p>
                </div>

                {{-- Out-Stream — iframe Embed (Testing) --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Out-Stream — iframe Embed (Testing)</p>
                        <button type="button"
                            @click="navigator.clipboard.writeText($refs.jsCode.textContent.trim()); copied = 'js'; setTimeout(() => copied = null, 2000)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold transition-colors"
                            :class="copied === 'js' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                            <svg x-show="copied !== 'js'" class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <svg x-show="copied === 'js'" class="w-3 h-3" viewBox="0 0 24 24" fill="none" style="display:none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span x-text="copied === 'js' ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-3 overflow-x-auto">
                        <code class="text-[11px] text-amber-400 font-mono whitespace-pre-wrap break-all" x-ref="jsCode">&lt;iframe src="{{ route('ad.serve', $ad['id']) }}" width="{{ $ad['width'] ?? 640 }}" height="{{ $ad['height'] ?? 360 }}" frameborder="0" scrolling="no" allow="autoplay; encrypted-media" allowfullscreen style="border:0;overflow:hidden;max-width:100%;display:block;margin:20px auto;"&gt;&lt;/iframe&gt;</code>
                    </div>
                </div>

                @elseif($ad['ad_type'] === 'video')
                {{-- ═══ VIDEO / IN-STREAM AD SPECIFIC CODES ═══ --}}

                {{-- Video Ad — iframe Embed --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Video Ad — iframe Embed</p>
                        <button type="button"
                            @click="navigator.clipboard.writeText($refs.iframeCode.textContent.trim()); copied = 'iframe'; setTimeout(() => copied = null, 2000)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold transition-colors"
                            :class="copied === 'iframe' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                            <svg x-show="copied !== 'iframe'" class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <svg x-show="copied === 'iframe'" class="w-3 h-3" viewBox="0 0 24 24" fill="none" style="display:none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span x-text="copied === 'iframe' ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-3 overflow-x-auto">
                        <code class="text-[11px] text-emerald-400 font-mono whitespace-pre-wrap break-all" x-ref="iframeCode">&lt;iframe src="{{ route('ad.serve', $ad['id']) }}" width="{{ $ad['width'] ?? 640 }}" height="{{ $ad['height'] ?? 360 }}" frameborder="0" scrolling="no" allow="autoplay; encrypted-media" allowfullscreen style="border:0;overflow:hidden;max-width:100%;"&gt;&lt;/iframe&gt;</code>
                    </div>
                </div>

                {{-- Video Ad — JavaScript Tag --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Video Ad — JavaScript Tag</p>
                        <button type="button"
                            @click="navigator.clipboard.writeText($refs.jsCode.textContent.trim()); copied = 'js'; setTimeout(() => copied = null, 2000)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold transition-colors"
                            :class="copied === 'js' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                            <svg x-show="copied !== 'js'" class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <svg x-show="copied === 'js'" class="w-3 h-3" viewBox="0 0 24 24" fill="none" style="display:none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span x-text="copied === 'js' ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-3 overflow-x-auto">
                        <code class="text-[11px] text-amber-400 font-mono whitespace-pre-wrap break-all" x-ref="jsCode">&lt;script&gt;
(function(){
  var d=document,i=d.createElement('iframe');
  i.src='{{ route('ad.serve', $ad['id']) }}';
  i.width='{{ $ad['width'] ?? 640 }}';i.height='{{ $ad['height'] ?? 360 }}';
  i.frameBorder='0';i.scrolling='no';
  i.allow='autoplay; encrypted-media';
  i.allowFullscreen=true;
  i.style.border='0';i.style.overflow='hidden';
  i.style.maxWidth='100%';
  d.currentScript.parentNode.insertBefore(i,d.currentScript);
})();
&lt;/script&gt;</code>
                    </div>
                </div>

                @elseif($ad['ad_type'] === 'text' && !in_array($ad['ad_format'] ?? '', ['social_bar']))
                {{-- ═══ TEXT AD SPECIFIC CODES ═══ --}}

                {{-- Text Ad iframe embed --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Text Ad — iframe Embed</p>
                        <button type="button"
                            @click="navigator.clipboard.writeText($refs.iframeCode.textContent.trim()); copied = 'iframe'; setTimeout(() => copied = null, 2000)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold transition-colors"
                            :class="copied === 'iframe' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                            <svg x-show="copied !== 'iframe'" class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <svg x-show="copied === 'iframe'" class="w-3 h-3" viewBox="0 0 24 24" fill="none" style="display:none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span x-text="copied === 'iframe' ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-3 overflow-x-auto">
                        <code class="text-[11px] text-emerald-400 font-mono whitespace-pre-wrap break-all" x-ref="iframeCode">&lt;iframe src="{{ route('ad.serve', $ad['id']) }}" width="400" height="130" frameborder="0" scrolling="no" style="border:0;overflow:hidden;max-width:100%;"&gt;&lt;/iframe&gt;</code>
                    </div>
                </div>

                {{-- Text Ad JavaScript tag --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Text Ad — JavaScript Tag</p>
                        <button type="button"
                            @click="navigator.clipboard.writeText($refs.jsCode.textContent.trim()); copied = 'js'; setTimeout(() => copied = null, 2000)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold transition-colors"
                            :class="copied === 'js' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                            <svg x-show="copied !== 'js'" class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <svg x-show="copied === 'js'" class="w-3 h-3" viewBox="0 0 24 24" fill="none" style="display:none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span x-text="copied === 'js' ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-3 overflow-x-auto">
                        <code class="text-[11px] text-amber-400 font-mono whitespace-pre-wrap break-all" x-ref="jsCode">&lt;script&gt;
(function(){
  var d=document,i=d.createElement('iframe');
  i.src='{{ route('ad.serve', $ad['id']) }}';
  i.width='400';i.height='130';
  i.frameBorder='0';i.scrolling='no';
  i.style.border='0';i.style.overflow='hidden';
  i.style.maxWidth='100%';
  d.currentScript.parentNode.insertBefore(i,d.currentScript);
})();
&lt;/script&gt;</code>
                    </div>
                </div>

                @else
                {{-- ═══ STANDARD AD CODES ═══ --}}

                {{-- iframe embed --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">iframe Embed</p>
                        <button type="button"
                            @click="navigator.clipboard.writeText($refs.iframeCode.textContent.trim()); copied = 'iframe'; setTimeout(() => copied = null, 2000)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold transition-colors"
                            :class="copied === 'iframe' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                            <svg x-show="copied !== 'iframe'" class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <svg x-show="copied === 'iframe'" class="w-3 h-3" viewBox="0 0 24 24" fill="none" style="display:none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span x-text="copied === 'iframe' ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-3 overflow-x-auto">
                        <code class="text-[11px] text-emerald-400 font-mono whitespace-pre-wrap break-all" x-ref="iframeCode">&lt;iframe src="{{ route('ad.serve', $ad['id']) }}" width="{{ $ad['width'] ?? 300 }}" height="{{ $ad['height'] ?? 250 }}" frameborder="0" scrolling="no" style="border:0;overflow:hidden;"&gt;&lt;/iframe&gt;</code>
                    </div>
                </div>

                {{-- JavaScript tag --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">JavaScript Tag</p>
                        <button type="button"
                            @click="navigator.clipboard.writeText($refs.jsCode.textContent.trim()); copied = 'js'; setTimeout(() => copied = null, 2000)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold transition-colors"
                            :class="copied === 'js' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                            <svg x-show="copied !== 'js'" class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <svg x-show="copied === 'js'" class="w-3 h-3" viewBox="0 0 24 24" fill="none" style="display:none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span x-text="copied === 'js' ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-3 overflow-x-auto">
                        <code class="text-[11px] text-amber-400 font-mono whitespace-pre-wrap break-all" x-ref="jsCode">&lt;script&gt;
(function(){
  var d=document,i=d.createElement('iframe');
  i.src='{{ route('ad.serve', $ad['id']) }}';
  i.width='{{ $ad['width'] ?? 300 }}';i.height='{{ $ad['height'] ?? 250 }}';
  i.frameBorder='0';i.scrolling='no';
  i.style.border='0';i.style.overflow='hidden';
  d.currentScript.parentNode.insertBefore(i,d.currentScript);
})();
&lt;/script&gt;</code>
                    </div>
                </div>
                @endif

                {{-- Direct link --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Direct Link</p>
                        <button type="button"
                            @click="navigator.clipboard.writeText($refs.directLink.textContent.trim()); copied = 'link'; setTimeout(() => copied = null, 2000)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold transition-colors"
                            :class="copied === 'link' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                            <svg x-show="copied !== 'link'" class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <svg x-show="copied === 'link'" class="w-3 h-3" viewBox="0 0 24 24" fill="none" style="display:none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span x-text="copied === 'link' ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-3 overflow-x-auto">
                        <code class="text-[11px] text-blue-400 font-mono whitespace-pre-wrap break-all" x-ref="directLink">{{ route('ad.serve', $ad['id']) }}</code>
                    </div>
                </div>

                {{-- Popunder script (for popunder/interstitial ads) --}}
                @if(in_array($ad['ad_type'], ['html', 'rich_media']) && !$ad['file_path'])
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Popunder Script</p>
                        <button type="button"
                            @click="navigator.clipboard.writeText($refs.popCode.textContent.trim()); copied = 'pop'; setTimeout(() => copied = null, 2000)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold transition-colors"
                            :class="copied === 'pop' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                            <svg x-show="copied !== 'pop'" class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <svg x-show="copied === 'pop'" class="w-3 h-3" viewBox="0 0 24 24" fill="none" style="display:none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span x-text="copied === 'pop' ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-3 overflow-x-auto">
                        <code class="text-[11px] text-pink-400 font-mono whitespace-pre-wrap break-all" x-ref="popCode">&lt;script&gt;
(function(){
  var fired=false;
  document.addEventListener('click',function(){
    if(fired)return;fired=true;
    window.open('{{ route('ad.click', $ad['id']) }}','_blank');
  },{once:true});
})();
&lt;/script&gt;</code>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Ad Copy (only for text/native ads — special formats show this info in their own preview) --}}
        @if(in_array($ad['ad_type'], ['text', 'native', 'video']) && !in_array($ad['ad_format'] ?? '', ['social_bar', 'in_page_push', 'interstitial', 'popunder', 'direct_link']) && ($ad['headline'] || $ad['body_text'] || $ad['brand_name'] || $ad['call_to_action']))
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-5 py-3.5 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Ad Copy</h3>
                </div>
                <div class="p-5 space-y-3">
                    @if($ad['headline'])
                        <div>
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Headline</p>
                            <p class="text-sm text-gray-900">{{ $ad['headline'] }}</p>
                        </div>
                    @endif
                    @if($ad['brand_name'])
                        <div>
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Brand</p>
                            <p class="text-sm text-gray-700">{{ $ad['brand_name'] }}</p>
                        </div>
                    @endif
                    @if($ad['body_text'])
                        <div>
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Body</p>
                            <p class="text-xs text-gray-600 leading-relaxed">{!! nl2br(e($ad['body_text'])) !!}</p>
                        </div>
                    @endif
                    @if($ad['call_to_action'])
                        <div>
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">CTA</p>
                            <span class="inline-block px-3 py-1 rounded-lg bg-brand-600 text-white text-xs font-semibold">{{ $ad['call_to_action'] }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

