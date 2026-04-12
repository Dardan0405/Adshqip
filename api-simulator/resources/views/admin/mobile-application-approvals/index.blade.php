@extends('layouts.admin')

@section('title', 'Mobile Application Approvals')

@section('content')
    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-700 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Mobile Application Approvals</h1>
            <p class="text-sm text-gray-500 mt-1">Review submitted mobile applications and approve or reject pending entries.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        @php
            $statCards = [
                ['label' => 'Total Applications', 'value' => number_format($totalApplications), 'color' => 'text-gray-900', 'bg' => 'bg-gray-50', 'border' => 'border-gray-200', 'icon' => '<path d="M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z" stroke="currentColor" stroke-width="1.5"/><path d="M11 18h2M9 7h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Pending Review', 'value' => number_format($pendingCount), 'color' => 'text-amber-700', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'icon' => '<path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Approved', 'value' => number_format($approvedCount), 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'icon' => '<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Rejected', 'value' => number_format($rejectedCount), 'color' => 'text-red-700', 'bg' => 'bg-red-50', 'border' => 'border-red-200', 'icon' => '<path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/>'],
            ];
        @endphp
        @foreach($statCards as $card)
            <div class="rounded-xl border {{ $card['border'] }} {{ $card['bg'] }} p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                    <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none">{!! $card['icon'] !!}</svg>
                </div>
                <div class="text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4">
            <form method="GET" action="{{ route('admin.mobile-application-approvals') }}" class="flex items-center gap-2 flex-1 flex-wrap">
                <div class="relative flex-1 min-w-[220px] max-w-md">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search id, email, app name, url..."
                           class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium text-gray-600 hover:bg-gray-200 transition-colors">Filter</button>
                <a href="{{ route('admin.mobile-application-approvals') }}" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</a>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-t border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Id</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Email</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Application URL</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Application Name</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($applications as $application)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-900">#{{ $application->id }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $application->owner?->email ?? 'N/A' }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ $application->app_url }}" target="_blank" rel="noopener noreferrer" class="text-brand-600 hover:text-brand-700 underline underline-offset-2">
                                    {{ $application->app_url }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-gray-900 font-medium">{{ $application->app_name }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="inline-flex items-center gap-1.5 flex-wrap justify-center">
                                    <button onclick="approveApplication({{ $application->id }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold hover:bg-emerald-100 transition-colors">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        Approve
                                    </button>
                                    <button onclick="rejectApplication({{ $application->id }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs font-semibold hover:bg-red-100 transition-colors">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z" stroke="currentColor" stroke-width="1.5"/><path d="M11 18h2M9 7h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    <p class="text-sm text-gray-500">No mobile application approvals found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($applications->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $applications->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    function approveApplication(id) {
        if (!confirm('Are you sure you want to approve this mobile application?')) return;

        fetch(`/admin/mobile-application-approvals/${id}/approve`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            }
        })
        .catch(() => alert('Failed to approve mobile application.'));
    }

    function rejectApplication(id) {
        if (!confirm('Are you sure you want to reject this mobile application?')) return;

        fetch(`/admin/mobile-application-approvals/${id}/reject`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            }
        })
        .catch(() => alert('Failed to reject mobile application.'));
    }
</script>
@endpush
