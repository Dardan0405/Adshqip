@extends('layouts.publisher')

@section('title', 'Notification Settings')

@section('content')
    @php
        $profile        = $user->profile;
        $initials       = strtoupper(substr($user->email, 0, 2));
        $enabledEvents  = $settings['enabled_events'] ?? [];
        $deliveryChannels = $settings['delivery_channels'] ?? [];
    @endphp

    <div class="space-y-6">

        {{-- Hero --}}
        <div class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-gradient-to-br from-slate-950 via-slate-900 to-sky-900 px-6 py-7 text-white shadow-sm" style="--tw-gradient-to: #0c4a6e;">
            <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(56,189,248,0.28),_transparent_55%)]"></div>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-sky-100">Notification Controls</span>
                    <h1 class="mt-4 text-3xl font-semibold tracking-tight">Choose which publisher events should reach you and where they should arrive.</h1>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-slate-200/85">
                        Turn alerts on or off for site activity, adblock changes, payments, account updates, and login security. All preferences are saved to your publisher profile.
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
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-300">Publisher profile</p>
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

        <form method="POST" action="{{ route('publisher.notification-settings.update') }}" class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.5fr)_360px]">
            @csrf
            @method('PUT')

            {{-- Left: event sections --}}
            <div class="space-y-6">
                @foreach($definition['sections'] as $sectionKey => $section)
                    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-5">
                            <h2 class="text-lg font-semibold text-slate-900">{{ $section['title'] }}</h2>
                            <p class="mt-1 text-sm text-slate-500">Choose which {{ strtolower($section['title']) }} alerts should be delivered.</p>
                        </div>
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            @foreach($section['items'] as $itemKey => $label)
                                <label class="inline-flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 cursor-pointer hover:bg-slate-100 transition-colors">
                                    <input type="checkbox" name="enabled_events[]" value="{{ $itemKey }}"
                                           {{ in_array($itemKey, old('enabled_events', $enabledEvents), true) ? 'checked' : '' }}
                                           class="mt-1 h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                    <span class="text-sm font-medium text-slate-800">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                {{-- Campaign Newsletter --}}
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-5">
                        <h2 class="text-lg font-semibold text-slate-900">Campaign Newsletter</h2>
                        <p class="mt-1 text-sm text-slate-500">Choose which campaigns should trigger newsletter notifications.</p>
                    </div>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        @foreach($definition['campaign_newsletter_options'] as $optKey => $optLabel)
                            <label class="inline-flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 cursor-pointer hover:bg-slate-100 transition-colors">
                                <input type="radio" name="campaign_newsletter_filter" value="{{ $optKey }}"
                                       {{ old('campaign_newsletter_filter', $settings['campaign_newsletter_filter']) === $optKey ? 'checked' : '' }}
                                       class="mt-1 h-4 w-4 border-slate-300 text-brand-600 focus:ring-brand-500">
                                <span class="text-sm font-medium text-slate-800">{{ $optLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                </section>
            </div>

            {{-- Right: delivery + summary --}}
            <div class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-5">
                        <h2 class="text-lg font-semibold text-slate-900">Delivery Preferences</h2>
                        <p class="mt-1 text-sm text-slate-500">Pick the channels that can deliver your publisher notifications.</p>
                    </div>

                    <label class="inline-flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 cursor-pointer hover:bg-slate-100 transition-colors">
                        <input type="checkbox" name="receive_newsletter" value="1"
                               {{ old('receive_newsletter', $settings['receive_newsletter']) ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        <span>
                            <span class="block text-sm font-semibold text-slate-900">I wish to receive newsletter from Adshqip</span>
                            <span class="mt-1 block text-xs leading-5 text-slate-500">News, updates, and product messages related to your publisher account.</span>
                        </span>
                    </label>

                    <div class="mt-5 space-y-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Send me mail by</p>
                        @foreach($definition['channels'] as $channelKey => $channelLabel)
                            <label class="inline-flex w-full items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4 cursor-pointer hover:bg-slate-50 transition-colors">
                                <input type="checkbox" name="delivery_channels[]" value="{{ $channelKey }}"
                                       {{ in_array($channelKey, old('delivery_channels', $deliveryChannels), true) ? 'checked' : '' }}
                                       class="mt-1 h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                <span class="text-sm font-medium text-slate-800">{{ $channelLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                </section>

                {{-- Summary card --}}
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
                                <span class="font-medium text-white">{{ count(old('delivery_channels', $deliveryChannels)) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Enabled events</span>
                                <span class="font-medium text-white">{{ count(old('enabled_events', $enabledEvents)) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Campaign newsletter</span>
                                <span class="font-medium text-white">{{ $definition['campaign_newsletter_options'][$settings['campaign_newsletter_filter'] ?? ''] ?? 'Not set' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 rounded-2xl bg-sky-50 p-4 text-sm text-sky-900">
                        Use the profile dropdown to return here any time and fine-tune which alerts reach your publisher workspace.
                    </div>
                </section>

                {{-- Push notification test --}}
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm" x-data="pushNotif()">
                    <div class="mb-5">
                        <h2 class="text-lg font-semibold text-slate-900">Browser Push Notifications</h2>
                        <p class="mt-1 text-sm text-slate-500">Allow Adshqip to send instant alerts directly to your browser.</p>
                    </div>

                    <div x-show="status === 'unsupported'" class="rounded-2xl bg-slate-50 border border-slate-200 px-4 py-4 text-sm text-slate-600">
                        Your browser does not support push notifications.
                    </div>

                    <div x-show="status !== 'unsupported'" class="space-y-3">
                        <div class="flex items-center justify-between rounded-2xl border border-slate-200 px-4 py-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Push permission</p>
                                <p class="text-xs text-slate-500 mt-0.5" x-text="permissionLabel"></p>
                            </div>
                            <span class="text-xs font-semibold px-3 py-1 rounded-full"
                                  :class="status === 'granted' ? 'bg-emerald-100 text-emerald-700' : (status === 'denied' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700')"
                                  x-text="status === 'granted' ? 'Enabled' : (status === 'denied' ? 'Blocked' : 'Not set')">
                            </span>
                        </div>

                        <button type="button" x-show="status === 'default'" @click="requestPermission()"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            Enable Push Notifications
                        </button>

                        <button type="button" x-show="status === 'granted'" @click="sendTest()"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100">
                            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            Send Test Notification
                        </button>

                        <p x-show="status === 'denied'" class="text-xs text-rose-600 px-1">
                            Push notifications are blocked. Enable them in your browser's site settings and refresh.
                        </p>

                        <p x-show="testSent" class="text-xs text-emerald-600 px-1 font-medium">
                            Test notification sent! Check your browser notifications.
                        </p>
                    </div>
                </section>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                    Save Notification Settings
                </button>

                <a href="{{ route('publisher.account-settings') }}" class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Back to Account Settings
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
function pushNotif() {
    return {
        status: 'unsupported',
        testSent: false,

        get permissionLabel() {
            if (this.status === 'granted')  return 'Browser will receive push alerts from Adshqip.';
            if (this.status === 'denied')   return 'Permission blocked in browser settings.';
            if (this.status === 'default')  return 'Click "Enable" to allow browser notifications.';
            return '';
        },

        init() {
            if (!('Notification' in window)) return;
            this.status = Notification.permission;
        },

        async requestPermission() {
            const result = await Notification.requestPermission();
            this.status = result;
        },

        sendTest() {
            if (this.status !== 'granted') return;
            new Notification('Adshqip Publisher', {
                body: 'Push notifications are working correctly for your publisher account.',
                icon: '/AdshqipSVG.svg',
                tag:  'adshqip-test',
            });
            this.testSent = true;
            setTimeout(() => { this.testSent = false; }, 4000);
        },
    };
}
</script>
@endpush
