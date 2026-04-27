@extends('layouts.publisher')

@section('title', 'Campaign AdMarket')

@section('content')
<div
    x-data="admarket()"
    x-init="init()"
    class="space-y-6"
>
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Campaign AdMarket</h1>
            <p class="text-sm text-gray-500 mt-0.5">Browse available campaigns and add them to your favorites.</p>
        </div>

        {{-- Site / App Selector --}}
        <div class="flex items-center gap-2">
            <label class="text-xs font-medium text-gray-500 whitespace-nowrap">Managing:</label>
            <select
                x-model="selectedProperty"
                class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white min-w-[220px]"
            >
                <option value="">— select a site or app —</option>
                @foreach($allSites as $site)
                    <option value="site_{{ $site->id }}">{{ $site->name }} ({{ $site->domain }})</option>
                @endforeach
                @foreach($mobileApps as $app)
                    <option value="app_{{ $app->id }}">{{ $app->app_name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 flex flex-wrap items-center gap-3">
        {{-- All / Favorite toggle --}}
        <div class="flex rounded-lg border border-gray-200 overflow-hidden text-sm">
            <button
                @click="setFilter('all')"
                :class="filter === 'all' ? 'bg-brand-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                class="px-4 py-1.5 font-medium transition-colors"
            >All</button>
            <button
                @click="setFilter('favorite')"
                :class="filter === 'favorite' ? 'bg-brand-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                class="px-4 py-1.5 font-medium transition-colors border-l border-gray-200"
            >
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                    Favorites
                    <span x-text="favorites.length > 0 ? '(' + favorites.length + ')' : ''" class="text-xs"></span>
                </span>
            </button>
        </div>

        <div class="h-6 w-px bg-gray-200"></div>

        {{-- Country filter --}}
        <div class="flex items-center gap-1.5">
            <label class="text-xs text-gray-500 font-medium">Country</label>
            <select
                x-model="filterCountry"
                @change="loadCampaigns(1)"
                class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
            >
                <option value="">All Countries</option>
                @foreach($countries as $code)
                    <option value="{{ $code }}">{{ $code }}</option>
                @endforeach
            </select>
        </div>

        {{-- Category filter --}}
        <div class="flex items-center gap-1.5">
            <label class="text-xs text-gray-500 font-medium">Category</label>
            <select
                x-model="filterCategory"
                @change="loadCampaigns(1)"
                class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
            >
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Ad Format filter --}}
        <div class="flex items-center gap-1.5">
            <label class="text-xs text-gray-500 font-medium">Format</label>
            <select
                x-model="filterSize"
                @change="loadCampaigns(1)"
                class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 min-w-[180px]"
            >
                <option value="">All Formats</option>
                @foreach($adFormats as $groupKey => $group)
                    <optgroup label="{{ $group['label'] }}">
                        @foreach($group['sizes'] as $sizeKey => $sizeLabel)
                            <option value="{{ $sizeKey }}">{{ $sizeLabel }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>

        <div class="ml-auto text-xs text-gray-400" x-text="meta.total + ' campaigns'"></div>
    </div>

    {{-- Error banner --}}
    <div x-show="loadError" class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
        <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <span x-text="loadError"></span>
        <button @click="loadCampaigns(1)" class="ml-auto text-xs font-medium underline hover:no-underline">Retry</button>
    </div>

    {{-- Campaign Grid --}}
    <div>
        {{-- Loading skeleton --}}
        <div x-show="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <template x-for="i in 8" :key="i">
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden animate-pulse">
                    <div class="bg-gray-200 h-40"></div>
                    <div class="p-3 space-y-2">
                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                        <div class="h-3 bg-gray-100 rounded w-1/2"></div>
                        <div class="h-3 bg-gray-100 rounded w-1/3"></div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Empty state --}}
        <div x-show="!loading && campaigns.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
            <svg class="w-16 h-16 text-gray-200 mb-4" viewBox="0 0 24 24" fill="none"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <p class="text-gray-500 font-medium">No campaigns found</p>
            <p class="text-gray-400 text-sm mt-1">Try adjusting your filters.</p>
        </div>

        {{-- Campaign cards --}}
        <div x-show="!loading && campaigns.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <template x-for="campaign in campaigns" :key="campaign.id">
                <div
                    class="bg-white border rounded-xl overflow-hidden cursor-pointer transition-all duration-150 hover:shadow-md hover:-translate-y-0.5 group"
                    :class="campaign.is_favorite ? 'border-brand-300 ring-1 ring-brand-200' : 'border-gray-200'"
                    @click="openCampaignModal(campaign)"
                    draggable="true"
                    @dragstart="handleDragStart($event, campaign)"
                >
                    {{-- Image / Preview --}}
                    <div class="relative bg-gray-100 h-40 overflow-hidden">
                        <img
                            x-show="campaign.image_url"
                            :src="campaign.image_url"
                            :alt="campaign.name"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            x-on:error="campaign.image_url = null"
                        >
                        <div
                            x-show="!campaign.image_url"
                            class="w-full h-full flex items-center justify-center"
                        >
                            <div class="text-center">
                                <svg class="w-10 h-10 text-gray-300 mx-auto mb-1" viewBox="0 0 24 24" fill="none"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                <span class="text-xs text-gray-400" x-text="campaign.campaign_type || 'Ad Campaign'"></span>
                            </div>
                        </div>

                        {{-- Favorite badge --}}
                        <div
                            class="absolute top-2 right-2 w-7 h-7 rounded-full flex items-center justify-center shadow-sm transition-all"
                            :class="campaign.is_favorite ? 'bg-brand-600 text-white' : 'bg-white/80 text-gray-400 group-hover:text-brand-500'"
                        >
                            <svg class="w-4 h-4" viewBox="0 0 24 24" :fill="campaign.is_favorite ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                        </div>

                        {{-- Type badge --}}
                        <div
                            x-show="campaign.campaign_type"
                            class="absolute bottom-2 left-2 px-2 py-0.5 rounded-md bg-black/50 text-white text-[10px] font-medium uppercase tracking-wide backdrop-blur-sm"
                            x-text="campaign.campaign_type"
                        ></div>
                    </div>

                    {{-- Info --}}
                    <div class="p-3 space-y-2">
                        <p class="text-sm font-semibold text-gray-900 truncate" x-text="campaign.name"></p>

                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500" x-text="campaign.brand_name || campaign.headline || '—'"></span>
                            <span class="text-sm font-bold text-brand-600" x-text="'$' + campaign.bid_amount.toFixed(4)"></span>
                        </div>

                        {{-- Categories --}}
                        <div x-show="campaign.categories && campaign.categories.length" class="flex flex-wrap gap-1">
                            <template x-for="cat in campaign.categories.slice(0, 2)" :key="cat">
                                <span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 text-[10px]" x-text="cat"></span>
                            </template>
                        </div>

                        {{-- Countries & Sizes --}}
                        <div class="flex items-center justify-between text-[10px] text-gray-400">
                            <span x-text="campaign.targeting_geo && campaign.targeting_geo.length ? campaign.targeting_geo.slice(0,3).join(', ') + (campaign.targeting_geo.length > 3 ? ' +' + (campaign.targeting_geo.length - 3) : '') : 'Global'"></span>
                            <span x-text="campaign.ad_formats && campaign.ad_formats.length ? campaign.ad_formats[0] : ''"></span>
                        </div>

                        {{-- CTA --}}
                        <button
                            class="w-full text-xs py-1.5 rounded-lg border font-medium transition-colors"
                            :class="campaign.is_favorite
                                ? 'border-brand-300 bg-brand-50 text-brand-700 hover:bg-brand-100'
                                : 'border-gray-200 bg-gray-50 text-gray-600 hover:bg-brand-50 hover:border-brand-200 hover:text-brand-700'"
                            @click.stop="toggleFavorite(campaign)"
                        >
                            <span x-text="campaign.is_favorite ? '★ Favorited' : '☆ Add to Favorites'"></span>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Pagination --}}
        <div x-show="!loading && meta.last_page > 1" class="flex items-center justify-center gap-2 mt-6">
            <button
                @click="loadCampaigns(meta.current_page - 1)"
                :disabled="meta.current_page <= 1"
                class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
            >Prev</button>
            <template x-for="p in pageRange()" :key="p">
                <button
                    @click="loadCampaigns(p)"
                    :class="p === meta.current_page ? 'bg-brand-600 text-white border-brand-600' : 'border-gray-200 text-gray-600 hover:bg-gray-50'"
                    class="w-9 h-9 rounded-lg border text-sm font-medium"
                    x-text="p"
                ></button>
            </template>
            <button
                @click="loadCampaigns(meta.current_page + 1)"
                :disabled="meta.current_page >= meta.last_page"
                class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
            >Next</button>
        </div>
    </div>

    {{-- ═══ Favorites Panel ═══ --}}
    <div x-show="favorites.length > 0" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="bg-white border border-brand-200 rounded-xl overflow-hidden shadow-sm">
        <div class="flex items-center justify-between px-5 py-3 bg-brand-50 border-b border-brand-200">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-brand-600" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                <h3 class="text-sm font-bold text-gray-900">Your Favorite Campaigns</h3>
                <span class="text-xs bg-brand-600 text-white rounded-full px-2 py-0.5 font-semibold" x-text="favorites.length"></span>
            </div>
            <p class="text-xs text-gray-500">Click a campaign above to toggle favorites</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                        <th class="px-4 py-2 text-left font-medium">Campaign Name</th>
                        <th class="px-4 py-2 text-left font-medium">Type</th>
                        <th class="px-4 py-2 text-right font-medium">Bid Price</th>
                        <th class="px-4 py-2 text-right font-medium">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <template x-for="fav in favorites" :key="fav.id">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-brand-500 flex-shrink-0"></span>
                                    <span class="font-medium text-gray-900" x-text="fav.name"></span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-500 capitalize" x-text="fav.campaign_type || '—'"></td>
                            <td class="px-4 py-3 text-right font-bold text-brand-600" x-text="'$' + Number(fav.bid_amount).toFixed(4)"></td>
                            <td class="px-4 py-3 text-right">
                                <button
                                    @click="removeFavoriteById(fav.id)"
                                    class="text-xs text-red-500 hover:text-red-700 font-medium hover:bg-red-50 px-2 py-1 rounded-lg transition-colors"
                                >Remove</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-200 bg-gray-50">
                        <td colspan="2" class="px-4 py-2 text-xs text-gray-500 font-medium" x-text="favorites.length + ' campaign(s) favorited'"></td>
                        <td class="px-4 py-2 text-right text-xs font-bold text-gray-700">
                            Avg: <span class="text-brand-600" x-text="'$' + avgBid()"></span>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ═══ Ad Rotator Builder ═══ --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 bg-gray-50 border-b border-gray-200">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <h3 class="text-sm font-bold text-gray-900">Ad Rotator Builder</h3>
                <span x-show="rotatorCampaigns.length > 0" class="text-xs bg-gray-600 text-white rounded-full px-2 py-0.5 font-semibold" x-text="rotatorCampaigns.length"></span>
            </div>
            <p class="text-xs text-gray-500">Drag campaigns here to create a manual ad rotator</p>
        </div>

        <div class="p-4">
            {{-- Zone selector --}}
            <div class="flex items-center gap-3 mb-4">
                <label class="text-xs font-medium text-gray-500">Select AdBlock:</label>
                <select
                    x-model="selectedZoneId"
                    @change="loadZones()"
                    class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 min-w-[250px]"
                >
                    <option value="">— select an adblock —</option>
                    <template x-for="zone in zones" :key="zone.id">
                        <option :value="zone.id" x-text="zone.name + ' (' + zone.site_name + ')'"></option>
                    </template>
                </select>
                <a href="{{ route('publisher.adblocks') }}" class="text-xs text-brand-600 hover:underline">Manage AdBlocks</a>
            </div>

            {{-- Drop zone --}}
            <div
                @dragover.prevent="dragOver = true"
                @dragleave.prevent="dragOver = false"
                @drop.prevent="handleDrop($event)"
                :class="dragOver ? 'border-brand-400 bg-brand-50' : 'border-gray-300 bg-gray-50'"
                class="border-2 border-dashed rounded-xl p-6 min-h-[120px] transition-colors"
            >
                <div x-show="rotatorCampaigns.length === 0" class="text-center text-gray-400">
                    <svg class="w-10 h-10 mx-auto mb-2 opacity-50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <p class="text-sm">Drag campaigns from the grid above and drop them here</p>
                </div>

                <div x-show="rotatorCampaigns.length > 0" class="flex flex-wrap gap-2">
                    <template x-for="(rc, idx) in rotatorCampaigns" :key="rc.id">
                        <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2 shadow-sm">
                            <span class="w-5 h-5 flex items-center justify-center bg-brand-100 text-brand-700 rounded text-xs font-bold" x-text="idx + 1"></span>
                            <span class="text-sm font-medium text-gray-800" x-text="rc.name"></span>
                            <span class="text-xs text-gray-400" x-text="'$' + rc.bid_amount.toFixed(4)"></span>
                            <button @click="removeFromRotator(rc.id)" class="ml-1 text-gray-400 hover:text-red-500 transition-colors">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Get Rotator button --}}
            <div x-show="rotatorCampaigns.length > 0" class="mt-4 flex items-center justify-between">
                <button
                    @click="rotatorCampaigns = []"
                    class="text-sm text-gray-500 hover:text-gray-700 transition-colors"
                >Clear All</button>
                <button
                    @click="generateRotator()"
                    :disabled="!selectedZoneId || generatingRotator"
                    class="px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-2"
                >
                    <svg x-show="generatingRotator" class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/><path d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" fill="currentColor" class="opacity-75"/></svg>
                    <span x-text="generatingRotator ? 'Generating...' : 'Get Rotator Code'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ═══ Campaign Detail Modal ═══ --}}
    <div
        x-show="showCampaignModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto bg-black/50"
        @click.self="showCampaignModal = false"
    >
        <div class="flex min-h-full items-center justify-center p-4">
            <div
                class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col transform"
                @click.stop
            >
            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-8 py-5 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900" x-text="selectedCampaign?.name || 'Campaign Details'"></h2>
                <button @click="showCampaignModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Modal Tabs --}}
            <div class="flex border-b border-gray-200">
                <button
                    @click="modalTab = 'info'"
                    :class="modalTab === 'info' ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="px-8 py-3 text-sm font-medium border-b-2 transition-colors"
                >Info</button>
                <button
                    @click="modalTab = 'tags'"
                    :class="modalTab === 'tags' ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="px-8 py-3 text-sm font-medium border-b-2 transition-colors"
                >Tags</button>
            </div>

            {{-- Modal Content --}}
            <div class="flex-1 overflow-y-auto p-8">
                {{-- Info Tab --}}
                <div x-show="modalTab === 'info'" class="space-y-4">
                    <div class="flex gap-4">
                        <div x-show="selectedCampaign?.image_url" class="w-32 h-24 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                            <img :src="selectedCampaign?.image_url" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs font-medium rounded uppercase" x-text="selectedCampaign?.campaign_type"></span>
                                <span class="text-lg font-bold text-brand-600" x-text="'$' + (selectedCampaign?.bid_amount || 0).toFixed(4)"></span>
                            </div>
                            <p class="text-sm text-gray-600" x-text="selectedCampaign?.headline || selectedCampaign?.brand_name || '—'"></p>
                            <p x-show="selectedCampaign?.body_text" class="text-xs text-gray-500" x-text="selectedCampaign?.body_text"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                        <div>
                            <label class="text-xs text-gray-400 uppercase tracking-wide">Ad Type</label>
                            <p class="text-sm text-gray-700 capitalize" x-text="selectedCampaign?.ad_type || '—'"></p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 uppercase tracking-wide">Creative Size</label>
                            <p class="text-sm text-gray-700" x-text="selectedCampaign?.creative_size || '—'"></p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 uppercase tracking-wide">Targeting</label>
                            <p class="text-sm text-gray-700" x-text="selectedCampaign?.targeting_geo?.length ? selectedCampaign.targeting_geo.join(', ') : 'Global'"></p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 uppercase tracking-wide">Categories</label>
                            <p class="text-sm text-gray-700" x-text="selectedCampaign?.categories?.length ? selectedCampaign.categories.join(', ') : '—'"></p>
                        </div>
                    </div>
                </div>

                {{-- Tags Tab --}}
                <div x-show="modalTab === 'tags'" class="space-y-4">
                    <p class="text-sm text-gray-600">Choose the ad block you want to use for this campaign, then copy the tag code.</p>

                    <div class="flex items-center gap-3">
                        <label class="text-xs font-medium text-gray-500 whitespace-nowrap">Select AdBlock:</label>
                        <select
                            x-model="tagZoneId"
                            class="flex-1 text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
                        >
                            <option value="">— select an adblock —</option>
                            <template x-for="zone in zones" :key="zone.id">
                                <option :value="zone.id" x-text="zone.name + ' (' + zone.site_name + ')'"></option>
                            </template>
                        </select>
                        <button
                            @click="generateTag()"
                            :disabled="!tagZoneId || generatingTag"
                            class="px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <span x-text="generatingTag ? 'Generating...' : 'Get Tag'"></span>
                        </button>
                    </div>

                    <div x-show="tagCodes" class="space-y-3 pt-4">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-medium text-gray-600">JavaScript Tag</label>
                                <button @click="copyCode(tagCodes?.js)" class="text-xs text-brand-600 hover:underline">Copy</button>
                            </div>
                            <pre class="bg-gray-900 text-green-400 text-xs p-3 rounded-lg overflow-x-auto"><code x-text="tagCodes?.js"></code></pre>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-medium text-gray-600">iFrame Tag</label>
                                <button @click="copyCode(tagCodes?.iframe)" class="text-xs text-brand-600 hover:underline">Copy</button>
                            </div>
                            <pre class="bg-gray-900 text-green-400 text-xs p-3 rounded-lg overflow-x-auto"><code x-text="tagCodes?.iframe"></code></pre>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-medium text-gray-600">Async Tag</label>
                                <button @click="copyCode(tagCodes?.async)" class="text-xs text-brand-600 hover:underline">Copy</button>
                            </div>
                            <pre class="bg-gray-900 text-green-400 text-xs p-3 rounded-lg overflow-x-auto"><code x-text="tagCodes?.async"></code></pre>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-end gap-3 px-8 py-5 border-t border-gray-200 bg-gray-50">
                <button @click="showCampaignModal = false" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition-colors">Close</button>
                <button
                    @click="toggleFavoriteFromModal()"
                    class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                    :class="selectedCampaign?.is_favorite ? 'bg-brand-100 text-brand-700' : 'bg-brand-600 text-white hover:bg-brand-700'"
                >
                    <span x-text="selectedCampaign?.is_favorite ? '★ Favorited' : '☆ Add to Favorites'"></span>
                </button>
            </div>
        </div>
        </div>
    </div>

    {{-- ═══ Rotator Code Modal ═══ --}}
    <div
        x-show="showRotatorModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        class="fixed inset-0 z-50 overflow-y-auto bg-black/50"
        @click.self="showRotatorModal = false"
    >
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[80vh] overflow-hidden flex flex-col" @click.stop>
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">Ad Rotator Code</h2>
                <button @click="showRotatorModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-6">
                <p class="text-sm text-gray-600 mb-4">Copy and paste this code into your website to display a rotating selection of the campaigns you selected.</p>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-medium text-gray-600">Rotator Code (<span x-text="rotatorResult?.campaign_count || 0"></span> campaigns)</label>
                    <button @click="copyCode(rotatorResult?.rotator_code)" class="text-xs text-brand-600 hover:underline">Copy Code</button>
                </div>
                <pre class="bg-gray-900 text-green-400 text-xs p-4 rounded-lg overflow-x-auto whitespace-pre-wrap"><code x-text="rotatorResult?.rotator_code"></code></pre>
            </div>
            <div class="flex items-center justify-end px-6 py-4 border-t border-gray-200 bg-gray-50">
                <button @click="showRotatorModal = false" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition-colors">Close</button>
            </div>
        </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function admarket() {
    return {
        // State
        campaigns: [],
        favorites: [],
        loading: true,
        loadError: '',
        filter: 'all',
        filterCountry: '',
        filterCategory: '',
        filterSize: '',
        selectedProperty: '',
        meta: { current_page: 1, last_page: 1, total: 0 },
        csrfToken: document.querySelector('meta[name="csrf-token"]').content,

        // Campaign Modal
        showCampaignModal: false,
        selectedCampaign: null,
        modalTab: 'info',

        // Zones / Tags
        zones: [],
        tagZoneId: '',
        tagCodes: null,
        generatingTag: false,

        // Ad Rotator
        rotatorCampaigns: [],
        dragOver: false,
        selectedZoneId: '',
        generatingRotator: false,
        showRotatorModal: false,
        rotatorResult: null,

        init() {
            this.loadCampaigns(1);
            this.loadZones();
        },

        setFilter(val) {
            this.filter = val;
            this.loadCampaigns(1);
        },

        async loadCampaigns(page = 1) {
            this.loading   = true;
            this.loadError = '';
            const params = new URLSearchParams({
                filter:   this.filter,
                country:  this.filterCountry,
                category: this.filterCategory,
                size:     this.filterSize,
                page:     page,
            });

            try {
                const res = await fetch('/publisher/admarket/campaigns?' + params, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) {
                    const text = await res.text();
                    this.loadError = 'Server error (' + res.status + '). Check Laravel logs.';
                    console.error('AdMarket API error', res.status, text);
                    return;
                }
                const json     = await res.json();
                this.campaigns = json.data   ?? [];
                this.meta      = json.meta   ?? { current_page: 1, last_page: 1, total: 0 };
                this.favorites = json.favorites ?? [];
            } catch (e) {
                this.loadError = 'Network error: ' + e.message;
                console.error('Failed to load campaigns', e);
            } finally {
                this.loading = false;
            }
        },

        async toggleFavorite(campaign) {
            try {
                const res  = await fetch('/publisher/admarket/' + campaign.id + '/favorite', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                });
                const json = await res.json();
                if (json.success) {
                    campaign.is_favorite = json.favorited;
                    this.favorites       = json.favorites;
                }
            } catch (e) {
                console.error('Failed to toggle favorite', e);
            }
        },

        async removeFavoriteById(campaignId) {
            const campaign = this.campaigns.find(c => c.id === campaignId);
            if (campaign) {
                await this.toggleFavorite(campaign);
            } else {
                // Campaign not on current page – call API directly
                try {
                    const res  = await fetch('/publisher/admarket/' + campaignId + '/favorite', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                        },
                    });
                    const json = await res.json();
                    if (json.success) {
                        this.favorites = json.favorites;
                    }
                } catch (e) {
                    console.error('Failed to remove favorite', e);
                }
            }
        },

        avgBid() {
            if (!this.favorites.length) return '0.0000';
            const sum = this.favorites.reduce((acc, f) => acc + Number(f.bid_amount), 0);
            return (sum / this.favorites.length).toFixed(4);
        },

        pageRange() {
            const current  = this.meta.current_page;
            const last     = this.meta.last_page;
            const delta    = 2;
            const range    = [];
            const rangeWithDots = [];
            let l;

            for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
                range.push(i);
            }
            if (current - delta > 2) range.unshift('...');
            if (current + delta < last - 1) range.push('...');
            range.unshift(1);
            if (last !== 1) range.push(last);

            // Filter out '...' for the x-for loop (use numbers only)
            return range.filter(p => typeof p === 'number');
        },

        // ═══ Campaign Modal Methods ═══
        async openCampaignModal(campaign) {
            this.selectedCampaign = { ...campaign };
            this.modalTab = 'info';
            this.tagCodes = null;
            this.tagZoneId = '';
            this.showCampaignModal = true;

            // Load detailed campaign info
            try {
                const res = await fetch('/publisher/admarket/campaign/' + campaign.id, {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const json = await res.json();
                    if (json.success) {
                        this.selectedCampaign = { ...this.selectedCampaign, ...json.campaign };
                    }
                }
            } catch (e) {
                console.error('Failed to load campaign details', e);
            }
        },

        async toggleFavoriteFromModal() {
            if (!this.selectedCampaign) return;
            await this.toggleFavorite(this.selectedCampaign);
            // Update modal campaign state
            const campaign = this.campaigns.find(c => c.id === this.selectedCampaign.id);
            if (campaign) {
                this.selectedCampaign.is_favorite = campaign.is_favorite;
            }
        },

        // ═══ Zones Methods ═══
        async loadZones() {
            try {
                const res = await fetch('/publisher/admarket/zones', {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const json = await res.json();
                    this.zones = json.zones ?? [];
                }
            } catch (e) {
                console.error('Failed to load zones', e);
            }
        },

        // ═══ Tag Generation Methods ═══
        async generateTag() {
            if (!this.tagZoneId || !this.selectedCampaign) return;

            this.generatingTag = true;
            this.tagCodes = null;

            try {
                const res = await fetch('/publisher/admarket/generate-tag', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        campaign_id: this.selectedCampaign.id,
                        zone_id: this.tagZoneId,
                    }),
                });

                const json = await res.json();
                if (json.success) {
                    this.tagCodes = json.codes;
                } else {
                    alert(json.message || 'Failed to generate tag');
                }
            } catch (e) {
                console.error('Failed to generate tag', e);
                alert('Error generating tag: ' + e.message);
            } finally {
                this.generatingTag = false;
            }
        },

        // ═══ Ad Rotator Methods ═══
        handleDragStart(event, campaign) {
            event.dataTransfer.setData('application/json', JSON.stringify(campaign));
            event.dataTransfer.effectAllowed = 'copy';
        },

        handleDrop(event) {
            this.dragOver = false;
            try {
                const data = event.dataTransfer.getData('application/json');
                if (!data) return;

                const campaign = JSON.parse(data);
                // Don't add duplicates
                if (!this.rotatorCampaigns.find(c => c.id === campaign.id)) {
                    this.rotatorCampaigns.push(campaign);
                }
            } catch (e) {
                console.error('Failed to handle drop', e);
            }
        },

        removeFromRotator(campaignId) {
            this.rotatorCampaigns = this.rotatorCampaigns.filter(c => c.id !== campaignId);
        },

        async generateRotator() {
            if (!this.selectedZoneId || this.rotatorCampaigns.length === 0) {
                alert('Please select an adblock and add at least one campaign');
                return;
            }

            this.generatingRotator = true;

            try {
                const res = await fetch('/publisher/admarket/generate-rotator', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        zone_id: this.selectedZoneId,
                        campaign_ids: this.rotatorCampaigns.map(c => c.id),
                    }),
                });

                const json = await res.json();
                if (json.success) {
                    this.rotatorResult = json;
                    this.showRotatorModal = true;
                } else {
                    alert(json.message || 'Failed to generate rotator');
                }
            } catch (e) {
                console.error('Failed to generate rotator', e);
                alert('Error generating rotator: ' + e.message);
            } finally {
                this.generatingRotator = false;
            }
        },

        // ═══ Utility Methods ═══
        async copyCode(code) {
            if (!code) return;
            try {
                await navigator.clipboard.writeText(code);
                // Brief visual feedback
                const btn = event.target;
                const originalText = btn.textContent;
                btn.textContent = 'Copied!';
                setTimeout(() => btn.textContent = originalText, 1500);
            } catch (e) {
                console.error('Failed to copy', e);
                // Fallback
                const textarea = document.createElement('textarea');
                textarea.value = code;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
            }
        },
    };
}
</script>
@endpush
