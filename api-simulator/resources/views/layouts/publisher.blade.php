<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Adshqip Publisher</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('AdshqipSVG.svg') }}">
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
                        }
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
                    <span class="block text-[10px] text-gray-400 -mt-0.5 uppercase tracking-wider">publisher</span>
                </div>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 py-4 px-3 space-y-0.5 text-sm">
                {{-- Dashboard --}}
                <a href="{{ route('publisher.dashboard') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('publisher.dashboard') ? 'active' : '' }}">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    dashboard
                </a>

                {{-- Websites --}}
                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    websites
                </a>

                {{-- Zones --}}
                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" stroke="currentColor" stroke-width="1.5"/></svg>
                    zones
                </a>

                {{-- Ad Formats --}}
                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    ad formats
                </a>

                {{-- Admarket --}}
                <div class="pt-3">
                    <button onclick="toggleSubmenu('admarketMenu')" class="sidebar-link flex items-center justify-between w-full px-3 py-2 rounded-lg text-gray-600 hover:text-brand-600">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            admarket
                        </span>
                        <svg class="w-3 h-3 transition-transform" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                    <div id="admarketMenu" class="ml-7 mt-1 space-y-0.5 hidden">
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">campaign admarket</a>
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">direct campaigns</a>
                    </div>
                </div>

                {{-- Statistics --}}
                <div class="pt-3">
                    <button onclick="toggleSubmenu('statsMenu')" class="sidebar-link flex items-center justify-between w-full px-3 py-2 rounded-lg text-gray-600 hover:text-brand-600">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            statistics
                        </span>
                        <svg class="w-3 h-3 transition-transform" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                    <div id="statsMenu" class="ml-7 mt-1 space-y-0.5 hidden">
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">overview</a>
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">daily reports</a>
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">geo breakdown</a>
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">traffic sources</a>
                    </div>
                </div>

                {{-- Wallet --}}
                <div class="pt-3">
                    <button onclick="toggleSubmenu('walletMenu')" class="sidebar-link flex items-center justify-between w-full px-3 py-2 rounded-lg text-gray-600 hover:text-brand-600">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            wallet
                        </span>
                        <svg class="w-3 h-3 transition-transform" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                    <div id="walletMenu" class="ml-7 mt-1 space-y-0.5 hidden">
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">manage wallet</a>
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">payments</a>
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">invoices history</a>
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">subscription plan</a>
                    </div>
                </div>

                {{-- Referrals --}}
                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600 mt-2">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    referrals
                </a>

                {{-- Team --}}
                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    teams
                </a>

                {{-- Direct Links --}}
                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    direct links
                </a>

                {{-- Settings --}}
                <div class="pt-4 mt-4 border-t border-gray-100">
                    <span class="px-3 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">settings</span>
                    <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-500 mt-2 text-xs">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" stroke="currentColor" stroke-width="1.5"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/></svg>
                        account settings
                    </a>
                    <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-500 text-xs">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        notifications
                    </a>
                    <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-500 text-xs">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        security
                    </a>
                    <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-500 text-xs">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        KYC verification
                    </a>
                    <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-500 text-xs">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        API keys
                    </a>
                </div>

                {{-- Help --}}
                <div class="pt-4 mt-4 border-t border-gray-100">
                    <button onclick="toggleSubmenu('helpMenu')" class="sidebar-link flex items-center justify-between w-full px-3 py-2 rounded-lg text-gray-500 text-xs">
                        <span class="flex items-center gap-3">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            help
                        </span>
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                    <div id="helpMenu" class="ml-7 mt-1 space-y-0.5 hidden">
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">help center</a>
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">support tickets</a>
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">give feedback</a>
                        <a href="#" class="sidebar-link block px-3 py-1.5 rounded-lg text-gray-500 text-xs">contacts</a>
                    </div>
                </div>
            </nav>

            {{-- AdGate Feed Promo --}}
            <div class="mx-3 mb-3 p-4 rounded-xl bg-brand-50 border border-brand-200">
                <h4 class="text-sm font-bold text-gray-800">Adshqip Feed</h4>
                <p class="text-[11px] text-gray-500 mt-1">explore how it's a game changer for publisher revenue and audience retaining.</p>
                <a href="#" class="inline-block mt-3 px-4 py-1.5 rounded-lg bg-brand-600 text-white text-xs font-medium hover:bg-brand-700 transition-colors">LEARN MORE</a>
                <img src="https://placehold.co/140x80/fff1f2/e11d48?text=Adshqip+Feed" alt="Adshqip Feed" class="mt-3 rounded-lg w-full">
            </div>

            {{-- User --}}
            <div class="border-t border-gray-100 px-4 py-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-semibold text-sm">
                        {{ strtoupper(substr(Auth::user()->email, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-400">Revenue Manager</p>
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
                    <a href="{{ route('publisher.dashboard') }}" class="flex items-center gap-2 text-sm text-gray-500">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        Dashboard
                    </a>
                    <div class="relative">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        <input type="text" placeholder="search..." class="pl-10 pr-4 py-2 w-64 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    {{-- Active Sites --}}
                    <div class="hidden md:flex items-center gap-1.5 px-3 py-2 rounded-lg bg-gray-50 border border-gray-200">
                        <svg class="w-4 h-4 text-brand-600" viewBox="0 0 24 24" fill="none"><path d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        <span class="text-sm font-semibold text-gray-700">4</span>
                        <span class="text-xs text-gray-400">Sites</span>
                    </div>
                    {{-- Balance --}}
                    <div class="flex items-center gap-1 px-3 py-2 rounded-lg bg-gray-50 border border-gray-200">
                        <svg class="w-4 h-4 text-brand-600" viewBox="0 0 24 24" fill="none"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/></svg>
                        <span class="text-sm font-semibold">${{ number_format($balance ?? 17491.53, 2) }}</span>
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
                                <p class="text-xs text-gray-400 mt-0.5 capitalize">{{ Auth::user()->role ?? 'publisher' }}</p>
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
                <span>Copyright &copy; {{ date('Y') }} AdShqip.</span>
                <div class="flex items-center gap-4">
                    <a href="#" class="hover:text-gray-600">Security</a>
                    <a href="#" class="hover:text-gray-600">Get App</a>
                    <a href="#" class="hover:text-gray-600">Give Feedback</a>
                    <a href="#" class="hover:text-gray-600">Other</a>
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
    </script>
    @stack('scripts')
</body>
</html>
