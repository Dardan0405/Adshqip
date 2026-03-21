@extends('layouts.admin')

@section('title', 'Demo — ' . $ad['name'])

@section('content')
{{-- Page Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-1">
            <a href="{{ route('admin.adformats') }}" class="hover:text-gray-600 transition-colors">Ad Formats</a>
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            <span class="text-gray-600">#{{ $ad['id'] }} — Demo</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $ad['name'] }}</h1>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.adformats') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Back
        </a>
        <a href="{{ route('admin.adformats.edit', $ad['id']) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm transition-colors">
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

                @elseif($ad['ad_type'] === 'text')
                    <div class="w-full max-w-md rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        @if($ad['headline'])
                            <p class="text-base font-semibold text-blue-700 mb-1">{{ $ad['headline'] }}</p>
                        @endif
                        @if($ad['display_url'])
                            <p class="text-xs text-emerald-700 mb-2">{{ $ad['display_url'] }}</p>
                        @endif
                        @if($ad['body_text'])
                            <p class="text-sm text-gray-600">{{ $ad['body_text'] }}</p>
                        @endif
                        @if($ad['call_to_action'])
                            <span class="inline-block mt-3 px-4 py-1.5 rounded-lg bg-brand-600 text-white text-xs font-semibold">{{ $ad['call_to_action'] }}</span>
                        @endif
                    </div>

                @elseif($ad['ad_type'] === 'video')
                    <div class="rounded-lg overflow-hidden shadow-sm"
                        @if($ad['width'] && $ad['height'])
                            style="width: {{ $ad['width'] }}px; max-width: 100%; height: {{ $ad['height'] }}px;"
                        @else
                            style="width: 480px; max-width: 100%; height: 270px;"
                        @endif
                    >
                        <div class="flex flex-col items-center justify-center w-full h-full bg-gray-900 relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-purple-900/30 to-gray-900"></div>
                            <div class="relative flex flex-col items-center">
                                <div class="w-16 h-16 rounded-full bg-white/10 backdrop-blur flex items-center justify-center mb-3 border border-white/20 cursor-pointer hover:bg-white/20 transition-colors">
                                    <svg class="w-7 h-7 text-white ml-0.5" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                                </div>
                                <p class="text-sm font-medium text-white/70">{{ $ad['name'] }}</p>
                            </div>
                        </div>
                    </div>

                @elseif($ad['ad_type'] === 'html' || $ad['ad_type'] === 'rich_media')
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
                    @if($ad['file_type'])
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
                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Status</p>
                    @php
                        $statusColors = [
                            'active' => 'bg-emerald-100 text-emerald-700',
                            'paused' => 'bg-amber-100 text-amber-700',
                            'pending_review' => 'bg-blue-100 text-blue-700',
                            'rejected' => 'bg-red-100 text-red-700',
                            'archived' => 'bg-gray-100 text-gray-600',
                        ];
                        $sColor = $statusColors[$ad['status']] ?? 'bg-gray-100 text-gray-600';
                    @endphp
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $sColor }}">{{ ucfirst(str_replace('_', ' ', $ad['status'])) }}</span>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Weight</p>
                    <div class="flex items-center gap-2">
                        <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                            @php $w = $ad['weight']; @endphp
                            <div class="h-full rounded-full {{ $w >= 7 ? 'bg-emerald-500' : ($w >= 4 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $w * 10 }}%"></div>
                        </div>
                        <span class="text-sm font-bold tabular-nums {{ $w >= 7 ? 'text-emerald-600' : ($w >= 4 ? 'text-amber-600' : 'text-red-600') }}">{{ $w }}/10</span>
                    </div>
                </div>
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

        {{-- Branding (if native/text) --}}
        @if($ad['headline'] || $ad['body_text'] || $ad['brand_name'] || $ad['call_to_action'])
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
                            <p class="text-xs text-gray-600 leading-relaxed">{{ $ad['body_text'] }}</p>
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
