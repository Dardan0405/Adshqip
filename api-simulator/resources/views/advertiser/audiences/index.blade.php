@extends('layouts.advertiser')

@section('title', 'Audiences')

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
            <h1 class="text-2xl font-bold text-gray-900">Audiences</h1>
            <p class="mt-1 text-sm text-gray-500">Build reusable audience segments and attach them to campaign targeting.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach([
            ['label' => 'Total Audiences', 'value' => $summary['total']],
            ['label' => 'Active', 'value' => $summary['active']],
            ['label' => 'Targeted Campaigns', 'value' => $summary['campaigns']],
            ['label' => 'Estimated Reach', 'value' => $summary['reach']],
        ] as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($card['value']) }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-[420px,1fr]">
        <div class="space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <h2 class="mb-4 text-sm font-semibold text-gray-900">Create Audience</h2>
                <form method="POST" action="{{ route('advertiser.audiences.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Name</label>
                        <input name="name" value="{{ old('name') }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="High intent Balkan visitors">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">Type</label>
                            <select name="type" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                                @foreach($types as $type)
                                    <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="2" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Who should be included in this segment?">{{ old('description') }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Countries</label>
                        <select name="countries[]" multiple class="min-h-32 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                            @foreach($countries as $code => $label)
                                <option value="{{ $code }}">{{ $label }} ({{ $code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Devices</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($devices as $device)
                                <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600">
                                    <input type="checkbox" name="devices[]" value="{{ $device }}">
                                    {{ ucwords(str_replace('_', ' ', $device)) }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Interests</label>
                        <textarea name="interests" rows="2" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="sports, finance, travel">{{ old('interests') }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Keywords</label>
                        <textarea name="keywords" rows="2" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="insurance, ecommerce, app install">{{ old('keywords') }}</textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">Min Visits</label>
                            <input type="number" min="0" name="min_visits" value="{{ old('min_visits', 1) }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">Days Since Visit</label>
                            <input type="number" min="1" max="365" name="days_since_visit" value="{{ old('days_since_visit', 30) }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <button class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Save Audience</button>
                </form>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <h2 class="mb-4 text-sm font-semibold text-gray-900">Attach To Campaign</h2>
                <form method="POST" action="{{ route('advertiser.audiences.attach') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Campaign</label>
                        <select name="campaign_id" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                            @foreach($campaigns as $campaign)
                                <option value="{{ $campaign->id }}">#{{ $campaign->id }} {{ $campaign->name }} ({{ $campaign->status }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Audiences</label>
                        <select name="audience_ids[]" multiple required class="min-h-32 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                            @foreach($audiences as $audience)
                                <option value="{{ $audience->id }}">{{ $audience->name }} ({{ $audience->status }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Targeting Mode</label>
                        <select name="mode" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                            <option value="include">Include selected audience</option>
                            <option value="exclude">Exclude selected audience</option>
                        </select>
                    </div>
                    <button class="w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">Attach Audience</button>
                </form>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-100 p-4">
                <form method="GET" action="{{ route('advertiser.audiences') }}" class="flex flex-wrap gap-3">
                    <input name="search" value="{{ request('search') }}" class="min-w-64 flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Search audiences...">
                    <select name="type" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" onchange="this.form.submit()">
                        <option value="">All types</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" onchange="this.form.submit()">
                        <option value="">All statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
                    <a href="{{ route('advertiser.audiences') }}" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-600">Reset</a>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50">
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-gray-400">Audience</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-gray-400">Rules</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-gray-400">Campaigns</th>
                            <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($audiences as $audience)
                            @php
                                $statusClass = match($audience->status) {
                                    'active' => 'bg-emerald-100 text-emerald-700',
                                    'paused' => 'bg-amber-100 text-amber-700',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <div class="font-semibold text-gray-900">{{ $audience->name }}</div>
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase {{ $statusClass }}">{{ $audience->status }}</span>
                                    </div>
                                    <div class="mt-1 text-xs text-gray-400">{{ $audience->slug }} · {{ ucfirst($audience->type) }} · {{ number_format($audience->estimated_size) }} reach</div>
                                    <div class="mt-1 max-w-xl text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($audience->description, 110) }}</div>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    <div>Countries: {{ $audience->countries ? implode(', ', $audience->countries) : 'Any' }}</div>
                                    <div>Devices: {{ $audience->devices ? implode(', ', $audience->devices) : 'Any' }}</div>
                                    <div>Keywords: {{ $audience->keywords ? implode(', ', array_slice($audience->keywords, 0, 3)) : 'None' }}</div>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    <div class="font-semibold text-gray-900">{{ number_format($audience->campaigns_count) }}</div>
                                    @foreach($audience->campaigns->take(3) as $campaign)
                                        <div class="mt-1 flex items-center justify-between gap-2">
                                            <span>{{ $campaign->name }}</span>
                                            <form method="POST" action="{{ route('advertiser.audiences.detach', [$campaign, $audience]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-rose-600 hover:text-rose-700">Detach</button>
                                            </form>
                                        </div>
                                    @endforeach
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <button type="button"
                                            class="edit-audience-btn rounded-lg border border-blue-200 px-3 py-1.5 text-xs font-semibold text-blue-600 hover:bg-blue-50"
                                            data-id="{{ $audience->id }}"
                                            data-name="{{ e($audience->name) }}"
                                            data-type="{{ $audience->type }}"
                                            data-status="{{ $audience->status }}"
                                            data-description="{{ e($audience->description) }}"
                                            data-countries='@json($audience->countries ?? [])'
                                            data-devices='@json($audience->devices ?? [])'
                                            data-interests="{{ e(implode(', ', $audience->interests ?? [])) }}"
                                            data-keywords="{{ e(implode(', ', $audience->keywords ?? [])) }}"
                                            data-min-visits="{{ $audience->rules['min_visits'] ?? 0 }}"
                                            data-days-since-visit="{{ $audience->rules['days_since_visit'] ?? 30 }}">Edit</button>
                                        <form method="POST" action="{{ route('advertiser.audiences.destroy', $audience) }}" onsubmit="return confirm('Delete this audience?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-12 text-center text-gray-500">No audiences found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($audiences->hasPages())
                <div class="border-t border-gray-100 px-4 py-3">{{ $audiences->links() }}</div>
            @endif
        </div>
    </div>

    <div id="editAudienceModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/40 px-4">
        <div class="max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900">Edit Audience</h3>
                <button type="button" onclick="closeEditAudience()" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100">x</button>
            </div>
            <form id="editAudienceForm" method="POST" class="grid gap-4 p-6 md:grid-cols-2">
                @csrf
                @method('PUT')
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Name</label>
                    <input id="edit_name" name="name" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Type</label>
                    <select id="edit_type" name="type" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        @foreach($types as $type)<option value="{{ $type }}">{{ ucfirst($type) }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Status</label>
                    <select id="edit_status" name="status" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        @foreach($statuses as $status)<option value="{{ $status }}">{{ ucfirst($status) }}</option>@endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="edit_description" name="description" rows="3" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"></textarea>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Countries</label>
                    <select id="edit_countries" name="countries[]" multiple class="min-h-32 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        @foreach($countries as $code => $label)<option value="{{ $code }}">{{ $label }} ({{ $code }})</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Devices</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($devices as $device)
                            <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600">
                                <input class="edit-device" type="checkbox" name="devices[]" value="{{ $device }}">
                                {{ ucwords(str_replace('_', ' ', $device)) }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Interests</label>
                    <textarea id="edit_interests" name="interests" rows="3" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"></textarea>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Keywords</label>
                    <textarea id="edit_keywords" name="keywords" rows="3" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"></textarea>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Min Visits</label>
                    <input id="edit_min_visits" type="number" min="0" name="min_visits" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Days Since Visit</label>
                    <input id="edit_days_since_visit" type="number" min="1" max="365" name="days_since_visit" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-2 flex justify-end gap-3">
                    <button type="button" onclick="closeEditAudience()" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-500">Cancel</button>
                    <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Update Audience</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('editAudienceModal');
    const form = document.getElementById('editAudienceForm');

    document.querySelectorAll('.edit-audience-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            form.action = '{{ url("advertisers/audiences") }}/' + this.dataset.id;
            document.getElementById('edit_name').value = this.dataset.name || '';
            document.getElementById('edit_type').value = this.dataset.type || 'custom';
            document.getElementById('edit_status').value = this.dataset.status || 'active';
            document.getElementById('edit_description').value = this.dataset.description || '';
            document.getElementById('edit_interests').value = this.dataset.interests || '';
            document.getElementById('edit_keywords').value = this.dataset.keywords || '';
            document.getElementById('edit_min_visits').value = this.dataset.minVisits || 0;
            document.getElementById('edit_days_since_visit').value = this.dataset.daysSinceVisit || 30;

            const countries = JSON.parse(this.dataset.countries || '[]');
            Array.from(document.getElementById('edit_countries').options).forEach(option => {
                option.selected = countries.includes(option.value);
            });

            const devices = JSON.parse(this.dataset.devices || '[]');
            document.querySelectorAll('.edit-device').forEach(input => {
                input.checked = devices.includes(input.value);
            });

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });
});

function closeEditAudience() {
    const modal = document.getElementById('editAudienceModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endpush
