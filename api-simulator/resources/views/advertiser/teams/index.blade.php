@extends('layouts.advertiser')

@section('title', 'Teams')

@section('content')
@php
    $statusClasses = [
        'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
        'disabled' => 'bg-gray-50 text-gray-700 border-gray-200',
        'accepted' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'revoked' => 'bg-gray-50 text-gray-700 border-gray-200',
        'expired' => 'bg-red-50 text-red-700 border-red-200',
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Teams</h1>
            <p class="mt-1 text-sm text-gray-500">Invite users into your advertiser company and control their access.</p>
        </div>
        <a href="{{ route('advertiser.company-information') }}" class="inline-flex w-fit items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
            Company Information
        </a>
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

    @if(blank($companyName))
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-5">
            <h2 class="text-lg font-semibold text-amber-900">Company information required</h2>
            <p class="mt-1 text-sm text-amber-800">Teams are only available for advertiser companies. Add your company name before inviting users.</p>
            <a href="{{ route('advertiser.company-information') }}" class="mt-4 inline-flex rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">Add Company</a>
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Active Members</p>
            <p class="mt-2 text-2xl font-bold text-emerald-700">{{ number_format($summary['active']) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Pending Members</p>
            <p class="mt-2 text-2xl font-bold text-amber-700">{{ number_format($summary['pending']) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Disabled Members</p>
            <p class="mt-2 text-2xl font-bold text-gray-700">{{ number_format($summary['disabled']) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Open Invites</p>
            <p class="mt-2 text-2xl font-bold text-brand-700">{{ number_format($summary['open_invitations']) }}</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Invite User</h2>
            <form method="POST" action="{{ route('advertiser.teams.invite') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="teammate@example.com">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Name</label>
                    <input name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="Optional">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Role</label>
                    <select name="team_role" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        @foreach($roles as $value => $label)
                            <option value="{{ $value }}" {{ old('team_role', 'viewer') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">Permissions</p>
                    <div class="mt-2 grid gap-2">
                        @foreach($permissions as $value => $label)
                            <label class="flex items-center gap-2 rounded-lg border border-gray-100 px-3 py-2 text-sm text-gray-600">
                                <input type="checkbox" name="permissions[]" value="{{ $value }}" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <button class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700" {{ blank($companyName) ? 'disabled' : '' }}>
                    Send Invitation
                </button>
            </form>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white shadow-sm xl:col-span-2">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Team Members</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-5 py-3">User</th>
                            <th class="px-5 py-3">Role</th>
                            <th class="px-5 py-3">Permissions</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($members as $member)
                            <tr>
                                <td class="px-5 py-4">
                                    <div class="font-medium text-gray-900">{{ $member->name ?: $member->email }}</div>
                                    <div class="text-xs text-gray-500">{{ $member->email }}</div>
                                </td>
                                <td class="px-5 py-4 text-gray-600">{{ $member->team_role === 'owner' ? 'Owner' : ($roles[$member->team_role] ?? ucfirst($member->team_role)) }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex max-w-sm flex-wrap gap-1">
                                        @foreach(array_slice($member->permissions ?? [], 0, 4) as $permission)
                                            <span class="rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-600">{{ $permissions[$permission] ?? $permission }}</span>
                                        @endforeach
                                        @if(count($member->permissions ?? []) > 4)
                                            <span class="rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-600">+{{ count($member->permissions) - 4 }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$member->status] ?? 'bg-gray-50 text-gray-700 border-gray-200' }}">{{ ucfirst($member->status) }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    @if($member->team_role === 'owner')
                                        <span class="block text-right text-xs text-gray-400">Owner</span>
                                    @else
                                        <div class="flex justify-end gap-2">
                                            <form method="POST" action="{{ route('advertiser.teams.members.status', $member) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="{{ $member->status === 'disabled' ? 'active' : 'disabled' }}">
                                                <button class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50">{{ $member->status === 'disabled' ? 'Activate' : 'Disable' }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('advertiser.teams.members.destroy', $member) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">Remove</button>
                                            </form>
                                        </div>
                                        <details class="mt-2 text-left">
                                            <summary class="cursor-pointer text-right text-xs font-semibold text-brand-600">Edit access</summary>
                                            <form method="POST" action="{{ route('advertiser.teams.members.update', $member) }}" class="mt-3 rounded-lg border border-gray-100 bg-gray-50 p-3">
                                                @csrf
                                                @method('PUT')
                                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-400">Role</label>
                                                <select name="team_role" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-xs">
                                                    @foreach($roles as $value => $label)
                                                        <option value="{{ $value }}" {{ $member->team_role === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="mt-3 grid gap-2">
                                                    @foreach($permissions as $value => $label)
                                                        <label class="flex items-center gap-2 text-xs text-gray-600">
                                                            <input type="checkbox" name="permissions[]" value="{{ $value }}" class="rounded border-gray-300 text-brand-600" {{ in_array($value, $member->permissions ?? [], true) ? 'checked' : '' }}>
                                                            <span>{{ $label }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                                <button class="mt-3 w-full rounded-lg bg-brand-600 px-3 py-2 text-xs font-semibold text-white hover:bg-brand-700">Save Access</button>
                                            </form>
                                        </details>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-gray-500">No team members yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Invitations</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-5 py-3">Invitee</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Accept Link</th>
                        <th class="px-5 py-3">Expires</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($invitations as $invitation)
                        <tr>
                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-900">{{ $invitation->name ?: $invitation->email }}</div>
                                <div class="text-xs text-gray-500">{{ $invitation->email }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ $roles[$invitation->team_role] ?? ucfirst($invitation->team_role) }}</td>
                            <td class="px-5 py-4">
                                @if($invitation->status === 'pending')
                                    <div class="flex max-w-md items-center gap-2">
                                        <input readonly value="{{ $invitation->accept_url }}" class="min-w-0 flex-1 rounded-lg border border-gray-200 px-3 py-2 text-xs text-gray-600">
                                        <button type="button" data-copy="{{ $invitation->accept_url }}" class="copy-link rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50">Copy</button>
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ $invitation->expires_at?->format('M d, Y') ?? '-' }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$invitation->status] ?? 'bg-gray-50 text-gray-700 border-gray-200' }}">{{ ucfirst($invitation->status) }}</span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                @if($invitation->status === 'pending')
                                    <form method="POST" action="{{ route('advertiser.teams.invitations.revoke', $invitation) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">Revoke</button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400">Closed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-gray-500">No invitations yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.copy-link').forEach((button) => {
        button.addEventListener('click', async () => {
            await navigator.clipboard.writeText(button.dataset.copy);
            const original = button.textContent;
            button.textContent = 'Copied';
            setTimeout(() => button.textContent = original, 1200);
        });
    });
</script>
@endpush
