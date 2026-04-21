@extends('layouts.admin')
@section('title', 'Referral Code')
@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-2 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Referral Code</h1>
        <p class="text-sm text-gray-500">Manage live referral links, commission setup, and referral earnings visibility.</p>
    </div>

    <div class="grid grid-cols-2 xl:grid-cols-5 gap-3 mb-6">
        @foreach([
            ['label' => 'Links', 'value' => number_format($summary['links'])],
            ['label' => 'Signups', 'value' => number_format($summary['signups'])],
            ['label' => 'Qualified', 'value' => number_format($summary['qualified'])],
            ['label' => 'Earned', 'value' => number_format($summary['earned'], 2)],
            ['label' => 'Payouts', 'value' => number_format($summary['payouts'], 2)],
        ] as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-[420px,1fr]">
        <div class="rounded-xl border border-gray-200 bg-white p-5 h-fit">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Create Referral Code</h2>
            <form method="POST" action="{{ route('admin.referral-codes.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Referrer</label>
                    <select name="referrer_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->email }} ({{ ucfirst($user->role) }}){{ $user->referral_code ? ' · Base code '.$user->referral_code : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Target Role</label>
                    <select name="target_role" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        <option value="any">Any</option>
                        <option value="advertiser">Advertiser</option>
                        <option value="publisher">Publisher</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Campaign Name</label>
                    <input type="text" name="campaign_name" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Spring launch referral">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Landing URL</label>
                    <input type="url" name="landing_url" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="https://example.com/register">
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Commission Type</label>
                        <select name="commission_type" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                            <option value="percentage">Percentage</option>
                            <option value="flat">Flat</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Commission Rate</label>
                        <input type="number" step="0.0001" min="0" name="commission_rate" value="5" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Duration Days</label>
                        <input type="number" min="1" max="3650" name="commission_duration_days" value="365" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Max Commission</label>
                        <input type="number" step="0.0001" min="0" name="max_commission_per_referral" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Optional">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Status</label>
                    <select name="status" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        <option value="active">Active</option>
                        <option value="paused">Paused</option>
                        <option value="expired">Expired</option>
                        <option value="revoked">Revoked</option>
                    </select>
                </div>
                <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                    Save Referral Code
                </button>
            </form>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white">
            <form method="GET" action="{{ route('admin.referral-codes') }}" class="flex flex-wrap items-center gap-2 border-b border-gray-100 p-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search code, campaign, or email"
                       class="min-w-[220px] flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <select name="status" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach(['active', 'paused', 'expired', 'revoked'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <select name="target_role" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" onchange="this.form.submit()">
                    <option value="">All Targets</option>
                    <option value="any" @selected(request('target_role') === 'any')>Any</option>
                    <option value="advertiser" @selected(request('target_role') === 'advertiser')>Advertiser</option>
                    <option value="publisher" @selected(request('target_role') === 'publisher')>Publisher</option>
                </select>
                <button type="submit" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Search</button>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Code</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Referrer</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Target</th>
                            <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Clicks</th>
                            <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Signups</th>
                            <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Earned</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                            <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($links as $link)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-4 py-3 font-semibold text-gray-900">#{{ $link->id }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $link->code }}</div>
                                    <div class="text-xs text-gray-400">{{ $link->campaign_name ?: 'No campaign label' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $link->referrer?->email }}</div>
                                    <div class="text-xs text-gray-400">{{ ucfirst((string) $link->referrer?->role) }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ ucfirst($link->target_role) }}</td>
                                <td class="px-4 py-3 text-right font-medium text-gray-700">{{ number_format($link->total_clicks) }}</td>
                                <td class="px-4 py-3 text-right font-medium text-gray-700">{{ number_format($link->total_signups) }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-emerald-700">{{ number_format((float) $link->total_earned, 2) }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ match($link->status) {
                                        'active' => 'bg-emerald-100 text-emerald-700',
                                        'paused' => 'bg-amber-100 text-amber-700',
                                        'expired' => 'bg-slate-100 text-slate-700',
                                        default => 'bg-rose-100 text-rose-700',
                                    } }}">{{ ucfirst($link->status) }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <form method="POST" action="{{ route('admin.referral-codes.update-status', $link) }}" class="inline-flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $link->status === 'active' ? 'paused' : 'active' }}">
                                        <button type="submit" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                            {{ $link->status === 'active' ? 'Pause' : 'Activate' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-12 text-center text-sm text-gray-500">No referral codes found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($links->hasPages())
                <div class="border-t border-gray-100 px-4 py-3">{{ $links->links() }}</div>
            @endif
        </div>
    </div>
@endsection
