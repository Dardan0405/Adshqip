@extends('layouts.advertiser')

@section('title', 'Company Information')

@section('content')
    @php
        $profile = $user->profile;
        $initials = strtoupper(substr($user->email, 0, 2));
        $companyName = $profile?->company_name ?: 'Company details not set';
    @endphp

    <div class="space-y-6">
        <div class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-900 px-6 py-7 text-white shadow-sm">
            <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(16,185,129,0.32),_transparent_55%)]"></div>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-emerald-100">Company Profile</span>
                    <h1 class="mt-4 text-3xl font-semibold tracking-tight">Manage the business details connected to your admin account.</h1>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-slate-200/85">
                        Keep your company name and address information current so the account stays ready for invoices, billing records, and admin operations.
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
                        <p class="mt-1 text-lg font-semibold">{{ $companyName }}</p>
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

        <form method="POST" action="{{ route('advertiser.company-information.update') }}" class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.4fr)_360px]">
            @csrf
            @method('PUT')

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Company details</h2>
                        <p class="mt-1 text-sm text-slate-500">These fields describe the business information attached to this admin account.</p>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700">
                        Profile record: {{ $profile ? 'Connected' : 'Will be created on save' }}
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Company Name</label>
                        <input type="text" name="company_name" value="{{ old('company_name', $profile?->company_name) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-100" placeholder="Enter company name">
                        @error('company_name') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Website</label>
                        <input type="url" name="website_url" value="{{ old('website_url', $profile?->website_url) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-100" placeholder="https://example.com">
                        @error('website_url') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Address</label>
                        <input type="text" name="company_address_line1" value="{{ old('company_address_line1', $profile?->company_address_line1) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-100" placeholder="Street address">
                        @error('company_address_line1') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Number of Apartment</label>
                        <input type="text" name="company_address_line2" value="{{ old('company_address_line2', $profile?->company_address_line2) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-100" placeholder="Apartment, suite, or unit">
                        @error('company_address_line2') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">City</label>
                        <input type="text" name="company_city" value="{{ old('company_city', $profile?->company_city) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-100" placeholder="Enter city">
                        @error('company_city') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">State</label>
                        <input type="text" name="company_state_region" value="{{ old('company_state_region', $profile?->company_state_region) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-100" placeholder="Enter state or region">
                        @error('company_state_region') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Country</label>
                        <select name="company_country_code" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                            <option value="">Select country</option>
                            @foreach($countries as $code => $label)
                                <option value="{{ $code }}" {{ old('company_country_code', $profile?->company_country_code) === $code ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('company_country_code') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <div class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-5">
                        <h2 class="text-lg font-semibold text-slate-900">Company summary</h2>
                        <p class="mt-1 text-sm text-slate-500">A quick snapshot of the current business profile details.</p>
                    </div>

                    <div class="rounded-3xl bg-slate-950 p-5 text-white">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Business record</p>
                        <p class="mt-3 text-2xl font-semibold">{{ $companyName }}</p>
                        <div class="mt-5 space-y-3 text-sm text-slate-300">
                            <div class="flex items-center justify-between gap-4">
                                <span>Address</span>
                                <span class="text-right font-medium text-white">{{ $profile?->company_address_line1 ?: 'Not set' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Apartment</span>
                                <span class="font-medium text-white">{{ $profile?->company_address_line2 ?: 'Not set' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Country</span>
                                <span class="font-medium text-white">{{ $countries[$profile?->company_country_code] ?? 'Not set' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 space-y-3">
                        <a href="{{ route('advertiser.personal-information') }}" class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Back to Personal Information
                        </a>
                    </div>
                </section>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                    Save Company Information
                </button>
            </div>
        </form>
    </div>
@endsection
