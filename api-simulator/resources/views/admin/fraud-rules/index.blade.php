@extends('layouts.admin')

@section('title', 'Fraud Rules')

@section('content')
<div class="space-y-6" x-data="fraudRulesPage()">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Fraud Rules</h1>
            <p class="mt-1 text-sm text-gray-500">Create rules that block, flag, or throttle suspicious traffic.</p>
        </div>
        <button type="button" @click="showCreateModal = true" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Rule
        </button>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"><div class="text-xs uppercase tracking-wide text-gray-500">Total</div><div class="mt-2 text-2xl font-bold">{{ number_format($stats['total']) }}</div></div>
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"><div class="text-xs uppercase tracking-wide text-gray-500">Active</div><div class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($stats['active']) }}</div></div>
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"><div class="text-xs uppercase tracking-wide text-gray-500">Inactive</div><div class="mt-2 text-2xl font-bold text-orange-600">{{ number_format($stats['inactive']) }}</div></div>
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"><div class="text-xs uppercase tracking-wide text-gray-500">Triggers</div><div class="mt-2 text-2xl font-bold text-red-600">{{ number_format($stats['triggers']) }}</div></div>
    </div>

    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.fraud-rules') }}" class="grid gap-3 md:grid-cols-4 md:items-end">
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Search</label>
                <input name="search" value="{{ request('search') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Rule name, type, match value">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Rule Type</label>
                <select name="rule_type" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All</option>
                    @foreach($ruleTypes as $value => $label)
                        <option value="{{ $value }}" @selected(request('rule_type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Status</label>
                <select name="status" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white">Filter</button>
                <a href="{{ route('admin.fraud-rules') }}" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700">Reset</a>
            </div>
        </form>
    </div>

    <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b bg-gray-50">
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Rule</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Match</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Action</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Triggered</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Action</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rules as $rule)
                        @php
                            $payload = [
                                'id' => $rule->id,
                                'rule_name' => $rule->rule_name,
                                'rule_type' => $rule->rule_type,
                                'match_value' => $rule->match_value,
                                'threshold_value' => $rule->threshold_value,
                                'reset_period_seconds' => $rule->reset_period_seconds,
                                'action' => $rule->action,
                                'severity' => $rule->severity,
                                'description' => $rule->description,
                                'is_active' => $rule->is_active,
                            ];
                            $severityClass = ['low'=>'bg-gray-100 text-gray-700','medium'=>'bg-blue-100 text-blue-700','high'=>'bg-orange-100 text-orange-700','critical'=>'bg-red-100 text-red-700'][$rule->severity] ?? 'bg-gray-100 text-gray-700';
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $rule->rule_name }}</div>
                                <div class="mt-1 flex flex-wrap gap-2">
                                    <span class="rounded-full bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-700">{{ $ruleTypes[$rule->rule_type] ?? $rule->rule_type }}</span>
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $rule->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ $rule->is_active ? 'Active' : 'Inactive' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="max-w-sm truncate text-xs text-gray-700" title="{{ $rule->match_value }}">{{ $rule->match_value ?: 'Threshold based' }}</div>
                                <div class="mt-1 text-xs text-gray-500">Threshold {{ $rule->threshold_value ?: '-' }} / {{ $rule->reset_period_seconds ?: '-' }} sec</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">{{ $actions[$rule->action] ?? $rule->action }}</span>
                                <span class="ml-1 rounded-full px-2 py-0.5 text-xs font-medium {{ $severityClass }}">{{ $severities[$rule->severity] ?? $rule->severity }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ number_format($rule->triggered_count) }}</div>
                                <div class="text-xs text-gray-500">{{ $rule->last_triggered_at?->diffForHumans() ?? 'Never' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-center gap-1">
                                    <button type="button" @click="openEditModal({{ Illuminate\Support\Js::from($payload) }})" class="rounded-lg p-1.5 text-gray-400 hover:bg-blue-50 hover:text-blue-600">Edit</button>
                                    <form method="POST" action="{{ $rule->is_active ? route('admin.fraud-rules.deactivate', $rule) : route('admin.fraud-rules.activate', $rule) }}">
                                        @csrf @method('PATCH')
                                        <button class="rounded-lg p-1.5 text-gray-400 hover:bg-emerald-50 hover:text-emerald-600">{{ $rule->is_active ? 'Off' : 'On' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.fraud-rules.destroy', $rule) }}">
                                        @csrf @method('DELETE')
                                        <button onclick="return confirm('Delete this fraud rule?')" class="rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-gray-500">No fraud rules found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($rules->hasPages())<div class="border-t px-4 py-3">{{ $rules->links() }}</div>@endif
    </div>

    @include('admin.fraud-rules.partials.form-modal', ['mode' => 'create', 'showProperty' => 'showCreateModal', 'action' => route('admin.fraud-rules.store'), 'method' => 'POST'])
    @include('admin.fraud-rules.partials.form-modal', ['mode' => 'edit', 'showProperty' => 'showEditModal', 'action' => null, 'method' => 'PUT'])
</div>

<script>
function fraudRulesPage() {
    return {
        showCreateModal: false,
        showEditModal: false,
        editItem: {},
        openEditModal(rule) {
            this.editItem = rule;
            this.showEditModal = true;
        }
    };
}
</script>
@endsection
