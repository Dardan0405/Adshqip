<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Adshqip Admin</title>
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
                        },
                        admin: { sidebar: '#111827', hover: '#1f2937', accent: '#6366f1' }
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
        <aside class="hidden lg:flex lg:flex-col w-64 border-r border-gray-200 bg-white h-screen sticky top-0 overflow-y-auto">
            {{-- Logo --}}
            <div class="flex items-center gap-3 px-5 h-16 border-b border-gray-100">
                <div class="w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 133 102" class="w-6 h-auto"><image width="133" height="102" href="{{ asset('AdshqipSVG.svg') }}"></image></svg>
                </div>
                <div>
                    <span class="font-bold text-base text-gray-900">Adshqip</span>
                    <span class="block text-[10px] text-brand-600 -mt-0.5 uppercase tracking-wider">admin panel</span>
                </div>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 py-4 px-3 space-y-0.5 text-sm">
                {{-- Dashboard --}}
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600 {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 11a1 1 0 011-1h4a1 1 0 011 1v8a1 1 0 01-1 1h-4a1 1 0 01-1-1v-8z" stroke="currentColor" stroke-width="1.5"/></svg>
                    Dashboard
                </a>

                {{-- Platform --}}
                <div class="pt-4">
                    <span class="px-3 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Platform</span>
                </div>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600 mt-1">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Users
                </a>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Campaigns
                </a>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Sites & Zones
                </a>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Ad Formats
                </a>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Fraud Center
                </a>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Categories
                </a>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    KYC Verification
                </a>

                {{-- Finance --}}
                <div class="pt-4">
                    <span class="px-3 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Finance</span>
                </div>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600 mt-1">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/></svg>
                    Transactions
                </a>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Invoices
                </a>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Payouts
                </a>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2m-16 0H3m2 0h14M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Pricing Plans
                </a>

                {{-- Analytics --}}
                <div class="pt-4">
                    <span class="px-3 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Analytics</span>
                </div>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600 mt-1">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Reports
                </a>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Geo Analytics
                </a>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    RTB / Programmatic
                </a>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Video Analytics
                </a>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Traffic Sources
                </a>

                {{-- Content --}}
                <div class="pt-4">
                    <span class="px-3 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Content</span>
                </div>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600 mt-1">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V9a2 2 0 012-2h2a2 2 0 012 2v9a2 2 0 01-2 2h-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Newsletters
                </a>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    FAQ Management
                </a>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" stroke="currentColor" stroke-width="1.5"/></svg>
                    Testimonials
                </a>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Case Studies
                </a>

                {{-- Support --}}
                <div class="pt-4">
                    <span class="px-3 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Support</span>
                </div>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600 mt-1">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Support Tickets
                </a>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Notifications
                </a>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Referral Program
                </a>

                {{-- System --}}
                <div class="pt-4">
                    <span class="px-3 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">System</span>
                </div>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600 mt-1">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" stroke="currentColor" stroke-width="1.5"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/></svg>
                    Settings
                </a>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    API Keys
                </a>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Sessions & Security
                </a>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Audit Log
                </a>

                <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Telegram Integration
                </a>
            </nav>

            {{-- User --}}
            <div class="border-t border-gray-100 px-4 py-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-semibold text-sm">
                        {{ strtoupper(substr(Auth::user()->email, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-400">Administrator</p>
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ Auth::user()->email }}</p>
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
            <header class="sticky top-0 z-30 h-14 border-b border-gray-200 bg-white flex items-center justify-between px-6">
                <div class="flex items-center gap-4">
                    <h2 class="text-sm font-semibold text-gray-700">@yield('title', 'Dashboard')</h2>
                    <div class="relative ml-4">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        <input type="text" placeholder="Search users, campaigns..." class="pl-10 pr-4 py-2 w-72 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                </div>
                <div class="flex items-center gap-3">
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
                            <span class="text-sm font-medium text-gray-700">{{ Auth::user()->email }}</span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none"><path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-2 w-56 bg-white rounded-xl border border-gray-200 shadow-lg py-1 z-50" style="display: none;">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->email }}</p>
                                <p class="text-xs text-gray-400 mt-0.5 capitalize">{{ Auth::user()->role ?? 'admin' }}</p>
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
                                <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                Audit Log
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
                <span>copyright &copy; {{ date('Y') }} AdShqip — Admin Panel</span>
                <div class="flex items-center gap-4">
                    <a href="#" class="hover:text-gray-600">API Docs</a>
                    <a href="#" class="hover:text-gray-600">Status</a>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-brand-500"></span> All systems operational</span>
                </div>
            </footer>
        </div>
    </div>

    <script>
        function toggleSubmenu(id) {
            document.getElementById(id).classList.toggle('hidden');
        }
    </script>
    @stack('scripts')
</body>
</html>
