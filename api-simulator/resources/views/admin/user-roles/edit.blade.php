@extends('layouts.admin')
@section('title', 'Edit User Role')
@section('content')

    <div class="max-w-6xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Edit User Role</h1>
            <p class="text-sm text-gray-500 mt-1">Update the display name, status, and admin permissions for this predefined role.</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ $role->role_name }}</h2>
                        <p class="text-sm text-gray-400 mt-1">Role key: {{ $role->role_key }}</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold uppercase {{ $role->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $role->status }}
                    </span>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.user-roles.update', $role) }}" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label for="role_name" class="block text-sm font-medium text-gray-700 mb-2">Role Name</label>
                        <input
                            id="role_name"
                            name="role_name"
                            type="text"
                            value="{{ old('role_name', $role->role_name) }}"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10"
                        >
                        @error('role_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select
                            id="status"
                            name="status"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10"
                        >
                            <option value="active" {{ old('status', $role->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $role->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role Id</label>
                        <input
                            type="text"
                            value="{{ $role->id }}"
                            disabled
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-500"
                        >
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6">
                    <div class="mb-5">
                        <h3 class="text-base font-semibold text-gray-900">Role Permissions</h3>
                        <p class="text-sm text-gray-500 mt-1">Enable or disable the admin permissions listed in your permission catalog.</p>
                    </div>

                    @error('permissions')
                        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">{{ $message }}</div>
                    @enderror

                    <div class="space-y-6">
                        @foreach($permissionSections as $section)
                            <div class="rounded-2xl border border-gray-200 overflow-hidden">
                                <div class="px-5 py-4 bg-gray-50 border-b border-gray-100">
                                    <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-700">{{ $section['label'] }}</h4>
                                </div>

                                <div class="p-5 space-y-5">
                                    @foreach($section['groups'] as $group)
                                        <div class="rounded-2xl border border-gray-100 bg-white p-4">
                                            <h5 class="text-sm font-semibold text-gray-900 mb-3">{{ $group['label'] }}</h5>

                                            @if(!empty($group['permissions']))
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    @foreach($group['permissions'] as $permission)
                                                        <label class="flex items-start gap-3 rounded-xl border border-gray-200 px-4 py-3 hover:bg-gray-50 transition-colors">
                                                            <input
                                                                type="checkbox"
                                                                name="permissions[]"
                                                                value="{{ $permission['key'] }}"
                                                                {{ in_array($permission['key'], old('permissions', $selectedPermissions), true) ? 'checked' : '' }}
                                                                class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500"
                                                            >
                                                            <span class="text-sm text-gray-700">{{ $permission['label'] }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if(!empty($group['subgroups']))
                                                <div class="space-y-4 mt-4">
                                                    @foreach($group['subgroups'] as $subgroup)
                                                        <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-4">
                                                            <h6 class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-3">{{ $subgroup['label'] }}</h6>
                                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                                @foreach($subgroup['permissions'] as $permission)
                                                                    <label class="flex items-start gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 hover:bg-gray-50 transition-colors">
                                                                        <input
                                                                            type="checkbox"
                                                                            name="permissions[]"
                                                                            value="{{ $permission['key'] }}"
                                                                            {{ in_array($permission['key'], old('permissions', $selectedPermissions), true) ? 'checked' : '' }}
                                                                            class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500"
                                                                        >
                                                                        <span class="text-sm text-gray-700">{{ $permission['label'] }}</span>
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
                    <a href="{{ route('admin.user-roles') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-5 py-3 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                        Back to User Roles
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white hover:bg-brand-700 transition-colors">
                        Save Role
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
