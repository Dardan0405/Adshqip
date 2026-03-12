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
</head>
<body class="h-full bg-gray-50 font-sans text-gray-800 antialiased">
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
                    <button onclick="toggleSubmenu('campaignsMenu')" class="sidebar-link flex items-center justify-between w-full px-3 py-2 rounded-lg text-gray-600 hover:text-brand-600">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            Campaigns
                        </span>
                        <svg class="w-3 h-3 transition-transform" id="campaignsMenuArrow" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                    <div id="campaignsMenu" class="ml-7 mt-1 space-y-0.5 hidden">
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">All Campaigns</a>
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">Create New Campaign</a>
                    </div>
                </div>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Creatives
                </a>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Audiences
                </a>

                <div class="pt-3">
                    <button onclick="toggleSubmenu('advancedMenu')" class="sidebar-link flex items-center justify-between w-full px-3 py-2 rounded-lg text-gray-600 hover:text-brand-600">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            Advanced
                        </span>
                        <svg class="w-3 h-3 transition-transform" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                    <div id="advancedMenu" class="ml-7 mt-1 space-y-0.5 hidden">
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">Campaign Admarket</a>
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">Direct Campaigns</a>
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">Dynamic Creatives</a>
                    </div>
                </div>

                <div class="pt-3">
                    <button onclick="toggleSubmenu('analyticsMenu')" class="sidebar-link flex items-center justify-between w-full px-3 py-2 rounded-lg text-gray-600 hover:text-brand-600">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            Analytics
                        </span>
                        <svg class="w-3 h-3 transition-transform" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                    <div id="analyticsMenu" class="ml-7 mt-1 space-y-0.5 hidden">
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">Summary</a>
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">Reports</a>
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">Daily Activity</a>
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">Geo Breakdown</a>
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">Recommendations</a>
                    </div>
                </div>

                <div class="pt-3">
                    <button onclick="toggleSubmenu('trackingMenu')" class="sidebar-link flex items-center justify-between w-full px-3 py-2 rounded-lg text-gray-600 hover:text-brand-600">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            Tracking
                        </span>
                        <svg class="w-3 h-3 transition-transform" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                    <div id="trackingMenu" class="ml-7 mt-1 space-y-0.5 hidden">
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">Conversion Tracking</a>
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">Goals</a>
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">Event Log</a>
                    </div>
                </div>

                <div class="pt-3">
                    <button onclick="toggleSubmenu('walletMenu')" class="sidebar-link flex items-center justify-between w-full px-3 py-2 rounded-lg text-gray-600 hover:text-brand-600">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            Wallet
                        </span>
                        <svg class="w-3 h-3 transition-transform" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                    <div id="walletMenu" class="ml-7 mt-1 space-y-0.5 hidden">
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">Manage Wallet</a>
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">Payments</a>
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">Add Funds</a>
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">Invoices History</a>
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">Subscription Plan</a>
                    </div>
                </div>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600 mt-3">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Referral Program
                </a>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Teams
                </a>

                <div class="pt-4 mt-4 border-t border-gray-100">
                    <span class="px-3 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Settings</span>
                    <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-500 mt-2 text-xs">Account Settings</a>
                    <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-500 text-xs">Notifications</a>
                    <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-500 text-xs">Security</a>
                    <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-500 text-xs">API Keys</a>
                </div>

                <div class="pt-4 mt-4 border-t border-gray-100">
                    <span class="px-3 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Help</span>
                    <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-500 mt-2 text-xs">Help Center</a>
                    <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-500 text-xs">Support Tickets</a>
                    <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-500 text-xs">Give Feedback</a>
                    <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-500 text-xs">Contacts</a>
                </div>
            </nav>

            {{-- User --}}
            <div class="border-t border-gray-100 px-4 py-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-semibold text-sm">
                        {{ strtoupper(substr(Auth::user()->email, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-400">Account Manager</p>
                        <p class="text-sm font-semibold truncate">{{ Auth::user()->email }}</p>
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
                    <a href="#" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-medium hover:bg-brand-700 transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        new campaign
                    </a>
                    {{-- Balance --}}
                    <div class="flex items-center gap-1 px-3 py-2 rounded-lg bg-gray-50 border border-gray-200">
                        <svg class="w-4 h-4 text-brand-600" viewBox="0 0 24 24" fill="none"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/></svg>
                        <span class="text-sm font-semibold">${{ number_format($balance ?? 172.12, 2) }}</span>
                    </div>
                    {{-- Notifications --}}
                    <button class="relative p-2 rounded-lg hover:bg-gray-100" title="Notifications">
                        <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-red-500"></span>
                    </button>
                    {{-- Messages --}}
                    <button class="relative p-2 rounded-lg hover:bg-gray-100" title="Messages">
                        <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                    <div class="h-6 w-px bg-gray-200"></div>
                    {{-- User dropdown --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 cursor-pointer rounded-lg px-2 py-1 hover:bg-gray-50 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-brand-600 flex items-center justify-center text-white text-xs font-bold">
                                {{ strtoupper(substr(Auth::user()->email, 0, 1)) }}
                            </div>
                            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none"><path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-2 w-56 bg-white rounded-xl border border-gray-200 shadow-lg py-1 z-50" style="display: none;">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->email }}</p>
                                <p class="text-xs text-gray-400 mt-0.5 capitalize">{{ Auth::user()->role ?? 'advertiser' }}</p>
                            </div>
                            <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                My Profile
                            </a>
                            <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" stroke="currentColor" stroke-width="1.5"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/></svg>
                                Settings
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
