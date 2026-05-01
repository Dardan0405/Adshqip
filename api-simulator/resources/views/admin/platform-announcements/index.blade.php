@extends('layouts.admin')

@section('title', 'Platform Announcements')

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
            <h1 class="text-2xl font-bold text-gray-900">Platform Announcements</h1>
            <p class="mt-1 text-sm text-gray-500">Create scheduled notices, pinned banners, and user notifications from one place.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach([
            ['label' => 'Total', 'value' => $summary['total']],
            ['label' => 'Published', 'value' => $summary['published']],
            ['label' => 'Scheduled', 'value' => $summary['scheduled']],
            ['label' => 'Pinned', 'value' => $summary['pinned']],
        ] as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($card['value']) }}</div>
            </div>
        @endforeach
    </div>

    <div class="rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-100 px-6 py-4">
            <h2 class="text-sm font-semibold text-gray-900">Create Announcement</h2>
        </div>
        <form method="POST" action="{{ route('admin.platform-announcements.store') }}" class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-4">
            @csrf
            <div class="xl:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Title</label>
                <input name="title" value="{{ old('title') }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Scheduled maintenance window">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Slug</label>
                <input name="slug" value="{{ old('slug') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="scheduled-maintenance">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Status</label>
                <select name="status" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(old('status', 'draft') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Audience</label>
                <select name="audience" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    @foreach($audiences as $audience)
                        <option value="{{ $audience }}" @selected(old('audience', 'all') === $audience)>{{ ucwords(str_replace('_', ' ', $audience)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Placement</label>
                <select name="placement" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    @foreach($placements as $placement)
                        <option value="{{ $placement }}" @selected(old('placement', 'dashboard') === $placement)>{{ ucwords(str_replace('_', ' ', $placement)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Type</label>
                <select name="type" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    @foreach($types as $type)
                        <option value="{{ $type }}" @selected(old('type', 'info') === $type)>{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">CTA Label</label>
                <input name="cta_label" value="{{ old('cta_label') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Read more">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">CTA URL</label>
                <input name="cta_url" value="{{ old('cta_url') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="/admin/api-docs">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Starts At</label>
                <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Ends At</label>
                <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <div class="md:col-span-2 xl:col-span-4">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Summary</label>
                <textarea name="summary" rows="2" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Short text used in notification cards.">{{ old('summary') }}</textarea>
            </div>
            <div class="md:col-span-2 xl:col-span-4">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Body</label>
                <textarea name="body" required rows="5" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Full announcement message.">{{ old('body') }}</textarea>
            </div>
            <div class="xl:col-span-4 flex flex-wrap items-center gap-3">
                <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700">
                    <input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned'))>
                    Pinned
                </label>
                <button class="ml-auto rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Save Announcement</button>
            </div>
        </form>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-100 p-4">
            <form method="GET" action="{{ route('admin.platform-announcements') }}" class="flex flex-wrap gap-3">
                <input name="search" value="{{ request('search') }}" class="min-w-64 flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Search announcements...">
                <select name="status" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <select name="audience" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" onchange="this.form.submit()">
                    <option value="">All audiences</option>
                    @foreach($audiences as $audience)
                        <option value="{{ $audience }}" @selected(request('audience') === $audience)>{{ ucwords(str_replace('_', ' ', $audience)) }}</option>
                    @endforeach
                </select>
                <select name="type" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" onchange="this.form.submit()">
                    <option value="">All types</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
                <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
                <a href="{{ route('admin.platform-announcements') }}" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-600">Reset</a>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-gray-400">Announcement</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-gray-400">Target</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-gray-400">Schedule</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-gray-400">Notifications</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($announcements as $announcement)
                        @php
                            $statusClass = match($announcement->status) {
                                'published' => 'bg-emerald-100 text-emerald-700',
                                'scheduled' => 'bg-blue-100 text-blue-700',
                                'archived' => 'bg-gray-100 text-gray-600',
                                default => 'bg-amber-100 text-amber-700',
                            };
                            $typeClass = match($announcement->type) {
                                'success' => 'bg-emerald-50 text-emerald-700',
                                'warning', 'maintenance' => 'bg-amber-50 text-amber-700',
                                'incident' => 'bg-rose-50 text-rose-700',
                                'release' => 'bg-violet-50 text-violet-700',
                                default => 'bg-sky-50 text-sky-700',
                            };
                        @endphp
                        <tr>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <div class="font-semibold text-gray-900">{{ $announcement->title }}</div>
                                    @if($announcement->is_pinned)
                                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-amber-700">Pinned</span>
                                    @endif
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase {{ $statusClass }}">{{ $announcement->status }}</span>
                                </div>
                                <div class="mt-1 text-xs text-gray-400">{{ $announcement->slug }} by {{ $announcement->createdBy?->email ?? 'System' }}</div>
                                <div class="mt-1 max-w-2xl text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($announcement->summary ?: $announcement->body, 130) }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-700">{{ ucwords(str_replace('_', ' ', $announcement->audience)) }}</div>
                                <div class="mt-1 flex flex-wrap gap-1">
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-gray-600">{{ str_replace('_', ' ', $announcement->placement) }}</span>
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase {{ $typeClass }}">{{ $announcement->type }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                <div>Start: {{ optional($announcement->starts_at)->format('M d, Y H:i') ?: 'Immediate' }}</div>
                                <div>End: {{ optional($announcement->ends_at)->format('M d, Y H:i') ?: 'No end' }}</div>
                                <div class="mt-1 font-semibold {{ $announcement->is_active ? 'text-emerald-600' : 'text-gray-400' }}">{{ $announcement->is_active ? 'Active now' : 'Not active' }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                <div class="font-semibold text-gray-900">{{ number_format($announcement->notification_count) }}</div>
                                <div>{{ optional($announcement->last_notified_at)->format('M d, Y H:i') ?: 'Not sent' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <button type="button"
                                        class="edit-announcement-btn rounded-lg border border-blue-200 px-3 py-1.5 text-xs font-semibold text-blue-600 hover:bg-blue-50"
                                        data-id="{{ $announcement->id }}"
                                        data-title="{{ e($announcement->title) }}"
                                        data-slug="{{ e($announcement->slug) }}"
                                        data-audience="{{ $announcement->audience }}"
                                        data-placement="{{ $announcement->placement }}"
                                        data-type="{{ $announcement->type }}"
                                        data-status="{{ $announcement->status }}"
                                        data-summary="{{ e($announcement->summary) }}"
                                        data-body="{{ e($announcement->body) }}"
                                        data-cta-label="{{ e($announcement->cta_label) }}"
                                        data-cta-url="{{ e($announcement->cta_url) }}"
                                        data-starts-at="{{ optional($announcement->starts_at)->format('Y-m-d\TH:i') }}"
                                        data-ends-at="{{ optional($announcement->ends_at)->format('Y-m-d\TH:i') }}"
                                        data-is-pinned="{{ $announcement->is_pinned ? '1' : '0' }}">Edit</button>
                                    @if($announcement->status === 'published')
                                        <form method="POST" action="{{ route('admin.platform-announcements.unpublish', $announcement) }}">@csrf @method('PATCH')<button class="rounded-lg border border-amber-200 px-3 py-1.5 text-xs font-semibold text-amber-600 hover:bg-amber-50">Draft</button></form>
                                    @else
                                        <form method="POST" action="{{ route('admin.platform-announcements.publish', $announcement) }}">@csrf @method('PATCH')<button class="rounded-lg border border-emerald-200 px-3 py-1.5 text-xs font-semibold text-emerald-600 hover:bg-emerald-50">Publish</button></form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.platform-announcements.notify', $announcement) }}" onsubmit="return confirm('Send this announcement as notifications to the selected audience?')">@csrf <button class="rounded-lg border border-indigo-200 px-3 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-indigo-50">Notify</button></form>
                                    <form method="POST" action="{{ route('admin.platform-announcements.archive', $announcement) }}">@csrf @method('PATCH')<button class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50">Archive</button></form>
                                    <form method="POST" action="{{ route('admin.platform-announcements.destroy', $announcement) }}" onsubmit="return confirm('Delete this announcement?')">@csrf @method('DELETE')<button class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">Delete</button></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-12 text-center text-gray-500">No platform announcements found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($announcements->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $announcements->links() }}</div>
        @endif
    </div>

    <div id="editAnnouncementModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/40 px-4">
        <div class="max-h-[92vh] w-full max-w-4xl overflow-y-auto rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900">Edit Announcement</h3>
                <button type="button" onclick="closeEditAnnouncement()" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100">x</button>
            </div>
            <form id="editAnnouncementForm" method="POST" class="grid gap-4 p-6 md:grid-cols-2">
                @csrf
                @method('PUT')
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Title</label>
                    <input id="edit_title" name="title" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Slug</label>
                    <input id="edit_slug" name="slug" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Status</label>
                    <select id="edit_status" name="status" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        @foreach($statuses as $status)<option value="{{ $status }}">{{ ucfirst($status) }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Audience</label>
                    <select id="edit_audience" name="audience" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        @foreach($audiences as $audience)<option value="{{ $audience }}">{{ ucwords(str_replace('_', ' ', $audience)) }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Placement</label>
                    <select id="edit_placement" name="placement" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        @foreach($placements as $placement)<option value="{{ $placement }}">{{ ucwords(str_replace('_', ' ', $placement)) }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Type</label>
                    <select id="edit_type" name="type" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        @foreach($types as $type)<option value="{{ $type }}">{{ ucfirst($type) }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">CTA Label</label>
                    <input id="edit_cta_label" name="cta_label" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">CTA URL</label>
                    <input id="edit_cta_url" name="cta_url" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Starts At</label>
                    <input id="edit_starts_at" type="datetime-local" name="starts_at" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Ends At</label>
                    <input id="edit_ends_at" type="datetime-local" name="ends_at" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Summary</label>
                    <textarea id="edit_summary" name="summary" rows="3" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Body</label>
                    <textarea id="edit_body" name="body" required rows="6" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"></textarea>
                </div>
                <div class="md:col-span-2 flex items-center gap-3">
                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700">
                        <input id="edit_is_pinned" type="checkbox" name="is_pinned" value="1">
                        Pinned
                    </label>
                    <div class="ml-auto flex gap-3">
                        <button type="button" onclick="closeEditAnnouncement()" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-500">Cancel</button>
                        <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Update Announcement</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('editAnnouncementModal');
    const form = document.getElementById('editAnnouncementForm');

    document.querySelectorAll('.edit-announcement-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            form.action = '{{ url("admin/platform-announcements") }}/' + this.dataset.id;
            ['title', 'slug', 'audience', 'placement', 'type', 'status', 'summary', 'body', 'ctaLabel', 'ctaUrl', 'startsAt', 'endsAt'].forEach((key) => {
                const id = 'edit_' + key.replace(/[A-Z]/g, letter => '_' + letter.toLowerCase());
                const input = document.getElementById(id);
                if (input) input.value = this.dataset[key] || '';
            });
            document.getElementById('edit_is_pinned').checked = this.dataset.isPinned === '1';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });
});

function closeEditAnnouncement() {
    const modal = document.getElementById('editAnnouncementModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endpush
