@extends('layouts.publisher')

@section('title', 'Account Settings')

@section('content')
    @php
        $profile  = $user->profile;
        $initials = strtoupper(substr($user->email, 0, 2));
    @endphp

    <div class="space-y-6">
        {{-- Hero banner --}}
        <div class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-gradient-to-br from-slate-950 via-slate-900 to-rose-900 px-6 py-7 text-white shadow-sm">
            <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(251,113,133,0.35),_transparent_52%)]"></div>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-rose-100">Basic Information</span>
                    <h1 class="mt-4 text-3xl font-semibold tracking-tight">Manage your account, security, and profile identity.</h1>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-slate-200/85">
                        Keep your main email up to date, choose the profile image used across the dashboard, set your currency view, and protect access with password updates and 2-factor authentication.
                    </p>
                </div>
                <div class="flex items-center gap-4 rounded-3xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                    @if($profile?->avatar_url)
                        <img src="{{ $profile->avatar_url }}" alt="Profile picture" class="h-20 w-20 rounded-2xl object-cover ring-2 ring-white/20">
                    @else
                        <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-white/15 text-2xl font-bold text-white ring-2 ring-white/20">
                            {{ $initials }}
                        </div>
                    @endif
                    <div>
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-300">{{ ucfirst($user->role ?? 'publisher') }}</p>
                        <p class="mt-1 text-lg font-semibold">{{ trim(($profile?->first_name ?? '') . ' ' . ($profile?->last_name ?? '')) ?: $user->email }}</p>
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
                Please review the highlighted fields and try again.
            </div>
        @endif

        <form method="POST" action="{{ route('publisher.account-settings.update') }}" enctype="multipart/form-data" class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            @csrf
            @method('PUT')

            {{-- Left: Profile + Security --}}
            <div class="space-y-6 xl:col-span-2">

                {{-- Profile details --}}
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-6 flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Profile details</h2>
                            <p class="mt-1 text-sm text-slate-500">This information is used in your account and publisher identity.</p>
                        </div>
                        <div class="rounded-2xl bg-rose-50 px-3 py-2 text-xs font-medium text-rose-700">
                            Last login: {{ $user->last_login_at?->format('M d, Y H:i') ?? 'Never' }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                   class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-rose-400 focus:outline-none focus:ring-4 focus:ring-rose-100">
                            @error('email') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Alternative Email</label>
                            <input type="email" name="alternative_email" value="{{ old('alternative_email', $profile?->alternative_email) }}"
                                   class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-rose-400 focus:outline-none focus:ring-4 focus:ring-rose-100"
                                   placeholder="backup@example.com">
                            @error('alternative_email') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                {{-- Security --}}
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-slate-900">Security</h2>
                        <p class="mt-1 text-sm text-slate-500">Change your password and manage 2-factor authentication.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="inline-flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <input type="checkbox" name="two_factor_enabled" value="1"
                                       {{ old('two_factor_enabled', $user->two_factor_enabled) ? 'checked' : '' }}
                                       class="mt-1 h-4 w-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                <span>
                                    <span class="block text-sm font-semibold text-slate-900">Enable 2 Factor Authentication</span>
                                    <span class="mt-1 block text-xs leading-5 text-slate-500">When enabled, sign in will require a valid authenticator app code or one of your backup codes.</span>
                                </span>
                            </label>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Current Password</label>
                            <input type="password" name="current_password"
                                   class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-rose-400 focus:outline-none focus:ring-4 focus:ring-rose-100">
                            @error('current_password') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div></div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">New Password</label>
                            <input type="password" name="new_password"
                                   class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-rose-400 focus:outline-none focus:ring-4 focus:ring-rose-100">
                            @error('new_password') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Confirm Password</label>
                            <input type="password" name="new_password_confirmation"
                                   class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-rose-400 focus:outline-none focus:ring-4 focus:ring-rose-100">
                        </div>
                    </div>

                    {{-- 2FA setup box --}}
                    <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900">Authenticator app setup</h3>
                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    Add this account to Google Authenticator, Microsoft Authenticator, 1Password, or any TOTP app using the setup key below.
                                </p>
                            </div>
                            @if($user->two_factor_enabled)
                                <label class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700">
                                    <input type="checkbox" name="regenerate_backup_codes" value="1" class="h-4 w-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                    Regenerate backup codes
                                </label>
                            @endif
                        </div>

                        @if($twoFactorSecret)
                            <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
                                <div class="rounded-2xl bg-white p-4 shadow-sm">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">Setup Key</p>
                                    <p class="mt-3 break-all rounded-2xl bg-slate-950 px-4 py-3 font-mono text-sm tracking-[0.24em] text-emerald-300">{{ $twoFactorSecret }}</p>
                                    <p class="mt-3 text-xs leading-5 text-slate-500">Enter this exact key in your authenticator app if setting it up manually.</p>
                                </div>
                                <div class="rounded-2xl bg-white p-4 shadow-sm">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">Authenticator URI</p>
                                    <textarea readonly rows="6" class="mt-3 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600 focus:outline-none">{{ $setupUri }}</textarea>
                                    <p class="mt-3 text-xs leading-5 text-slate-500">Advanced apps can import this URI directly.</p>
                                </div>
                            </div>
                        @else
                            <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-4 text-sm text-slate-500">
                                Enable 2 Factor Authentication and save this page to generate your setup key and backup codes.
                            </div>
                        @endif

                        @if(!empty($backupCodes))
                            <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                                <p class="text-sm font-semibold text-amber-900">Backup codes</p>
                                <p class="mt-1 text-xs leading-5 text-amber-800">Store these in a safe place. Each code works once and is only shown here.</p>
                                <div class="mt-4 grid grid-cols-2 gap-3">
                                    @foreach($backupCodes as $backupCode)
                                        <div class="rounded-xl bg-white px-3 py-2 text-center font-mono text-sm font-semibold tracking-[0.2em] text-slate-900 shadow-sm">
                                            {{ $backupCode }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @elseif($user->two_factor_enabled)
                            <div class="mt-4 rounded-2xl border border-slate-200 bg-white px-4 py-4 text-sm text-slate-600">
                                <span class="font-semibold text-slate-900">{{ $backupCodeCount }}</span> backup codes available. Check "Regenerate backup codes" and save if you want a fresh set.
                            </div>
                        @endif
                    </div>
                </section>
            </div>

            {{-- Right: Avatar + Preferences --}}
            <div class="space-y-6">

                {{-- Profile picture --}}
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-5">
                        <h2 class="text-lg font-semibold text-slate-900">Profile picture</h2>
                        <p class="mt-1 text-sm text-slate-500">Select the image used for your profile across the dashboard.</p>
                    </div>
                    <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-5 text-center" x-data="avatarPreview()">
                        <div class="relative mx-auto h-28 w-28">
                            @if($profile?->avatar_url)
                                <img id="avatarCurrentImg" src="{{ $profile->avatar_url }}" alt="Current profile image"
                                     class="h-28 w-28 rounded-3xl object-cover shadow-sm"
                                     :class="previewUrl ? 'hidden' : ''">
                            @else
                                <div id="avatarInitials"
                                     class="flex h-28 w-28 items-center justify-center rounded-3xl bg-gradient-to-br from-rose-500 to-orange-400 text-3xl font-bold text-white shadow-sm"
                                     :class="previewUrl ? 'hidden' : ''">
                                    {{ $initials }}
                                </div>
                            @endif
                            <img x-show="previewUrl" :src="previewUrl" alt="Preview"
                                 class="mx-auto h-28 w-28 rounded-3xl object-cover shadow-sm" style="display:none;">
                        </div>
                        <div class="mt-4">
                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Choose Image
                                <input type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp" class="sr-only"
                                       @change="onFileChange($event)">
                            </label>
                            <p class="mt-2 text-xs text-slate-400" x-text="fileName || 'PNG, JPG, or WEBP up to 3 MB'"></p>
                            @error('avatar') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                {{-- Preferences --}}
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-5">
                        <h2 class="text-lg font-semibold text-slate-900">Preferences</h2>
                        <p class="mt-1 text-sm text-slate-500">Set how monetary values appear across the dashboard.</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Currency View</label>
                        <select name="currency" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-rose-400 focus:outline-none focus:ring-4 focus:ring-rose-100">
                            @foreach($currencies as $code => $label)
                                <option value="{{ $code }}" {{ old('currency', $profile?->currency ?? 'EUR') === $code ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('currency') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="mt-5 rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Current status</p>
                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-sm text-slate-600">2FA</span>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $user->two_factor_enabled ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $user->two_factor_enabled ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-sm text-slate-600">Role</span>
                            <span class="text-sm font-semibold text-slate-900">{{ ucfirst($user->role ?? 'publisher') }}</span>
                        </div>
                    </div>
                </section>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                    Save Account Settings
                </button>

                <a href="{{ route('publisher.personal-information') }}" class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Personal Information
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    function avatarPreview() {
        return {
            previewUrl: null,
            fileName: null,
            onFileChange(event) {
                const file = event.target.files[0];
                if (!file) return;
                this.fileName = file.name;
                const reader = new FileReader();
                reader.onload = (e) => { this.previewUrl = e.target.result; };
                reader.readAsDataURL(file);
            }
        };
    }
</script>
@endpush
