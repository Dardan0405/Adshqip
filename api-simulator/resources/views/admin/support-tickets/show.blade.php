@extends('layouts.admin')
@section('title', 'Support Ticket')
@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <a href="{{ route('admin.support-tickets') }}" class="text-sm text-brand-600 hover:text-brand-700">← Back to support tickets</a>
            <h1 class="mt-2 text-2xl font-bold text-gray-900">Ticket #{{ $supportTicket->id }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $supportTicket->subject }}</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[340px,1fr]">
        <div class="space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Ticket Settings</h2>
                <form method="POST" action="{{ route('admin.support-tickets.update', $supportTicket) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Priority</label>
                        <select name="priority" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                            @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('priority', $supportTicket->priority) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Status</label>
                        <select name="status" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                            @foreach(['open' => 'Open', 'in_progress' => 'In Progress', 'waiting_reply' => 'Waiting Reply', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $supportTicket->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Assigned To</label>
                        <select name="assigned_to" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                            <option value="">Unassigned</option>
                            @foreach($assignableAdmins as $adminUser)
                                <option value="{{ $adminUser->id }}" @selected((string) old('assigned_to', $supportTicket->assigned_to) === (string) $adminUser->id)>
                                    {{ $adminUser->email }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                        Save Ticket
                    </button>
                </form>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Requester</h2>
                <div class="text-sm text-gray-700 space-y-2">
                    <div><span class="font-medium text-gray-900">Email:</span> {{ $supportTicket->user?->email }}</div>
                    <div><span class="font-medium text-gray-900">Role:</span> {{ ucfirst((string) $supportTicket->user?->role) }}</div>
                    <div><span class="font-medium text-gray-900">Category:</span> {{ ucfirst($supportTicket->category) }}</div>
                    <div><span class="font-medium text-gray-900">Created:</span> {{ optional($supportTicket->created_at)->format('M d, Y H:i') }}</div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Conversation</h2>
                <div class="space-y-4">
                    @forelse($supportTicket->messages as $message)
                        <div class="rounded-xl border {{ $message->is_internal_note ? 'border-amber-200 bg-amber-50' : 'border-gray-200 bg-gray-50' }} p-4">
                            <div class="flex items-center justify-between gap-3 mb-2">
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $message->sender?->email ?? 'Unknown sender' }}
                                    @if($message->is_internal_note)
                                        <span class="ml-2 inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-amber-700">Internal Note</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-400">{{ optional($message->created_at)->format('M d, Y H:i') }}</div>
                            </div>
                            <div class="whitespace-pre-line text-sm text-gray-700">{{ $message->message }}</div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 p-6 text-sm text-gray-500">
                            No messages on this ticket yet.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Add Reply</h2>
                <form method="POST" action="{{ route('admin.support-tickets.reply', $supportTicket) }}" class="space-y-4">
                    @csrf
                    <textarea name="message" rows="6" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Write your reply or internal note...">{{ old('message') }}</textarea>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="is_internal_note" value="1" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        Save as internal note
                    </label>
                    <div>
                        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                            Save Reply
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
