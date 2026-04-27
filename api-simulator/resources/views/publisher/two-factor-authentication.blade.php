@extends('layouts.publisher')

@section('title', '2 Factor Authentication')

@section('content')
    @php
        $initials      = strtoupper(substr($user->email, 0, 2));
        $profileAvatar = $user->profile?->avatar_url;
        $backupCodes   = session('two_factor_backup_codes', []);
    @endphp

    <div class="space-y-6">

        {{-- Hero banner --}}
        <div class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-gradient-to-br from-slate-950 via-slate-900 to-brand-900 px-6 py-7 text-white shadow-sm" style="--tw-gradient-to: #1e1b4b;">
            <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(244,63,94,0.28),_transparent_55%)]"></div>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-rose-100">Security Controls</span>
                    <h1 class="mt-4 text-3xl font-semibold tracking-tight">Control when 2FA should challenge and which verification methods can be used.</h1>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-slate-200/85">
                        Configure trusted-device checks, delivery channels, and recovery options for your publisher account.
                    </p>
                </div>
                <div class="flex items-center gap-4 rounded-3xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                    @if($profileAvatar)
                        <img src="{{ $profileAvatar }}" alt="Profile picture" class="h-20 w-20 rounded-2xl object-cover ring-2 ring-white/20">
                    @else
                        <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-white/15 text-2xl font-bold text-white ring-2 ring-white/20">
                            {{ $initials }}
                        </div>
                    @endif
                    <div>
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-300">2FA Profile</p>
                        <p class="mt-1 text-lg font-semibold">Verification Policy</p>
                        <p class="mt-1 text-sm text-slate-200/80">{{ $user->email }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                Please review the highlighted 2FA settings and try again.
            </div>
        @endif

        <form method="POST" action="{{ route('publisher.two-factor-authentication.update') }}" class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.4fr)_360px]">
            @csrf
            @method('PUT')

            {{-- Left: main settings --}}
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">

                {{-- Verification options --}}
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-slate-900">Verification Options</h2>
                    <p class="mt-1 text-sm text-slate-500">Choose which environment changes should force a 2FA challenge. If none are checked, 2FA will challenge on every sign in.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @foreach($verificationOptions as $value => $label)
                        <label class="flex items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4 cursor-pointer hover:bg-slate-50 transition-colors">
                            <input type="checkbox" name="verification_options[]" value="{{ $value }}"
                                   {{ in_array($value, old('verification_options', $enabledVerificationOptions), true) ? 'checked' : '' }}
                                   class="mt-1 h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                            <span>
                                <span class="block text-sm font-semibold text-slate-900">{{ $label }}</span>
                                <span class="mt-1 block text-sm text-slate-500">Trigger a challenge when this fingerprint does not match the last trusted successful 2FA sign in.</span>
                            </span>
                        </label>
                    @endforeach
                </div>

                {{-- Token types --}}
                <div class="mt-8 border-t border-slate-200 pt-6">
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-slate-900">Token Types</h2>
                        <p class="mt-1 text-sm text-slate-500">Choose which methods can be used during the challenge.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4">

                        {{-- SMS --}}
                        <label class="flex items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4 cursor-pointer hover:bg-slate-50 transition-colors">
                            <input type="checkbox" name="token_types[]" value="sms"
                                   {{ in_array('sms', old('token_types', $enabledTokenTypes), true) ? 'checked' : '' }}
                                   class="mt-1 h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                            <span class="flex-1">
                                <span class="block text-sm font-semibold text-slate-900">SMS</span>
                                <span class="mt-1 block text-sm text-slate-500">Send a six-digit code to the mobile number below.</span>
                                <input type="text" name="two_factor_phone" value="{{ old('two_factor_phone', $user->two_factor_phone) }}"
                                       class="mt-3 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-100"
                                       placeholder="+355 69 000 0000">
                                @error('two_factor_phone') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </span>
                        </label>

                        {{-- Email OTP --}}
                        <label class="flex items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4 cursor-pointer hover:bg-slate-50 transition-colors">
                            <input type="checkbox" name="token_types[]" value="email_otp"
                                   {{ in_array('email_otp', old('token_types', $enabledTokenTypes), true) ? 'checked' : '' }}
                                   class="mt-1 h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                            <span class="flex-1">
                                <span class="block text-sm font-semibold text-slate-900">Email</span>
                                <span class="mt-1 block text-sm text-slate-500">Send a six-digit code to the email address below.</span>
                                <input type="email" name="two_factor_email" value="{{ old('two_factor_email', $user->two_factor_email) }}"
                                       class="mt-3 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-100"
                                       placeholder="security@example.com">
                                @error('two_factor_email') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </span>
                        </label>

                        {{-- Backup code --}}
                        <label class="flex items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4 cursor-pointer hover:bg-slate-50 transition-colors">
                            <input type="checkbox" name="token_types[]" value="backup_code"
                                   {{ in_array('backup_code', old('token_types', $enabledTokenTypes), true) ? 'checked' : '' }}
                                   class="mt-1 h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                            <span class="flex-1">
                                <span class="block text-sm font-semibold text-slate-900">Backup code</span>
                                <span class="mt-1 block text-sm text-slate-500">Allow one-time backup codes and a recovery question/answer fallback.</span>
                                <input type="text" name="two_factor_backup_question" value="{{ old('two_factor_backup_question', $user->two_factor_backup_question) }}"
                                       class="mt-3 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-100"
                                       placeholder="Recovery question">
                                @error('two_factor_backup_question') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                                <input type="text" name="two_factor_backup_answer" value=""
                                       class="mt-3 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-100"
                                       placeholder="Recovery answer">
                                @error('two_factor_backup_answer') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                                <label class="mt-3 inline-flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                                    <input type="checkbox" name="regenerate_backup_codes" value="1" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                    Regenerate backup codes
                                </label>
                            </span>
                        </label>

                    </div>
                </div>
            </section>

            {{-- Right: summary sidebar --}}
            <div class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-5">
                        <h2 class="text-lg font-semibold text-slate-900">Current Behavior</h2>
                        <p class="mt-1 text-sm text-slate-500">A quick summary of the trusted-device state and recovery capacity.</p>
                    </div>

                    <div class="rounded-3xl bg-slate-950 p-5 text-white">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Trusted fingerprint</p>
                        <div class="mt-5 space-y-3 text-sm text-slate-300">
                            <div class="flex items-center justify-between gap-4">
                                <span>Trusted IP</span>
                                <span class="font-medium text-white">{{ $user->two_factor_trusted_ip ?: 'Not learned yet' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Trusted subnet</span>
                                <span class="font-medium text-white">{{ $user->two_factor_trusted_subnet ?: 'Not learned yet' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Trusted browser</span>
                                <span class="font-medium text-white">{{ $user->two_factor_trusted_browser ?: 'Not learned yet' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Trusted OS</span>
                                <span class="font-medium text-white">{{ $user->two_factor_trusted_os ?: 'Not learned yet' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Challenge methods</p>
                        <div class="mt-5 space-y-3 text-sm text-slate-600">
                            <div class="flex items-center justify-between gap-4">
                                <span>Enabled verification options</span>
                                <span class="font-medium text-slate-900">{{ count($enabledVerificationOptions) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Enabled token types</span>
                                <span class="font-medium text-slate-900">{{ count($enabledTokenTypes) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Unused backup codes</span>
                                <span class="font-medium text-slate-900">{{ $backupCodeCount }}</span>
                            </div>
                        </div>
                    </div>

                    @if($backupCodes !== [])
                        <div class="mt-4 rounded-3xl border border-amber-200 bg-amber-50 p-5">
                            <p class="text-xs uppercase tracking-[0.24em] text-amber-600">New backup codes</p>
                            <p class="mt-1 text-xs text-amber-700">Store these somewhere safe — they are only shown once.</p>
                            <div class="mt-4 grid grid-cols-2 gap-3 text-sm font-semibold text-amber-900">
                                @foreach($backupCodes as $code)
                                    <div class="rounded-2xl border border-amber-200 bg-white px-3 py-2 text-center">{{ $code }}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mt-4 rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Current request fingerprint</p>
                        <div class="mt-5 space-y-3 text-sm text-slate-600">
                            <div class="flex items-center justify-between gap-4">
                                <span>Current IP</span>
                                <span class="font-medium text-slate-900">{{ $debugTrustedContext['ip'] ?: 'Unknown' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Current subnet</span>
                                <span class="font-medium text-slate-900">{{ $debugTrustedContext['subnet'] ?: 'Unknown' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Current browser</span>
                                <span class="font-medium text-slate-900">{{ $debugTrustedContext['browser'] }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Current OS</span>
                                <span class="font-medium text-slate-900">{{ $debugTrustedContext['os'] }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                    Save 2 Factor Authentication
                </button>

                <a href="{{ route('publisher.account-settings') }}" class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Back to Account Settings
                </a>
            </div>
        </form>
    </div>
@endsection
