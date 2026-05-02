@extends('layouts.publisher')

@section('title', 'Contacts')

@section('content')
@php
    $statusClasses = [
        'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'inactive' => 'bg-amber-50 text-amber-700 border-amber-200',
        'archived' => 'bg-gray-50 text-gray-700 border-gray-200',
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Contacts</h1>
            <p class="mt-1 text-sm text-gray-500">Keep publisher-side contacts in one place and quickly reach platform support teams.</p>
        </div>
        <a href="{{ route('publisher.contacts.export', request()->query()) }}" class="inline-flex w-fit rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Export CSV</a>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="grid gap-4 sm:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($summary['total']) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Active</p>
            <p class="mt-2 text-2xl font-bold text-emerald-700">{{ number_format($summary['active']) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Primary</p>
            <p class="mt-2 text-2xl font-bold text-blue-700">{{ number_format($summary['primary']) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Contacted 30d</p>
            <p class="mt-2 text-2xl font-bold text-indigo-700">{{ number_format($summary['recent']) }}</p>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        @foreach($platformContacts as $platformContact)
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">{{ $platformContact['label'] }}</p>
                <h2 class="mt-2 text-lg font-semibold text-gray-900">{{ $platformContact['name'] }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $platformContact['description'] }}</p>
                <div class="mt-4 space-y-2 text-sm">
                    @if($platformContact['email'])
                        <a href="mailto:{{ $platformContact['email'] }}" class="block font-medium text-brand-700 hover:text-brand-800">{{ $platformContact['email'] }}</a>
                    @endif
                    @if($platformContact['phone'])
                        <a href="tel:{{ $platformContact['phone'] }}" class="block font-medium text-gray-700 hover:text-brand-700">{{ $platformContact['phone'] }}</a>
                    @endif
                    @if(! $platformContact['email'] && ! $platformContact['phone'])
                        <p class="text-gray-500">Open a support ticket to reach this team.</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Add Contact</h2>
            <form method="POST" action="{{ route('publisher.contacts.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="text-sm font-medium text-gray-700">Name</label>
                    <input name="name" value="{{ old('name') }}" required maxlength="150" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="Jane Cooper">
                </div>
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" maxlength="255" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="jane@example.com">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Phone</label>
                        <input name="phone" value="{{ old('phone') }}" maxlength="40" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="+355 ...">
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Company</label>
                        <input name="company" value="{{ old('company') }}" maxlength="180" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="Company name">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Job Title</label>
                        <input name="job_title" value="{{ old('job_title') }}" maxlength="150" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="Marketing Manager">
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Type</label>
                        <select name="type" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" {{ old('type', 'client') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Status</label>
                        <select name="status" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" {{ old('status', 'active') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
                    <input type="checkbox" name="is_primary" value="1" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" {{ old('is_primary') ? 'checked' : '' }}>
                    Primary contact
                </label>
                <div>
                    <label class="text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" rows="4" maxlength="5000" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="Useful context, preferences, or next steps.">{{ old('notes') }}</textarea>
                </div>
                <button class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Create Contact</button>
            </form>
        </div>

        <div class="space-y-4 xl:col-span-2">
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <form method="GET" action="{{ route('publisher.contacts') }}" class="grid gap-3 lg:grid-cols-5">
                    <div class="lg:col-span-2">
                        <label class="text-sm font-medium text-gray-700">Search</label>
                        <input name="search" value="{{ $filters['search'] }}" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="Name, email, phone, company, or ID">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Type</label>
                        <select name="type" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            <option value="">All</option>
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" {{ $filters['type'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Status</label>
                        <select name="status" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            <option value="">All</option>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" {{ $filters['status'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Apply</button>
                    </div>
                </form>
            </div>

            <div class="grid gap-4">
                @forelse($contacts as $contact)
                    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-lg font-semibold text-gray-900">#{{ $contact->id }} {{ $contact->name }}</h3>
                                    @if($contact->is_primary)
                                        <span class="rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">Primary</span>
                                    @endif
                                    <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$contact->status] ?? 'bg-gray-50 text-gray-700 border-gray-200' }}">{{ $statuses[$contact->status] ?? ucfirst($contact->status) }}</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">{{ $contact->job_title ?: 'No title' }}{{ $contact->company ? ' at ' . $contact->company : '' }}</p>
                                <div class="mt-3 flex flex-wrap gap-3 text-sm">
                                    @if($contact->email)
                                        <a href="mailto:{{ $contact->email }}" class="font-medium text-brand-700 hover:text-brand-800">{{ $contact->email }}</a>
                                    @endif
                                    @if($contact->phone)
                                        <a href="tel:{{ $contact->phone }}" class="font-medium text-gray-700 hover:text-brand-700">{{ $contact->phone }}</a>
                                    @endif
                                    <span class="text-gray-500">{{ $types[$contact->type] ?? ucfirst($contact->type) }}</span>
                                    <span class="text-gray-500">Last contacted: {{ $contact->last_contacted_at?->format('M d, Y H:i') ?? 'Never' }}</span>
                                </div>
                                @if($contact->notes)
                                    <p class="mt-3 text-sm text-gray-600">{{ $contact->notes }}</p>
                                @endif
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('publisher.contacts.touch', $contact) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">Mark Contacted</button>
                                </form>
                                <form method="POST" action="{{ route('publisher.contacts.destroy', $contact) }}" onsubmit="return confirm('Delete this contact?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50">Delete</button>
                                </form>
                            </div>
                        </div>

                        <details class="mt-4 rounded-lg border border-gray-100 bg-gray-50 p-4">
                            <summary class="cursor-pointer text-sm font-semibold text-gray-700">Edit Contact</summary>
                            <form method="POST" action="{{ route('publisher.contacts.update', $contact) }}" class="mt-4 grid gap-3 lg:grid-cols-2">
                                @csrf
                                @method('PUT')
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Name</label>
                                    <input name="name" value="{{ old('name', $contact->name) }}" required maxlength="150" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Email</label>
                                    <input type="email" name="email" value="{{ old('email', $contact->email) }}" maxlength="255" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Phone</label>
                                    <input name="phone" value="{{ old('phone', $contact->phone) }}" maxlength="40" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Company</label>
                                    <input name="company" value="{{ old('company', $contact->company) }}" maxlength="180" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Job Title</label>
                                    <input name="job_title" value="{{ old('job_title', $contact->job_title) }}" maxlength="150" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Type</label>
                                        <select name="type" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                                            @foreach($types as $value => $label)
                                                <option value="{{ $value }}" {{ old('type', $contact->type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Status</label>
                                        <select name="status" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                                            @foreach($statuses as $value => $label)
                                                <option value="{{ $value }}" {{ old('status', $contact->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <label class="flex items-center gap-2 text-sm font-medium text-gray-700 lg:col-span-2">
                                    <input type="checkbox" name="is_primary" value="1" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" {{ old('is_primary', $contact->is_primary) ? 'checked' : '' }}>
                                    Primary contact
                                </label>
                                <div class="lg:col-span-2">
                                    <label class="text-sm font-medium text-gray-700">Notes</label>
                                    <textarea name="notes" rows="3" maxlength="5000" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">{{ old('notes', $contact->notes) }}</textarea>
                                </div>
                                <div class="lg:col-span-2">
                                    <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Save Contact</button>
                                </div>
                            </form>
                        </details>
                    </div>
                @empty
                    <div class="rounded-lg border border-gray-200 bg-white px-5 py-8 text-center text-sm text-gray-500 shadow-sm">No contacts found.</div>
                @endforelse
            </div>

            @if($contacts->hasPages())
                <div class="rounded-lg border border-gray-200 bg-white px-5 py-4 shadow-sm">{{ $contacts->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
