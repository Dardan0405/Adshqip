<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Adshqip Advertiser</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui'] },
                    colors: {
                        brand: {
                            50:'#fff1f2',100:'#ffe4e6',200:'#fecdd3',300:'#fda4af',
                            400:'#fb7185',500:'#f43f5e',600:'#e11d48',700:'#be123c',
                            800:'#9f1239',900:'#881337',950:'#4c0519'
                        },
                        adgate: { green: '#2db67d', dark: '#1a1a2e', sidebar: '#fafbfc' }
                    }
                }
            }
        }
    </script>
    <style>
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 999px; }
        ::-webkit-scrollbar-track { background: transparent; }
        .sidebar-link { transition: all 0.15s ease; }
        .sidebar-link:hover, .sidebar-link.active { background: #fff1f2; color: #e11d48; }
        .sidebar-link.active { font-weight: 600; border-right: 3px solid #e11d48; }
    </style>
    @stack('styles')
</head>
<body class="h-full bg-gray-50 font-sans text-gray-800 antialiased">
    @php
        $authUser = Auth::user();
        $profileAvatar = $authUser?->profile?->avatar_url;
        $sidebarInitials = strtoupper(substr($authUser?->email ?? 'AD', 0, 2));
        $headerInitial = strtoupper(substr($authUser?->email ?? 'A', 0, 1));
    @endphp
    <div class="flex h-full">

        {{-- ═══════════ SIDEBAR ═══════════ --}}
        <aside class="hidden lg:flex lg:flex-col w-60 border-r border-gray-200 bg-white h-screen sticky top-0 overflow-y-auto">
            {{-- Logo --}}
            <div class="flex items-center gap-2 px-5 h-16 border-b border-gray-100">
                <div class="w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 133 102" class="w-6 h-auto"><image width="133" height="102" href="{{ asset('AdshqipSVG.svg') }}"></image></svg>
                </div>
                <div>
                    <span class="font-bold text-base text-gray-900">Adshqip</span>
                    <span class="block text-[10px] text-gray-400 -mt-0.5 uppercase tracking-wider">advertiser</span>
                </div>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 py-4 px-3 space-y-0.5 text-sm">
                <a href="{{ route('advertiser.dashboard') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('advertiser.dashboard') ? 'active' : '' }}">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Dashboard
                </a>

                <div class="pt-3">
                    <button onclick="toggleSubmenu('campaignsMenu')" class="sidebar-link flex items-center justify-between w-full px-3 py-2 rounded-lg text-gray-600 hover:text-brand-600 {{ request()->routeIs('advertiser.campaigns*') ? 'active' : '' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            Campaigns
                        </span>
                        <svg class="w-3 h-3 transition-transform" id="campaignsMenuArrow" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                    <div id="campaignsMenu" class="ml-7 mt-1 space-y-0.5 {{ request()->routeIs('advertiser.campaigns*') ? '' : 'hidden' }}">
                        <a href="{{ route('advertiser.campaigns') }}" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.campaigns') ? 'active' : '' }}">All Campaigns</a>
                        <a href="{{ route('advertiser.campaigns.create') }}" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.campaigns.create') ? 'active' : '' }}">Create New Campaign</a>
                    </div>
                </div>

                <a href="{{ route('advertiser.adformats') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600 {{ request()->routeIs('advertiser.adformats*') ? 'active' : '' }}">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Creatives
                </a>

                <a href="{{ route('advertiser.audiences') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600 {{ request()->routeIs('advertiser.audiences*') ? 'active' : '' }}">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Audiences
                </a>

                <div class="pt-3">
                    <button onclick="toggleSubmenu('advancedMenu')" class="sidebar-link flex items-center justify-between w-full px-3 py-2 rounded-lg text-gray-600 hover:text-brand-600 {{ request()->routeIs('advertiser.campaign-admarket*') || request()->routeIs('advertiser.direct-campaigns*') ? 'active' : '' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            Advanced
                        </span>
                        <svg class="w-3 h-3 transition-transform" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                    <div id="advancedMenu" class="ml-7 mt-1 space-y-0.5 {{ request()->routeIs('advertiser.campaign-admarket*') || request()->routeIs('advertiser.direct-campaigns*') ? '' : 'hidden' }}">
                        <a href="{{ route('advertiser.campaign-admarket') }}" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.campaign-admarket*') ? 'active' : '' }}">Campaign AdMarket</a>
                        <a href="{{ route('advertiser.direct-campaigns') }}" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.direct-campaigns*') ? 'active' : '' }}">Direct Campaigns</a>
                    </div>
                </div>

                <div class="pt-3">
                    <button onclick="toggleSubmenu('reportsMenu')" class="sidebar-link flex items-center justify-between w-full px-3 py-2 rounded-lg text-gray-600 hover:text-brand-600 {{ request()->routeIs('advertiser.reports*') ? 'active' : '' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            Reports
                        </span>
                        <svg class="w-3 h-3 transition-transform" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                    <div id="reportsMenu" class="ml-7 mt-1 space-y-0.5 {{ request()->routeIs('advertiser.reports*') ? '' : 'hidden' }}">
                        <a href="{{ route('advertiser.reports.overview') }}" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.reports.overview*') ? 'active' : '' }}">Overview Report</a>
                        <a href="{{ route('advertiser.reports.graphical') }}" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.reports.graphical*') ? 'active' : '' }}">Geographical Reports</a>
                        <a href="{{ route('advertiser.reports.campaign') }}" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.reports.campaign*') ? 'active' : '' }}">Campaign Reports</a>
                        <a href="{{ route('advertiser.reports.creative') }}" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.reports.creative*') ? 'active' : '' }}">Creative Reports</a>
                        <a href="{{ route('advertiser.reports.video-creative') }}" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.reports.video-creative*') ? 'active' : '' }}">Video Creative Report</a>
                        <a href="{{ route('advertiser.reports.site-url') }}" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.reports.site-url*') ? 'active' : '' }}">Site URL Report</a>
                        <a href="{{ route('advertiser.reports.group-settings') }}" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.reports.group-settings*') ? 'active' : '' }}">Group Settings</a>
                    </div>
                </div>

                <div class="pt-3">
                    <button onclick="toggleSubmenu('trackingMenu')" class="sidebar-link flex items-center justify-between w-full px-3 py-2 rounded-lg text-gray-600 hover:text-brand-600 {{ request()->routeIs('advertiser.tracking*') ? 'active' : '' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            Tracking
                        </span>
                        <svg class="w-3 h-3 transition-transform" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                    <div id="trackingMenu" class="ml-7 mt-1 space-y-0.5 {{ request()->routeIs('advertiser.tracking*') ? '' : 'hidden' }}">
                        <a href="{{ route('advertiser.tracking.conversions') }}" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.tracking.conversions*') ? 'active' : '' }}">Conversion Tracking</a>
                        <a href="{{ route('advertiser.tracking.goals') }}" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.tracking.goals*') ? 'active' : '' }}">Goals</a>
                        <a href="{{ route('advertiser.tracking.event-log') }}" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.tracking.event-log*') ? 'active' : '' }}">Event Log</a>
                    </div>
                </div>

                <div class="pt-3">
                    <button onclick="toggleSubmenu('paymentMenu')" class="sidebar-link flex items-center justify-between w-full px-3 py-2 rounded-lg text-gray-600 hover:text-brand-600 {{ request()->routeIs('advertiser.payments*') ? 'active' : '' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            Payment
                        </span>
                        <svg class="w-3 h-3 transition-transform" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                    <div id="paymentMenu" class="ml-7 mt-1 space-y-0.5 {{ request()->routeIs('advertiser.payments*') ? '' : 'hidden' }}">
                        <a href="{{ route('advertiser.payments.history') }}" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.payments.history') ? 'active' : '' }}">Payment History</a>
                        <a href="{{ route('advertiser.payments.deposit-history') }}" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.payments.deposit-history') ? 'active' : '' }}">Deposit History</a>
                        <a href="{{ route('advertiser.payments.add-funds') }}" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.payments.add-funds*') ? 'active' : '' }}">Add Funds</a>
                        <a href="{{ route('advertiser.payments.invoices') }}" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.payments.invoices*') ? 'active' : '' }}">Invoices History</a>
                        <a href="{{ route('advertiser.payments.subscription-plan') }}" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.payments.subscription-plan*') ? 'active' : '' }}">Subscription Plan</a>
                    </div>
                </div>

                <div class="pt-3">
                    <button onclick="toggleSubmenu('networkMenu')" class="sidebar-link flex items-center justify-between w-full px-3 py-2 rounded-lg text-gray-600 hover:text-brand-600 {{ request()->routeIs('advertiser.network*') ? 'active' : '' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 21a9 9 0 100-18 9 9 0 000 18zm0 0c2.5-2.25 4-5.25 4-9s-1.5-6.75-4-9m0 18c-2.5-2.25-4-5.25-4-9s1.5-6.75 4-9M3.6 9h16.8M3.6 15h16.8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            Network
                        </span>
                        <svg class="w-3 h-3 transition-transform" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                    <div id="networkMenu" class="ml-7 mt-1 space-y-0.5 {{ request()->routeIs('advertiser.network*') ? '' : 'hidden' }}">
                        <a href="{{ route('advertiser.network.country-wise-bidding') }}" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.network.country-wise-bidding*') ? 'active' : '' }}">Country Wise Bidding</a>
                        <a href="{{ route('advertiser.network.traffic-sources') }}" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.network.traffic-sources*') ? 'active' : '' }}">Traffic Source</a>
                        <a href="{{ route('advertiser.network.zone-limitations') }}" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.network.zone-limitations*') ? 'active' : '' }}">Zone Limitation</a>
                        <a href="{{ route('advertiser.network.pixel-trackers') }}" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.network.pixel-trackers*') ? 'active' : '' }}">Pixel Tracker</a>
                        <a href="{{ route('advertiser.network.network-kit') }}" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.network.network-kit*') ? 'active' : '' }}">Network Kit</a>
                    </div>
                </div>

                <a href="{{ route('advertiser.referrals') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600 mt-3 {{ request()->routeIs('advertiser.referrals*') ? 'active' : '' }}">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Referral Program
                </a>

                <a href="{{ route('advertiser.teams') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600 {{ request()->routeIs('advertiser.teams*') ? 'active' : '' }}">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Teams
                </a>

                <div class="pt-4 mt-4 border-t border-gray-100">
                    <span class="px-3 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Help</span>
                    <a href="{{ route('advertiser.help-center') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-500 mt-2 text-xs {{ request()->routeIs('advertiser.help-center') ? 'active' : '' }}">Help Center</a>
                    <a href="{{ route('advertiser.support-tickets') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.support-tickets*') ? 'active' : '' }}">Support Tickets</a>
                    <a href="{{ route('advertiser.feedback') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.feedback*') ? 'active' : '' }}">Give Feedback</a>
                    <a href="{{ route('advertiser.contacts') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-500 text-xs {{ request()->routeIs('advertiser.contacts*') ? 'active' : '' }}">Contacts</a>
                </div>
            </nav>

            {{-- User --}}
            <div class="border-t border-gray-100 px-4 py-3">
                <div class="flex items-center gap-3">
                    @if($profileAvatar)
                        <img src="{{ $profileAvatar }}" alt="Profile picture" class="w-9 h-9 rounded-full object-cover ring-2 ring-brand-100">
                    @else
                        <div class="w-9 h-9 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-semibold text-sm">
                            {{ $sidebarInitials }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-400">Account Manager</p>
                        <p class="text-sm font-semibold truncate">{{ $authUser->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-500" title="Sign out">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- ═══════════ MAIN ═══════════ --}}
        <div class="flex-1 flex flex-col min-h-screen overflow-x-hidden">
            {{-- Top bar --}}
            <header class="sticky top-0 z-30 h-[4.5rem] border-b border-gray-200 bg-white flex items-center justify-between px-8">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        <input type="text" placeholder="Search..." class="pl-10 pr-4 py-2 w-64 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    {{-- CTA --}}
                    <a href="{{ route('advertiser.campaigns.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-medium hover:bg-brand-700 transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        new campaign
                    </a>
                    {{-- Balance --}}
                    <div class="flex items-center gap-1 px-3 py-2 rounded-lg bg-gray-50 border border-gray-200">
                        <svg class="w-4 h-4 text-brand-600" viewBox="0 0 24 24" fill="none"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/></svg>
                        <span class="text-sm font-semibold">${{ number_format($balance ?? 172.12, 2) }}</span>
                    </div>
                    {{-- Notifications --}}
                    <div class="relative" x-data="advertiserNotifications()" x-init="init()">
                        <button @click="notifOpen = !notifOpen" class="relative p-2 rounded-lg hover:bg-gray-100" title="Notifications">
                            <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <span x-show="unreadCount > 0" class="absolute top-1 right-1 min-w-[18px] h-[18px] flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold px-1" x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                        </button>

                        {{-- Notification Dropdown --}}
                        <div x-show="notifOpen" @click.away="notifOpen = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-96 bg-white rounded-xl border border-gray-200 shadow-xl z-50" style="display: none;">

                            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                                <h4 class="text-sm font-bold text-gray-900">Notifications</h4>
                                <button x-show="unreadCount > 0" @click="
                                    fetch('/advertisers/notifications/read-all', {
                                        method: 'POST',
                                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
                                    }).then(() => { notifications.forEach(n => { n.is_read = true; }); unreadCount = 0; });
                                " class="text-xs text-brand-600 hover:text-brand-700 font-medium">Mark all read</button>
                            </div>

                            <div class="max-h-80 overflow-y-auto divide-y divide-gray-50">
                                <template x-if="notifications.length === 0">
                                    <div class="px-4 py-8 text-center">
                                        <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" viewBox="0 0 24 24" fill="none"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                        <p class="text-sm text-gray-400">No notifications yet</p>
                                    </div>
                                </template>
                                <template x-for="notif in notifications" :key="notif.id">
                                    <div @click="
                                        if (!notif.is_read) {
                                            fetch('/advertisers/notifications/' + notif.id + '/read', {
                                                method: 'POST',
                                                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
                                            }).then(() => { notif.is_read = true; unreadCount = Math.max(0, unreadCount - 1); });
                                        }
                                        if (notif.action_url) window.location.href = notif.action_url;
                                    " class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors" :class="!notif.is_read ? 'bg-brand-50/30' : ''">
                                        <div class="flex items-start gap-3">
                                            <div class="flex-shrink-0 mt-0.5">
                                                <template x-if="notif.type === 'success'"><span class="w-2 h-2 rounded-full bg-emerald-500 block"></span></template>
                                                <template x-if="notif.type === 'warning'"><span class="w-2 h-2 rounded-full bg-amber-500 block"></span></template>
                                                <template x-if="notif.type === 'error'"><span class="w-2 h-2 rounded-full bg-red-500 block"></span></template>
                                                <template x-if="notif.type === 'info'"><span class="w-2 h-2 rounded-full bg-blue-500 block"></span></template>
                                                <template x-if="notif.type === 'payment'"><span class="w-2 h-2 rounded-full bg-green-500 block"></span></template>
                                                <template x-if="notif.type === 'campaign'"><span class="w-2 h-2 rounded-full bg-purple-500 block"></span></template>
                                                <template x-if="notif.type === 'system'"><span class="w-2 h-2 rounded-full bg-gray-500 block"></span></template>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900" :class="!notif.is_read ? 'font-semibold' : ''" x-text="notif.title"></p>
                                                <p class="text-xs text-gray-500 mt-0.5 line-clamp-2" x-text="notif.message"></p>
                                                <p class="text-[10px] text-gray-400 mt-1" x-text="new Date(notif.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })"></p>
                                            </div>
                                            <span x-show="!notif.is_read" class="w-2 h-2 rounded-full bg-brand-500 flex-shrink-0 mt-2"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    {{-- Messages --}}
                    <div class="relative" x-data="advertiserMessages()" x-init="init()">
                        <button @click="open = !open" class="relative p-2 rounded-lg hover:bg-gray-100" title="Messages">
                            <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <span x-show="unreadCount > 0" class="absolute top-1 right-1 min-w-[18px] h-[18px] flex items-center justify-center rounded-full bg-brand-600 text-white text-[10px] font-bold px-1" x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                        </button>
                        <div x-show="open" @click.away="open = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-96 bg-white rounded-xl border border-gray-200 shadow-xl z-50" style="display: none;">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                                <h4 class="text-sm font-bold text-gray-900">Messages</h4>
                                <button x-show="unreadCount > 0" @click="markAllRead()" class="text-xs text-brand-600 hover:text-brand-700 font-medium">Mark all read</button>
                            </div>
                            <div class="max-h-80 overflow-y-auto divide-y divide-gray-50">
                                <template x-if="messages.length === 0">
                                    <div class="px-4 py-8 text-center">
                                        <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" viewBox="0 0 24 24" fill="none"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                        <p class="text-sm text-gray-400">No messages yet</p>
                                    </div>
                                </template>
                                <template x-for="message in messages" :key="message.id">
                                    <div @click="markRead(message)" class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors" :class="!message.is_read ? 'bg-brand-50/30' : ''">
                                        <div class="flex items-start gap-3">
                                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-[11px] font-semibold text-slate-700" x-text="message.sender_initials"></div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between gap-3">
                                                    <p class="text-sm text-gray-900 truncate" :class="!message.is_read ? 'font-semibold' : 'font-medium'" x-text="message.subject"></p>
                                                    <span class="text-[10px] uppercase tracking-[0.18em] text-gray-400" x-text="message.priority"></span>
                                                </div>
                                                <p class="text-xs text-gray-500 mt-0.5 truncate" x-text="message.sender"></p>
                                                <p class="text-xs text-gray-500 mt-1 line-clamp-2" x-text="message.preview"></p>
                                                <p class="text-[10px] text-gray-400 mt-1" x-text="formatDate(message.created_at)"></p>
                                            </div>
                                            <span x-show="!message.is_read" class="w-2 h-2 rounded-full bg-brand-500 flex-shrink-0 mt-2"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="h-6 w-px bg-gray-200"></div>
                    <div class="relative" x-data="advertiserPushNotifications()" x-init="init()">
                        <button @click="toggle()" :disabled="loading || !supported" class="relative p-2 rounded-lg hover:bg-gray-100 disabled:opacity-50" :title="subscribed ? 'Disable Push Notifications' : 'Enable Push Notifications'">
                            <svg class="w-5 h-5" :class="subscribed ? 'text-brand-600' : 'text-gray-500'" viewBox="0 0 24 24" fill="none"><path d="M12 18h.01M8.5 14.5A5 5 0 0112 6a5 5 0 013.5 8.5M5 11a7 7 0 1114 0c0 2.123-.883 4.041-2.302 5.404-.655.629-1.044 1.489-1.044 2.397V20H8.346v-1.199c0-.908-.389-1.768-1.044-2.397A6.964 6.964 0 015 11z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                    {{-- User dropdown --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 cursor-pointer rounded-lg px-2 py-1 hover:bg-gray-50 transition-colors">
                            @if($profileAvatar)
                                <img src="{{ $profileAvatar }}" alt="Profile picture" class="w-8 h-8 rounded-full object-cover ring-2 ring-brand-100">
                            @else
                                <div class="w-8 h-8 rounded-full bg-brand-600 flex items-center justify-center text-white text-xs font-bold">
                                    {{ $headerInitial }}
                                </div>
                            @endif
                            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none"><path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-2 w-56 bg-white rounded-xl border border-gray-200 shadow-lg py-1 z-50" style="display: none;">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->email }}</p>
                                <p class="text-xs text-gray-400 mt-0.5 capitalize">{{ Auth::user()->role ?? 'advertiser' }}</p>
                            </div>
                            <a href="{{ route('advertiser.personal-information') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors hover:bg-gray-50 {{ request()->routeIs('advertiser.personal-information*') ? 'bg-brand-50 text-brand-600 font-medium' : 'text-gray-600' }}">
                                <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Personal Information
                            </a>
                            <a href="{{ route('advertiser.company-information') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors hover:bg-gray-50 {{ request()->routeIs('advertiser.company-information*') ? 'bg-brand-50 text-brand-600 font-medium' : 'text-gray-600' }}">
                                <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" stroke="currentColor" stroke-width="1.5"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/></svg>
                                Company Info
                            </a>
                            <a href="{{ route('advertiser.account-settings') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors hover:bg-gray-50 {{ request()->routeIs('advertiser.account-settings*') ? 'bg-brand-50 text-brand-600 font-medium' : 'text-gray-600' }}">
                                <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" stroke="currentColor" stroke-width="1.5"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/></svg>
                                Account Settings
                            </a>
                            <a href="{{ route('advertiser.audit-logs') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors hover:bg-gray-50 {{ request()->routeIs('advertiser.audit-logs*') ? 'bg-brand-50 text-brand-600 font-medium' : 'text-gray-600' }}">
                                <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                Audit Log
                            </a>
                            <a href="{{ route('advertiser.two-factor-authentication') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors hover:bg-gray-50 {{ request()->routeIs('advertiser.two-factor-authentication*') ? 'bg-brand-50 text-brand-600 font-medium' : 'text-gray-600' }}">
                                <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M12 11c1.657 0 3-1.343 3-3V7a3 3 0 10-6 0v1c0 1.657 1.343 3 3 3zm-6 9v-3a6 6 0 1112 0v3M9 17h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                2 Factor Authentication
                            </a>
                            <a href="{{ route('advertiser.notification-settings') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors hover:bg-gray-50 {{ request()->routeIs('advertiser.notification-settings*') ? 'bg-brand-50 text-brand-600 font-medium' : 'text-gray-600' }}">
                                <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                Notification Settings
                            </a>
                            <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                Billing
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors w-full">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 p-6">
                @yield('content')
            </main>

            {{-- Footer --}}
            <footer class="border-t border-gray-200 px-6 py-3 bg-white flex items-center justify-between text-xs text-gray-400">
                <span>copyright &copy; {{ date('Y') }} AdShqip</span>
                <div class="flex items-center gap-4">
                    <a href="#" class="hover:text-gray-600">security</a>
                    <a href="#" class="hover:text-gray-600">ios app</a>
                    <a href="#" class="hover:text-gray-600">data retention</a>
                    <a href="#" class="hover:text-gray-600">crew</a>
                    <span class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-brand-500"></span> EN
                    </span>
                </div>
            </footer>
        </div>
    </div>

    <script>
        function advertiserNotifications() {
            return {
                notifOpen: false,
                notifications: [],
                unreadCount: 0,
                async load() {
                    const response = await fetch('/advertisers/notifications', { headers: { 'Accept': 'application/json' } });
                    const data = await response.json();
                    this.notifications = data;
                    this.unreadCount = data.filter(n => !n.is_read).length;
                },
                async markAllRead() {
                    await fetch('/advertisers/notifications/read-all', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
                    });
                    this.notifications.forEach(n => { n.is_read = true; });
                    this.unreadCount = 0;
                },
                async init() {
                    await this.load();
                    setInterval(() => this.load(), 30000);
                },
            };
        }

        function advertiserMessages() {
            return {
                open: false,
                messages: [],
                unreadCount: 0,
                async load() {
                    const response = await fetch('/advertisers/messages', { headers: { 'Accept': 'application/json' } });
                    const data = await response.json();
                    this.messages = data.messages || [];
                    this.unreadCount = data.unread_count || 0;
                },
                async markRead(message) {
                    if (!message.is_read) {
                        await fetch(`/advertisers/messages/${message.id}/read`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
                        });
                        message.is_read = true;
                        this.unreadCount = Math.max(0, this.unreadCount - 1);
                    }
                },
                async markAllRead() {
                    await fetch('/advertisers/messages/read-all', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
                    });
                    this.messages.forEach(message => { message.is_read = true; });
                    this.unreadCount = 0;
                },
                formatDate(value) {
                    return new Date(value).toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
                },
                async init() {
                    await this.load();
                    setInterval(() => this.load(), 60000);
                },
            };
        }

        function advertiserPushNotifications() {
            return {
                supported: false,
                subscribed: false,
                loading: false,
                permission: 'default',
                async init() {
                    this.supported = 'serviceWorker' in navigator && 'PushManager' in window;
                    if (this.supported) {
                        this.permission = Notification.permission;
                        await this.checkSubscription();
                    }
                },
                async checkSubscription() {
                    try {
                        const registration = await navigator.serviceWorker.ready;
                        const subscription = await registration.pushManager.getSubscription();
                        this.subscribed = !!subscription;
                    } catch (e) {
                        console.error('Failed to check advertiser push subscription:', e);
                    }
                },
                async toggle() {
                    if (this.loading || !this.supported) return;
                    this.loading = true;
                    try {
                        if (this.subscribed) {
                            await this.unsubscribe();
                        } else {
                            await this.subscribe();
                        }
                    } finally {
                        this.loading = false;
                    }
                },
                async subscribe() {
                    const permission = await Notification.requestPermission();
                    this.permission = permission;
                    if (permission !== 'granted') return;

                    const registration = await navigator.serviceWorker.register('/sw.js');
                    await navigator.serviceWorker.ready;

                    const vapidResponse = await fetch('{{ route('advertiser.push.vapid-key') }}');
                    const { publicKey } = await vapidResponse.json();

                    if (!publicKey) {
                        this.subscribed = true;
                        await fetch('{{ route('advertiser.push.test') }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                        });
                        return;
                    }

                    const subscription = await registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: this.urlBase64ToUint8Array(publicKey)
                    });

                    await fetch('{{ route('advertiser.push.subscribe') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(subscription.toJSON())
                    });

                    this.subscribed = true;
                    await fetch('{{ route('advertiser.push.test') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    });
                },
                async unsubscribe() {
                    const registration = await navigator.serviceWorker.ready;
                    const subscription = await registration.pushManager.getSubscription();
                    if (subscription) {
                        await fetch('{{ route('advertiser.push.unsubscribe') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ endpoint: subscription.endpoint })
                        });
                        await subscription.unsubscribe();
                    }
                    this.subscribed = false;
                },
                urlBase64ToUint8Array(base64String) {
                    const padding = '='.repeat((4 - base64String.length % 4) % 4);
                    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
                    const rawData = window.atob(base64);
                    return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0)));
                },
            };
        }

        function toggleSubmenu(id) {
            const el = document.getElementById(id);
            el.classList.toggle('hidden');
        }
        // Auto-expand active submenus
        document.querySelectorAll('.sidebar-link.active').forEach(link => {
            const parent = link.closest('[id]');
            if (parent) parent.classList.remove('hidden');
        });
    </script>
    @stack('scripts')
</body>
</html>
