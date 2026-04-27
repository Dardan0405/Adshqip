@extends('layouts.publisher')

@section('title', 'Referrals')

@section('content')
    <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Referrals</h1>
            <p class="mt-1 text-sm text-gray-500">Share your referral links and track registrations and referral income.</p>
        </div>
    </div>

    {{-- Referral links --}}
    <div class="grid grid-cols-1 gap-3 mb-4 sm:grid-cols-2">
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Advertiser Referral Link</div>
            <div class="mt-2 text-xs font-medium text-blue-700 break-all">{{ $referralLink }}</div>
            <a href="{{ $referralLink }}" target="_blank" rel="noopener noreferrer"
               class="mt-2 inline-block text-[11px] text-brand-600 hover:text-brand-700 font-medium">Open link ↗</a>
        </div>
        <div class="rounded-xl border border-violet-200 bg-violet-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Publisher Referral Link</div>
            <div class="mt-2 text-xs font-medium text-violet-700 break-all">{{ $publisherReferralLink }}</div>
            <a href="{{ $publisherReferralLink }}" target="_blank" rel="noopener noreferrer"
               class="mt-2 inline-block text-[11px] text-brand-600 hover:text-brand-700 font-medium">Open link ↗</a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-3 mb-6 sm:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Advertiser Referrals</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($totalReferrals) }}</div>
        </div>
        <div class="rounded-xl border border-violet-200 bg-violet-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Publisher Referrals</div>
            <div class="mt-2 text-2xl font-bold text-violet-700">{{ number_format($totalPublisherReferrals) }}</div>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Advertiser Earning</div>
            <div class="mt-2 text-2xl font-bold text-emerald-700">EUR {{ number_format($totalAdvertiserEarnings, 2) }}</div>
        </div>
        <div class="rounded-xl border border-brand-200 bg-brand-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Referral Income</div>
            <div class="mt-2 text-2xl font-bold text-brand-700">EUR {{ number_format($totalReferralIncome, 2) }}</div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200">
        <div class="border-b border-gray-100 px-4 py-4">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Advertiser Referrals</h2>
                    <p class="mt-1 text-sm text-gray-500">Track advertisers who registered through your referral link.</p>
                </div>
                <div class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-500">
                    <span class="font-medium text-gray-700">Share Link</span>
                    <a href="{{ $referralLink }}" target="_blank" rel="noopener noreferrer" class="max-w-[220px] truncate text-brand-600 hover:text-brand-700">{{ $referralLink }}</a>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Name</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Email</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Registration Date</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Earning</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Referral Income</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rows as $row)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $row->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $row->email }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $row->registration_date?->format('M d, Y') ?? '-' }}</td>
                            <td class="px-4 py-3 text-right font-medium text-emerald-700">EUR {{ number_format($row->earning, 2) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-brand-700">EUR {{ number_format($row->referral_income, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-gray-500">No advertiser referrals found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rows->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $rows->links() }}
            </div>
        @endif
    </div>
@endsection
