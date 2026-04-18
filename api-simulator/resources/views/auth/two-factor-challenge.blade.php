<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Two Factor Authentication - Adshqip</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','ui-sans-serif','system-ui']},colors:{brand:{50:'#fff1f2',100:'#ffe4e6',200:'#fecdd3',300:'#fda4af',400:'#fb7185',500:'#f43f5e',600:'#e11d48',700:'#be123c',800:'#9f1239',900:'#881337'}}}}}
    </script>
</head>
<body class="min-h-screen bg-slate-950 font-sans text-white antialiased">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(244,63,94,0.22),_transparent_40%),radial-gradient(circle_at_bottom_right,_rgba(249,115,22,0.16),_transparent_32%)]"></div>

    <main class="relative flex min-h-screen items-center justify-center px-4 py-12">
        <div class="w-full max-w-md rounded-[28px] border border-white/10 bg-white/10 p-8 shadow-2xl backdrop-blur-xl">
            <div class="mb-8 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-600 shadow-lg shadow-brand-900/30">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none">
                        <path d="M12 3 4 7v5c0 5 3.5 8.5 8 9 4.5-.5 8-4 8-9V7l-8-4Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="m9.5 12 1.7 1.7 3.3-3.7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h1 class="mt-5 text-2xl font-semibold tracking-tight">Two Factor Authentication</h1>
                <p class="mt-2 text-sm leading-6 text-slate-300">
                    Complete verification for <span class="font-semibold text-white">{{ $user->email }}</span> using one of the enabled methods below.
                </p>
            </div>

            @if ($errors->any())
                <div class="mb-5 rounded-2xl border border-rose-400/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-100">
                    {{ $errors->first('code') }}
                </div>
            @endif

            @if (session('success'))
                <div class="mb-5 rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('two-factor.verify') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-200">Verification Method</label>
                    <div class="space-y-2">
                        @foreach($availableMethods as $method)
                            @php
                                $checked = old('method', $currentMethod) === $method;
                                $label = match ($method) {
                                    'email_otp' => 'Email Code',
                                    'sms' => 'SMS Code',
                                    'backup_code' => 'Backup Code / Recovery Answer',
                                    default => 'Authenticator App',
                                };
                            @endphp
                            <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-slate-950/30 px-4 py-3 text-sm text-slate-200">
                                <input type="radio" name="method" value="{{ $method }}" {{ $checked ? 'checked' : '' }} class="h-4 w-4 border-white/20 text-brand-500 focus:ring-brand-500">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                @if($backupQuestion)
                    <div class="rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm text-slate-300">
                        <span class="font-semibold text-white">Recovery question:</span> {{ $backupQuestion }}
                    </div>
                @endif

                <div>
                    <label for="code" class="mb-1.5 block text-sm font-medium text-slate-200">Code / Backup Code / Recovery Answer</label>
                    <input
                        id="code"
                        name="code"
                        type="text"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        placeholder="123456 or ABCD-EFGH"
                        value="{{ old('code') }}"
                        class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm tracking-[0.18em] text-white placeholder:text-slate-500 focus:border-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-500/15"
                    >
                </div>

                

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-brand-700">
                    Verify and continue
                </button>
            </form>

            <form method="POST" action="{{ route('two-factor.cancel') }}" class="mt-3">
                @csrf
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-semibold text-slate-200 transition hover:bg-white/10">
                    Cancel sign in
                </button>
            </form>

            <div class="mt-6 rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-4 text-xs leading-6 text-slate-300">
                Email and SMS codes expire automatically. Backup codes are single-use, and the recovery answer works only when backup recovery is enabled.
            </div>
        </div>
    </main>
</body>
</html>
