@extends('layouts.publisher')

@section('title', 'Support Ticket')

@section('content')
@php
    $statusClasses = [
        'open' => 'bg-blue-50 text-blue-700 border-blue-200',
        'in_progress' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'waiting_reply' => 'bg-amber-50 text-amber-700 border-amber-200',
        'resolved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'closed' => 'bg-gray-50 text-gray-700 border-gray-200',
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('publisher.help-center') }}" class="text-sm font-semibold text-brand-700 hover:text-brand-800">Back to Help Center</a>
            <h1 class="mt-2 text-2xl font-semibold text-gray-900">#{{ $ticket->id }} {{ $ticket->subject }}</h1>
            <div class="mt-3 flex flex-wrap gap-2">
                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">{{ $categories[$ticket->category] ?? ucfirst($ticket->category) }}</span>
                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">{{ $priorities[$ticket->priority] ?? ucfirst($ticket->priority) }}</span>
                <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$ticket->status] ?? 'bg-gray-50 text-gray-700 border-gray-200' }}">{{ $statuses[$ticket->status] ?? ucfirst($ticket->status) }}</span>
            </div>
        </div>
        @if(! in_array($ticket->status, ['resolved', 'closed'], true))
            <form method="POST" action="{{ route('publisher.help-center.tickets.close', $ticket) }}">
                @csrf
                @method('PATCH')
                <button class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Close Ticket</button>
            </form>
        @endif
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

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Conversation</h2>
        </div>
        <div class="space-y-4 p-5">
            @forelse($ticket->messages as $message)
                @php
                    $isUser = (int) $message->sender_id === (int) auth()->id();
                    $profile = $message->sender?->userProfile ?? $message->sender?->profile;
                    $name = trim((string) (($profile?->first_name ?? '') . ' ' . ($profile?->last_name ?? ''))) ?: ($message->sender?->email ?? 'Support');
                @endphp
                <div class="flex {{ $isUser ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-3xl rounded-lg border px-4 py-3 {{ $isUser ? 'border-brand-200 bg-brand-50' : 'border-gray-200 bg-gray-50' }}">
                        <div class="flex items-center justify-between gap-4">
                            <p class="text-sm font-semibold {{ $isUser ? 'text-brand-800' : 'text-gray-900' }}">{{ $isUser ? 'You' : $name }}</p>
                            <p class="text-xs text-gray-400">{{ $message->created_at?->format('M d, Y H:i') }}</p>
                        </div>
                        <div class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-700">{{ $message->message }}</div>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-sm text-gray-500">No messages yet.</div>
            @endforelse
        </div>
    </div>

    @if(! in_array($ticket->status, ['resolved', 'closed'], true))
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Reply</h2>
            <form method="POST" action="{{ route('publisher.help-center.tickets.reply', $ticket) }}" class="mt-4 space-y-4">
                @csrf
                <textarea name="message" required rows="5" maxlength="5000" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="Write your reply...">{{ old('message') }}</textarea>
                <button class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Send Reply</button>
            </form>
        </div>
    @endif
</div>
@endsection
