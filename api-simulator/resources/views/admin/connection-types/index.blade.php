@extends('layouts.admin')

@section('title', 'Connections')

@section('content')
<div class="space-y-6" x-data="connectionsPage()">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Connections</h1>
            <p class="mt-1 text-sm text-gray-500">Manage connection targeting values for campaigns.</p>
        </div>
        <button @click="showCreateModal = true" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Connection
        </button>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.connection-types') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="max-w-md flex-1">
                <label class="mb-1 block text-xs font-medium text-gray-600">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by connection name or value..." class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">Search</button>
                <a href="{{ route('admin.connection-types') }}" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200">Reset</a>
            </div>
        </form>
    </div>

    <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Id</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Connection Name</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Connection Value</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($connections as $connection)
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $connection->id }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $connection->connection_name }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <code class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-600">{{ $connection->connection_value }}</code>
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $connection->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-orange-100 text-orange-700' }}">
                                        {{ $connection->status === 'active' ? 'Active' : 'Blocked' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <button
                                        type="button"
                                        data-item="{{ json_encode(['id' => $connection->id, 'connection_name' => $connection->connection_name, 'connection_value' => $connection->connection_value]) }}"
                                        @click="openEditModal(JSON.parse($event.currentTarget.dataset.item))"
                                        class="rounded-lg p-1.5 text-gray-400 transition hover:bg-blue-50 hover:text-blue-600"
                                        title="Edit"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    @if($connection->status === 'active')
                                        <form method="POST" action="{{ route('admin.connection-types.block', $connection->id) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-orange-50 hover:text-orange-600" title="Block" onclick="return confirm('Are you sure you want to block this connection?')">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.connection-types.unblock', $connection->id) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-emerald-50 hover:text-emerald-600" title="Unblock">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('admin.connection-types.destroy', $connection->id) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Delete" onclick="return confirm('Are you sure you want to delete this connection?')">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                <svg class="mx-auto mb-4 h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                No connections found yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($connections->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $connections->links() }}
            </div>
        @endif
    </div>

    <div x-show="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex min-h-screen items-center justify-center px-4">
            <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false"></div>
            <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl" @click.stop>
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Create Connection</h3>
                    <button @click="showCreateModal = false" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.connection-types.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Connection Name <span class="text-red-500">*</span></label>
                        <input type="text" name="connection_name" value="{{ old('connection_name') }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20" placeholder="e.g. Wi-Fi">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Connection Value <span class="text-red-500">*</span></label>
                        <input type="text" name="connection_value" value="{{ old('connection_value') }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20" placeholder="e.g. wifi">
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showCreateModal = false" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200">Cancel</button>
                        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-700">Create Connection</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex min-h-screen items-center justify-center px-4">
            <div class="fixed inset-0 bg-black/50" @click="showEditModal = false"></div>
            <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl" @click.stop>
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Edit Connection</h3>
                    <button @click="showEditModal = false" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="'/admin/connection-types/' + editItem.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Connection Name <span class="text-red-500">*</span></label>
                        <input type="text" name="connection_name" x-model="editItem.connection_name" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Connection Value <span class="text-red-500">*</span></label>
                        <input type="text" name="connection_value" x-model="editItem.connection_value" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showEditModal = false" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200">Cancel</button>
                        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-700">Update Connection</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function connectionsPage() {
    return {
        showCreateModal: false,
        showEditModal: false,
        editItem: {
            id: null,
            connection_name: '',
            connection_value: '',
        },
        openEditModal(item) {
            this.editItem = {
                id: item.id,
                connection_name: item.connection_name,
                connection_value: item.connection_value,
            };
            this.showEditModal = true;
        },
    };
}
</script>
@endsection
