@extends('layouts.admin')
@section('title', 'KYC Review')

@section('content')
    @if(session('success'))
        <div class="mb-4 flex items-center gap-2 rounded-xl border border-emerald-300 bg-emerald-50 p-3 text-sm text-emerald-700">
            <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
            <p class="font-semibold">Please review the KYC review fields.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $statusClasses = match($verification->status) {
            'approved' => 'bg-emerald-100 text-emerald-700',
            'rejected' => 'bg-rose-100 text-rose-700',
            'in_review' => 'bg-blue-100 text-blue-700',
            default => 'bg-amber-100 text-amber-700',
        };
    @endphp

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('admin.kyc-verifications') }}" class="inline-flex items-center gap-2 text-sm font-medium text-brand-600 hover:text-brand-700">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"><path d="m15 18-6-6 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Back to KYC Verification
            </a>
            <h1 class="mt-3 text-2xl font-bold text-gray-900">KYC Review #{{ $verification->id }}</h1>
            <p class="mt-1 text-sm text-gray-500">Review user identity details, uploaded files, and approval outcome.</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase {{ $statusClasses }}">{{ str_replace('_', ' ', $verification->status) }}</span>
            <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">{{ $levels[$verification->verification_level] ?? ucfirst((string) $verification->verification_level) }}</span>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-blue-500">User</div>
            <div class="mt-2 text-sm font-bold text-blue-700">{{ $verification->user?->email ?? 'Unknown user' }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Submitted</div>
            <div class="mt-2 text-sm font-semibold text-gray-800">{{ optional($verification->submitted_at)->format('M d, Y H:i') ?? '—' }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Reviewed</div>
            <div class="mt-2 text-sm font-semibold text-gray-800">{{ optional($verification->reviewed_at)->format('M d, Y H:i') ?? '—' }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Rejections</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($verification->rejection_count) }}</div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr),380px]">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Verification Details</h2>
                <p class="mt-1 text-sm text-gray-500">Update the submitted KYC record and replace or add documents.</p>
            </div>

            <form method="POST" action="{{ route('admin.kyc-verifications.update', $verification) }}" enctype="multipart/form-data" class="p-6">
                @csrf
                @method('PUT')
                @include('admin.kyc-verifications._form', ['verification' => $verification])

                <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-5">
                    <a href="{{ route('admin.kyc-verifications') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Save Changes</button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-emerald-200 bg-white shadow-sm">
                <div class="border-b border-emerald-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Approve KYC</h2>
                    <p class="mt-1 text-sm text-gray-500">Approve the verification and mark uploaded documents as verified.</p>
                </div>
                <form method="POST" action="{{ route('admin.kyc-verifications.approve', $verification) }}" class="space-y-4 p-6">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Approval Notes</label>
                        <textarea name="notes" rows="4" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">{{ old('notes', $verification->notes) }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Expiry Date</label>
                        <input type="date" name="expires_at" value="{{ old('expires_at', optional($verification->expires_at)->format('Y-m-d')) }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Approve Verification</button>
                </form>
            </div>

            <div class="rounded-2xl border border-rose-200 bg-white shadow-sm">
                <div class="border-b border-rose-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Reject KYC</h2>
                    <p class="mt-1 text-sm text-gray-500">Reject the verification and record why it failed review.</p>
                </div>
                <form method="POST" action="{{ route('admin.kyc-verifications.reject', $verification) }}" class="space-y-4 p-6">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Rejection Reason</label>
                        <textarea name="rejection_reason" rows="5" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20" required>{{ old('rejection_reason', $verification->rejection_reason) }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Internal Notes</label>
                        <textarea name="notes" rows="3" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">{{ old('notes', $verification->notes) }}</textarea>
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700">Reject Verification</button>
                </form>
            </div>
        </div>
    </div>
@endsection
