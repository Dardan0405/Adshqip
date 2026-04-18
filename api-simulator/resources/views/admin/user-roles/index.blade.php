@extends('layouts.admin')
@section('title', 'User Roles')
@section('content')

    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-700 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">User Roles</h1>
            <p class="text-sm text-gray-500 mt-1">Manage the predefined admin-side roles and control whether each role is active.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Total Roles</div>
            <div class="mt-2 text-2xl font-bold text-blue-700">{{ number_format($roles->count()) }}</div>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Active Roles</div>
            <div class="mt-2 text-2xl font-bold text-emerald-700">{{ number_format($activeCount) }}</div>
        </div>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Inactive Roles</div>
            <div class="mt-2 text-2xl font-bold text-amber-700">{{ number_format($inactiveCount) }}</div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-4 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">Roles List</h2>
                <p class="text-xs text-gray-400 mt-1">Default roles are created automatically. Use edit to update the displayed name or status.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/70 border-b border-gray-100">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Id</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Role Name</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($roles as $role)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-800">{{ $role->id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $role->role_name }}</div>
                                <div class="text-xs text-gray-400">{{ str_replace('_', ' ', $role->role_key) }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold uppercase {{ $role->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $role->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <a href="{{ route('admin.user-roles.edit', $role) }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M16.862 4.487a2.25 2.25 0 113.182 3.182L8.25 19.463 4 20l.537-4.25L16.862 4.487z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center text-sm text-gray-500">No user roles found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
