@extends('layouts.advertiser')

@section('title', 'Subscription Plans')

@section('content')
@php
    $formatMoney = function ($value, $currency) {
        if ($value === null) {
            return 'Custom';
        }

        return ($currency ?: 'EUR') . ' ' . number_format((float) $value, 2);
    };

    $statusClasses = [
        'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'trial' => 'bg-blue-50 text-blue-700 border-blue-200',
        'cancelled' => 'bg-gray-50 text-gray-700 border-gray-200',
        'expired' => 'bg-red-50 text-red-700 border-red-200',
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Subscription Plans</h1>
            <p class="mt-1 text-sm text-gray-500">Choose from the active plans managed by Admin Pricing Plans.</p>
        </div>
        <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1 shadow-sm w-fit">
            <a href="{{ route('advertiser.payments.subscription-plan', ['cycle' => 'monthly']) }}" class="px-4 py-2 text-sm font-medium rounded-md {{ $cycle === 'monthly' ? 'bg-brand-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }}">Monthly</a>
            <a href="{{ route('advertiser.payments.subscription-plan', ['cycle' => 'yearly']) }}" class="px-4 py-2 text-sm font-medium rounded-md {{ $cycle === 'yearly' ? 'bg-brand-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }}">Yearly</a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Current Subscription</p>
                @if($currentSubscription)
                    <div class="mt-2 flex flex-wrap items-center gap-3">
                        <h2 class="text-xl font-semibold text-gray-900">{{ $currentSubscription->plan?->name ?? 'Deleted Plan' }}</h2>
                        <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$currentSubscription->status] ?? 'bg-gray-50 text-gray-700 border-gray-200' }}">
                            {{ ucfirst($currentSubscription->status) }}
                        </span>
                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">
                            {{ ucfirst($currentSubscription->billing_cycle) }}
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        Period: {{ $currentSubscription->current_period_start?->format('M d, Y') }} to {{ $currentSubscription->current_period_end?->format('M d, Y') }}
                    </p>
                @else
                    <h2 class="mt-2 text-xl font-semibold text-gray-900">No active plan</h2>
                    <p class="mt-2 text-sm text-gray-500">Select a plan below to activate subscription access.</p>
                @endif
            </div>
            @if($currentSubscription && in_array($currentSubscription->status, ['active', 'trial'], true))
                <form method="POST" action="{{ route('advertiser.payments.subscription-plan.cancel', $currentSubscription) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">
                        Cancel Subscription
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if($plans->isEmpty())
        <div class="rounded-lg border border-dashed border-gray-300 bg-white p-8 text-center">
            <h2 class="text-lg font-semibold text-gray-900">No advertiser plans are active</h2>
            <p class="mt-2 text-sm text-gray-500">Create or unblock pricing plans in Admin Pricing Plans with target audience Advertiser or Both.</p>
        </div>
    @else
        <div class="grid gap-5 xl:grid-cols-3 md:grid-cols-2">
            @foreach($plans as $plan)
                @php
                    $selectedPrice = $cycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;
                    $monthlyPrice = $formatMoney($plan->price_monthly, $plan->currency);
                    $yearlyPrice = $formatMoney($plan->price_yearly, $plan->currency);
                    $isCurrent = $currentSubscription && (int) $currentSubscription->plan_id === (int) $plan->id && $currentSubscription->billing_cycle === $cycle;
                    $features = is_array($plan->features) ? $plan->features : [];
                @endphp
                <div class="relative rounded-lg border bg-white p-5 shadow-sm {{ $plan->is_popular ? 'border-brand-300 ring-1 ring-brand-100' : 'border-gray-200' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">{{ $plan->name }}</h2>
                            <p class="mt-1 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ ucfirst($plan->target_audience) }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            @if($plan->is_popular)
                                <span class="rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700">Popular</span>
                            @endif
                            @if($plan->is_enterprise)
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">Enterprise</span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-5">
                        <div class="flex items-end gap-2">
                            <span class="text-3xl font-bold text-gray-900">{{ $formatMoney($selectedPrice, $plan->currency) }}</span>
                            @if($selectedPrice !== null)
                                <span class="pb-1 text-sm text-gray-500">/ {{ $cycle === 'yearly' ? 'year' : 'month' }}</span>
                            @endif
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            Monthly: {{ $monthlyPrice }} | Yearly: {{ $yearlyPrice }}
                        </p>
                    </div>

                    @if($plan->description)
                        <p class="mt-4 text-sm leading-6 text-gray-600">{{ $plan->description }}</p>
                    @endif

                    <div class="mt-4 rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-600">
                        Impressions:
                        <span class="font-semibold text-gray-900">
                            {{ $plan->impressions_limit ? number_format($plan->impressions_limit) : 'Unlimited' }}
                        </span>
                    </div>

                    <ul class="mt-4 space-y-2 text-sm text-gray-600">
                        @forelse(array_slice($features, 0, 8) as $feature)
                            <li class="flex gap-2">
                                <span class="mt-1 h-1.5 w-1.5 rounded-full bg-brand-500 flex-shrink-0"></span>
                                <span>{{ $feature }}</span>
                            </li>
                        @empty
                            <li class="text-gray-400">No feature list configured.</li>
                        @endforelse
                    </ul>

                    <form method="POST" action="{{ route('advertiser.payments.subscription-plan.subscribe', $plan) }}" class="mt-5">
                        @csrf
                        <input type="hidden" name="billing_cycle" value="{{ $cycle }}">
                        <button type="submit" class="w-full rounded-lg px-4 py-2.5 text-sm font-semibold {{ $isCurrent ? 'bg-gray-100 text-gray-500 cursor-default' : 'bg-brand-600 text-white hover:bg-brand-700' }}" {{ $isCurrent ? 'disabled' : '' }}>
                            {{ $isCurrent ? 'Current Plan' : 'Select Plan' }}
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Subscription History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-5 py-3">Plan</th>
                        <th class="px-5 py-3">Cycle</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Period</th>
                        <th class="px-5 py-3">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($subscriptionHistory as $subscription)
                        <tr>
                            <td class="px-5 py-4 font-medium text-gray-900">{{ $subscription->plan?->name ?? 'Deleted Plan' }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ ucfirst($subscription->billing_cycle) }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$subscription->status] ?? 'bg-gray-50 text-gray-700 border-gray-200' }}">
                                    {{ ucfirst($subscription->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                {{ $subscription->current_period_start?->format('M d, Y') }} - {{ $subscription->current_period_end?->format('M d, Y') }}
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ $subscription->created_at?->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-gray-500">No subscriptions yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
