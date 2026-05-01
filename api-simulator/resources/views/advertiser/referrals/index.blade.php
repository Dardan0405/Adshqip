@extends('layouts.advertiser')

@section('title', 'Referral Program')

@section('content')
@php
    $statusClasses = [
        'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'paused' => 'bg-amber-50 text-amber-700 border-amber-200',
        'expired' => 'bg-red-50 text-red-700 border-red-200',
        'revoked' => 'bg-gray-50 text-gray-700 border-gray-200',
        'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
        'qualified' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'processing' => 'bg-blue-50 text-blue-700 border-blue-200',
        'failed' => 'bg-red-50 text-red-700 border-red-200',
        'cancelled' => 'bg-gray-50 text-gray-700 border-gray-200',
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Referral Program</h1>
            <p class="mt-1 text-sm text-gray-500">Share tracked signup links, manage campaigns, and monitor referral income.</p>
        </div>
        <a href="{{ route('advertiser.referrals.export') }}" class="inline-flex w-fit items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
            Export CSV
        </a>
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

    <div class="grid gap-4 md:grid-cols-2">
        <div class="rounded-lg border border-brand-200 bg-brand-50 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-700">Advertiser Signup Link</p>
            <div class="mt-3 flex gap-2">
                <input readonly value="{{ $advertiserLink }}" class="min-w-0 flex-1 rounded-lg border border-brand-200 bg-white px-3 py-2 text-sm text-gray-700">
                <button type="button" data-copy="{{ $advertiserLink }}" class="copy-link rounded-lg bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700">Copy</button>
            </div>
            <a href="{{ $advertiserLink }}" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex text-sm font-semibold text-brand-700 hover:text-brand-800">Open link</a>
        </div>
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Publisher Signup Link</p>
            <div class="mt-3 flex gap-2">
                <input readonly value="{{ $publisherLink }}" class="min-w-0 flex-1 rounded-lg border border-emerald-200 bg-white px-3 py-2 text-sm text-gray-700">
                <button type="button" data-copy="{{ $publisherLink }}" class="copy-link rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Copy</button>
            </div>
            <a href="{{ $publisherLink }}" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex text-sm font-semibold text-emerald-700 hover:text-emerald-800">Open link</a>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Links</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($summary['links']) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Signups</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($summary['signups']) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Qualified</p>
            <p class="mt-2 text-2xl font-bold text-emerald-700">{{ number_format($summary['qualified']) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Earned</p>
            <p class="mt-2 text-2xl font-bold text-brand-700">EUR {{ number_format($summary['earned'], 2) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Pending</p>
            <p class="mt-2 text-2xl font-bold text-amber-700">EUR {{ number_format($summary['pending_payouts'], 2) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Paid</p>
            <p class="mt-2 text-2xl font-bold text-emerald-700">EUR {{ number_format($summary['paid_payouts'], 2) }}</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm xl:col-span-1">
            <h2 class="text-lg font-semibold text-gray-900">Create Campaign Link</h2>
            <form method="POST" action="{{ route('advertiser.referrals.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="text-sm font-medium text-gray-700">Campaign name</label>
                    <input name="campaign_name" value="{{ old('campaign_name') }}" required maxlength="255" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="Spring partner push">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Signup target</label>
                    <select name="target_role" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <option value="advertiser" {{ old('target_role') === 'advertiser' ? 'selected' : '' }}>Advertiser</option>
                        <option value="publisher" {{ old('target_role') === 'publisher' ? 'selected' : '' }}>Publisher</option>
                        <option value="any" {{ old('target_role') === 'any' ? 'selected' : '' }}>Any role</option>
                    </select>
                </div>
                <div class="grid gap-3 sm:grid-cols-3 xl:grid-cols-1">
                    <div>
                        <label class="text-sm font-medium text-gray-700">UTM source</label>
                        <input name="utm_source" value="{{ old('utm_source') }}" maxlength="100" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="linkedin">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">UTM medium</label>
                        <input name="utm_medium" value="{{ old('utm_medium') }}" maxlength="100" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="social">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">UTM campaign</label>
                        <input name="utm_campaign" value="{{ old('utm_campaign') }}" maxlength="100" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="q2_referrals">
                    </div>
                </div>
                <button class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Create Link</button>
            </form>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white shadow-sm xl:col-span-2">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Referral Links</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Campaign</th>
                            <th class="px-5 py-3">Target</th>
                            <th class="px-5 py-3">Clicks</th>
                            <th class="px-5 py-3">Signups</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($links as $link)
                            <tr>
                                <td class="px-5 py-4">
                                    <div class="font-medium text-gray-900">{{ $link->campaign_name ?: 'Referral Link' }}</div>
                                    <div class="mt-1 max-w-md truncate text-xs text-brand-600">{{ $link->share_url }}</div>
                                </td>
                                <td class="px-5 py-4 text-gray-600">{{ ucfirst($link->target_role) }}</td>
                                <td class="px-5 py-4 text-gray-600">{{ number_format($link->total_clicks) }}</td>
                                <td class="px-5 py-4 text-gray-600">{{ number_format($link->total_signups) }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$link->status] ?? 'bg-gray-50 text-gray-700 border-gray-200' }}">
                                        {{ ucfirst($link->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" data-copy="{{ $link->share_url }}" class="copy-link rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50">Copy</button>
                                        <form method="POST" action="{{ route('advertiser.referrals.status', $link) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $link->status === 'active' ? 'paused' : 'active' }}">
                                            <button class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50">{{ $link->status === 'active' ? 'Pause' : 'Activate' }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('advertiser.referrals.destroy', $link) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">Archive</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-gray-500">No referral links found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Referral Signups</h2>
                    <p class="mt-1 text-sm text-gray-500">Registrations attributed to your active referral links.</p>
                </div>
                <form method="GET" action="{{ route('advertiser.referrals') }}">
                    <select name="target_role" onchange="this.form.submit()" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <option value="">All roles</option>
                        <option value="advertiser" {{ $filters['target_role'] === 'advertiser' ? 'selected' : '' }}>Advertisers</option>
                        <option value="publisher" {{ $filters['target_role'] === 'publisher' ? 'selected' : '' }}>Publishers</option>
                    </select>
                </form>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Campaign</th>
                        <th class="px-5 py-3">Signup Date</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Commission</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($conversions as $conversion)
                        @php
                            $profile = $conversion->referredUser?->userProfile;
                            $name = trim((string) (($profile?->first_name ?? '') . ' ' . ($profile?->last_name ?? '')));
                        @endphp
                        <tr>
                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-900">{{ $name !== '' ? $name : ($conversion->referredUser?->email ?? 'Unknown') }}</div>
                                <div class="text-xs text-gray-500">{{ $conversion->referredUser?->email ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ ucfirst($conversion->referred_role) }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $conversion->link?->campaign_name ?: '-' }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $conversion->created_at?->format('M d, Y H:i') }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$conversion->status] ?? 'bg-gray-50 text-gray-700 border-gray-200' }}">
                                    {{ ucfirst($conversion->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right font-semibold text-brand-700">{{ $conversion->commission_currency }} {{ number_format((float) $conversion->commission_earned, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-gray-500">No referral signups yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($conversions->hasPages())
            <div class="border-t border-gray-100 px-5 py-4">
                {{ $conversions->links() }}
            </div>
        @endif
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Referral Payouts</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-5 py-3">Invoice</th>
                        <th class="px-5 py-3">Period</th>
                        <th class="px-5 py-3">Conversions</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payouts as $payout)
                        <tr>
                            <td class="px-5 py-4 font-medium text-gray-900">{{ $payout->invoice_number }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $payout->period_start?->format('M d, Y') }} - {{ $payout->period_end?->format('M d, Y') }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ number_format($payout->conversions_count) }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$payout->status] ?? 'bg-gray-50 text-gray-700 border-gray-200' }}">
                                    {{ ucfirst($payout->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right font-semibold text-emerald-700">{{ $payout->currency }} {{ number_format((float) $payout->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-gray-500">No payout records yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.copy-link').forEach((button) => {
        button.addEventListener('click', async () => {
            await navigator.clipboard.writeText(button.dataset.copy);
            const original = button.textContent;
            button.textContent = 'Copied';
            setTimeout(() => button.textContent = original, 1200);
        });
    });
</script>
@endpush
