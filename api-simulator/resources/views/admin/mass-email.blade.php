@extends('layouts.admin')
@section('title', 'Mass Email')
@section('content')

    @if(session('success'))
        <div class="mb-4 flex items-center gap-2 rounded-xl border border-emerald-300 bg-emerald-50 p-3 text-sm text-emerald-700">
            <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 flex items-center gap-2 rounded-xl border border-rose-300 bg-rose-50 p-3 text-sm text-rose-700">
            <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Mass Email</h1>
            <p class="mt-1 text-sm text-gray-500">Send bulk emails to advertisers and publishers.</p>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-blue-500">Total Advertisers</div>
            <div class="mt-2 text-2xl font-bold text-blue-700">{{ number_format($stats['totalAdvertisers']) }}</div>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-emerald-500">Total Publishers</div>
            <div class="mt-2 text-2xl font-bold text-emerald-700">{{ number_format($stats['totalPublishers']) }}</div>
        </div>
        <div class="rounded-xl border border-purple-200 bg-purple-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-purple-500">Emails Sent</div>
            <div class="mt-2 text-2xl font-bold text-purple-700">{{ number_format($stats['totalEmailsSent']) }}</div>
        </div>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-amber-500">Campaigns</div>
            <div class="mt-2 text-2xl font-bold text-amber-700">{{ number_format($stats['recentCampaigns']) }}</div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-100 px-6 py-4">
            <h3 class="text-lg font-semibold text-gray-900">Compose Email</h3>
            <p class="text-sm text-gray-500">Select recipients and compose your message.</p>
        </div>
        <form method="POST" action="{{ route('admin.mass-email.send') }}" class="p-6" id="massEmailForm">
            @csrf

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Recipient Type</label>
                    <select name="recipient_type" id="recipientType" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" required>
                        <option value="">Select recipient type...</option>
                        <option value="advertisers" {{ old('recipient_type') === 'advertisers' ? 'selected' : '' }}>Advertisers</option>
                        <option value="publishers" {{ old('recipient_type') === 'publishers' ? 'selected' : '' }}>Publishers</option>
                    </select>
                    @error('recipient_type') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Recipient Scope</label>
                    <select name="recipient_scope" id="recipientScope" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" required>
                        <option value="all" {{ old('recipient_scope', 'all') === 'all' ? 'selected' : '' }}>All (Send to everyone)</option>
                        <option value="selected" {{ old('recipient_scope') === 'selected' ? 'selected' : '' }}>Selected (Choose specific recipients)</option>
                    </select>
                    @error('recipient_scope') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div id="advertiserSelection" class="mt-4 hidden">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Select Advertisers</label>
                <div class="rounded-lg border border-gray-200 p-3 max-h-48 overflow-y-auto">
                    <div class="mb-2 flex items-center gap-2">
                        <input type="checkbox" id="selectAllAdvertisers" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <label for="selectAllAdvertisers" class="text-sm font-medium text-gray-700">Select All Advertisers</label>
                    </div>
                    <div class="border-t border-gray-100 pt-2 space-y-1">
                        @foreach($advertisers as $advertiser)
                            <label class="flex items-center gap-2 rounded p-1 hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" name="selected_recipients[]" value="{{ $advertiser->id }}" class="advertiser-checkbox h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" {{ in_array($advertiser->id, old('selected_recipients', [])) ? 'checked' : '' }}>
                                <span class="text-sm text-gray-700">{{ $advertiser->email }}</span>
                                @if($advertiser->profile?->company_name)
                                    <span class="text-xs text-gray-400">({{ $advertiser->profile->company_name }})</span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>
                @error('selected_recipients') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div id="publisherSelection" class="mt-4 hidden">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Select Publishers</label>
                <div class="rounded-lg border border-gray-200 p-3 max-h-48 overflow-y-auto">
                    <div class="mb-2 flex items-center gap-2">
                        <input type="checkbox" id="selectAllPublishers" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <label for="selectAllPublishers" class="text-sm font-medium text-gray-700">Select All Publishers</label>
                    </div>
                    <div class="border-t border-gray-100 pt-2 space-y-1">
                        @foreach($publishers as $publisher)
                            <label class="flex items-center gap-2 rounded p-1 hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" name="selected_recipients[]" value="{{ $publisher->id }}" class="publisher-checkbox h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" {{ in_array($publisher->id, old('selected_recipients', [])) ? 'checked' : '' }}>
                                <span class="text-sm text-gray-700">{{ $publisher->email }}</span>
                                @if($publisher->profile?->company_name)
                                    <span class="text-xs text-gray-400">({{ $publisher->profile->company_name }})</span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>
                @error('selected_recipients') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div class="mt-4">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Subject</label>
                <input type="text" name="subject" value="{{ old('subject') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="Enter email subject..." required>
                @error('subject') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div class="mt-4">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Message</label>
                <textarea name="message" rows="8" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="Enter your message here..." required>{{ old('message') }}</textarea>
                @error('message') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div class="mt-6 flex items-center justify-between border-t border-gray-100 pt-4">
                <div id="recipientCount" class="text-sm text-gray-500">
                    Select recipient type and scope to see count
                </div>
                <button type="submit" class="rounded-lg bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-brand-700 flex items-center gap-2">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Send Email
                </button>
            </div>
        </form>
    </div>

    @if($recentEmails->isNotEmpty())
        <div class="mt-6 rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-100 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900">Recent Email Campaigns</h3>
                <p class="text-sm text-gray-500">Last 10 mass email campaigns.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/70">
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Type</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Subject</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Recipients</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Sent</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Failed</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($recentEmails as $email)
                            <tr class="transition-colors hover:bg-gray-50/60">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $email->id }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase {{ $email->recipient_type === 'advertisers' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700' }}">
                                        {{ $email->recipient_type }}
                                    </span>
                                    <span class="ml-1 text-xs text-gray-400">({{ $email->recipient_scope }})</span>
                                </td>
                                <td class="px-4 py-3 max-w-xs truncate text-gray-700">{{ $email->subject }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ number_format($email->recipients_count) }}</td>
                                <td class="px-4 py-3 text-emerald-600 font-medium">{{ number_format($email->sent_count) }}</td>
                                <td class="px-4 py-3 text-rose-600 font-medium">{{ number_format($email->failed_count) }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase
                                        {{ $email->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                        {{ $email->status === 'sending' ? 'bg-amber-100 text-amber-700' : '' }}
                                        {{ $email->status === 'pending' ? 'bg-gray-100 text-gray-700' : '' }}
                                        {{ $email->status === 'failed' ? 'bg-rose-100 text-rose-700' : '' }}
                                    ">
                                        {{ $email->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $email->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var recipientType = document.getElementById('recipientType');
    var recipientScope = document.getElementById('recipientScope');
    var advertiserSelection = document.getElementById('advertiserSelection');
    var publisherSelection = document.getElementById('publisherSelection');
    var recipientCount = document.getElementById('recipientCount');
    var selectAllAdvertisers = document.getElementById('selectAllAdvertisers');
    var selectAllPublishers = document.getElementById('selectAllPublishers');

    var totalAdvertisers = {{ $stats['totalAdvertisers'] }};
    var totalPublishers = {{ $stats['totalPublishers'] }};

    function updateSelectionVisibility() {
        var type = recipientType.value;
        var scope = recipientScope.value;

        advertiserSelection.classList.add('hidden');
        publisherSelection.classList.add('hidden');

        if (scope === 'selected') {
            if (type === 'advertisers') {
                advertiserSelection.classList.remove('hidden');
            } else if (type === 'publishers') {
                publisherSelection.classList.remove('hidden');
            }
        }

        updateRecipientCount();
    }

    function updateRecipientCount() {
        var type = recipientType.value;
        var scope = recipientScope.value;
        var count = 0;

        if (!type) {
            recipientCount.textContent = 'Select recipient type and scope to see count';
            return;
        }

        if (scope === 'all') {
            count = type === 'advertisers' ? totalAdvertisers : totalPublishers;
        } else {
            var checkboxes = type === 'advertisers'
                ? document.querySelectorAll('.advertiser-checkbox:checked')
                : document.querySelectorAll('.publisher-checkbox:checked');
            count = checkboxes.length;
        }

        var label = type === 'advertisers' ? 'advertiser' : 'publisher';
        recipientCount.textContent = 'Will send to ' + count + ' ' + label + (count !== 1 ? 's' : '');
    }

    recipientType.addEventListener('change', updateSelectionVisibility);
    recipientScope.addEventListener('change', updateSelectionVisibility);

    selectAllAdvertisers.addEventListener('change', function() {
        document.querySelectorAll('.advertiser-checkbox').forEach(function(cb) {
            cb.checked = selectAllAdvertisers.checked;
        });
        updateRecipientCount();
    });

    selectAllPublishers.addEventListener('change', function() {
        document.querySelectorAll('.publisher-checkbox').forEach(function(cb) {
            cb.checked = selectAllPublishers.checked;
        });
        updateRecipientCount();
    });

    document.querySelectorAll('.advertiser-checkbox, .publisher-checkbox').forEach(function(cb) {
        cb.addEventListener('change', updateRecipientCount);
    });

    // Initialize on page load
    updateSelectionVisibility();
});
</script>
@endpush
