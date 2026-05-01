@extends('layouts.admin')

@section('title', 'System Providers')

@section('content')
<div class="space-y-6" x-data="systemProvidersPage()">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">System Providers</h1>
            <p class="mt-1 text-sm text-gray-500">Manage external providers, credentials, webhooks, and health checks.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <form method="POST" action="{{ route('admin.system-providers.sync') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Sync Existing Settings
                </button>
            </form>
            <button type="button" @click="showCreateModal = true" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create Provider
            </button>
        </div>
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

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Total</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Active</div>
            <div class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($stats['active']) }}</div>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Inactive</div>
            <div class="mt-2 text-2xl font-bold text-orange-600">{{ number_format($stats['inactive']) }}</div>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Healthy</div>
            <div class="mt-2 text-2xl font-bold text-blue-600">{{ number_format($stats['healthy']) }}</div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.system-providers') }}" class="grid gap-3 md:grid-cols-4 md:items-end">
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, slug, URL..." class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Type</label>
                <select name="type" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All types</option>
                    @foreach($providerTypes as $value => $label)
                        <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Status</label>
                <select name="status" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All statuses</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">Filter</button>
                <a href="{{ route('admin.system-providers') }}" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200">Reset</a>
            </div>
        </form>
    </div>

    <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Provider</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Type</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Source</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Endpoint</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Auth</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Last Check</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($providers as $provider)
                        @php
                            $providerPayload = [
                                'id' => $provider->id,
                                'name' => $provider->name,
                                'slug' => $provider->slug,
                                'provider_type' => $provider->provider_type,
                                'environment' => $provider->environment,
                                'status' => $provider->status,
                                'source' => $provider->source,
                                'source_key' => $provider->source_key,
                                'base_url' => $provider->base_url,
                                'webhook_url' => $provider->webhook_url,
                                'auth_type' => $provider->auth_type,
                                'api_key' => $provider->api_key,
                                'priority' => $provider->priority,
                                'timeout_seconds' => $provider->timeout_seconds,
                                'config_json' => $provider->config ? json_encode($provider->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '',
                                'notes' => $provider->notes,
                            ];
                        @endphp
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $provider->name }}</div>
                                <div class="mt-1 flex flex-wrap gap-2">
                                    <code class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600">{{ $provider->slug }}</code>
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $provider->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-orange-100 text-orange-700' }}">{{ ucfirst($provider->status) }}</span>
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">{{ $environments[$provider->environment] ?? $provider->environment }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-700">{{ $providerTypes[$provider->provider_type] ?? $provider->provider_type }}</span>
                                <div class="mt-1 text-xs text-gray-500">Priority {{ $provider->priority }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-xs font-medium text-gray-700">{{ str_replace('_', ' ', ucfirst($provider->source ?? 'manual')) }}</div>
                                @if($provider->source_key)
                                    <code class="mt-1 inline-block rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600">{{ $provider->source_key }}</code>
                                @endif
                                @if($provider->last_used_at)
                                    <div class="mt-1 text-xs text-gray-500">Used {{ $provider->last_used_at->diffForHumans() }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($provider->base_url)
                                    <a href="{{ $provider->base_url }}" target="_blank" class="break-all text-xs text-blue-600 hover:underline">{{ $provider->base_url }}</a>
                                @else
                                    <span class="text-xs text-gray-400">No base URL</span>
                                @endif
                                @if($provider->webhook_url)
                                    <div class="mt-1 break-all text-xs text-gray-500">Webhook: {{ $provider->webhook_url }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-xs font-medium text-gray-700">{{ $authTypes[$provider->auth_type] ?? $provider->auth_type }}</div>
                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $provider->api_key ? 'Key saved' : 'No key' }} / {{ $provider->api_secret ? 'Secret saved' : 'No secret' }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $checkClass = match($provider->last_check_status) {
                                        'success' => 'bg-emerald-100 text-emerald-700',
                                        'warning' => 'bg-amber-100 text-amber-700',
                                        'error' => 'bg-red-100 text-red-700',
                                        default => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $checkClass }}">{{ $provider->last_check_status ? ucfirst($provider->last_check_status) : 'Not tested' }}</span>
                                @if($provider->last_checked_at)
                                    <div class="mt-1 text-xs text-gray-500">{{ $provider->last_checked_at->diffForHumans() }}</div>
                                @endif
                                @if($provider->last_check_message)
                                    <div class="mt-1 max-w-xs truncate text-xs text-gray-500" title="{{ $provider->last_check_message }}">{{ $provider->last_check_message }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <button
                                        type="button"
                                        @click="openEditModal({{ Illuminate\Support\Js::from($providerPayload) }})"
                                        class="rounded-lg p-1.5 text-gray-400 transition hover:bg-blue-50 hover:text-blue-600"
                                        title="Edit"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    <form method="POST" action="{{ route('admin.system-providers.test', $provider) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Test provider">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.868v4.264a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                    </form>

                                    @if($provider->status === 'active')
                                        <form method="POST" action="{{ route('admin.system-providers.deactivate', $provider) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-orange-50 hover:text-orange-600" title="Deactivate">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.system-providers.activate', $provider) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-emerald-50 hover:text-emerald-600" title="Activate">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('admin.system-providers.destroy', $provider) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Delete" onclick="return confirm('Delete this system provider?')">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                                <svg class="mx-auto mb-3 h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                No system providers found yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($providers->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $providers->links() }}
            </div>
        @endif
    </div>

    @include('admin.system-providers.partials.form-modal', [
        'mode' => 'create',
        'showProperty' => 'showCreateModal',
        'action' => route('admin.system-providers.store'),
        'method' => 'POST',
    ])

    @include('admin.system-providers.partials.form-modal', [
        'mode' => 'edit',
        'showProperty' => 'showEditModal',
        'action' => null,
        'method' => 'PUT',
    ])
</div>

<script>
    function systemProvidersPage() {
        return {
            showCreateModal: false,
            showEditModal: false,
            editItem: {},
            openEditModal(provider) {
                this.editItem = provider;
                this.showEditModal = true;
            }
        };
    }
</script>
@endsection
