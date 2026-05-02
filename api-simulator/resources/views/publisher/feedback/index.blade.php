@extends('layouts.publisher')

@section('title', 'Feedback')

@section('content')
@php
    $statusClasses = [
        'submitted' => 'bg-blue-50 text-blue-700 border-blue-200',
        'reviewed' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'planned' => 'bg-amber-50 text-amber-700 border-amber-200',
        'resolved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'closed' => 'bg-gray-50 text-gray-700 border-gray-200',
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Feedback</h1>
            <p class="mt-1 text-sm text-gray-500">Share product feedback, report bugs, and track your submissions.</p>
        </div>
        <a href="{{ route('publisher.feedback.export', request()->query()) }}" class="inline-flex w-fit rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Export CSV</a>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="grid gap-4 sm:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($summary['total']) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Submitted</p>
            <p class="mt-2 text-2xl font-bold text-blue-700">{{ number_format($summary['submitted']) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Planned</p>
            <p class="mt-2 text-2xl font-bold text-amber-700">{{ number_format($summary['planned']) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Resolved</p>
            <p class="mt-2 text-2xl font-bold text-emerald-700">{{ number_format($summary['resolved']) }}</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Send Feedback</h2>
            <form method="POST" action="{{ route('publisher.feedback.store') }}" class="mt-4 space-y-4">
                @csrf
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Type</label>
                        <select name="type" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" {{ old('type', 'general') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Rating</label>
                        <select name="rating" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            <option value="">No rating</option>
                            @for($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" {{ (string) old('rating') === (string) $i ? 'selected' : '' }}>{{ $i }} / 5</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Subject</label>
                    <input name="subject" value="{{ old('subject') }}" required maxlength="255" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="What should we improve?">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Page URL</label>
                    <input type="url" name="page_url" value="{{ old('page_url') }}" maxlength="500" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="https://...">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Message</label>
                    <textarea name="message" required rows="7" maxlength="5000" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="Describe your feedback clearly.">{{ old('message') }}</textarea>
                </div>
                <button class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Submit Feedback</button>
            </form>
        </div>

        <div class="space-y-4 xl:col-span-2">
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <form method="GET" action="{{ route('publisher.feedback') }}" class="grid gap-3 lg:grid-cols-4">
                    <div class="lg:col-span-2">
                        <label class="text-sm font-medium text-gray-700">Search</label>
                        <input name="search" value="{{ $filters['search'] }}" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="Subject, message, or ID">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Type</label>
                        <select name="type" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            <option value="">All</option>
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" {{ $filters['type'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Status</label>
                        <select name="status" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            <option value="">All</option>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" {{ $filters['status'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lg:col-span-4">
                        <button class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Apply Filters</button>
                    </div>
                </form>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-5 py-3">Feedback</th>
                                <th class="px-5 py-3">Type</th>
                                <th class="px-5 py-3">Rating</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3">Updated</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($feedback as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-4">
                                        <a href="{{ route('publisher.feedback.show', $item) }}" class="font-semibold text-brand-700 hover:text-brand-800">#{{ $item->id }} {{ $item->subject }}</a>
                                        <p class="mt-1 line-clamp-1 max-w-lg text-xs text-gray-500">{{ $item->message }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-gray-600">{{ $types[$item->type] ?? ucfirst($item->type) }}</td>
                                    <td class="px-5 py-4 text-gray-600">{{ $item->rating ? $item->rating . ' / 5' : '-' }}</td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$item->status] ?? 'bg-gray-50 text-gray-700 border-gray-200' }}">{{ $statuses[$item->status] ?? ucfirst($item->status) }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-gray-600">{{ $item->updated_at?->format('M d, Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-gray-500">No feedback found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($feedback->hasPages())
                    <div class="border-t border-gray-100 px-5 py-4">{{ $feedback->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
