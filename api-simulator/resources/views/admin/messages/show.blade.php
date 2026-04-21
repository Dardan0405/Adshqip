@extends('layouts.admin')
@section('title', 'Message: ' . Str::limit($message->subject, 30))
@section('content')
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.messages') }}" class="p-2 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="flex-1">
            <h1 class="text-xl font-bold text-gray-900">{{ $message->subject }}</h1>
            <p class="text-sm text-gray-500">Message from {{ $message->sender?->email ?? 'Unknown' }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($message->priority === 'urgent')
                <span class="px-2 py-1 rounded text-xs font-bold bg-red-100 text-red-700">URGENT</span>
            @elseif($message->priority === 'high')
                <span class="px-2 py-1 rounded text-xs font-bold bg-amber-100 text-amber-700">HIGH PRIORITY</span>
            @elseif($message->priority === 'low')
                <span class="px-2 py-1 rounded text-xs font-bold bg-gray-100 text-gray-600">LOW</span>
            @endif
            <form method="POST" action="{{ route('admin.messages.archive', $message) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">Archive</button>
            </form>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white">
        {{-- Message Header --}}
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-bold text-lg">
                    {{ strtoupper(substr($message->sender?->email ?? 'U', 0, 2)) }}
                </div>
                <div class="flex-1">
                    <div class="text-sm font-semibold text-gray-900">{{ $message->sender?->email ?? 'Unknown' }}</div>
                    <div class="text-xs text-gray-500">{{ ucfirst($message->sender?->role ?? 'admin') }}</div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-600">{{ $message->created_at->format('M d, Y') }}</div>
                    <div class="text-xs text-gray-400">{{ $message->created_at->format('h:i A') }}</div>
                </div>
            </div>
        </div>

        {{-- Message Body --}}
        <div class="px-6 py-6">
            <div class="prose prose-sm max-w-none text-gray-700 whitespace-pre-wrap">{{ $message->message }}</div>
        </div>

        {{-- Message Footer --}}
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <div class="text-xs text-gray-500">
                @if($message->is_read)
                    <span class="flex items-center gap-1"><svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg> Read {{ $message->read_at?->diffForHumans() }}</span>
                @else
                    Unread
                @endif
            </div>
            <a href="{{ route('admin.messages') }}?reply_to={{ $message->sender_id }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                Reply
            </a>
        </div>
    </div>
@endsection
