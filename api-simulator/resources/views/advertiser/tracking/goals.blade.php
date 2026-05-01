@extends('layouts.advertiser')

@section('title', 'Goals')

@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Goals</h1>
            <p class="mt-1 text-sm text-gray-500">Define purchase, lead, signup, install, pageview, and custom conversion goals.</p>
        </div>
        <button type="button" onclick="resetGoalForm(); document.getElementById('goalName').focus();" class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            New Goal
        </button>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach([
            ['Goals', number_format($totalGoals), 'text-gray-900'],
            ['Active', number_format($activeGoals), 'text-emerald-700'],
            ['Goal Fires', number_format($totalPixelFires), 'text-blue-700'],
            ['Conversions', number_format($totalConversions), 'text-brand-700'],
        ] as $card)
            <div class="rounded-lg border border-gray-200 bg-white p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card[0] }}</div>
                <div class="mt-2 text-2xl font-bold {{ $card[2] }}">{{ $card[1] }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr,380px]">
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
            <div class="border-b border-gray-100 p-4">
                <form method="GET" action="{{ route('advertiser.tracking.goals') }}" class="flex flex-wrap items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search goals" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <select name="goal_type" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        <option value="">All Types</option>
                        @foreach(['purchase', 'lead', 'signup', 'install', 'pageview', 'custom'] as $type)
                            <option value="{{ $type }}" @selected(request('goal_type') === $type)>{{ Str::headline($type) }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        <option value="">All Statuses</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="paused" @selected(request('status') === 'paused')>Paused</option>
                        <option value="archived" @selected(request('status') === 'archived')>Archived</option>
                    </select>
                    <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">Filter</button>
                    <a href="{{ route('advertiser.tracking.goals') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600">Reset</a>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/70">
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Goal</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Value</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Tracker</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                            <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($goals as $goal)
                            @php
                                $tracker = $goal->pixelTracker;
                                $linkedCampaign = $tracker ? $campaigns->firstWhere('pixel_tracker_id', $tracker->id) : null;
                            @endphp
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-gray-900">{{ $goal->name }}</div>
                                    <div class="font-mono text-xs text-gray-400">{{ $goal->goal_key }}</div>
                                    <div class="mt-1 text-xs text-gray-500">{{ Str::headline($goal->goal_type) }} / {{ Str::headline($goal->counting_method) }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-gray-900">{{ $goal->currency }} {{ number_format((float) $goal->default_value, 2) }}</div>
                                    <div class="text-xs text-gray-400">{{ $goal->attribution_window_days }} day window</div>
                                </td>
                                <td class="px-4 py-3">
                                    @if($tracker)
                                        <div class="font-mono text-xs text-gray-700">{{ $tracker->pixel_code }}</div>
                                        <div class="text-xs text-gray-400">{{ Str::headline($tracker->type) }} / {{ number_format($tracker->fire_count) }} fires</div>
                                        @if($linkedCampaign)
                                            <div class="mt-1 text-xs text-gray-500">Campaign: {{ $linkedCampaign->name }}</div>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-400">No tracker</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $goal->status === 'active' ? 'bg-emerald-50 text-emerald-700' : ($goal->status === 'paused' ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-600') }}">
                                        {{ ucfirst($goal->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" onclick="showGoalCode({{ $goal->id }})" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50">Code</button>
                                        <button type="button"
                                            onclick="editGoal(this)"
                                            data-id="{{ $goal->id }}"
                                            data-name="{{ e($goal->name) }}"
                                            data-key="{{ e($goal->goal_key) }}"
                                            data-type="{{ $goal->goal_type }}"
                                            data-value="{{ $goal->default_value }}"
                                            data-currency="{{ $goal->currency }}"
                                            data-counting="{{ $goal->counting_method }}"
                                            data-window="{{ $goal->attribution_window_days }}"
                                            data-status="{{ $goal->status }}"
                                            data-description="{{ e($goal->description) }}"
                                            data-tracker="{{ $tracker?->id }}"
                                            data-campaign="{{ $linkedCampaign?->id }}"
                                            class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50">Edit</button>
                                        <form method="POST" action="{{ route('advertiser.tracking.goals.destroy', $goal) }}" onsubmit="return confirm('Archive this goal?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">Archive</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-sm text-gray-500">No goals found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($goals->hasPages())
                <div class="border-t border-gray-100 px-4 py-3">{{ $goals->links() }}</div>
            @endif
        </div>

        <form id="goalForm" method="POST" action="{{ route('advertiser.tracking.goals.store') }}" class="h-max rounded-lg border border-gray-200 bg-white p-4">
            @csrf
            <input type="hidden" name="_method" id="goalMethod" value="PUT" disabled>
            <div class="mb-4 flex items-center justify-between">
                <h2 id="goalFormTitle" class="text-sm font-semibold text-gray-900">New Goal</h2>
                <button type="button" onclick="resetGoalForm()" class="text-xs font-semibold text-gray-500 hover:text-gray-700">Reset</button>
            </div>
            <div class="space-y-3">
                <input id="goalName" name="name" value="{{ old('name') }}" placeholder="Goal name" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" required>
                <input id="goalKey" name="goal_key" value="{{ old('goal_key') }}" placeholder="Goal key, for example purchase" class="w-full rounded-lg border border-gray-200 px-3 py-2 font-mono text-sm">
                <select id="goalType" name="goal_type" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" required>
                    @foreach(['purchase', 'lead', 'signup', 'install', 'pageview', 'custom'] as $type)
                        <option value="{{ $type }}">{{ Str::headline($type) }}</option>
                    @endforeach
                </select>
                <div class="grid grid-cols-2 gap-2">
                    <input id="goalValue" name="default_value" type="number" step="0.0001" min="0" value="{{ old('default_value', 0) }}" placeholder="Value" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <input id="goalCurrency" name="currency" maxlength="3" value="{{ old('currency', 'USD') }}" placeholder="USD" class="rounded-lg border border-gray-200 px-3 py-2 text-sm uppercase">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <select id="goalCounting" name="counting_method" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" required>
                        <option value="every">Every</option>
                        <option value="once_per_click">Once Per Click</option>
                        <option value="once_per_user">Once Per User</option>
                    </select>
                    <input id="goalWindow" name="attribution_window_days" type="number" min="1" max="365" value="{{ old('attribution_window_days', 30) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" required>
                </div>
                <select id="goalStatus" name="status" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" required>
                    <option value="active">Active</option>
                    <option value="paused">Paused</option>
                    <option value="archived">Archived</option>
                </select>
                <select id="trackerMode" name="tracker_mode" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" onchange="toggleTrackerFields()" required>
                    <option value="create">Create Tracker</option>
                    <option value="existing">Use Existing Tracker</option>
                    <option value="none">No Tracker</option>
                </select>
                <select id="trackerType" name="tracker_type" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="html_pixel">HTML Pixel</option>
                    <option value="s2s_pixel">S2S Pixel</option>
                    <option value="mobile_s2s">Mobile S2S</option>
                </select>
                <select id="pixelTrackerId" name="pixel_tracker_id" class="hidden w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">Select tracker</option>
                    @foreach($availableTrackers as $tracker)
                        <option value="{{ $tracker->id }}">{{ $tracker->name }} / {{ $tracker->pixel_code }}</option>
                    @endforeach
                </select>
                <select id="campaignId" name="campaign_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">Link campaign later</option>
                    @foreach($campaigns as $campaign)
                        <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                    @endforeach
                </select>
                <textarea id="goalDescription" name="description" rows="3" placeholder="Description" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">{{ old('description') }}</textarea>
                <button id="goalSubmit" class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Create Goal</button>
            </div>
        </form>
    </div>

    <div id="goalCodeModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
        <div class="w-full max-w-2xl rounded-lg bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-gray-100 p-4">
                <div>
                    <h2 id="goalCodeTitle" class="text-base font-semibold text-gray-900">Goal Code</h2>
                    <p id="goalCodeMeta" class="mt-1 text-xs text-gray-500"></p>
                </div>
                <button type="button" onclick="closeGoalCode()" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600">Close</button>
            </div>
            <div class="space-y-4 p-4">
                <textarea id="goalCodeSnippet" rows="6" readonly class="w-full rounded-lg border border-gray-200 bg-gray-50 p-3 font-mono text-xs text-gray-700"></textarea>
                <input id="goalTestUrl" readonly class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 font-mono text-xs text-gray-700">
                <button type="button" onclick="copyGoalCode()" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Copy Code</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const goalsStoreAction = @json(route('advertiser.tracking.goals.store'));

        function toggleTrackerFields() {
            const mode = document.getElementById('trackerMode').value;
            document.getElementById('trackerType').classList.toggle('hidden', mode !== 'create');
            document.getElementById('pixelTrackerId').classList.toggle('hidden', mode !== 'existing');
        }

        function resetGoalForm() {
            const form = document.getElementById('goalForm');
            const method = document.getElementById('goalMethod');
            form.action = goalsStoreAction;
            method.disabled = true;
            form.reset();
            document.getElementById('goalFormTitle').textContent = 'New Goal';
            document.getElementById('goalSubmit').textContent = 'Create Goal';
            toggleTrackerFields();
        }

        function editGoal(button) {
            const form = document.getElementById('goalForm');
            const method = document.getElementById('goalMethod');
            form.action = `${goalsStoreAction}/${button.dataset.id}`;
            method.disabled = false;
            document.getElementById('goalFormTitle').textContent = 'Edit Goal';
            document.getElementById('goalSubmit').textContent = 'Update Goal';
            document.getElementById('goalName').value = button.dataset.name || '';
            document.getElementById('goalKey').value = button.dataset.key || '';
            document.getElementById('goalType').value = button.dataset.type || 'purchase';
            document.getElementById('goalValue').value = button.dataset.value || 0;
            document.getElementById('goalCurrency').value = button.dataset.currency || 'USD';
            document.getElementById('goalCounting').value = button.dataset.counting || 'every';
            document.getElementById('goalWindow').value = button.dataset.window || 30;
            document.getElementById('goalStatus').value = button.dataset.status || 'active';
            document.getElementById('goalDescription').value = button.dataset.description || '';
            document.getElementById('trackerMode').value = button.dataset.tracker ? 'existing' : 'create';
            document.getElementById('pixelTrackerId').value = button.dataset.tracker || '';
            document.getElementById('campaignId').value = button.dataset.campaign || '';
            toggleTrackerFields();
            document.getElementById('goalName').focus();
        }

        async function showGoalCode(id) {
            const response = await fetch(`${goalsStoreAction}/${id}/code`, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) return;
            const data = await response.json();
            document.getElementById('goalCodeTitle').textContent = data.name;
            document.getElementById('goalCodeMeta').textContent = `${data.goal_key} | ${data.pixel_code}`;
            document.getElementById('goalCodeSnippet').value = data.snippet;
            document.getElementById('goalTestUrl').value = data.test_url;
            document.getElementById('goalCodeModal').classList.remove('hidden');
            document.getElementById('goalCodeModal').classList.add('flex');
        }

        function closeGoalCode() {
            document.getElementById('goalCodeModal').classList.add('hidden');
            document.getElementById('goalCodeModal').classList.remove('flex');
        }

        async function copyGoalCode() {
            await navigator.clipboard.writeText(document.getElementById('goalCodeSnippet').value);
        }

        toggleTrackerFields();
    </script>
@endpush
