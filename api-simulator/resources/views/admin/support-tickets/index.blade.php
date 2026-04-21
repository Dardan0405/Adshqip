@extends('layouts.admin')
@section('title', 'Support Tickets')
@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-2 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Support Tickets</h1>
        <p class="text-sm text-gray-500">Review ticket queue, assign owners, and continue support replies.</p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        @foreach([
            ['label' => 'Total Tickets', 'value' => number_format($summary['total'])],
            ['label' => 'Open Queue', 'value' => number_format($summary['open'])],
            ['label' => 'Resolved', 'value' => number_format($summary['resolved'])],
            ['label' => 'Urgent', 'value' => number_format($summary['urgent'])],
        ] as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="rounded-xl border border-gray-200 bg-white mb-6">
        <form method="GET" action="{{ route('admin.support-tickets') }}" class="p-4 flex flex-wrap gap-2 items-center">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search subject or user email"
                   class="min-w-[220px] flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
            <select name="status" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                @foreach(['open' => 'Open', 'in_progress' => 'In Progress', 'waiting_reply' => 'Waiting Reply', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="priority" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" onchange="this.form.submit()">
                <option value="">All Priorities</option>
                @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('priority') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="category" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach(['billing' => 'Billing', 'technical' => 'Technical', 'campaign' => 'Campaign', 'account' => 'Account', 'fraud' => 'Fraud', 'other' => 'Other'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="assigned_to" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" onchange="this.form.submit()">
                <option value="">All Assignees</option>
                @foreach($assignableAdmins as $adminUser)
                    <option value="{{ $adminUser->id }}" @selected((string) request('assigned_to') === (string) $adminUser->id)>
                        {{ $adminUser->email }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Search</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-y border-gray-100">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Subject</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">User</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Category</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Priority</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Assigned To</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Messages</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 font-semibold text-gray-900">#{{ $ticket->id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $ticket->subject }}</div>
                                <div class="text-xs text-gray-400">{{ optional($ticket->created_at)->diffForHumans() }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $ticket->user?->email }}</div>
                                <div class="text-xs text-gray-400">{{ ucfirst((string) $ticket->user?->role) }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ ucfirst(str_replace('_', ' ', $ticket->category)) }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ match($ticket->priority) {
                                    'urgent' => 'bg-rose-100 text-rose-700',
                                    'high' => 'bg-orange-100 text-orange-700',
                                    'medium' => 'bg-amber-100 text-amber-700',
                                    default => 'bg-slate-100 text-slate-700',
                                } }}">{{ ucfirst($ticket->priority) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ match($ticket->status) {
                                    'resolved' => 'bg-emerald-100 text-emerald-700',
                                    'closed' => 'bg-slate-100 text-slate-700',
                                    'waiting_reply' => 'bg-indigo-100 text-indigo-700',
                                    'in_progress' => 'bg-blue-100 text-blue-700',
                                    default => 'bg-amber-100 text-amber-700',
                                } }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $ticket->assignedTo?->email ?? 'Unassigned' }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-700">{{ number_format($ticket->messages_count) }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.support-tickets.show', $ticket) }}" class="inline-flex rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-sm text-gray-500">No support tickets found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
@endsection
