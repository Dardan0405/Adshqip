@extends('layouts.admin')

@section('title', 'Data Export Jobs')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Data Export Jobs</h1>
            <p class="mt-1 text-sm text-gray-500">Create CSV export files, download completed jobs, and keep export history.</p>
        </div>
        <form method="POST" action="{{ route('admin.data-export-jobs.clear-expired') }}">
            @csrf
            @method('DELETE')
            <button class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Clear Expired</button>
        </form>
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach([
            ['label' => 'Total Jobs', 'value' => $summary['total']],
            ['label' => 'Completed', 'value' => $summary['completed']],
            ['label' => 'Failed', 'value' => $summary['failed']],
            ['label' => 'Rows Exported', 'value' => $summary['rows']],
        ] as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($card['value']) }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-[380px,1fr]">
        <div class="h-fit rounded-xl border border-gray-200 bg-white p-5">
            <h2 class="mb-4 text-sm font-semibold text-gray-900">Create Export Job</h2>
            <form method="POST" action="{{ route('admin.data-export-jobs.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Job Name</label>
                    <input name="name" value="{{ old('name') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="April users export">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Dataset</label>
                    <select name="dataset" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        @foreach($datasets as $key => $dataset)
                            <option value="{{ $key }}" @selected(old('dataset') === $key)>{{ $dataset['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Date From</label>
                        <input type="date" name="date_from" value="{{ old('date_from') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Date To</label>
                        <input type="date" name="date_to" value="{{ old('date_to') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Row Limit</label>
                    <input type="number" name="limit" min="1" max="10000" value="{{ old('limit', 1000) }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <button class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Run Export</button>
            </form>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-100 p-4">
                <form method="GET" action="{{ route('admin.data-export-jobs') }}" class="flex flex-wrap gap-3">
                    <input name="search" value="{{ request('search') }}" class="min-w-64 flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Search export jobs...">
                    <select name="dataset" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" onchange="this.form.submit()">
                        <option value="">All datasets</option>
                        @foreach($datasets as $key => $dataset)
                            <option value="{{ $key }}" @selected(request('dataset') === $key)>{{ $dataset['label'] }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" onchange="this.form.submit()">
                        <option value="">All statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
                    <a href="{{ route('admin.data-export-jobs') }}" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-600">Reset</a>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50">
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-gray-400">Job</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-gray-400">Dataset</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-gray-400">Status</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-gray-400">File</th>
                            <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($jobs as $job)
                            @php
                                $statusClass = match($job->status) {
                                    'completed' => 'bg-emerald-100 text-emerald-700',
                                    'running' => 'bg-blue-100 text-blue-700',
                                    'failed' => 'bg-rose-100 text-rose-700',
                                    default => 'bg-amber-100 text-amber-700',
                                };
                                $datasetLabel = $datasets[$job->dataset]['label'] ?? ucwords(str_replace('_', ' ', $job->dataset));
                            @endphp
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-gray-900">{{ $job->name }}</div>
                                    <div class="text-xs text-gray-400">#{{ $job->id }} by {{ $job->createdBy?->email ?? 'System' }}</div>
                                    <div class="mt-1 text-xs text-gray-500">Created {{ optional($job->created_at)->format('M d, Y H:i') }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-700">{{ $datasetLabel }}</div>
                                    <div class="text-xs text-gray-400">CSV · limit {{ number_format((int) ($job->filters['limit'] ?? 0)) }}</div>
                                    @if(($job->filters['date_from'] ?? null) || ($job->filters['date_to'] ?? null))
                                        <div class="text-xs text-gray-400">{{ $job->filters['date_from'] ?? 'Any' }} to {{ $job->filters['date_to'] ?? 'Any' }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase {{ $statusClass }}">{{ $job->status }}</span>
                                    <div class="mt-1 text-xs text-gray-500">{{ number_format($job->row_count) }} rows</div>
                                    @if($job->error_message)
                                        <div class="mt-1 max-w-xs text-xs text-rose-600">{{ \Illuminate\Support\Str::limit($job->error_message, 90) }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    @if($job->file_name)
                                        <div class="font-medium text-gray-900">{{ $job->file_name }}</div>
                                        <div>{{ $job->file_size ? number_format($job->file_size / 1024, 1) . ' KB' : '0 KB' }}</div>
                                        <div>Expires {{ optional($job->expires_at)->format('M d, Y') ?: 'Never' }}</div>
                                    @else
                                        <span class="text-gray-400">No file</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        @if($job->is_downloadable)
                                            <a href="{{ route('admin.data-export-jobs.download', $job) }}" class="rounded-lg border border-emerald-200 px-3 py-1.5 text-xs font-semibold text-emerald-600 hover:bg-emerald-50">Download</a>
                                        @endif
                                        <form method="POST" action="{{ route('admin.data-export-jobs.retry', $job) }}">@csrf <button class="rounded-lg border border-blue-200 px-3 py-1.5 text-xs font-semibold text-blue-600 hover:bg-blue-50">Retry</button></form>
                                        <form method="POST" action="{{ route('admin.data-export-jobs.destroy', $job) }}" onsubmit="return confirm('Delete this export job and its file?')">@csrf @method('DELETE')<button class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">Delete</button></form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-12 text-center text-gray-500">No data export jobs found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($jobs->hasPages())
                <div class="border-t border-gray-100 px-4 py-3">{{ $jobs->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
