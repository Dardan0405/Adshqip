@extends('layouts.admin')
@section('title', 'KYC Verification')

@section('content')
    @if(session('success'))
        <div class="mb-4 flex items-center gap-2 rounded-xl border border-emerald-300 bg-emerald-50 p-3 text-sm text-emerald-700">
            <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
            <p class="font-semibold">Please review the KYC form fields.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">KYC Verification</h1>
            <p class="mt-1 text-sm text-gray-500">Review user identity, business documents, and approval decisions from one place.</p>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-blue-500">Total</div>
            <div class="mt-2 text-2xl font-bold text-blue-700">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-amber-500">Pending Review</div>
            <div class="mt-2 text-2xl font-bold text-amber-700">{{ number_format($stats['pending']) }}</div>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-emerald-500">Approved</div>
            <div class="mt-2 text-2xl font-bold text-emerald-700">{{ number_format($stats['approved']) }}</div>
        </div>
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-rose-500">Rejected</div>
            <div class="mt-2 text-2xl font-bold text-rose-700">{{ number_format($stats['rejected']) }}</div>
        </div>
    </div>

    <div class="mb-6 rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Create KYC Verification</h2>
            <p class="mt-1 text-sm text-gray-500">Start a new KYC review and upload the first supporting documents.</p>
        </div>

        <form method="POST" action="{{ route('admin.kyc-verifications.store') }}" enctype="multipart/form-data" class="p-6">
            @csrf
            @include('admin.kyc-verifications._form')

            <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-5">
                <button type="reset" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</button>
                <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Submit KYC</button>
            </div>
        </form>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Verification Queue</h2>
                <p class="mt-1 text-sm text-gray-500">Review uploaded KYC cases, documents, and approval state.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/80">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">User</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Level</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Documents</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Submitted</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Reviewed</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($verifications as $verification)
                        @php
                            $fullName = trim(($verification->legal_first_name ?? '') . ' ' . ($verification->legal_last_name ?? ''));
                            $statusClasses = match($verification->status) {
                                'approved' => 'bg-emerald-100 text-emerald-700',
                                'rejected' => 'bg-rose-100 text-rose-700',
                                'in_review' => 'bg-blue-100 text-blue-700',
                                default => 'bg-amber-100 text-amber-700',
                            };
                        @endphp
                        <tr class="transition-colors hover:bg-gray-50/60">
                            <td class="px-4 py-3 font-medium text-gray-900">#{{ $verification->id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $verification->user?->email ?? 'Unknown user' }}</div>
                                <div class="text-xs text-gray-500">{{ $fullName ?: 'No legal name saved' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">{{ $levels[$verification->verification_level] ?? ucfirst((string) $verification->verification_level) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold uppercase {{ $statusClasses }}">{{ str_replace('_', ' ', $verification->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $verification->documents->count() }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ optional($verification->submitted_at)->format('M d, Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ optional($verification->reviewed_at)->format('M d, Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end">
                                    <a href="{{ route('admin.kyc-verifications.edit', $verification) }}" class="rounded-lg border border-brand-200 px-3 py-1.5 text-xs font-semibold text-brand-600 hover:bg-brand-50">Review</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="h-12 w-12 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M12 3 4 7v5c0 5 3.5 8.5 8 9 4.5-.5 8-4 8-9V7l-8-4Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    <p class="text-sm text-gray-500">No KYC verifications submitted yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($verifications->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $verifications->links() }}
            </div>
        @endif
    </div>
@endsection
