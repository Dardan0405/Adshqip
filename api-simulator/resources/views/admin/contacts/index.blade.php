@extends('layouts.admin')

@section('title', 'Advertiser Contacts')

@section('content')
@php
    $statusClasses = [
        'active' => 'bg-emerald-100 text-emerald-700',
        'inactive' => 'bg-amber-100 text-amber-700',
        'archived' => 'bg-slate-100 text-slate-700',
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Advertiser Contacts</h1>
            <p class="mt-1 text-sm text-gray-500">View contact books created by advertiser accounts and export them for account follow-up.</p>
        </div>
        <a href="{{ route('admin.contacts.export', request()->query()) }}" class="inline-flex w-fit rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Export CSV</a>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach([
            ['label' => 'Total', 'value' => $summary['total']],
            ['label' => 'Active', 'value' => $summary['active']],
            ['label' => 'Primary', 'value' => $summary['primary']],
            ['label' => 'Contacted 30d', 'value' => $summary['recent']],
        ] as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($card['value']) }}</div>
            </div>
        @endforeach
    </div>

    <div class="rounded-xl border border-gray-200 bg-white">
        <form method="GET" action="{{ route('admin.contacts') }}" class="flex flex-wrap items-end gap-3 p-4">
            <div class="min-w-[220px] flex-1">
                <label class="mb-1 block text-xs font-medium text-gray-600">Search</label>
                <input name="search" value="{{ $filters['search'] }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="Name, email, phone, company, advertiser, or ID">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Advertiser</label>
                <select name="advertiser_id" class="max-w-[220px] rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All</option>
                    @foreach($advertisers as $advertiser)
                        <option value="{{ $advertiser->id }}" @selected((string) $filters['advertiser_id'] === (string) $advertiser->id)>
                            {{ $advertiser->userProfile?->company_name ?: $advertiser->email }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Type</label>
                <select name="type" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All</option>
                    @foreach($types as $value => $label)
                        <option value="{{ $value }}" @selected($filters['type'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Status</label>
                <select name="status" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Filter</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-y border-gray-100 bg-gray-50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Contact</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Advertiser</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Type</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Last Contacted</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($contacts as $contact)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <div class="font-semibold text-gray-900">#{{ $contact->id }} {{ $contact->name }}</div>
                                    @if($contact->is_primary)
                                        <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-blue-700">Primary</span>
                                    @endif
                                </div>
                                <div class="mt-1 text-xs text-gray-500">{{ $contact->job_title ?: 'No title' }}{{ $contact->company ? ' at ' . $contact->company : '' }}</div>
                                <div class="mt-1 flex flex-wrap gap-3 text-xs">
                                    @if($contact->email)
                                        <a href="mailto:{{ $contact->email }}" class="font-medium text-brand-700 hover:text-brand-800">{{ $contact->email }}</a>
                                    @endif
                                    @if($contact->phone)
                                        <a href="tel:{{ $contact->phone }}" class="font-medium text-gray-700 hover:text-brand-700">{{ $contact->phone }}</a>
                                    @endif
                                </div>
                                @if($contact->notes)
                                    <div class="mt-1 max-w-xl truncate text-xs text-gray-400">{{ $contact->notes }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $contact->user?->email }}</div>
                                <div class="text-xs text-gray-400">{{ $contact->user?->userProfile?->company_name ?: 'No company' }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $types[$contact->type] ?? ucfirst($contact->type) }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$contact->status] ?? 'bg-slate-100 text-slate-700' }}">{{ $statuses[$contact->status] ?? ucfirst($contact->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $contact->last_contacted_at?->format('M d, Y H:i') ?? 'Never' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-sm text-gray-500">No advertiser contacts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($contacts->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $contacts->links() }}</div>
        @endif
    </div>
</div>
@endsection
