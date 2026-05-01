@extends('layouts.admin')

@section('title', 'Case Studies')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Case Studies</h1>
            <p class="mt-1 text-sm text-gray-500">Manage the case studies shown in the public Results section.</p>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white">
        <form method="POST" action="{{ route('admin.case-studies.store') }}" class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-4">
            @csrf
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Title</label>
                <input name="title" value="{{ old('title') }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Publisher revenue lift">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Slug</label>
                <input name="slug" value="{{ old('slug') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="publisher-revenue-lift">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Audience</label>
                <select name="audience_type" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="publisher">Publisher</option>
                    <option value="advertiser">Advertiser</option>
                    <option value="both">Both</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Industry</label>
                <input name="industry" value="{{ old('industry') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="News portal">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Metric Value</label>
                <input name="metric_value" value="{{ old('metric_value') }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="+42% eCPM">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Metric Label</label>
                <input name="metric_label" value="{{ old('metric_label') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Revenue increase">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Company</label>
                <input name="company_name" value="{{ old('company_name') }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Lajmeri.al">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Logo URL</label>
                <input name="logo_url" value="{{ old('logo_url') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="/Lajmeri.png">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Accent Color</label>
                <input name="accent_color" value="{{ old('accent_color', '#e11d48') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="#e11d48">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Chart Type</label>
                <select name="chart_type" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="comparison">Comparison</option>
                    <option value="line">Line</option>
                    <option value="bar">Bar</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Before</label>
                <div class="grid grid-cols-2 gap-2">
                    <input name="before_label" value="{{ old('before_label') }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Before">
                    <input name="before_value" value="{{ old('before_value') }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="$3.1">
                </div>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">After</label>
                <div class="grid grid-cols-2 gap-2">
                    <input name="after_label" value="{{ old('after_label') }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="After">
                    <input name="after_value" value="{{ old('after_value') }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="$4.4">
                </div>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">CTA URL</label>
                <input name="cta_url" value="{{ old('cta_url') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="#contact">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Sort Order</label>
                <input type="number" min="0" name="sort_order" value="{{ old('sort_order', 0) }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <div class="md:col-span-2 xl:col-span-4">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" required rows="3" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">{{ old('description') }}</textarea>
            </div>
            <div class="md:col-span-2 xl:col-span-4">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Detail Content</label>
                <textarea name="content" rows="7" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Long case study detail shown on the detail page.">{{ old('content') }}</textarea>
            </div>
            <div class="xl:col-span-4 flex flex-wrap items-center gap-3">
                <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700">
                    <input type="checkbox" name="is_published" value="1" checked>
                    Published
                </label>
                <button class="ml-auto rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Save Case Study</button>
            </div>
        </form>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-100 p-4">
            <form method="GET" action="{{ route('admin.case-studies') }}" class="flex flex-wrap gap-3">
                <input name="search" value="{{ request('search') }}" class="min-w-64 flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Search case studies...">
                <select name="status" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All statuses</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
                <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
                <a href="{{ route('admin.case-studies') }}" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-600">Reset</a>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-gray-400">Case</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-gray-400">Metric</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-gray-400">Company</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-gray-400">Status</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($caseStudies as $caseStudy)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">{{ $caseStudy->title }}</div>
                                <div class="text-xs text-gray-400">{{ $caseStudy->slug }} · {{ ucfirst($caseStudy->audience_type) }}{{ $caseStudy->industry ? ' · ' . $caseStudy->industry : '' }}</div>
                                <div class="mt-1 max-w-xl text-xs text-gray-500">{{ Str::limit($caseStudy->description, 120) }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold" style="color: {{ $caseStudy->accent_color }}">{{ $caseStudy->metric_value }}</div>
                                <div class="text-xs text-gray-400">{{ $caseStudy->metric_label ?: $caseStudy->chart_type }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $caseStudy->company_name }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase {{ $caseStudy->is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $caseStudy->is_published ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <button type="button"
                                        class="edit-case-btn rounded-lg border border-blue-200 px-3 py-1.5 text-xs font-semibold text-blue-600 hover:bg-blue-50"
                                        data-id="{{ $caseStudy->id }}"
                                        data-slug="{{ $caseStudy->slug }}"
                                        data-title="{{ e($caseStudy->title) }}"
                                        data-audience-type="{{ $caseStudy->audience_type }}"
                                        data-industry="{{ e($caseStudy->industry) }}"
                                        data-metric-value="{{ e($caseStudy->metric_value) }}"
                                        data-metric-label="{{ e($caseStudy->metric_label) }}"
                                        data-description="{{ e($caseStudy->description) }}"
                                        data-content="{{ e($caseStudy->content) }}"
                                        data-company-name="{{ e($caseStudy->company_name) }}"
                                        data-logo-url="{{ e($caseStudy->logo_url) }}"
                                        data-accent-color="{{ e($caseStudy->accent_color) }}"
                                        data-chart-type="{{ $caseStudy->chart_type }}"
                                        data-before-label="{{ e($caseStudy->before_label) }}"
                                        data-before-value="{{ e($caseStudy->before_value) }}"
                                        data-after-label="{{ e($caseStudy->after_label) }}"
                                        data-after-value="{{ e($caseStudy->after_value) }}"
                                        data-cta-url="{{ e($caseStudy->cta_url) }}"
                                        data-sort-order="{{ $caseStudy->sort_order }}"
                                        data-is-published="{{ $caseStudy->is_published ? '1' : '0' }}">Edit</button>
                                    @if($caseStudy->is_published)
                                        <form method="POST" action="{{ route('admin.case-studies.unpublish', $caseStudy) }}">@csrf @method('PATCH')<button class="rounded-lg border border-amber-200 px-3 py-1.5 text-xs font-semibold text-amber-600 hover:bg-amber-50">Unpublish</button></form>
                                    @else
                                        <form method="POST" action="{{ route('admin.case-studies.publish', $caseStudy) }}">@csrf @method('PATCH')<button class="rounded-lg border border-emerald-200 px-3 py-1.5 text-xs font-semibold text-emerald-600 hover:bg-emerald-50">Publish</button></form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.case-studies.destroy', $caseStudy) }}" onsubmit="return confirm('Delete this case study?')">@csrf @method('DELETE')<button class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">Delete</button></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-12 text-center text-gray-500">No case studies found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($caseStudies->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $caseStudies->links() }}</div>
        @endif
    </div>

    <div id="editCaseModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/40 px-4">
        <div class="max-h-[92vh] w-full max-w-4xl overflow-y-auto rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900">Edit Case Study</h3>
                <button type="button" onclick="closeEditCase()" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100">×</button>
            </div>
            <form id="editCaseForm" method="POST" class="grid gap-4 p-6 md:grid-cols-2">
                @csrf
                @method('PUT')
                @foreach(['title' => 'Title', 'slug' => 'Slug', 'industry' => 'Industry', 'metric_value' => 'Metric Value', 'metric_label' => 'Metric Label', 'company_name' => 'Company', 'logo_url' => 'Logo URL', 'accent_color' => 'Accent Color', 'before_label' => 'Before Label', 'before_value' => 'Before Value', 'after_label' => 'After Label', 'after_value' => 'After Value', 'cta_url' => 'CTA URL'] as $field => $label)
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">{{ $label }}</label>
                        <input id="edit_{{ $field }}" name="{{ $field }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" {{ in_array($field, ['title', 'metric_value', 'company_name']) ? 'required' : '' }}>
                    </div>
                @endforeach
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Audience</label>
                    <select id="edit_audience_type" name="audience_type" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        <option value="publisher">Publisher</option><option value="advertiser">Advertiser</option><option value="both">Both</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Chart Type</label>
                    <select id="edit_chart_type" name="chart_type" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        <option value="comparison">Comparison</option><option value="line">Line</option><option value="bar">Bar</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Sort Order</label>
                    <input id="edit_sort_order" type="number" min="0" name="sort_order" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="edit_description" name="description" required rows="4" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Detail Content</label>
                    <textarea id="edit_content" name="content" rows="7" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"></textarea>
                </div>
                <div class="md:col-span-2 flex items-center gap-3">
                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700">
                        <input id="edit_is_published" type="checkbox" name="is_published" value="1">
                        Published
                    </label>
                    <div class="ml-auto flex gap-3">
                        <button type="button" onclick="closeEditCase()" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-500">Cancel</button>
                        <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Update Case Study</button>
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
    const modal = document.getElementById('editCaseModal');
    const form = document.getElementById('editCaseForm');
    document.querySelectorAll('.edit-case-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            form.action = '{{ url("admin/case-studies") }}/' + this.dataset.id;
            ['slug','title','industry','metricValue','metricLabel','description','content','companyName','logoUrl','accentColor','beforeLabel','beforeValue','afterLabel','afterValue','ctaUrl','sortOrder'].forEach((key) => {
                const id = 'edit_' + key.replace(/[A-Z]/g, letter => '_' + letter.toLowerCase());
                const input = document.getElementById(id);
                if (input) input.value = this.dataset[key] || '';
            });
            document.getElementById('edit_audience_type').value = this.dataset.audienceType || 'both';
            document.getElementById('edit_chart_type').value = this.dataset.chartType || 'comparison';
            document.getElementById('edit_is_published').checked = this.dataset.isPublished === '1';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });
});

function closeEditCase() {
    const modal = document.getElementById('editCaseModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endpush
