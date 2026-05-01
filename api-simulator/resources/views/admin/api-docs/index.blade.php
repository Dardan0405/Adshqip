@extends('layouts.admin')

@section('title', 'API Docs')

@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
    @endif

    <div class="mb-6 flex flex-col gap-2">
        <h1 class="text-2xl font-bold text-gray-900">API Docs</h1>
        <p class="text-sm text-gray-500">Manage internal API documentation for integrations, protected endpoints, and examples.</p>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach([
            ['label' => 'Total Docs', 'value' => number_format($stats['total'])],
            ['label' => 'Published', 'value' => number_format($stats['published'])],
            ['label' => 'Draft', 'value' => number_format($stats['draft'])],
            ['label' => 'Protected', 'value' => number_format($stats['protected'])],
        ] as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="mb-6 rounded-xl border border-gray-200 bg-white">
        <form method="POST" action="{{ route('admin.api-docs.store') }}" class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-4">
            @csrf
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Title</label>
                <input name="title" value="{{ old('title') }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Integration ping">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Slug</label>
                <input name="slug" value="{{ old('slug') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="integration-ping">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Category</label>
                <input name="category" value="{{ old('category', 'Authentication') }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Method</label>
                <select name="http_method" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    @foreach(['GET','POST','PUT','PATCH','DELETE','OPTIONS'] as $method)
                        <option value="{{ $method }}">{{ $method }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Endpoint Path</label>
                <input name="endpoint_path" value="{{ old('endpoint_path') }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 font-mono text-sm" placeholder="/api/integration/ping">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Required Permission</label>
                <input name="required_permission" value="{{ old('required_permission') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="read_reports">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Sort Order</label>
                <input type="number" min="0" name="sort_order" value="{{ old('sort_order', 0) }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <div class="md:col-span-2 xl:col-span-4">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" rows="3" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">{{ old('description') }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Headers Example</label>
                <textarea name="headers_example" rows="5" class="w-full rounded-lg border border-gray-200 px-3 py-2 font-mono text-xs" placeholder="X-API-Key: AK_...&#10;X-API-Secret: SK_...">{{ old('headers_example') }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Request Example</label>
                <textarea name="request_example" rows="5" class="w-full rounded-lg border border-gray-200 px-3 py-2 font-mono text-xs">{{ old('request_example') }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Response Example</label>
                <textarea name="response_example" rows="6" class="w-full rounded-lg border border-gray-200 px-3 py-2 font-mono text-xs">{{ old('response_example') }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Notes</label>
                <textarea name="notes" rows="6" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">{{ old('notes') }}</textarea>
            </div>
            <div class="xl:col-span-4 flex flex-wrap items-center gap-3">
                <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700">
                    <input type="checkbox" name="auth_required" value="1" checked>
                    Requires API Key
                </label>
                <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700">
                    <input type="checkbox" name="is_published" value="1" checked>
                    Published
                </label>
                <button class="ml-auto rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Save API Doc</button>
            </div>
        </form>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-100 p-4">
            <form method="GET" action="{{ route('admin.api-docs') }}" class="flex flex-wrap gap-3">
                <input name="search" value="{{ request('search') }}" class="min-w-64 flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Search docs...">
                <select name="category" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                    @endforeach
                </select>
                <select name="status" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All statuses</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
                <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
                <a href="{{ route('admin.api-docs') }}" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-600">Reset</a>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-gray-400">Endpoint</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-gray-400">Category</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-gray-400">Auth</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-gray-400">Status</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($apiDocs as $doc)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="rounded bg-gray-900 px-2 py-0.5 font-mono text-[10px] font-bold text-white">{{ $doc->http_method }}</span>
                                    <span class="font-semibold text-gray-900">{{ $doc->title }}</span>
                                </div>
                                <div class="mt-1 font-mono text-xs text-gray-500">{{ $doc->endpoint_path }}</div>
                                <div class="mt-1 max-w-xl text-xs text-gray-500">{{ Str::limit($doc->description, 120) }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $doc->category }}</td>
                            <td class="px-4 py-3">
                                @if($doc->auth_required)
                                    <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-blue-700">API Key</span>
                                    @if($doc->required_permission)
                                        <div class="mt-1 font-mono text-xs text-gray-400">{{ $doc->required_permission }}</div>
                                    @endif
                                @else
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-gray-600">Public</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase {{ $doc->is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $doc->is_published ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <button type="button"
                                        class="edit-doc-btn rounded-lg border border-blue-200 px-3 py-1.5 text-xs font-semibold text-blue-600 hover:bg-blue-50"
                                        data-id="{{ $doc->id }}"
                                        data-slug="{{ e($doc->slug) }}"
                                        data-title="{{ e($doc->title) }}"
                                        data-category="{{ e($doc->category) }}"
                                        data-http-method="{{ $doc->http_method }}"
                                        data-endpoint-path="{{ e($doc->endpoint_path) }}"
                                        data-auth-required="{{ $doc->auth_required ? '1' : '0' }}"
                                        data-required-permission="{{ e($doc->required_permission) }}"
                                        data-description="{{ e($doc->description) }}"
                                        data-headers-example="{{ e($doc->headers_example) }}"
                                        data-request-example="{{ e($doc->request_example) }}"
                                        data-response-example="{{ e($doc->response_example) }}"
                                        data-notes="{{ e($doc->notes) }}"
                                        data-sort-order="{{ $doc->sort_order }}"
                                        data-is-published="{{ $doc->is_published ? '1' : '0' }}">Edit</button>
                                    @if($doc->is_published)
                                        <form method="POST" action="{{ route('admin.api-docs.unpublish', $doc) }}">@csrf @method('PATCH')<button class="rounded-lg border border-amber-200 px-3 py-1.5 text-xs font-semibold text-amber-600 hover:bg-amber-50">Unpublish</button></form>
                                    @else
                                        <form method="POST" action="{{ route('admin.api-docs.publish', $doc) }}">@csrf @method('PATCH')<button class="rounded-lg border border-emerald-200 px-3 py-1.5 text-xs font-semibold text-emerald-600 hover:bg-emerald-50">Publish</button></form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.api-docs.destroy', $doc) }}" onsubmit="return confirm('Delete this API doc?')">@csrf @method('DELETE')<button class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">Delete</button></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-12 text-center text-gray-500">No API docs found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($apiDocs->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $apiDocs->links() }}</div>
        @endif
    </div>

    <div id="editDocModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/40 px-4">
        <div class="max-h-[92vh] w-full max-w-5xl overflow-y-auto rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900">Edit API Doc</h3>
                <button type="button" onclick="closeEditDoc()" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100">×</button>
            </div>
            <form id="editDocForm" method="POST" class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-4">
                @csrf
                @method('PUT')
                @foreach(['title' => 'Title', 'slug' => 'Slug', 'category' => 'Category', 'endpoint_path' => 'Endpoint Path', 'required_permission' => 'Required Permission', 'sort_order' => 'Sort Order'] as $field => $label)
                    <div class="{{ $field === 'endpoint_path' ? 'md:col-span-2' : '' }}">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">{{ $label }}</label>
                        <input id="edit_{{ $field }}" name="{{ $field }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm {{ $field === 'endpoint_path' ? 'font-mono' : '' }}" {{ in_array($field, ['title', 'category', 'endpoint_path']) ? 'required' : '' }}>
                    </div>
                @endforeach
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Method</label>
                    <select id="edit_http_method" name="http_method" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        @foreach(['GET','POST','PUT','PATCH','DELETE','OPTIONS'] as $method)
                            <option value="{{ $method }}">{{ $method }}</option>
                        @endforeach
                    </select>
                </div>
                @foreach(['description' => ['Description', 3, 'text-sm'], 'headers_example' => ['Headers Example', 5, 'font-mono text-xs'], 'request_example' => ['Request Example', 5, 'font-mono text-xs'], 'response_example' => ['Response Example', 6, 'font-mono text-xs'], 'notes' => ['Notes', 5, 'text-sm']] as $field => [$label, $rows, $classes])
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">{{ $label }}</label>
                        <textarea id="edit_{{ $field }}" name="{{ $field }}" rows="{{ $rows }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 {{ $classes }}"></textarea>
                    </div>
                @endforeach
                <div class="xl:col-span-4 flex items-center gap-3">
                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700">
                        <input id="edit_auth_required" type="checkbox" name="auth_required" value="1">
                        Requires API Key
                    </label>
                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700">
                        <input id="edit_is_published" type="checkbox" name="is_published" value="1">
                        Published
                    </label>
                    <div class="ml-auto flex gap-3">
                        <button type="button" onclick="closeEditDoc()" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-500">Cancel</button>
                        <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Update API Doc</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('editDocModal');
    const form = document.getElementById('editDocForm');

    document.querySelectorAll('.edit-doc-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            form.action = '{{ url("admin/api-docs") }}/' + this.dataset.id;
            ['slug','title','category','endpointPath','requiredPermission','description','headersExample','requestExample','responseExample','notes','sortOrder'].forEach((key) => {
                const id = 'edit_' + key.replace(/[A-Z]/g, letter => '_' + letter.toLowerCase());
                const input = document.getElementById(id);
                if (input) input.value = this.dataset[key] || '';
            });
            document.getElementById('edit_http_method').value = this.dataset.httpMethod || 'GET';
            document.getElementById('edit_auth_required').checked = this.dataset.authRequired === '1';
            document.getElementById('edit_is_published').checked = this.dataset.isPublished === '1';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });
});

function closeEditDoc() {
    const modal = document.getElementById('editDocModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endpush
