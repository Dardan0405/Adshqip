@extends('layouts.publisher')

@section('title', 'Feedback Detail')

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
            <a href="{{ route('publisher.feedback') }}" class="text-sm font-semibold text-brand-700 hover:text-brand-800">Back to Feedback</a>
            <h1 class="mt-2 text-2xl font-semibold text-gray-900">#{{ $item->id }} {{ $item->subject }}</h1>
            <div class="mt-3 flex flex-wrap gap-2">
                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">{{ $types[$item->type] ?? ucfirst($item->type) }}</span>
                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">{{ $item->rating ? $item->rating . ' / 5' : 'No rating' }}</span>
                <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$item->status] ?? 'bg-gray-50 text-gray-700 border-gray-200' }}">{{ $statuses[$item->status] ?? ucfirst($item->status) }}</span>
            </div>
        </div>
        @if($item->status !== 'closed')
            <form method="POST" action="{{ route('publisher.feedback.close', $item) }}">
                @csrf
                @method('PATCH')
                <button class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Close Feedback</button>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm xl:col-span-2">
            <h2 class="text-lg font-semibold text-gray-900">Feedback Details</h2>
            <div class="mt-4 whitespace-pre-line text-sm leading-6 text-gray-700">{{ $item->message }}</div>
            @if($item->page_url)
                <a href="{{ $item->page_url }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-block break-all text-sm font-semibold text-brand-700 hover:text-brand-800">{{ $item->page_url }}</a>
            @endif
            @if($item->admin_response)
                <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                    <p class="text-sm font-semibold text-emerald-900">Response</p>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-emerald-800">{{ $item->admin_response }}</p>
                </div>
            @endif
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Timeline</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div>
                    <dt class="font-medium text-gray-500">Created</dt>
                    <dd class="text-gray-900">{{ $item->created_at?->format('M d, Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">Updated</dt>
                    <dd class="text-gray-900">{{ $item->updated_at?->format('M d, Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">Reviewed</dt>
                    <dd class="text-gray-900">{{ $item->reviewed_at?->format('M d, Y H:i') ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">Closed</dt>
                    <dd class="text-gray-900">{{ $item->closed_at?->format('M d, Y H:i') ?? '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    @if(in_array($item->status, ['submitted', 'reviewed'], true))
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Edit Feedback</h2>
            <form method="POST" action="{{ route('publisher.feedback.update', $item) }}" class="mt-4 grid gap-4 lg:grid-cols-2">
                @csrf
                @method('PUT')
                <div>
                    <label class="text-sm font-medium text-gray-700">Type</label>
                    <select name="type" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        @foreach($types as $value => $label)
                            <option value="{{ $value }}" {{ old('type', $item->type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Rating</label>
                    <select name="rating" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <option value="">No rating</option>
                        @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" {{ (string) old('rating', $item->rating) === (string) $i ? 'selected' : '' }}>{{ $i }} / 5</option>
                        @endfor
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label class="text-sm font-medium text-gray-700">Subject</label>
                    <input name="subject" value="{{ old('subject', $item->subject) }}" required maxlength="255" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                </div>
                <div class="lg:col-span-2">
                    <label class="text-sm font-medium text-gray-700">Page URL</label>
                    <input type="url" name="page_url" value="{{ old('page_url', $item->page_url) }}" maxlength="500" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                </div>
                <div class="lg:col-span-2">
                    <label class="text-sm font-medium text-gray-700">Message</label>
                    <textarea name="message" required rows="6" maxlength="5000" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">{{ old('message', $item->message) }}</textarea>
                </div>
                <div class="lg:col-span-2">
                    <button class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Save Changes</button>
                </div>
            </form>
        </div>
    @endif
</div>
@endsection
