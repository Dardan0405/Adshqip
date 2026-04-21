@extends('layouts.admin')

@section('title', 'Newsletters')

@section('content')
    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-700 text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Newsletters</h1>
            <p class="text-sm text-gray-500 mt-1">Manage subscribers coming from the public join-newsletter form.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        @php
            $cards = [
                ['label' => 'Total', 'value' => number_format($summary['total']), 'color' => 'text-slate-900', 'bg' => 'bg-slate-50', 'border' => 'border-slate-200'],
                ['label' => 'Subscribed', 'value' => number_format($summary['subscribed']), 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200'],
                ['label' => 'Unsubscribed', 'value' => number_format($summary['unsubscribed']), 'color' => 'text-amber-700', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200'],
                ['label' => 'Bounced', 'value' => number_format($summary['bounced']), 'color' => 'text-rose-700', 'bg' => 'bg-rose-50', 'border' => 'border-rose-200'],
            ];
        @endphp
        @foreach($cards as $card)
            <div class="rounded-xl border {{ $card['border'] }} {{ $card['bg'] }} p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-2">{{ $card['label'] }}</div>
                <div class="text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4">
            <form method="GET" action="{{ route('admin.newsletters') }}" class="flex items-center gap-2 flex-1 flex-wrap">
                <div class="relative flex-1 max-w-md">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search email..."
                           class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <select name="status" class="px-3 py-2 rounded-lg border border-gray-200 text-sm">
                    <option value="">All Status</option>
                    @foreach(['subscribed', 'unsubscribed', 'bounced'] as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <select name="source" class="px-3 py-2 rounded-lg border border-gray-200 text-sm">
                    <option value="">All Sources</option>
                    @foreach($sources as $source)
                        <option value="{{ $source }}" {{ request('source') === $source ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $source)) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium text-gray-600 hover:bg-gray-200">Search</button>
                <a href="{{ route('admin.newsletters') }}" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</a>
            </form>

            <a href="{{ route('admin.newsletters.export', request()->all()) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50">Export CSV</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-t border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Email</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Source</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Subscribed</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($subscribers as $subscriber)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">#{{ $subscriber->id }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $subscriber->email }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ ucfirst(str_replace('_', ' ', $subscriber->source)) }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $subscriber->status === 'subscribed' ? 'bg-emerald-100 text-emerald-700' : ($subscriber->status === 'unsubscribed' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">
                                    {{ ucfirst($subscriber->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ optional($subscriber->subscribed_at)->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    @if($subscriber->status === 'subscribed')
                                        <button type="button" onclick="toggleNewsletterStatus({{ $subscriber->id }}, 'unsubscribe')" class="px-3 py-1.5 rounded-lg text-xs font-medium bg-amber-50 text-amber-700 hover:bg-amber-100">Unsubscribe</button>
                                    @else
                                        <button type="button" onclick="toggleNewsletterStatus({{ $subscriber->id }}, 'resubscribe')" class="px-3 py-1.5 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-700 hover:bg-emerald-100">Resubscribe</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500">No newsletter subscribers found yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subscribers->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $subscribers->links() }}</div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    const newsletterCsrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function toggleNewsletterStatus(id, action) {
        fetch(`/admin/newsletters/${id}/${action}`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': newsletterCsrfToken,
                'Accept': 'application/json'
            }
        }).then(response => response.json()).then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Unable to update subscriber status.');
            }
        }).catch(() => alert('Unable to update subscriber status.'));
    }
</script>
@endpush
