@extends('layouts.admin')

@section('title', 'Advertiser Feedback')

@section('content')
@php
    $statusClasses = [
        'submitted' => 'bg-blue-100 text-blue-700',
        'reviewed' => 'bg-indigo-100 text-indigo-700',
        'planned' => 'bg-amber-100 text-amber-700',
        'resolved' => 'bg-emerald-100 text-emerald-700',
        'closed' => 'bg-slate-100 text-slate-700',
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Advertiser Feedback</h1>
            <p class="mt-1 text-sm text-gray-500">Review product feedback from advertisers and turn useful quotes into draft testimonials.</p>
        </div>
        <a href="{{ route('admin.feedback.export', request()->query()) }}" class="inline-flex w-fit rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Export CSV</a>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach([
            ['label' => 'Total', 'value' => $summary['total']],
            ['label' => 'Submitted', 'value' => $summary['submitted']],
            ['label' => 'Planned', 'value' => $summary['planned']],
            ['label' => 'Resolved', 'value' => $summary['resolved']],
        ] as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($card['value']) }}</div>
            </div>
        @endforeach
    </div>

    <div class="rounded-xl border border-gray-200 bg-white">
        <form method="GET" action="{{ route('admin.feedback') }}" class="flex flex-wrap items-end gap-3 p-4">
            <div class="min-w-[220px] flex-1">
                <label class="mb-1 block text-xs font-medium text-gray-600">Search</label>
                <input name="search" value="{{ $filters['search'] }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="Subject, message, email, company, or ID">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Type</label>
                <select name="type" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All</option>
                    @foreach($types as $value => $label)
                        <option value="{{ $value }}" @selected($filters['type'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Status</label>
                <select name="status" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Rating</label>
                <select name="rating" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All</option>
                    @for($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" @selected((string) $filters['rating'] === (string) $i)>{{ $i }} / 5</option>
                    @endfor
                </select>
            </div>
            <button class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Filter</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-y border-gray-100 bg-gray-50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Feedback</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Advertiser</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Type</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Rating</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($feedback as $item)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">#{{ $item->id }} {{ $item->subject }}</div>
                                <div class="mt-1 max-w-xl truncate text-xs text-gray-500">{{ $item->message }}</div>
                                <div class="mt-1 text-xs text-gray-400">{{ $item->updated_at?->diffForHumans() }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $item->user?->email }}</div>
                                <div class="text-xs text-gray-400">{{ $item->user?->userProfile?->company_name ?: 'No company' }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $types[$item->type] ?? ucfirst($item->type) }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $item->rating ? $item->rating . ' / 5' : '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$item->status] ?? 'bg-slate-100 text-slate-700' }}">{{ $statuses[$item->status] ?? ucfirst($item->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.feedback.show', $item) }}" class="inline-flex rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">Review</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500">No advertiser feedback found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($feedback->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $feedback->links() }}</div>
        @endif
    </div>
</div>
@endsection
