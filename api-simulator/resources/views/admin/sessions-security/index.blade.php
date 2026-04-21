@extends('layouts.admin')
@section('title', 'Sessions & Security')
@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Sessions & Security</h1>
            <p class="text-sm text-gray-500 mt-1">Monitor active admin sessions, clean up expired ones, and review login security state.</p>
        </div>
        <form method="POST" action="{{ route('admin.sessions-security.clear-expired') }}">
            @csrf
            <button type="submit" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Clear Expired Sessions</button>
        </form>
    </div>

    <div class="grid grid-cols-2 xl:grid-cols-5 gap-3 mb-6">
        @foreach([
            ['label' => 'Total Sessions', 'value' => number_format($summary['total'])],
            ['label' => 'Active', 'value' => number_format($summary['active'])],
            ['label' => 'Expired', 'value' => number_format($summary['expired'])],
            ['label' => '2FA Enabled Users', 'value' => number_format($summary['two_factor_users'])],
            ['label' => 'Telegram Linked', 'value' => number_format($summary['telegram_linked_users'])],
        ] as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="rounded-xl border border-gray-200 bg-white">
        <form method="GET" action="{{ route('admin.sessions-security') }}" class="flex flex-wrap gap-2 items-center p-4 border-b border-gray-100">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by user, IP, browser, OS"
                   class="min-w-[220px] flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm">
            <select name="device_type" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" onchange="this.form.submit()">
                <option value="">All Devices</option>
                @foreach(['desktop', 'mobile', 'tablet'] as $device)
                    <option value="{{ $device }}" @selected(request('device_type') === $device)>{{ ucfirst($device) }}</option>
                @endforeach
            </select>
            <select name="state" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" onchange="this.form.submit()">
                <option value="">Active + Expired</option>
                <option value="active" @selected(request('state') === 'active')>Active</option>
                <option value="expired" @selected(request('state') === 'expired')>Expired</option>
            </select>
            <button type="submit" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Search</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">User</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">IP</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Browser</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">OS</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Device</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Expires</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">State</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sessions as $session)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 font-semibold text-gray-900">#{{ $session->id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $session->user?->email }}</div>
                                <div class="text-xs text-gray-400">{{ optional($session->created_at)->format('M d, Y H:i') }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $session->ip_address }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $session->browser ?? 'Unknown' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $session->os ?? 'Unknown' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ ucfirst((string) $session->device_type) ?: 'Unknown' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ optional($session->expires_at)->format('M d, Y H:i') }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ optional($session->expires_at)->isFuture() ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ optional($session->expires_at)->isFuture() ? 'Active' : 'Expired' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.sessions-security.revoke', $session) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">Revoke</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-4 py-12 text-center text-sm text-gray-500">No tracked sessions found yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sessions->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $sessions->links() }}</div>
        @endif
    </div>
@endsection
