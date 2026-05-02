@extends('layouts.publisher')

@section('title', 'Help Center')

@section('content')
@php
    $statusClasses = [
        'open' => 'bg-blue-50 text-blue-700 border-blue-200',
        'in_progress' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'waiting_reply' => 'bg-amber-50 text-amber-700 border-amber-200',
        'resolved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'closed' => 'bg-gray-50 text-gray-700 border-gray-200',
    ];

    $priorityClasses = [
        'low' => 'text-gray-600',
        'medium' => 'text-blue-700',
        'high' => 'text-amber-700',
        'urgent' => 'text-red-700',
    ];
@endphp

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Help Center</h1>
        <p class="mt-1 text-sm text-gray-500">Find answers, create support tickets, and follow replies from the Adshqip team.</p>
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

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Open Tickets</p>
            <p class="mt-2 text-2xl font-bold text-blue-700">{{ number_format($summary['open']) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Resolved</p>
            <p class="mt-2 text-2xl font-bold text-emerald-700">{{ number_format($summary['resolved']) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Urgent</p>
            <p class="mt-2 text-2xl font-bold text-red-700">{{ number_format($summary['urgent']) }}</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm xl:col-span-1">
            <h2 class="text-lg font-semibold text-gray-900">Create Ticket</h2>
            <form method="POST" action="{{ route('publisher.help-center.tickets.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="text-sm font-medium text-gray-700">Subject</label>
                    <input name="subject" value="{{ old('subject') }}" required maxlength="255" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="Briefly describe the issue">
                </div>
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Category</label>
                        <select name="category" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            @foreach($categories as $value => $label)
                                <option value="{{ $value }}" {{ old('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Priority</label>
                        <select name="priority" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            @foreach($priorities as $value => $label)
                                <option value="{{ $value }}" {{ old('priority', 'medium') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Message</label>
                    <textarea name="message" required rows="6" maxlength="5000" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="Add details, links, IDs, or screenshots URL if needed">{{ old('message') }}</textarea>
                </div>
                <button class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Submit Ticket</button>
            </form>
        </div>

        <div class="space-y-6 xl:col-span-2">
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <form method="GET" action="{{ route('publisher.help-center') }}" class="grid flex-1 gap-3 sm:grid-cols-3">
                        <div class="sm:col-span-2">
                            <label class="text-sm font-medium text-gray-700">Search FAQs</label>
                            <input name="q" value="{{ $filters['q'] }}" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="Search billing, campaigns, tracking...">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700">FAQ Category</label>
                            <select name="category" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                                <option value="">All categories</option>
                                @foreach($faqCategories as $category)
                                    <option value="{{ $category }}" {{ $filters['category'] === $category ? 'selected' : '' }}>{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="sm:col-span-3 w-fit rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Search</button>
                    </form>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-5 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Frequently Asked Questions</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($faqs as $faq)
                        <details class="group px-5 py-4">
                            <summary class="flex cursor-pointer list-none items-start justify-between gap-4">
                                <span>
                                    <span class="block text-xs font-semibold uppercase tracking-wider text-brand-600">{{ $faq->category }}</span>
                                    <span class="mt-1 block font-semibold text-gray-900">{{ $faq->question }}</span>
                                </span>
                                <span class="text-gray-400 group-open:rotate-180">v</span>
                            </summary>
                            <div class="mt-3 text-sm leading-6 text-gray-600">{!! nl2br(e($faq->answer)) !!}</div>
                        </details>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-gray-500">No FAQs matched your search.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Your Support Tickets</h2>
                <form method="GET" action="{{ route('publisher.help-center') }}">
                    <select name="status" onchange="this.form.submit()" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <option value="">All statuses</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ $filters['status'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-5 py-3">Ticket</th>
                        <th class="px-5 py-3">Category</th>
                        <th class="px-5 py-3">Priority</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Messages</th>
                        <th class="px-5 py-3">Updated</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <a href="{{ route('publisher.help-center.tickets.show', $ticket) }}" class="font-semibold text-brand-700 hover:text-brand-800">#{{ $ticket->id }} {{ $ticket->subject }}</a>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ $categories[$ticket->category] ?? ucfirst($ticket->category) }}</td>
                            <td class="px-5 py-4 font-semibold {{ $priorityClasses[$ticket->priority] ?? 'text-gray-600' }}">{{ $priorities[$ticket->priority] ?? ucfirst($ticket->priority) }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$ticket->status] ?? 'bg-gray-50 text-gray-700 border-gray-200' }}">{{ $statuses[$ticket->status] ?? ucfirst($ticket->status) }}</span>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ number_format($ticket->messages_count) }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $ticket->updated_at?->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-gray-500">No support tickets yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tickets->hasPages())
            <div class="border-t border-gray-100 px-5 py-4">{{ $tickets->links() }}</div>
        @endif
    </div>
</div>
@endsection
