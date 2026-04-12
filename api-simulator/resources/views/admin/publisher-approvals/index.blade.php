@extends('layouts.admin')

@section('title', 'Publisher Approvals')

@section('content')
    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-700 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Publisher Approvals</h1>
            <p class="text-sm text-gray-500 mt-1">Review and approve or reject pending publisher registrations.</p>
        </div>
        <button onclick="document.getElementById('addPublisherModal').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-semibold shadow-sm hover:bg-brand-700 transition-colors">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            Add Publisher
        </button>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
        @php
            $statCards = [
                ['label' => 'Pending Approval', 'value' => number_format($totalPending), 'color' => 'text-amber-700', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'icon' => '<path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Approved Today', 'value' => number_format($approvedToday), 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'icon' => '<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
                ['label' => 'Rejected', 'value' => number_format($rejectedCount), 'color' => 'text-red-700', 'bg' => 'bg-red-50', 'border' => 'border-red-200', 'icon' => '<path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
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
            <form method="GET" action="{{ route('admin.publisher-approvals') }}" class="flex items-center gap-2 flex-1 flex-wrap">
                <div class="relative flex-1 min-w-[220px] max-w-md">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email..."
                           class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium text-gray-600 hover:bg-gray-200 transition-colors">Filter</button>
                <a href="{{ route('admin.publisher-approvals') }}" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</a>
            </form>

            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Export
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-44 bg-white rounded-xl border border-gray-200 shadow-lg py-1 z-50" style="display:none;">
                    <button onclick="copyTable()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Copy</button>
                    <a href="{{ route('admin.publisher-approvals.export', request()->all()) }}" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">CSV</a>
                    <button onclick="window.print()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Print</button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="approvalTable" class="w-full text-sm">
                <thead>
                    <tr class="border-t border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Publisher Name</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Email ID</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Registered</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pendingPublishers as $publisher)
                        @php
                            $name = trim(($publisher->userProfile->first_name ?? '') . ' ' . ($publisher->userProfile->last_name ?? '')) ?: 'Unknown';
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors" id="row-{{ $publisher->id }}">
                            <td class="px-4 py-3 font-medium text-gray-900">#{{ $publisher->id }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-semibold text-xs">
                                        {{ strtoupper(substr($name, 0, 2)) }}
                                    </div>
                                    <span class="font-medium text-gray-900">{{ $name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $publisher->email }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $publisher->created_at?->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-400">{{ $publisher->created_at?->format('H:i') }}</div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="inline-flex items-center gap-2">
                                    <button onclick="approvePublisher({{ $publisher->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold hover:bg-emerald-100 transition-colors">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        Approve
                                    </button>
                                    <button onclick="rejectPublisher({{ $publisher->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs font-semibold hover:bg-red-100 transition-colors">
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
                                    <svg class="w-12 h-12 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/></svg>
                                    <p class="text-sm text-gray-500">No pending publisher approvals.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pendingPublishers->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $pendingPublishers->links() }}
            </div>
        @endif
    </div>

    {{-- Add Publisher Modal --}}
    <div id="addPublisherModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900/50 transition-opacity" onclick="document.getElementById('addPublisherModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg z-10">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900">Add Publisher</h3>
                    <button onclick="document.getElementById('addPublisherModal').classList.add('hidden')" class="p-1 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.publisher-approvals.store') }}" class="p-6 space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">First Name <span class="text-red-500">*</span></label>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" required
                                   class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
                                   placeholder="Jane">
                            @error('first_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" name="last_name" value="{{ old('last_name') }}" required
                                   class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
                                   placeholder="Smith">
                            @error('last_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Email ID <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
                               placeholder="jane@example.com">
                        @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Website</label>
                        <input type="url" name="website_url" value="{{ old('website_url') }}"
                               class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
                               placeholder="https://example.com">
                        @error('website_url') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password" required
                                   class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
                                   placeholder="Min 6 characters">
                            @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Confirm Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password_confirmation" required
                                   class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
                                   placeholder="Repeat password">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Security Question <span class="text-red-500">*</span></label>
                        <select name="security_question_id" required
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                            <option value="">Select a question...</option>
                            @foreach($securityQuestions as $question)
                                <option value="{{ $question->id }}" {{ old('security_question_id') == $question->id ? 'selected' : '' }}>{{ $question->question }}</option>
                            @endforeach
                        </select>
                        @error('security_question_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Answer <span class="text-red-500">*</span></label>
                        <input type="text" name="security_answer" value="{{ old('security_answer') }}" required
                               class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
                               placeholder="Your answer">
                        @error('security_answer') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" onclick="document.getElementById('addPublisherModal').classList.add('hidden')"
                                class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2 rounded-lg bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 transition-colors shadow-sm">
                            Create Publisher
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function approvePublisher(id) {
        if (!confirm('Are you sure you want to approve this publisher?')) return;

        fetch(`/admin/publisher-approvals/${id}/approve`, {
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
                const row = document.getElementById('row-' + id);
                if (row) row.remove();
                alert(data.message);
                location.reload();
            }
        })
        .catch(() => alert('Failed to approve publisher.'));
    }

    function rejectPublisher(id) {
        if (!confirm('Are you sure you want to reject this publisher?')) return;

        fetch(`/admin/publisher-approvals/${id}/reject`, {
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
                const row = document.getElementById('row-' + id);
                if (row) row.remove();
                alert(data.message);
                location.reload();
            }
        })
        .catch(() => alert('Failed to reject publisher.'));
    }

    function copyTable() {
        const table = document.getElementById('approvalTable');
        const rows = table.querySelectorAll('tr');
        let text = '';

        rows.forEach((row) => {
            const cells = row.querySelectorAll('th, td');
            const rowData = [];

            cells.forEach((cell) => {
                rowData.push(cell.textContent.trim().replace(/\s+/g, ' '));
            });

            text += rowData.join('\t') + '\n';
        });

        navigator.clipboard.writeText(text).then(() => alert('Table data copied to clipboard!'));
    }

    @if($errors->any())
        document.getElementById('addPublisherModal').classList.remove('hidden');
    @endif
</script>
@endpush
