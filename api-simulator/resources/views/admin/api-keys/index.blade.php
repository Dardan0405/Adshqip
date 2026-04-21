@extends('layouts.admin')
@section('title', 'API Keys')
@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if($generatedApiKey)
        <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700">
            <div class="font-semibold mb-1">Copy these credentials now.</div>
            <div class="mb-1"><span class="font-semibold">API Key:</span> <span class="font-mono break-all">{{ $generatedApiKey['api_key'] }}</span></div>
            <div><span class="font-semibold">API Secret:</span> <span class="font-mono break-all">{{ $generatedApiKey['api_secret'] }}</span></div>
        </div>
    @endif

    <div class="flex flex-col gap-2 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">API Keys</h1>
        <p class="text-sm text-gray-500">Create and manage admin integration keys for external services.</p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        @foreach([
            ['label' => 'Total Keys', 'value' => number_format($summary['total'])],
            ['label' => 'Active', 'value' => number_format($summary['active'])],
            ['label' => 'Revoked', 'value' => number_format($summary['revoked'])],
            ['label' => 'Expiring Soon', 'value' => number_format($summary['expiring'])],
        ] as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-[380px,1fr]">
        <div class="rounded-xl border border-gray-200 bg-white p-5 h-fit">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Create API Key</h2>
            <form method="POST" action="{{ route('admin.api-keys.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Name</label>
                    <input type="text" name="name" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Reporting integration">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Permissions</label>
                    <div class="grid gap-2 sm:grid-cols-2 text-sm text-gray-600">
                        @foreach(['read_reports' => 'Read Reports', 'manage_campaigns' => 'Manage Campaigns', 'manage_payments' => 'Manage Payments', 'manage_users' => 'Manage Users'] as $value => $label)
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="permissions[]" value="{{ $value }}" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Rate Limit / Minute</label>
                    <input type="number" name="rate_limit_per_minute" value="60" min="1" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Allowed IPs</label>
                    <textarea name="allowed_ips" rows="3" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="127.0.0.1, 192.168.1.10"></textarea>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Expires At</label>
                    <input type="datetime-local" name="expires_at" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Generate API Key</button>
            </form>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Key</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Permissions</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Expires</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($apiKeys as $apiKey)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 font-semibold text-gray-900">#{{ $apiKey->id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $apiKey->name }}</div>
                                <div class="font-mono text-xs text-gray-400">{{ $apiKey->api_key }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ collect($apiKey->permissions ?? [])->implode(', ') ?: 'No permissions' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $apiKey->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">{{ ucfirst($apiKey->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $apiKey->expires_at ? $apiKey->expires_at->format('M d, Y H:i') : 'No expiry' }}</td>
                            <td class="px-4 py-3 text-right">
                                @if($apiKey->status === 'active')
                                    <form method="POST" action="{{ route('admin.api-keys.revoke', $apiKey) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">Revoke</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.api-keys.activate', $apiKey) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">Activate</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500">No API keys found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($apiKeys->hasPages())
                <div class="border-t border-gray-100 px-4 py-3">{{ $apiKeys->links() }}</div>
            @endif
        </div>
    </div>
@endsection
