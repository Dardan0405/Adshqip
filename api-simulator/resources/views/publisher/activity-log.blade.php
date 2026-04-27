@extends('layouts.publisher')

@section('title', 'Activity Log')

@section('content')
<div class="space-y-6" x-data="activityLog()">

    {{-- Hero --}}
    <div class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-gradient-to-br from-slate-950 via-slate-900 to-violet-900 px-6 py-7 text-white shadow-sm">
        <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(167,139,250,0.28),_transparent_55%)]"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <span class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-violet-100">Security</span>
                <h1 class="mt-4 text-3xl font-semibold tracking-tight">Activity Log</h1>
                <p class="mt-3 max-w-xl text-sm leading-6 text-slate-200/85">
                    A complete record of every action performed on your publisher account — logins, changes, and system events — so you can audit and stay in control.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="confirmClearAll()"
                        class="flex items-center gap-2 rounded-xl border border-red-400/40 bg-red-500/15 px-4 py-2.5 text-sm font-medium text-red-200 transition hover:bg-red-500/25">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Clear All
                </button>
            </div>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Table card --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">

        {{-- Stats bar --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-gray-50/60">
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <svg class="w-4 h-4 text-violet-500" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                <span class="font-semibold text-gray-800">{{ $logs->total() }}</span> total records
            </div>
            <span class="text-xs text-gray-400">Page {{ $logs->currentPage() }} of {{ $logs->lastPage() }}</span>
        </div>

        @if($logs->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </div>
                <p class="text-base font-medium text-gray-500">No activity recorded yet</p>
                <p class="text-sm text-gray-400 mt-1">Actions on your account will appear here.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                            <th class="px-5 py-3 w-16">#</th>
                            <th class="px-4 py-3 w-40">Date</th>
                            <th class="px-4 py-3">Activity</th>
                            <th class="px-4 py-3 w-32">IP Address</th>
                            <th class="px-4 py-3 w-28">Browser</th>
                            <th class="px-4 py-3 w-28">OS</th>
                            <th class="px-4 py-3">User Agent</th>
                            <th class="px-4 py-3 w-20 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50" id="log-table-body">
                        @foreach($logs as $log)
                            <tr id="log-row-{{ $log->id }}" class="hover:bg-gray-50/60 transition-colors">
                                <td class="px-5 py-3 text-xs font-mono text-gray-400">{{ $log->id }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="text-xs text-gray-700 font-medium">{{ $log->created_at->format('d M Y') }}</span>
                                    <span class="block text-[11px] text-gray-400">{{ $log->created_at->format('H:i:s') }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        @php
                                            $actionStr = str_replace(['_', '.'], ' ', $log->action);
                                            $badgeColor = match(true) {
                                                str_contains($log->action, 'delete') || str_contains($log->action, 'destroy') => 'bg-red-50 text-red-700 ring-red-200',
                                                str_contains($log->action, 'create') || str_contains($log->action, 'store') || str_contains($log->action, 'add') => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                                str_contains($log->action, 'login') || str_contains($log->action, 'signin') => 'bg-blue-50 text-blue-700 ring-blue-200',
                                                str_contains($log->action, 'update') || str_contains($log->action, 'edit') => 'bg-amber-50 text-amber-700 ring-amber-200',
                                                default => 'bg-gray-100 text-gray-600 ring-gray-200',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 {{ $badgeColor }}">
                                            {{ ucfirst($actionStr) }}
                                        </span>
                                        @if($log->entity_type)
                                            <span class="text-xs text-gray-400">{{ $log->entity_type }}{{ $log->entity_id ? ' #'.$log->entity_id : '' }}</span>
                                        @endif
                                    </div>
                                    @if(!empty($log->metadata['description']))
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $log->metadata['description'] }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 font-mono text-xs text-gray-600">
                                        <svg class="w-3 h-3 text-gray-400 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                        {{ $log->ip_address ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs text-gray-700">{{ $log->browser ?? '—' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs text-gray-700">{{ $log->os ?? '—' }}</span>
                                </td>
                                <td class="px-4 py-3 max-w-[220px]">
                                    <p class="text-xs text-gray-400 truncate" title="{{ $log->user_agent }}">{{ $log->user_agent ?? '—' }}</p>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button @click="deleteRow({{ $log->id }})"
                                            class="p-1.5 rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-500 transition-colors"
                                            title="Delete this entry">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($logs->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-xs text-gray-400">Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }}</span>
                    <div class="flex items-center gap-1">
                        @if($logs->onFirstPage())
                            <span class="px-3 py-1.5 text-xs text-gray-300 rounded-lg border border-gray-100">Previous</span>
                        @else
                            <a href="{{ $logs->previousPageUrl() }}" class="px-3 py-1.5 text-xs text-gray-600 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">Previous</a>
                        @endif
                        @foreach($logs->getUrlRange(max(1, $logs->currentPage() - 2), min($logs->lastPage(), $logs->currentPage() + 2)) as $page => $url)
                            @if($page == $logs->currentPage())
                                <span class="px-3 py-1.5 text-xs font-semibold text-white bg-brand-600 rounded-lg">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="px-3 py-1.5 text-xs text-gray-600 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">{{ $page }}</a>
                            @endif
                        @endforeach
                        @if($logs->hasMorePages())
                            <a href="{{ $logs->nextPageUrl() }}" class="px-3 py-1.5 text-xs text-gray-600 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">Next</a>
                        @else
                            <span class="px-3 py-1.5 text-xs text-gray-300 rounded-lg border border-gray-100">Next</span>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>

    {{-- Confirm modal --}}
    <div x-show="showConfirm" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" style="display:none;">
        <div @click.away="showConfirm = false" class="bg-white rounded-2xl shadow-2xl border border-gray-200 p-6 w-full max-w-sm mx-4">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-500" viewBox="0 0 24 24" fill="none"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900" x-text="confirmTitle"></p>
                    <p class="text-sm text-gray-500 mt-0.5" x-text="confirmMessage"></p>
                </div>
            </div>
            <div class="flex gap-3">
                <button @click="showConfirm = false" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">Cancel</button>
                <button @click="runConfirmed()" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-xl hover:bg-red-700 transition-colors">Delete</button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function activityLog() {
    return {
        showConfirm: false,
        confirmTitle: '',
        confirmMessage: '',
        pendingAction: null,
        csrfToken: document.querySelector('meta[name=csrf-token]').content,

        deleteRow(id) {
            this.confirmTitle = 'Delete log entry';
            this.confirmMessage = 'This entry will be permanently removed from your activity log.';
            this.pendingAction = () => this.doDelete(id);
            this.showConfirm = true;
        },

        confirmClearAll() {
            this.confirmTitle = 'Clear all activity';
            this.confirmMessage = 'All activity log entries for your account will be permanently deleted. This cannot be undone.';
            this.pendingAction = () => this.doClearAll();
            this.showConfirm = true;
        },

        runConfirmed() {
            this.showConfirm = false;
            if (this.pendingAction) this.pendingAction();
        },

        doDelete(id) {
            fetch('/publisher/activity-log/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const row = document.getElementById('log-row-' + id);
                    if (row) {
                        row.style.transition = 'opacity 0.25s';
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 260);
                    }
                }
            });
        },

        doClearAll() {
            fetch('/publisher/activity-log', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) window.location.reload();
            });
        },
    };
}
</script>
@endpush
