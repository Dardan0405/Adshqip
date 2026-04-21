@extends('layouts.admin')
@section('title', 'Notifications')
@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-2 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
        <p class="text-sm text-gray-500">Send platform notifications and review read or unread delivery status.</p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        @foreach([
            ['label' => 'Total', 'value' => number_format($summary['total'])],
            ['label' => 'Unread', 'value' => number_format($summary['unread'])],
            ['label' => 'Read', 'value' => number_format($summary['read'])],
            ['label' => 'Today', 'value' => number_format($summary['today'])],
        ] as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-[380px,1fr]">
        <div class="rounded-xl border border-gray-200 bg-white p-5 h-fit">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Send Notification</h2>
            <form method="POST" action="{{ route('admin.notifications.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Recipient</label>
                    <select name="user_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->email }} ({{ ucfirst($user->role) }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Type</label>
                    <select name="type" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        @foreach(['info' => 'Info', 'success' => 'Success', 'warning' => 'Warning', 'error' => 'Error', 'payment' => 'Payment', 'campaign' => 'Campaign', 'system' => 'System', 'push' => 'Push', 'broadcast' => 'Broadcast'] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Title</label>
                    <input type="text" name="title" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Notification title">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Message</label>
                    <textarea name="message" rows="5" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="What should the user see?"></textarea>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Action URL</label>
                    <input type="url" name="action_url" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="https://example.com/path">
                </div>
                <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                    Send Notification
                </button>
            </form>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white">
            <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:items-center sm:justify-between">
                <form method="GET" action="{{ route('admin.notifications') }}" class="flex flex-wrap items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title, message, or user"
                           class="min-w-[220px] rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <select name="type" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        @foreach(['info', 'success', 'warning', 'error', 'payment', 'campaign', 'system', 'push', 'broadcast'] as $type)
                            <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                    <select name="read_state" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" onchange="this.form.submit()">
                        <option value="">Read + Unread</option>
                        <option value="unread" @selected(request('read_state') === 'unread')>Unread</option>
                        <option value="read" @selected(request('read_state') === 'read')>Read</option>
                    </select>
                    <button type="submit" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Search</button>
                </form>
                <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Mark All Read
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">User</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Type</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Notification</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Created</th>
                            <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($notifications as $notification)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-4 py-3 font-semibold text-gray-900">#{{ $notification->id }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $notification->user?->email }}</div>
                                    <div class="text-xs text-gray-400">{{ ucfirst((string) $notification->user?->role) }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold bg-gray-100 text-gray-700">{{ ucfirst($notification->type) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $notification->title }}</div>
                                    <div class="mt-1 text-xs text-gray-500">{{ \Illuminate\Support\Str::limit((string) $notification->message, 110) }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $notification->is_read ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $notification->is_read ? 'Read' : 'Unread' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ optional($notification->created_at)->format('M d, Y H:i') }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if($notification->is_read)
                                        <form method="POST" action="{{ route('admin.notifications.unread', $notification) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                                Mark Unread
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.notifications.read', $notification) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                                Mark Read
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-sm text-gray-500">No notifications found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($notifications->hasPages())
                <div class="border-t border-gray-100 px-4 py-3">{{ $notifications->links() }}</div>
            @endif
        </div>
    </div>
@endsection
