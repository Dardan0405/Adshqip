@extends('layouts.advertiser')

@section('title', 'Notification Settings')

@section('content')
    @php
        $profile = $user->profile;
        $initials = strtoupper(substr($user->email, 0, 2));
        $enabledEvents = $settings['enabled_events'] ?? [];
        $deliveryChannels = $settings['delivery_channels'] ?? [];
    @endphp

    <div class="space-y-6">
        <div class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-gradient-to-br from-slate-950 via-slate-900 to-amber-800 px-6 py-7 text-white shadow-sm">
            <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(251,191,36,0.32),_transparent_55%)]"></div>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-amber-100">Notification Controls</span>
                    <h1 class="mt-4 text-3xl font-semibold tracking-tight">Choose which advertiser events should reach you and where they should arrive.</h1>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-slate-200/85">
                        Turn alerts on or off for campaign updates, payments, network changes, account activity, and login security. All preferences are saved to your advertiser profile.
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
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-300">Advertiser profile</p>
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
                Please review the highlighted notification preferences and try again.
            </div>
        @endif

        <form method="POST" action="{{ route('advertiser.notification-settings.update') }}" class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.5fr)_360px]">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                @foreach($definition['sections'] as $sectionKey => $section)
                    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-5">
                            <h2 class="text-lg font-semibold text-slate-900">{{ $section['title'] }}</h2>
                            <p class="mt-1 text-sm text-slate-500">Choose which {{ strtolower($section['title']) }} alerts should be delivered.</p>
                        </div>

                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            @foreach($section['items'] as $itemKey => $label)
                                <label class="inline-flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                    <input type="checkbox" name="enabled_events[]" value="{{ $itemKey }}" {{ in_array($itemKey, old('enabled_events', $enabledEvents), true) ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                                    <span class="text-sm font-medium text-slate-800">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>

            <div class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-5">
                        <h2 class="text-lg font-semibold text-slate-900">Delivery Preferences</h2>
                        <p class="mt-1 text-sm text-slate-500">Pick the channels that can deliver your advertiser notifications.</p>
                    </div>

                    <label class="inline-flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <input type="checkbox" name="receive_newsletter" value="1" {{ old('receive_newsletter', $settings['receive_newsletter']) ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                        <span>
                            <span class="block text-sm font-semibold text-slate-900">I wish to receive newsletter from Adshqip</span>
                            <span class="mt-1 block text-xs leading-5 text-slate-500">News, updates, and product messages related to your advertiser account.</span>
                        </span>
                    </label>

                    <div class="mt-5 space-y-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Send me mail by</p>
                        @foreach($definition['channels'] as $channelKey => $channelLabel)
                            <label class="inline-flex w-full items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4">
                                <input type="checkbox" name="delivery_channels[]" value="{{ $channelKey }}" {{ in_array($channelKey, old('delivery_channels', $deliveryChannels), true) ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                                <span class="text-sm font-medium text-slate-800">{{ $channelLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-5">
                        <h2 class="text-lg font-semibold text-slate-900">Summary</h2>
                        <p class="mt-1 text-sm text-slate-500">A quick view of the current notification coverage.</p>
                    </div>

                    <div class="rounded-3xl bg-slate-950 p-5 text-white">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Current selection</p>
                        <div class="mt-5 space-y-3 text-sm text-slate-300">
                            <div class="flex items-center justify-between gap-4">
                                <span>Newsletter</span>
                                <span class="font-medium text-white">{{ old('receive_newsletter', $settings['receive_newsletter']) ? 'Enabled' : 'Disabled' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Delivery channels</span>
                                <span class="text-right font-medium text-white">{{ count(old('delivery_channels', $deliveryChannels)) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Enabled events</span>
                                <span class="text-right font-medium text-white">{{ count(old('enabled_events', $enabledEvents)) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 rounded-2xl bg-amber-50 p-4 text-sm text-amber-900">
                        Use the profile dropdown to return here any time and fine-tune which alerts reach your advertiser workspace.
                    </div>
                </section>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                    Save Notification Settings
                </button>
            </div>
        </form>
    </div>
@endsection
