@extends('layouts.admin')

@section('title', 'Activity Log Settings')

@section('content')
    @php
        $initials = strtoupper(substr($user->email, 0, 2));
        $profileAvatar = $user->profile?->avatar_url;
    @endphp

    <div class="space-y-6">
        <div class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-900 px-6 py-7 text-white shadow-sm">
            <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(16,185,129,0.30),_transparent_55%)]"></div>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-emerald-100">Audit Controls</span>
                    <h1 class="mt-4 text-3xl font-semibold tracking-tight">Choose which admin activities should be written into the audit log.</h1>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-slate-200/85">
                        Enable or disable individual log events for campaigns, users, approvals, targeting, and account settings.
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
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-300">Monitoring</p>
                        <p class="mt-1 text-lg font-semibold">Activity Log Settings</p>
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

        <form method="POST" action="{{ route('admin.activity-log-settings.update') }}" class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.4fr)_360px]">
            @csrf
            @method('PUT')

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-slate-900">Log Event Sections</h2>
                </div>

                <div class="space-y-8">
                    @foreach($sections as $section)
                        <div class="border-t border-slate-200 pt-6 first:border-t-0 first:pt-0">
                            <div class="mb-5">
                                <h3 class="text-base font-semibold text-slate-900">{{ $section['title'] }}</h3>
                                <p class="mt-1 text-sm text-slate-500">Toggle the events inside this section individually.</p>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                @foreach($section['items'] as $key => $label)
                                    <label class="flex items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4">
                                        <input type="checkbox" name="activity_logs[{{ $key }}]" value="1" {{ old('activity_logs.' . $key, $settings[$key] ?? true) ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        <span>
                                            <span class="block text-sm font-semibold text-slate-900">{{ $label }}</span>
                                            <span class="mt-1 block text-xs uppercase tracking-[0.18em] text-slate-400">{{ str_replace('_', ' ', $key) }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-5">
                        <h2 class="text-lg font-semibold text-slate-900">Current Coverage</h2>
                        <p class="mt-1 text-sm text-slate-500">A quick view of how much of the audit catalog is currently active.</p>
                    </div>

                    <div class="rounded-3xl bg-slate-950 p-5 text-white">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Audit engine</p>
                        <div class="mt-5 space-y-3 text-sm text-slate-300">
                            <div class="flex items-center justify-between gap-4">
                                <span>Enabled events</span>
                                <span class="font-medium text-white">{{ number_format($enabledCount) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Disabled events</span>
                                <span class="font-medium text-white">{{ number_format($totalCount - $enabledCount) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Total events</span>
                                <span class="font-medium text-white">{{ number_format($totalCount) }}</span>
                            </div>
                        </div>
                    </div>

                    
                </section>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                    Save Activity Log Settings
                </button>
            </div>
        </form>
    </div>
@endsection
