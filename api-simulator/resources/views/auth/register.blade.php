<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Get Started — Adshqip</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        (function(){try{const s=localStorage.getItem('adshqip-theme');const d=window.matchMedia('(prefers-color-scheme:dark)').matches;if(s==='dark'||(!s&&d))document.documentElement.classList.add('dark');else document.documentElement.classList.remove('dark');}catch(_){}})();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config={darkMode:'class',theme:{extend:{fontFamily:{sans:['Inter','ui-sans-serif','system-ui']},colors:{brand:{50:'#fff1f2',100:'#ffe4e6',200:'#fecdd3',300:'#fda4af',400:'#fb7185',500:'#f43f5e',600:'#e11d48',700:'#be123c',800:'#9f1239',900:'#881337',950:'#4c0519'}},boxShadow:{glow:'0 0 0 1px rgb(244 63 94/.25),0 10px 40px -10px rgb(244 63 94/.45)','glow-lg':'0 0 0 1px rgb(244 63 94/.3),0 20px 60px -15px rgb(244 63 94/.5)'},backgroundImage:{'radial-fade-lg':'radial-gradient(70% 70% at 50% 0%,rgba(244,63,94,.3),transparent 70%)','grid-dark':'linear-gradient(to right,rgba(255,255,255,.06) 1px,transparent 1px),linear-gradient(to bottom,rgba(255,255,255,.06) 1px,transparent 1px)','grid-light':'linear-gradient(to right,rgba(0,0,0,.06) 1px,transparent 1px),linear-gradient(to bottom,rgba(0,0,0,.06) 1px,transparent 1px)'}}}}
    </script>
    <style>
        .text-gradient{background-clip:text;-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-image:linear-gradient(90deg,#e11d48,#f43f5e,#f59e0b,#e11d48);background-size:200% auto;animation:gx 8s ease infinite}
        @keyframes gx{0%,100%{background-position:left center}50%{background-position:right center}}
        ::-webkit-scrollbar{width:10px}::-webkit-scrollbar-thumb{background:#e11d48;border-radius:999px;border:2px solid transparent;background-clip:content-box}::-webkit-scrollbar-track{background:transparent}
        .loading-dots{display:inline-flex}.loading-dots span{width:.5rem;height:.5rem;border-radius:50%;margin:0 .125rem;background:currentColor;animation:ld 1.4s infinite ease-in-out both}.loading-dots span:nth-child(1){animation-delay:-.32s}.loading-dots span:nth-child(2){animation-delay:-.16s}@keyframes ld{0%,80%,100%{transform:scale(0)}40%{transform:scale(1)}}
    </style>
</head>
<body class="bg-white dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 antialiased selection:bg-brand-500/30">

    {{-- BG layers --}}
    <div class="pointer-events-none fixed inset-0 -z-10">
        <div class="absolute inset-0 bg-radial-fade-lg"></div>
        <div class="absolute inset-0" style="background-image:radial-gradient(800px 400px at 10% 10%,rgba(225,29,72,.15),transparent 50%),radial-gradient(600px 350px at 90% 10%,rgba(225,29,72,.12),transparent 50%)"></div>
        <div class="absolute inset-0 dark:opacity-40 opacity-[.07] bg-grid-light dark:bg-grid-dark bg-[length:32px_32px]"></div>
    </div>

    {{-- ═══════════ HEADER / NAVBAR ═══════════ --}}
    <header class="sticky top-0 z-50 border-b border-zinc-200/60 dark:border-zinc-800/60 bg-white/70 dark:bg-zinc-950/60 backdrop-blur">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-8">
                <a href="/" class="flex items-center gap-2 group">
                    <span class="relative inline-flex items-center justify-center w-8 h-8 rounded-lg bg-brand-600 text-white shadow-glow">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 133 102" class="w-6 h-auto"><image width="133" height="102" href="{{ asset('AdshqipSVG.svg') }}"></image></svg>
                    </span>
                    <span class="font-extrabold text-lg tracking-tight">Adshqip</span>
                    <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-brand-500/10 text-brand-600 border border-brand-500/20">AL</span>
                </a>
                <nav class="hidden lg:flex items-center gap-1">
                    <a href="/#formats" class="px-3 py-2 rounded-lg text-sm font-medium hover:text-brand-600 hover:bg-brand-50/50 dark:hover:bg-white/5 transition-colors">Ad Formats</a>
                    <a href="/#solutions" class="px-3 py-2 rounded-lg text-sm font-medium hover:text-brand-600 hover:bg-brand-50/50 dark:hover:bg-white/5 transition-colors">Solutions</a>
                    <a href="/#integrations" class="px-3 py-2 rounded-lg text-sm font-medium hover:text-brand-600 hover:bg-brand-50/50 dark:hover:bg-white/5 transition-colors">SDK & API</a>
                    <a href="/#pricing" class="px-3 py-2 rounded-lg text-sm font-medium hover:text-brand-600 hover:bg-brand-50/50 dark:hover:bg-white/5 transition-colors">Pricing</a>
                </nav>
            </div>
            <div class="flex items-center gap-2">
                <button id="themeToggle" class="p-2 rounded-lg hover:bg-zinc-950/5 dark:hover:bg-white/5" aria-label="Toggle theme">
                    <svg class="w-5 h-5 text-zinc-700 dark:hidden" viewBox="0 0 24 24" fill="none"><path d="M12 4V2m0 20v-2M4 12H2m20 0h-2M5.64 5.64 4.22 4.22m15.56 15.56-1.42-1.42M18.36 5.64l1.42-1.42M4.22 19.78l1.42-1.42" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.5"/></svg>
                    <svg class="w-5 h-5 text-zinc-200 hidden dark:block" viewBox="0 0 24 24" fill="none"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
                <a href="{{ route('signin') }}" class="hidden sm:inline-flex text-sm px-3 py-1.5 rounded-lg border border-brand-600 bg-brand-600/10 text-brand-600">Sign in</a>
                <a href="{{ route('register') }}" class="hidden sm:inline-flex text-sm px-3 py-1.5 rounded-lg bg-brand-600 text-white hover:bg-brand-700 shadow-glow transition-colors">Get started</a>
                <button id="mobileMenuBtn" class="lg:hidden p-2 rounded-lg hover:bg-zinc-950/5 dark:hover:bg-white/5"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg></button>
            </div>
        </div>
        <div id="mobileMenu" class="hidden lg:hidden border-t border-zinc-200 dark:border-zinc-800 bg-white/90 dark:bg-zinc-950/90 backdrop-blur">
            <div class="px-4 py-3 space-y-2 text-sm">
                <a href="/#formats" class="block px-3 py-2 rounded hover:bg-brand-500/5">Ad Formats</a>
                <a href="/#solutions" class="block px-3 py-2 rounded hover:bg-brand-500/5">Solutions</a>
                <a href="/#pricing" class="block px-3 py-2 rounded hover:bg-brand-500/5">Pricing</a>
                <div class="pt-2 mt-2 border-t border-zinc-200 dark:border-zinc-800 flex items-center gap-2">
                    <a href="{{ route('signin') }}" class="flex-1 text-center px-3 py-2 rounded-lg border border-brand-600 bg-brand-600/10 text-brand-600">Sign in</a>
                    <a href="{{ route('register') }}" class="flex-1 text-center px-3 py-2 rounded-lg bg-brand-600 text-white hover:bg-brand-700 shadow-glow">Get started</a>
                </div>
            </div>
        </div>
    </header>

    {{-- ═══════════ REGISTER FORM ═══════════ --}}
    <main class="min-h-[calc(100vh-4rem-16rem)] flex items-center justify-center py-12 sm:py-20 px-4">
        <div class="w-full max-w-lg">
            <div class="relative p-8 sm:p-10 rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl shadow-xl">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-600 shadow-glow mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 133 102" class="w-10 h-auto"><image width="133" height="102" href="{{ asset('AdshqipSVG.svg') }}"></image></svg>
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight">Create your account</h1>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Start growing with Adshqip today</p>
                </div>

                {{-- Error --}}
                @if ($errors->any())
                <div class="mb-5 p-3 rounded-xl border border-red-300 dark:border-red-800 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-sm flex items-start gap-2">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none"><path d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <div>
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Success (from session flash) --}}
                @if (session('success'))
                <div class="mb-5 p-3 rounded-xl border border-emerald-300 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 text-sm flex items-start gap-2">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                @endif

                <form method="POST" action="{{ route('register.submit') }}" class="space-y-5">
                    @csrf

                    {{-- Role selection --}}
                    <div>
                        <label class="block text-sm font-medium mb-2">I want to</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative cursor-pointer">
                                <input type="radio" name="role" value="advertiser" class="peer sr-only" {{ old('role', 'advertiser') === 'advertiser' ? 'checked' : '' }}>
                                <div class="flex items-center gap-3 p-3.5 rounded-xl border-2 border-zinc-200 dark:border-zinc-700 peer-checked:border-brand-500 peer-checked:bg-brand-500/5 transition-all">
                                    <div class="w-10 h-10 rounded-lg bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-brand-600" viewBox="0 0 24 24" fill="none"><path d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold">Advertiser</div>
                                        <div class="text-xs text-zinc-400">Run ad campaigns</div>
                                    </div>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="role" value="publisher" class="peer sr-only" {{ old('role') === 'publisher' ? 'checked' : '' }}>
                                <div class="flex items-center gap-3 p-3.5 rounded-xl border-2 border-zinc-200 dark:border-zinc-700 peer-checked:border-brand-500 peer-checked:bg-brand-500/5 transition-all">
                                    <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="none"><path d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold">Publisher</div>
                                        <div class="text-xs text-zinc-400">Monetize your site</div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Name fields --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="first_name" class="block text-sm font-medium mb-1.5">First name</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                    <svg class="w-5 h-5 text-zinc-400" viewBox="0 0 24 24" fill="none"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                </div>
                                <input type="text" id="first_name" name="first_name" required value="{{ old('first_name') }}" placeholder="John" class="w-full pl-11 pr-4 py-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-brand-500/40 focus:border-brand-500 text-sm transition-all placeholder-zinc-400">
                            </div>
                        </div>
                        <div>
                            <label for="last_name" class="block text-sm font-medium mb-1.5">Last name</label>
                            <input type="text" id="last_name" name="last_name" required value="{{ old('last_name') }}" placeholder="Doe" class="w-full px-4 py-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-brand-500/40 focus:border-brand-500 text-sm transition-all placeholder-zinc-400">
                        </div>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium mb-1.5">Email address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                <svg class="w-5 h-5 text-zinc-400" viewBox="0 0 24 24" fill="none"><path d="M4 6h16v12H4zM22 6l-10 7L2 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                            <input type="email" id="email" name="email" required value="{{ old('email') }}" autocomplete="email" placeholder="you@example.com" class="w-full pl-11 pr-4 py-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-brand-500/40 focus:border-brand-500 text-sm transition-all placeholder-zinc-400">
                        </div>
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium mb-1.5">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                <svg class="w-5 h-5 text-zinc-400" viewBox="0 0 24 24" fill="none"><rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M7 11V7a5 5 0 0110 0v4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            </div>
                            <input type="password" id="password" name="password" required autocomplete="new-password" placeholder="Min 8 characters" class="w-full pl-11 pr-12 py-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-brand-500/40 focus:border-brand-500 text-sm transition-all placeholder-zinc-400">
                            <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                                <svg id="eyeOff" class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                <svg id="eyeOn" class="w-5 h-5 hidden" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium mb-1.5">Confirm password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                <svg class="w-5 h-5 text-zinc-400" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            </div>
                            <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" placeholder="Repeat password" class="w-full pl-11 pr-4 py-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-brand-500/40 focus:border-brand-500 text-sm transition-all placeholder-zinc-400">
                        </div>
                    </div>

                    {{-- Company (optional) --}}
                    <div>
                        <label for="company_name" class="block text-sm font-medium mb-1.5">Company name <span class="text-zinc-400 font-normal">(optional)</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                <svg class="w-5 h-5 text-zinc-400" viewBox="0 0 24 24" fill="none"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2m-16 0H3m2 0h14M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            </div>
                            <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" placeholder="Your company" class="w-full pl-11 pr-4 py-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-brand-500/40 focus:border-brand-500 text-sm transition-all placeholder-zinc-400">
                        </div>
                    </div>

                    {{-- Website (optional, shown for publisher) --}}
                    <div id="websiteField">
                        <label for="website_url" class="block text-sm font-medium mb-1.5">Website URL <span class="text-zinc-400 font-normal">(optional)</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                <svg class="w-5 h-5 text-zinc-400" viewBox="0 0 24 24" fill="none"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            </div>
                            <input type="url" id="website_url" name="website_url" value="{{ old('website_url') }}" placeholder="https://yoursite.com" class="w-full pl-11 pr-4 py-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-brand-500/40 focus:border-brand-500 text-sm transition-all placeholder-zinc-400">
                        </div>
                    </div>

                    {{-- Country --}}
                    <div>
                        <label for="country_code" class="block text-sm font-medium mb-1.5">Country</label>
                        <select id="country_code" name="country_code" class="w-full px-4 py-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-brand-500/40 focus:border-brand-500 text-sm transition-all">
                            <option value="AL" {{ old('country_code', 'AL') === 'AL' ? 'selected' : '' }}>🇦🇱 Albania</option>
                            <option value="XK" {{ old('country_code') === 'XK' ? 'selected' : '' }}>🇽🇰 Kosovo</option>
                            <option value="MK" {{ old('country_code') === 'MK' ? 'selected' : '' }}>🇲🇰 North Macedonia</option>
                            <option value="ME" {{ old('country_code') === 'ME' ? 'selected' : '' }}>🇲🇪 Montenegro</option>
                            <option value="RS" {{ old('country_code') === 'RS' ? 'selected' : '' }}>🇷🇸 Serbia</option>
                            <option value="BA" {{ old('country_code') === 'BA' ? 'selected' : '' }}>🇧🇦 Bosnia & Herzegovina</option>
                            <option value="HR" {{ old('country_code') === 'HR' ? 'selected' : '' }}>🇭🇷 Croatia</option>
                            <option value="SI" {{ old('country_code') === 'SI' ? 'selected' : '' }}>🇸🇮 Slovenia</option>
                            <option value="DE" {{ old('country_code') === 'DE' ? 'selected' : '' }}>🇩🇪 Germany</option>
                            <option value="CH" {{ old('country_code') === 'CH' ? 'selected' : '' }}>🇨🇭 Switzerland</option>
                            <option value="AT" {{ old('country_code') === 'AT' ? 'selected' : '' }}>🇦🇹 Austria</option>
                            <option value="IT" {{ old('country_code') === 'IT' ? 'selected' : '' }}>🇮🇹 Italy</option>
                            <option value="US" {{ old('country_code') === 'US' ? 'selected' : '' }}>🇺🇸 United States</option>
                            <option value="GB" {{ old('country_code') === 'GB' ? 'selected' : '' }}>🇬🇧 United Kingdom</option>
                        </select>
                    </div>

                    {{-- Terms --}}
                    <div class="flex items-start gap-2">
                        <input type="checkbox" id="terms" name="terms" required class="w-4 h-4 mt-0.5 rounded border-zinc-300 dark:border-zinc-600 text-brand-600 focus:ring-brand-500/30">
                        <label for="terms" class="text-sm text-zinc-600 dark:text-zinc-400 select-none">
                            I agree to the <a href="#" class="text-brand-600 hover:text-brand-700">Terms of Service</a> and <a href="#" class="text-brand-600 hover:text-brand-700">Privacy Policy</a>
                        </label>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-brand-600 text-white font-semibold hover:bg-brand-700 shadow-glow transition-all duration-300 hover:-translate-y-0.5 hover:shadow-glow-lg">
                        <span>Create account</span>
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M5 12h14m0 0l-5-5m5 5l-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                </form>

                <div class="mt-6 flex items-center gap-3"><div class="flex-1 h-px bg-zinc-200 dark:bg-zinc-800"></div><span class="text-xs text-zinc-400">or</span><div class="flex-1 h-px bg-zinc-200 dark:bg-zinc-800"></div></div>
                <p class="mt-6 text-center text-sm text-zinc-500 dark:text-zinc-400">Already have an account? <a href="{{ route('signin') }}" class="font-semibold text-brand-600 hover:text-brand-700">Sign in</a></p>
            </div>
        </div>
    </main>

    {{-- ═══════════ FOOTER ═══════════ --}}
    <footer class="border-t border-zinc-200 dark:border-zinc-800 py-12 sm:py-16 bg-zinc-50/50 dark:bg-black/20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-12 gap-8">
                <div class="md:col-span-4">
                    <a href="/" class="flex items-center gap-2"><span class="font-extrabold text-xl tracking-tight"><img src="{{ asset('AdshqipSVG.svg') }}" alt="Adshqip" class="h-8"></span></a>
                    <p class="mt-4 text-zinc-600 dark:text-zinc-300 max-w-md">The Albanian ad network built for performance and great UX. Serving advertisers and publishers across Albania, Kosovo, North Macedonia and the entire Balkan region.</p>
                    <div class="mt-6 flex items-center gap-4">
                        <a href="#" class="p-2 rounded-lg border border-zinc-200 dark:border-zinc-800 hover:bg-zinc-950/5 dark:hover:bg-white/5 transition-colors"><svg class="w-5 h-5 text-zinc-600 dark:text-zinc-400" viewBox="0 0 24 24" fill="currentColor"><path d="M22.46 6c-.77.35-1.6.58-2.46.69a4.27 4.27 0 0 0-7.28 3.89A12.12 12.12 0 0 1 3 5.15a4.25 4.25 0 0 0 1.32 5.68 4.22 4.22 0 0 1-1.94-.54v.06a4.27 4.27 0 0 0 3.42 4.18 4.3 4.3 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98A8.56 8.56 0 0 1 2 19.54a12.07 12.07 0 0 0 6.56 1.92c7.88 0 12.2-6.55 12.2-12.23 0-.19 0-.38-.01-.57A8.75 8.75 0 0 0 24 5.3a8.56 8.56 0 0 1-2.54.7z"/></svg></a>
                        <a href="#" class="p-2 rounded-lg border border-zinc-200 dark:border-zinc-800 hover:bg-zinc-950/5 dark:hover:bg-white/5 transition-colors"><svg class="w-5 h-5 text-zinc-600 dark:text-zinc-400" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg></a>
                        <a href="mailto:hello@adshqip.com" class="p-2 rounded-lg border border-zinc-200 dark:border-zinc-800 hover:bg-zinc-950/5 dark:hover:bg-white/5 transition-colors"><svg class="w-5 h-5 text-zinc-600 dark:text-zinc-400" viewBox="0 0 24 24" fill="none"><path d="M4 6h16v12H4z" stroke="currentColor" stroke-width="1.5"/><path d="M22 6l-10 7L2 6" stroke="currentColor" stroke-width="1.5"/></svg></a>
                    </div>
                </div>
                <div class="md:col-span-2">
                    <div class="text-sm font-semibold mb-4">Product</div>
                    <ul class="space-y-3 text-sm text-zinc-600 dark:text-zinc-300">
                        <li><a href="/#formats" class="hover:text-brand-600 transition-colors">Ad Formats</a></li>
                        <li><a href="/#solutions" class="hover:text-brand-600 transition-colors">For Advertisers</a></li>
                        <li><a href="/#solutions" class="hover:text-brand-600 transition-colors">For Publishers</a></li>
                        <li><a href="/#pricing" class="hover:text-brand-600 transition-colors">Pricing</a></li>
                    </ul>
                </div>
                <div class="md:col-span-2">
                    <div class="text-sm font-semibold mb-4">Resources</div>
                    <ul class="space-y-3 text-sm text-zinc-600 dark:text-zinc-300">
                        <li><a href="#" class="hover:text-brand-600 transition-colors">Documentation</a></li>
                        <li><a href="#" class="hover:text-brand-600 transition-colors">API Reference</a></li>
                        <li><a href="#" class="hover:text-brand-600 transition-colors">Blog</a></li>
                    </ul>
                </div>
                <div class="md:col-span-2">
                    <div class="text-sm font-semibold mb-4">Company</div>
                    <ul class="space-y-3 text-sm text-zinc-600 dark:text-zinc-300">
                        <li><a href="#" class="hover:text-brand-600 transition-colors">About</a></li>
                        <li><a href="#" class="hover:text-brand-600 transition-colors">Contact</a></li>
                        <li><a href="#" class="hover:text-brand-600 transition-colors">Careers</a></li>
                    </ul>
                </div>
                <div class="md:col-span-2">
                    <div class="text-sm font-semibold mb-4">Legal</div>
                    <ul class="space-y-3 text-sm text-zinc-600 dark:text-zinc-300">
                        <li><a href="#" class="hover:text-brand-600 transition-colors">Terms of Service</a></li>
                        <li><a href="#" class="hover:text-brand-600 transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-brand-600 transition-colors">GDPR Compliance</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 pt-8 border-t border-zinc-200 dark:border-zinc-800 text-center text-sm text-zinc-500">&copy; <span id="year"></span> Adshqip. All rights reserved.</div>
        </div>
    </footer>

    <script>
        const $=s=>document.querySelector(s);
        $('#year').textContent=new Date().getFullYear();
        $('#themeToggle').addEventListener('click',()=>{const r=document.documentElement;const d=r.classList.toggle('dark');localStorage.setItem('adshqip-theme',d?'dark':'light');});
        document.getElementById('mobileMenuBtn')?.addEventListener('click',()=>document.getElementById('mobileMenu').classList.toggle('hidden'));
        $('#togglePassword').addEventListener('click',()=>{const p=$('#password');const isPw=p.type==='password';p.type=isPw?'text':'password';$('#eyeOff').classList.toggle('hidden',!isPw);$('#eyeOn').classList.toggle('hidden',isPw);});
    </script>
</body>
</html>
