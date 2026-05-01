@extends('layouts.admin')

@section('title', 'Review Feedback')

@section('content')
@php
    $profile = $item->user?->userProfile;
    $name = trim((string) (($profile?->first_name ?? '') . ' ' . ($profile?->last_name ?? ''))) ?: $item->user?->email;
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('admin.feedback') }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700">Back to Feedback</a>
            <h1 class="mt-2 text-2xl font-bold text-gray-900">#{{ $item->id }} {{ $item->subject }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $name }} · {{ $item->user?->email }}</p>
        </div>
        @if($item->rating)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">Rating: {{ $item->rating }} / 5</div>
        @endif
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-5 lg:col-span-2">
            <div class="mb-4 flex flex-wrap gap-2">
                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">{{ $types[$item->type] ?? ucfirst($item->type) }}</span>
                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">{{ $statuses[$item->status] ?? ucfirst($item->status) }}</span>
            </div>
            <h2 class="text-lg font-semibold text-gray-900">Message</h2>
            <div class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-700">{{ $item->message }}</div>
            @if($item->page_url)
                <a href="{{ $item->page_url }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-block break-all text-sm font-semibold text-brand-600 hover:text-brand-700">{{ $item->page_url }}</a>
            @endif
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <h2 class="text-lg font-semibold text-gray-900">Advertiser</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div>
                    <dt class="font-medium text-gray-500">Name</dt>
                    <dd class="text-gray-900">{{ $name }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">Company</dt>
                    <dd class="text-gray-900">{{ $profile?->company_name ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">Created</dt>
                    <dd class="text-gray-900">{{ $item->created_at?->format('M d, Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">Updated</dt>
                    <dd class="text-gray-900">{{ $item->updated_at?->format('M d, Y H:i') }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <h2 class="text-lg font-semibold text-gray-900">Review Feedback</h2>
            <form method="POST" action="{{ route('admin.feedback.update', $item) }}" class="mt-4 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $item->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Admin response</label>
                    <textarea name="admin_response" rows="6" maxlength="5000" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">{{ old('admin_response', $item->admin_response) }}</textarea>
                </div>
                <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Save Review</button>
            </form>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <h2 class="text-lg font-semibold text-gray-900">Marketing Use</h2>
            <p class="mt-2 text-sm leading-6 text-gray-600">If this feedback is a good public quote, create a draft testimonial. It will not publish automatically.</p>
            <form method="POST" action="{{ route('admin.feedback.testimonial', $item) }}" class="mt-4">
                @csrf
                <button class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Create Draft Testimonial</button>
            </form>
        </div>
    </div>
</div>
@endsection
