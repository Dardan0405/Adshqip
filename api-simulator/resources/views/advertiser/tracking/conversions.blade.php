@extends('layouts.advertiser')

@section('title', 'Conversion Tracking')

@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Conversion Tracking</h1>
            <p class="mt-1 text-sm text-gray-500">Create pixels, copy install code, and link conversions to campaigns.</p>
        </div>
        <button type="button" onclick="openCreatePanel()" class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            New Tracker
        </button>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach([
            ['Trackers', number_format($totalTrackers), 'text-gray-900'],
            ['Active', number_format($activeTrackers), 'text-emerald-700'],
            ['Pixel Fires', number_format($totalFires), 'text-blue-700'],
            ['Conversions', number_format($conversionCount), 'text-brand-700'],
        ] as $card)
            <div class="rounded-lg border border-gray-200 bg-white p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card[0] }}</div>
                <div class="mt-2 text-2xl font-bold {{ $card[2] }}">{{ $card[1] }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr,360px]">
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
            <div class="border-b border-gray-100 p-4">
                <form method="GET" action="{{ route('advertiser.tracking.conversions') }}" class="flex flex-wrap items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search trackers" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <select name="type" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        <option value="">All Types</option>
                        <option value="html_pixel" @selected(request('type') === 'html_pixel')>HTML Pixel</option>
                        <option value="s2s_pixel" @selected(request('type') === 's2s_pixel')>S2S Pixel</option>
                        <option value="mobile_s2s" @selected(request('type') === 'mobile_s2s')>Mobile S2S</option>
                    </select>
                    <select name="status" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        <option value="">All Statuses</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="paused" @selected(request('status') === 'paused')>Paused</option>
                        <option value="archived" @selected(request('status') === 'archived')>Archived</option>
                    </select>
                    <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">Filter</button>
                    <a href="{{ route('advertiser.tracking.conversions') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600">Reset</a>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/70">
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Tracker</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Type</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Linked Campaign</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Fires</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                            <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($trackers as $tracker)
                            @php
                                $linkedCampaign = $campaigns->firstWhere('pixel_tracker_id', $tracker->id);
                            @endphp
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-gray-900">{{ $tracker->name }}</div>
                                    <div class="font-mono text-xs text-gray-400">{{ $tracker->pixel_code }}</div>
                                    @if($tracker->pixel_goal)
                                        <div class="mt-1 text-xs text-gray-500">{{ $tracker->pixel_goal }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ Str::headline($tracker->type) }}</td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('advertiser.tracking.conversions.link', $tracker) }}" class="flex min-w-[210px] items-center gap-2">
                                        @csrf
                                        <select name="campaign_id" class="w-full rounded-lg border border-gray-200 px-2 py-1.5 text-xs">
                                            <option value="">Select campaign</option>
                                            @foreach($campaigns as $campaign)
                                                <option value="{{ $campaign->id }}" @selected($campaign->pixel_tracker_id === $tracker->id)>{{ $campaign->name }}</option>
                                            @endforeach
                                        </select>
                                        <button class="rounded-lg border border-gray-200 px-2 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50">Link</button>
                                    </form>
                                    @if($linkedCampaign)
                                        <div class="mt-1 text-xs text-gray-400">Current: {{ $linkedCampaign->name }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-semibold text-gray-900">{{ number_format($tracker->fire_count) }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $tracker->status === 'active' ? 'bg-emerald-50 text-emerald-700' : ($tracker->status === 'paused' ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-600') }}">
                                        {{ ucfirst($tracker->status ?? 'active') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" onclick="showCode({{ $tracker->id }})" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50">Code</button>
                                        <button type="button"
                                            onclick="openEditPanel(this)"
                                            data-id="{{ $tracker->id }}"
                                            data-name="{{ e($tracker->name) }}"
                                            data-description="{{ e($tracker->description) }}"
                                            data-type="{{ $tracker->type }}"
                                            data-goal="{{ e($tracker->pixel_goal) }}"
                                            data-category="{{ e($tracker->category) }}"
                                            data-status="{{ $tracker->status }}"
                                            data-append="{{ e($tracker->append_code) }}"
                                            data-campaign="{{ $linkedCampaign?->id }}"
                                            class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50">Edit</button>
                                        <form method="POST" action="{{ route('advertiser.tracking.conversions.destroy', $tracker) }}" onsubmit="return confirm('Archive this conversion tracker?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">Archive</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500">No conversion trackers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($trackers->hasPages())
                <div class="border-t border-gray-100 px-4 py-3">{{ $trackers->links() }}</div>
            @endif
        </div>

        <form id="trackerForm" method="POST" action="{{ route('advertiser.tracking.conversions.store') }}" class="h-max rounded-lg border border-gray-200 bg-white p-4">
            @csrf
            <input type="hidden" name="_method" id="trackerMethod" value="POST" disabled>
            <div class="mb-4 flex items-center justify-between">
                <h2 id="formTitle" class="text-sm font-semibold text-gray-900">New Conversion Tracker</h2>
                <button type="button" onclick="resetForm()" class="text-xs font-semibold text-gray-500 hover:text-gray-700">Reset</button>
            </div>
            <div class="space-y-3">
                <input id="trackerName" name="name" value="{{ old('name') }}" placeholder="Tracker name" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" required>
                <textarea id="trackerDescription" name="description" rows="2" placeholder="Description" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">{{ old('description') }}</textarea>
                <select id="trackerType" name="type" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" required>
                    <option value="html_pixel">HTML Pixel</option>
                    <option value="s2s_pixel">S2S Pixel</option>
                    <option value="mobile_s2s">Mobile S2S</option>
                </select>
                <input id="trackerGoal" name="pixel_goal" value="{{ old('pixel_goal') }}" placeholder="Goal, for example Purchase" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <input id="trackerCategory" name="category" value="{{ old('category') }}" placeholder="Category" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <select id="trackerStatus" name="status" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" required>
                    <option value="active">Active</option>
                    <option value="paused">Paused</option>
                    <option value="archived">Archived</option>
                </select>
                <select id="trackerCampaign" name="campaign_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">Link campaign later</option>
                    @foreach($campaigns as $campaign)
                        <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                    @endforeach
                </select>
                <textarea id="trackerAppend" name="append_code" rows="3" placeholder="Optional extra code to append after the pixel" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">{{ old('append_code') }}</textarea>
                <button id="formButton" class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Create Tracker</button>
            </div>
        </form>
    </div>

    <div id="codeModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
        <div class="w-full max-w-2xl rounded-lg bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-gray-100 p-4">
                <div>
                    <h2 id="codeTitle" class="text-base font-semibold text-gray-900">Tracking Code</h2>
                    <p id="codeMeta" class="mt-1 text-xs text-gray-500"></p>
                </div>
                <button type="button" onclick="closeCode()" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600">Close</button>
            </div>
            <div class="space-y-4 p-4">
                <div>
                    <div class="mb-1 text-xs font-semibold uppercase tracking-wider text-gray-400">Install Code or Postback URL</div>
                    <textarea id="codeSnippet" rows="6" readonly class="w-full rounded-lg border border-gray-200 bg-gray-50 p-3 font-mono text-xs text-gray-700"></textarea>
                </div>
                <div>
                    <div class="mb-1 text-xs font-semibold uppercase tracking-wider text-gray-400">Test URL</div>
                    <input id="testUrl" readonly class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 font-mono text-xs text-gray-700">
                </div>
                <button type="button" onclick="copySnippet()" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Copy Code</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const createAction = @json(route('advertiser.tracking.conversions.store'));

        function openCreatePanel() {
            resetForm();
            document.getElementById('trackerName').focus();
        }

        function openEditPanel(button) {
            const form = document.getElementById('trackerForm');
            const method = document.getElementById('trackerMethod');
            form.action = `${createAction}/${button.dataset.id}`;
            method.disabled = false;
            method.value = 'PUT';

            document.getElementById('formTitle').textContent = 'Edit Conversion Tracker';
            document.getElementById('formButton').textContent = 'Update Tracker';
            document.getElementById('trackerName').value = button.dataset.name || '';
            document.getElementById('trackerDescription').value = button.dataset.description || '';
            document.getElementById('trackerType').value = button.dataset.type || 'html_pixel';
            document.getElementById('trackerGoal').value = button.dataset.goal || '';
            document.getElementById('trackerCategory').value = button.dataset.category || '';
            document.getElementById('trackerStatus').value = button.dataset.status || 'active';
            document.getElementById('trackerAppend').value = button.dataset.append || '';
            document.getElementById('trackerCampaign').value = button.dataset.campaign || '';
            document.getElementById('trackerName').focus();
        }

        function resetForm() {
            const form = document.getElementById('trackerForm');
            const method = document.getElementById('trackerMethod');
            form.action = createAction;
            method.disabled = true;
            form.reset();
            document.getElementById('formTitle').textContent = 'New Conversion Tracker';
            document.getElementById('formButton').textContent = 'Create Tracker';
        }

        async function showCode(id) {
            const response = await fetch(`${createAction}/${id}/code`, { headers: { 'Accept': 'application/json' } });
            const data = await response.json();
            document.getElementById('codeTitle').textContent = data.name;
            document.getElementById('codeMeta').textContent = `${data.pixel_code} | ${data.type.replaceAll('_', ' ')}`;
            document.getElementById('codeSnippet').value = data.snippet;
            document.getElementById('testUrl').value = data.test_url;
            document.getElementById('codeModal').classList.remove('hidden');
            document.getElementById('codeModal').classList.add('flex');
        }

        function closeCode() {
            document.getElementById('codeModal').classList.add('hidden');
            document.getElementById('codeModal').classList.remove('flex');
        }

        async function copySnippet() {
            await navigator.clipboard.writeText(document.getElementById('codeSnippet').value);
        }
    </script>
@endpush
