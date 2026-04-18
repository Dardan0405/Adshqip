@extends('layouts.admin')

@section('title', 'Personal Information')

@section('content')
    @php
        $profile = $user->profile;
        $initials = strtoupper(substr($user->email, 0, 2));
        $displayName = trim(($profile?->first_name ?? '') . ' ' . ($profile?->last_name ?? '')) ?: $user->email;
    @endphp

    <div class="space-y-6">
        <div class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-gradient-to-br from-slate-950 via-slate-900 to-sky-900 px-6 py-7 text-white shadow-sm">
            <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(56,189,248,0.32),_transparent_55%)]"></div>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-sky-100">Personal Profile</span>
                    <h1 class="mt-4 text-3xl font-semibold tracking-tight">Keep your personal details complete and ready across the admin workspace.</h1>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-slate-200/85">
                        Update the personal information shown for your admin account, including your name, birth date, location, and mobile contact details.
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
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-300">{{ ucfirst($user->role ?? 'admin') }}</p>
                        <p class="mt-1 text-lg font-semibold">{{ $displayName }}</p>
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

        <form method="POST" action="{{ route('admin.personal-information.update') }}" class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.4fr)_360px]">
            @csrf
            @method('PUT')

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Personal details</h2>
                        <p class="mt-1 text-sm text-slate-500">These details help identify the admin account across the platform.</p>
                    </div>
                    <div class="rounded-2xl bg-sky-50 px-3 py-2 text-xs font-medium text-sky-700">
                        Profile record: {{ $profile ? 'Connected' : 'Will be created on save' }}
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">First Name</label>
                        <input type="text" name="first_name" value="{{ old('first_name', $profile?->first_name) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-100" placeholder="Enter first name">
                        @error('first_name') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Last Name</label>
                        <input type="text" name="last_name" value="{{ old('last_name', $profile?->last_name) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-100" placeholder="Enter last name">
                        @error('last_name') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Gender</label>
                        <select name="gender" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-100">
                            <option value="">Select gender</option>
                            @foreach($genders as $value => $label)
                                <option value="{{ $value }}" {{ old('gender', $profile?->gender) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('gender') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $profile?->date_of_birth) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-100">
                        @error('date_of_birth') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Mobile Number</label>
                        <input type="text" name="mobile_number" value="{{ old('mobile_number', $profile?->mobile_number ?? $profile?->phone) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-100" placeholder="+355 69 123 4567">
                        @error('mobile_number') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">City</label>
                        <input type="text" name="city" value="{{ old('city', $profile?->city) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-100" placeholder="Enter city">
                        @error('city') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">State</label>
                        <input type="text" name="state_region" value="{{ old('state_region', $profile?->state_region) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-100" placeholder="Enter state or region">
                        @error('state_region') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Country</label>
                        <select name="country_code" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-100">
                            <option value="">Select country</option>
                            @foreach($countries as $code => $label)
                                <option value="{{ $code }}" {{ old('country_code', $profile?->country_code) === $code ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('country_code') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <div class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-5">
                        <h2 class="text-lg font-semibold text-slate-900">Profile summary</h2>
                        <p class="mt-1 text-sm text-slate-500">A quick view of the identity details attached to this account.</p>
                    </div>

                    <div class="rounded-3xl bg-slate-950 p-5 text-white">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Admin identity</p>
                        <p class="mt-3 text-2xl font-semibold">{{ $displayName }}</p>
                        <div class="mt-5 space-y-3 text-sm text-slate-300">
                            <div class="flex items-center justify-between gap-4">
                                <span>Gender</span>
                                <span class="font-medium text-white">{{ $genders[$profile?->gender] ?? 'Not set' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Mobile</span>
                                <span class="font-medium text-white">{{ $profile?->mobile_number ?? $profile?->phone ?? 'Not set' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Country</span>
                                <span class="font-medium text-white">{{ $countries[$profile?->country_code] ?? 'Not set' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 rounded-2xl bg-sky-50 p-4 text-sm text-sky-900">
                        Keep these fields current so your admin record stays complete for future settings, approvals, and account-level activity.
                    </div>

                    <div class="mt-5">
                        <a href="{{ route('admin.company-information') }}" class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Company Information
                        </a>
                    </div>
                </section>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                    Save Personal Information
                </button>
            </div>
        </form>
    </div>
@endsection
