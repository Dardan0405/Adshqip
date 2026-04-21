@extends('layouts.admin')
@section('title', 'Messages')
@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="flex flex-col gap-2 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Messages</h1>
        <p class="text-sm text-gray-500">Internal messaging between admin team members.</p>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-3 gap-3 mb-6">
        @foreach([
            ['label' => 'Inbox', 'value' => number_format($summary['total']), 'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
            ['label' => 'Unread', 'value' => number_format($summary['unread']), 'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'color' => 'brand'],
            ['label' => 'Sent', 'value' => number_format($summary['sent']), 'icon' => 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8', 'color' => 'emerald'],
        ] as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-{{ $card['color'] ?? 'gray' }}-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-{{ $card['color'] ?? 'gray' }}-600" viewBox="0 0 24 24" fill="none"><path d="{{ $card['icon'] }}" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </div>
                    <div>
                        <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                        <div class="text-xl font-bold text-gray-900">{{ $card['value'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-[350px,1fr]">
        {{-- Compose Message --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 h-fit">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Compose Message</h2>
            <form method="POST" action="{{ route('admin.messages.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Recipient</label>
                    <select name="recipient_id" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        <option value="">Select admin...</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}">{{ $admin->email }} ({{ ucfirst($admin->role) }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Subject</label>
                    <input type="text" name="subject" required maxlength="255" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Message subject...">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Priority</label>
                    <select name="priority" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        <option value="low">Low</option>
                        <option value="normal" selected>Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Message</label>
                    <textarea name="message" required rows="5" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Type your message..."></textarea>
                </div>
                <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Send Message</button>
            </form>
        </div>

        {{-- Messages List --}}
        <div class="rounded-xl border border-gray-200 bg-white">
            {{-- Filters --}}
            <div class="px-4 py-3 border-b border-gray-100 flex flex-wrap items-center gap-3">
                <form method="GET" class="flex-1 flex items-center gap-3">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search messages..." class="flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <select name="read_state" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        <option value="">All</option>
                        <option value="unread" @selected(request('read_state') === 'unread')>Unread</option>
                        <option value="read" @selected(request('read_state') === 'read')>Read</option>
                    </select>
                    <button type="submit" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Filter</button>
                </form>
                <form method="POST" action="{{ route('admin.messages.read-all') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Mark All Read</button>
                </form>
            </div>

            {{-- Messages --}}
            <div class="divide-y divide-gray-100">
                @forelse($messages as $message)
                    <a href="{{ route('admin.messages.show', $message) }}" class="block px-4 py-4 hover:bg-gray-50 transition {{ !$message->is_read ? 'bg-brand-50/50' : '' }}">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-semibold text-sm flex-shrink-0">
                                {{ strtoupper(substr($message->sender?->email ?? 'U', 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-gray-900 {{ !$message->is_read ? 'font-bold' : '' }}">{{ $message->subject }}</span>
                                    <div class="flex items-center gap-2">
                                        @if($message->priority === 'urgent')
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700">URGENT</span>
                                        @elseif($message->priority === 'high')
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700">HIGH</span>
                                        @endif
                                        @if(!$message->is_read)
                                            <div class="w-2 h-2 rounded-full bg-brand-500"></div>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">From: {{ $message->sender?->email ?? 'Unknown' }}</div>
                                <div class="text-sm text-gray-600 mt-1 line-clamp-2">{{ Str::limit($message->message, 120) }}</div>
                                <div class="text-xs text-gray-400 mt-2">{{ $message->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="px-4 py-12 text-center text-sm text-gray-500">No messages found.</div>
                @endforelse
            </div>

            @if($messages->hasPages())
                <div class="border-t border-gray-100 px-4 py-3">{{ $messages->links() }}</div>
            @endif
        </div>
    </div>
@endsection
