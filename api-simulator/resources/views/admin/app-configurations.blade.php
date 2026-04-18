@extends('layouts.admin')

@section('title', 'App Configurations')

@section('content')
    @php
        $initials = strtoupper(substr($user->email, 0, 2));
        $profileAvatar = $user->profile?->avatar_url;
    @endphp

    <div class="space-y-6">
        <div class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-gradient-to-br from-slate-950 via-slate-900 to-cyan-900 px-6 py-7 text-white shadow-sm">
            <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(34,211,238,0.30),_transparent_55%)]"></div>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-cyan-100">System Controls</span>
                    <h1 class="mt-4 text-3xl font-semibold tracking-tight">Manage platform-level creative rules from one place.</h1>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-slate-200/85">
                        Update global creative behavior, anti-fraud protection, and mobile tracking paths from one place.
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
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-300">Platform</p>
                        <p class="mt-1 text-lg font-semibold">Creative Settings</p>
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
                Please review the highlighted settings and try again.
            </div>
        @endif

        <form method="POST" action="{{ route('admin.app-configurations.update') }}" class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.4fr)_360px]">
            @csrf
            @method('PUT')

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-slate-900">Creative settings</h2>
                    <p class="mt-1 text-sm text-slate-500">These values are used by creative creation and creative management flows.</p>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Minimum Impressions Per Day</label>
                        <input type="number" min="0" step="1" name="creative_minimum_impressions_per_day" value="{{ old('creative_minimum_impressions_per_day', $settings['creative_minimum_impressions_per_day']) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100" placeholder="1000">
                        @error('creative_minimum_impressions_per_day') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Creative Approval Type</label>
                        <select name="creative_approval_type" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100">
                            @foreach($creativeApprovalTypes as $value => $label)
                                <option value="{{ $value }}" {{ old('creative_approval_type', $settings['creative_approval_type']) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('creative_approval_type') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-8 border-t border-slate-200 pt-6">
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-slate-900">Anti fraud settings</h2>
                        <p class="mt-1 text-sm text-slate-500">Protect click tracking by blocking repeated clicks from the same IP inside a rolling time window.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4">
                                <input type="checkbox" name="anti_fraud_clicks_enabled" value="1" {{ old('anti_fraud_clicks_enabled', $settings['anti_fraud_clicks_enabled']) ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                <span>
                                    <span class="block text-sm font-semibold text-slate-900">Anti Fraud Clicks</span>
                                    <span class="mt-1 block text-sm text-slate-500">When enabled, extra clicks from the same IP are blocked and written into the anti-fraud log.</span>
                                </span>
                            </label>
                            @error('anti_fraud_clicks_enabled') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Reset Counter Time</label>
                            <input type="number" min="1" step="1" name="anti_fraud_reset_counter_seconds" value="{{ old('anti_fraud_reset_counter_seconds', $settings['anti_fraud_reset_counter_seconds']) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100" placeholder="300">
                            <p class="mt-1.5 text-xs text-slate-500">Measured in seconds. Example: `300` = 5 minutes.</p>
                            @error('anti_fraud_reset_counter_seconds') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Threshold Clicks</label>
                            <input type="number" min="1" step="1" name="anti_fraud_threshold_clicks" value="{{ old('anti_fraud_threshold_clicks', $settings['anti_fraud_threshold_clicks']) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100" placeholder="10">
                            <p class="mt-1.5 text-xs text-slate-500">The next click after this limit inside the reset window will be blocked.</p>
                            @error('anti_fraud_threshold_clicks') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-8 border-t border-slate-200 pt-6">
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-slate-900">Terawurl mobile tracking</h2>
                        <p class="mt-1 text-sm text-slate-500">Use a custom full Terawurl base link for mobile and tablet tracking URLs.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-5">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Terawurl Link For Mobile Device Tracking</label>
                            <input type="text" name="terawurl_mobile_tracking_url" value="{{ old('terawurl_mobile_tracking_url', $settings['terawurl_mobile_tracking_url']) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100" placeholder="https://track.example.com/t/mobile-track">
                            <p class="mt-1.5 text-xs text-slate-500">Example: `https://track.example.com/t/mobile-track`. The system will append `/ad/...` or `/direct/...` automatically.</p>
                            @error('terawurl_mobile_tracking_url') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-8 border-t border-slate-200 pt-6">
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-slate-900">JW Player</h2>
                        <p class="mt-1 text-sm text-slate-500">Store the JW Player licence key so video creatives can use it whenever `jwplayer` is available on the page.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-5">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">JW Player Licence Key</label>
                            <input type="text" name="jw_player_license_key" value="{{ old('jw_player_license_key', $settings['jw_player_license_key']) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100" placeholder="Paste your JW Player licence key">
                            <p class="mt-1.5 text-xs text-slate-500">Saved in platform settings and injected into video renderers with native player fallback.</p>
                            @error('jw_player_license_key') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-8 border-t border-slate-200 pt-6">
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-slate-900">Advertiser approval</h2>
                        <p class="mt-1 text-sm text-slate-500">Choose whether new advertisers verify by email or wait for manual admin approval.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Advertiser Approval Type</label>
                            <select name="advertiser_approval_type" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100">
                                @foreach($advertiserApprovalTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('advertiser_approval_type', $settings['advertiser_approval_type']) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('advertiser_approval_type') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-8 border-t border-slate-200 pt-6">
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-slate-900">Advertiser payments</h2>
                        <p class="mt-1 text-sm text-slate-500">Choose the default payment method for advertiser deposits.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Advertiser Payment Type</label>
                            <select name="advertiser_payment_type" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100">
                                @foreach($advertiserPaymentTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('advertiser_payment_type', $settings['advertiser_payment_type']) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('advertiser_payment_type') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-8 border-t border-slate-200 pt-6">
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-slate-900">Campaign settings</h2>
                        <p class="mt-1 text-sm text-slate-500">Configure minimum budget, bid rates, and default creative types for campaigns.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Minimum Budget</label>
                            <input type="number" min="0" step="0.01" name="campaign_minimum_budget" value="{{ old('campaign_minimum_budget', $settings['campaign_minimum_budget']) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100" placeholder="10.00">
                            <p class="mt-1.5 text-xs text-slate-500">Minimum budget required to create a new campaign.</p>
                            @error('campaign_minimum_budget') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Minimum Bid Rate</label>
                            <input type="number" min="0" step="0.001" name="campaign_minimum_bid_rate" value="{{ old('campaign_minimum_bid_rate', $settings['campaign_minimum_bid_rate']) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100" placeholder="0.01">
                            <p class="mt-1.5 text-xs text-slate-500">Minimum CPM/CPC bid rate for campaigns.</p>
                            @error('campaign_minimum_bid_rate') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Default Creative Type</label>
                            <select name="campaign_creative_type" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100">
                                @foreach($campaignCreativeTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('campaign_creative_type', $settings['campaign_creative_type']) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1.5 text-xs text-slate-500">Default creative type when creating new campaigns.</p>
                            @error('campaign_creative_type') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-8 border-t border-slate-200 pt-6">
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-slate-900">Publisher approval</h2>
                        <p class="mt-1 text-sm text-slate-500">Choose whether new publishers verify by email or wait for manual admin approval.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Publisher Approval Type</label>
                            <select name="publisher_approval_type" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100">
                                @foreach($publisherApprovalTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('publisher_approval_type', $settings['publisher_approval_type']) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('publisher_approval_type') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-8 border-t border-slate-200 pt-6">
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-slate-900">Message settings</h2>
                        <p class="mt-1 text-sm text-slate-500">Choose how registration follow-up messages are delivered to new advertisers and publishers.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Message Enable / Disable Settings</label>
                            <select name="message_delivery_mode" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100">
                                @foreach($messageDeliveryModes as $value => $label)
                                    <option value="{{ $value }}" {{ old('message_delivery_mode', $settings['message_delivery_mode']) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1.5 text-xs text-slate-500">`Default` keeps the normal success flow, `Send Message` creates an in-app notification, and `Send Email` dispatches an email through Laravel mail.</p>
                            @error('message_delivery_mode') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-8 border-t border-slate-200 pt-6">
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-slate-900">Advertiser publisher message settings</h2>
                        <p class="mt-1 text-sm text-slate-500">Control whether in-app messages can be delivered to advertiser and publisher accounts.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4">
                                <input type="checkbox" name="advertiser_messages_enabled" value="1" {{ old('advertiser_messages_enabled', $settings['advertiser_messages_enabled']) ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                <span>
                                    <span class="block text-sm font-semibold text-slate-900">Advertiser Message Enable / Disable</span>
                                    <span class="mt-1 block text-sm text-slate-500">When disabled, admin and signup flows will stop creating in-app notifications for advertisers.</span>
                                </span>
                            </label>
                            @error('advertiser_messages_enabled') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4">
                                <input type="checkbox" name="publisher_messages_enabled" value="1" {{ old('publisher_messages_enabled', $settings['publisher_messages_enabled']) ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                <span>
                                    <span class="block text-sm font-semibold text-slate-900">Publisher Message Enable / Disable</span>
                                    <span class="mt-1 block text-sm text-slate-500">When disabled, admin and signup flows will stop creating in-app notifications for publishers.</span>
                                </span>
                            </label>
                            @error('publisher_messages_enabled') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-8 border-t border-slate-200 pt-6">
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-slate-900">Publisher payments</h2>
                        <p class="mt-1 text-sm text-slate-500">Control floor price enforcement, invoice generation, and payout thresholds for publishers.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4">
                                <input type="checkbox" name="publisher_floor_price_enabled" value="1" {{ old('publisher_floor_price_enabled', $settings['publisher_floor_price_enabled']) ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                <span>
                                    <span class="block text-sm font-semibold text-slate-900">Floor Price</span>
                                    <span class="mt-1 block text-sm text-slate-500">Enable minimum floor price enforcement when publishers create or update AdBlocks.</span>
                                </span>
                            </label>
                            @error('publisher_floor_price_enabled') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Minimum Floor Price</label>
                            <input type="number" min="0" step="0.01" name="publisher_minimum_floor_price_percent" value="{{ old('publisher_minimum_floor_price_percent', $settings['publisher_minimum_floor_price_percent']) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100" placeholder="25">
                            <p class="mt-1.5 text-xs text-slate-500">Saved as percent. Example: `25` means the minimum floor price amount becomes `0.25`.</p>
                            @error('publisher_minimum_floor_price_percent') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Minimum Amount For Invoice Generation</label>
                            <input type="number" min="0" step="0.01" name="publisher_minimum_amount_for_invoice_generation" value="{{ old('publisher_minimum_amount_for_invoice_generation', $settings['publisher_minimum_amount_for_invoice_generation']) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100" placeholder="50">
                            @error('publisher_minimum_amount_for_invoice_generation') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4">
                                <input type="checkbox" name="publisher_auto_invoice_enabled" value="1" {{ old('publisher_auto_invoice_enabled', $settings['publisher_auto_invoice_enabled']) ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                <span>
                                    <span class="block text-sm font-semibold text-slate-900">Auto Invoice</span>
                                    <span class="mt-1 block text-sm text-slate-500">Automatically generate draft publisher payout invoices when outstanding earnings reach the configured minimum.</span>
                                </span>
                            </label>
                            @error('publisher_auto_invoice_enabled') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Publisher Minimum Payment Amount</label>
                            <input type="number" min="0" step="0.01" name="publisher_minimum_payment_amount" value="{{ old('publisher_minimum_payment_amount', $settings['publisher_minimum_payment_amount']) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100" placeholder="100">
                            @error('publisher_minimum_payment_amount') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-8 border-t border-slate-200 pt-6">
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-slate-900">Ad delivery modes</h2>
                        <p class="mt-1 text-sm text-slate-500">Control public ad serving behavior for video, mobile traffic, banner loading, and URL visibility.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4">
                                <input type="checkbox" name="video_ads_enabled" value="1" {{ old('video_ads_enabled', $settings['video_ads_enabled']) ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                <span>
                                    <span class="block text-sm font-semibold text-slate-900">Video & Mobile Ads: Video</span>
                                    <span class="mt-1 block text-sm text-slate-500">Enable or disable serving for video creatives and video direct campaigns.</span>
                                </span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4">
                                <input type="checkbox" name="mobile_ads_enabled" value="1" {{ old('mobile_ads_enabled', $settings['mobile_ads_enabled']) ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                <span>
                                    <span class="block text-sm font-semibold text-slate-900">Video & Mobile Ads: Mobile</span>
                                    <span class="mt-1 block text-sm text-slate-500">Enable or disable ad delivery for mobile and tablet visitors.</span>
                                </span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4">
                                <input type="checkbox" name="banner_autoload_enabled" value="1" {{ old('banner_autoload_enabled', $settings['banner_autoload_enabled']) ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                <span>
                                    <span class="block text-sm font-semibold text-slate-900">Banner Autoload</span>
                                    <span class="mt-1 block text-sm text-slate-500">Automatically inject banner HTML when the zone JavaScript loads.</span>
                                </span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4">
                                <input type="checkbox" name="hide_url_enabled" value="1" {{ old('hide_url_enabled', $settings['hide_url_enabled']) ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                <span>
                                    <span class="block text-sm font-semibold text-slate-900">Hide URL</span>
                                    <span class="mt-1 block text-sm text-slate-500">Hide visible destination URLs inside text and URL-led ad layouts.</span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-8 border-t border-slate-200 pt-6">
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-slate-900">Memcache</h2>
                        <p class="mt-1 text-sm text-slate-500">Control cached zone serving payloads and internal AdShqip reporting caches.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4">
                                <input type="checkbox" name="revive_memcache_enabled" value="1" {{ old('revive_memcache_enabled', $settings['revive_memcache_enabled']) ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                <span>
                                    <span class="block text-sm font-semibold text-slate-900">Revive Memcache</span>
                                    <span class="mt-1 block text-sm text-slate-500">Enable or disable cached public zone serving payloads.</span>
                                </span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4">
                                <input type="checkbox" name="adshqip_memcache_enabled" value="1" {{ old('adshqip_memcache_enabled', $settings['adshqip_memcache_enabled']) ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                <span>
                                    <span class="block text-sm font-semibold text-slate-900">AdShqip Memcache</span>
                                    <span class="mt-1 block text-sm text-slate-500">Enable or disable internal AdShqip report query caching.</span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-8 border-t border-slate-200 pt-6">
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-slate-900">Encryption</h2>
                        <p class="mt-1 text-sm text-slate-500">Control whether encrypted values are used in delivery links and click-tracking URLs.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4">
                                <input type="checkbox" name="encryption_enabled" value="1" {{ old('encryption_enabled', $settings['encryption_enabled']) ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                <span>
                                    <span class="block text-sm font-semibold text-slate-900">Encryption Enable / Disable</span>
                                    <span class="mt-1 block text-sm text-slate-500">Master switch for encrypted values used in public ad delivery flows.</span>
                                </span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4">
                                <input type="checkbox" name="url_encoding_enabled" value="1" {{ old('url_encoding_enabled', $settings['url_encoding_enabled']) ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                <span>
                                    <span class="block text-sm font-semibold text-slate-900">URL Encoding / Crypted</span>
                                    <span class="mt-1 block text-sm text-slate-500">Encrypt destination URLs passed through click-tracking links before redirect resolution.</span>
                                </span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4">
                                <input type="checkbox" name="report_url_ads_enabled" value="1" {{ old('report_url_ads_enabled', $settings['report_url_ads_enabled']) ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                <span>
                                    <span class="block text-sm font-semibold text-slate-900">Report of URL Ads</span>
                                    <span class="mt-1 block text-sm text-slate-500">Record serve and click URL activity into the URL ad reports table for auditing and review.</span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </section>

            <div class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-5">
                        <h2 class="text-lg font-semibold text-slate-900">Current behavior</h2>
                        <p class="mt-1 text-sm text-slate-500">A quick summary of how the platform will treat new creatives.</p>
                    </div>

                    <div class="rounded-3xl bg-slate-950 p-5 text-white">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Creative engine</p>
                        <div class="mt-5 space-y-3 text-sm text-slate-300">
                            <div class="flex items-center justify-between gap-4">
                                <span>Daily minimum</span>
                                <span class="font-medium text-white">{{ number_format((int) $settings['creative_minimum_impressions_per_day']) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Approval flow</span>
                                <span class="font-medium text-white">{{ $creativeApprovalTypes[$settings['creative_approval_type']] ?? 'Manual Review' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>New creative status</span>
                                <span class="font-medium text-white">{{ $settings['creative_approval_type'] === 'auto_approve' ? 'Active' : 'Pending Review' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Anti fraud engine</p>
                        <div class="mt-5 space-y-3 text-sm text-slate-600">
                            <div class="flex items-center justify-between gap-4">
                                <span>Click protection</span>
                                <span class="font-medium text-slate-900">{{ $settings['anti_fraud_clicks_enabled'] ? 'Enabled' : 'Disabled' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Reset counter time</span>
                                <span class="font-medium text-slate-900">{{ number_format((int) $settings['anti_fraud_reset_counter_seconds']) }} sec</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Threshold clicks</span>
                                <span class="font-medium text-slate-900">{{ number_format((int) $settings['anti_fraud_threshold_clicks']) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Block rule</span>
                                <span class="font-medium text-slate-900">Same IP inside the reset window</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Mobile tracking</p>
                        <div class="mt-5 space-y-3 text-sm text-slate-600">
                            <div class="flex items-center justify-between gap-4">
                                <span>Terawurl link</span>
                                <span class="font-medium text-slate-900">{{ $settings['terawurl_mobile_tracking_url'] ?: 'Not set' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Mobile tracking URL</span>
                                <span class="font-medium text-slate-900">{{ $settings['terawurl_mobile_tracking_url'] ?: 'Default tracking routes' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Video player</p>
                        <div class="mt-5 space-y-3 text-sm text-slate-600">
                            <div class="flex items-center justify-between gap-4">
                                <span>JW Player licence</span>
                                <span class="font-medium text-slate-900">{{ $settings['jw_player_license_key'] ? 'Configured' : 'Not set' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Fallback mode</span>
                                <span class="font-medium text-slate-900">Native HTML5 video</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Advertiser onboarding</p>
                        <div class="mt-5 space-y-3 text-sm text-slate-600">
                            <div class="flex items-center justify-between gap-4">
                                <span>Approval flow</span>
                                <span class="font-medium text-slate-900">{{ $advertiserApprovalTypes[$settings['advertiser_approval_type']] ?? 'Email Verification' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Advertiser payments</p>
                        <div class="mt-5 space-y-3 text-sm text-slate-600">
                            <div class="flex items-center justify-between gap-4">
                                <span>Default payment type</span>
                                <span class="font-medium text-slate-900">{{ $advertiserPaymentTypes[$settings['advertiser_payment_type']] ?? 'Wire Transfer' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Campaign settings</p>
                        <div class="mt-5 space-y-3 text-sm text-slate-600">
                            <div class="flex items-center justify-between gap-4">
                                <span>Minimum budget</span>
                                <span class="font-medium text-slate-900">{{ number_format((float) $settings['campaign_minimum_budget'], 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Minimum bid rate</span>
                                <span class="font-medium text-slate-900">{{ number_format((float) $settings['campaign_minimum_bid_rate'], 3) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Default creative type</span>
                                <span class="font-medium text-slate-900">{{ $campaignCreativeTypes[$settings['campaign_creative_type']] ?? 'Banner' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Publisher onboarding</p>
                        <div class="mt-5 space-y-3 text-sm text-slate-600">
                            <div class="flex items-center justify-between gap-4">
                                <span>Approval flow</span>
                                <span class="font-medium text-slate-900">{{ $publisherApprovalTypes[$settings['publisher_approval_type']] ?? 'Email Verification' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Message delivery</p>
                        <div class="mt-5 space-y-3 text-sm text-slate-600">
                            <div class="flex items-center justify-between gap-4">
                                <span>Registration follow-up</span>
                                <span class="font-medium text-slate-900">{{ $messageDeliveryModes[$settings['message_delivery_mode']] ?? 'Default' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Account messages</p>
                        <div class="mt-5 space-y-3 text-sm text-slate-600">
                            <div class="flex items-center justify-between gap-4">
                                <span>Advertiser messages</span>
                                <span class="font-medium text-slate-900">{{ $settings['advertiser_messages_enabled'] ? 'Enabled' : 'Disabled' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Publisher messages</span>
                                <span class="font-medium text-slate-900">{{ $settings['publisher_messages_enabled'] ? 'Enabled' : 'Disabled' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Publisher payments</p>
                        <div class="mt-5 space-y-3 text-sm text-slate-600">
                            <div class="flex items-center justify-between gap-4">
                                <span>Floor price enforcement</span>
                                <span class="font-medium text-slate-900">{{ $settings['publisher_floor_price_enabled'] ? 'Enabled' : 'Disabled' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Minimum floor price</span>
                                <span class="font-medium text-slate-900">{{ number_format((float) $settings['publisher_minimum_floor_price_percent'], 2) }}% ({{ number_format((float) $settings['publisher_minimum_floor_price_percent'] / 100, 2) }})</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Minimum invoice amount</span>
                                <span class="font-medium text-slate-900">{{ number_format((float) $settings['publisher_minimum_amount_for_invoice_generation'], 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Auto invoice</span>
                                <span class="font-medium text-slate-900">{{ $settings['publisher_auto_invoice_enabled'] ? 'Enabled' : 'Disabled' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Minimum payout amount</span>
                                <span class="font-medium text-slate-900">{{ number_format((float) $settings['publisher_minimum_payment_amount'], 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Ad delivery modes</p>
                        <div class="mt-5 space-y-3 text-sm text-slate-600">
                            <div class="flex items-center justify-between gap-4">
                                <span>Video ads</span>
                                <span class="font-medium text-slate-900">{{ $settings['video_ads_enabled'] ? 'Enabled' : 'Disabled' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Mobile ads</span>
                                <span class="font-medium text-slate-900">{{ $settings['mobile_ads_enabled'] ? 'Enabled' : 'Disabled' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Banner autoload</span>
                                <span class="font-medium text-slate-900">{{ $settings['banner_autoload_enabled'] ? 'Enabled' : 'Disabled' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Hide URL</span>
                                <span class="font-medium text-slate-900">{{ $settings['hide_url_enabled'] ? 'Enabled' : 'Disabled' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Memcache</p>
                        <div class="mt-5 space-y-3 text-sm text-slate-600">
                            <div class="flex items-center justify-between gap-4">
                                <span>Revive memcache</span>
                                <span class="font-medium text-slate-900">{{ $settings['revive_memcache_enabled'] ? 'Enabled' : 'Disabled' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>AdShqip memcache</span>
                                <span class="font-medium text-slate-900">{{ $settings['adshqip_memcache_enabled'] ? 'Enabled' : 'Disabled' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Encryption</p>
                        <div class="mt-5 space-y-3 text-sm text-slate-600">
                            <div class="flex items-center justify-between gap-4">
                                <span>Encryption</span>
                                <span class="font-medium text-slate-900">{{ $settings['encryption_enabled'] ? 'Enabled' : 'Disabled' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>URL encoding</span>
                                <span class="font-medium text-slate-900">{{ $settings['url_encoding_enabled'] ? 'Enabled' : 'Disabled' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Report URL ads</span>
                                <span class="font-medium text-slate-900">{{ $settings['report_url_ads_enabled'] ? 'Enabled' : 'Disabled' }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                    Save App Configurations
                </button>
            </div>
        </form>
    </div>
@endsection
