@extends('layouts.admin')

@section('title', 'FAQ Management')

@section('content')
<div class="space-y-6" x-data="faqPage()">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">FAQ Management</h1>
            <p class="text-sm text-gray-500 mt-1">Manage frequently asked questions displayed on the website</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="showCreateModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add FAQ
            </button>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Search Filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" action="{{ route('admin.faqs') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search questions or answers..." class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="w-40">
                <label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
                <select name="category" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-32">
                <label class="block text-xs font-medium text-gray-600 mb-1">Language</label>
                <select name="language" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All</option>
                    @foreach($languages as $lang)
                        <option value="{{ $lang }}" {{ request('language') == $lang ? 'selected' : '' }}>{{ strtoupper($lang) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                    Search
                </button>
                <a href="{{ route('admin.faqs') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- FAQs Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 w-12">ID</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 w-24">Order</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Question</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 w-32">Category</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600 w-20">Lang</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600 w-24">Status</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600 w-32">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($faqs as $faq)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-900 font-medium">{{ $faq->id }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-600 text-sm font-medium">{{ $faq->sort_order }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="max-w-md">
                                <p class="font-medium text-gray-900 truncate">{{ $faq->question }}</p>
                                <p class="text-xs text-gray-500 mt-1 truncate">{{ Str::limit(strip_tags($faq->answer), 80) }}</p>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $faq->category }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 uppercase">
                                {{ $faq->language }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($faq->is_published)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Published</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Draft</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                {{-- Edit --}}
                                <button @click="openEditModal({{ json_encode($faq) }})" class="p-1.5 rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-600 transition" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>

                                {{-- Publish/Unpublish --}}
                                @if($faq->is_published)
                                <form method="POST" action="{{ route('admin.faqs.unpublish', $faq->id) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="p-1.5 rounded-lg hover:bg-yellow-50 text-gray-400 hover:text-yellow-600 transition" title="Unpublish" onclick="return confirm('Unpublish this FAQ?')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('admin.faqs.publish', $faq->id) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="p-1.5 rounded-lg hover:bg-green-50 text-gray-400 hover:text-green-600 transition" title="Publish">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                </form>
                                @endif

                                {{-- Delete --}}
                                <form method="POST" action="{{ route('admin.faqs.destroy', $faq->id) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600 transition" title="Delete" onclick="return confirm('Are you sure you want to delete this FAQ? This action cannot be undone.')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            No FAQs found. Create your first FAQ to get started.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($faqs->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $faqs->links() }}
        </div>
        @endif
    </div>

    {{-- Create Modal --}}
    <div x-show="showCreateModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-2xl w-full p-6" @click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Add FAQ</h3>
                    <button @click="showCreateModal = false" class="p-1 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.faqs.store') }}" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                            <input type="text" name="category" required list="category-list" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g., General, Billing, Technical">
                            <datalist id="category-list">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Language <span class="text-red-500">*</span></label>
                                <select name="language" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="en">English (EN)</option>
                                    <option value="sq">Albanian (SQ)</option>
                                    <option value="de">German (DE)</option>
                                    <option value="it">Italian (IT)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                                <input type="number" name="sort_order" value="0" min="0" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Question <span class="text-red-500">*</span></label>
                        <input type="text" name="question" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter the question...">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Answer <span class="text-red-500">*</span></label>
                        <textarea name="answer" required rows="6" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter the answer... (HTML is supported)"></textarea>
                        <p class="mt-1 text-xs text-gray-500">You can use HTML for formatting (paragraphs, lists, etc.)</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition">
                            Create FAQ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div x-show="showEditModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="fixed inset-0 bg-black/50" @click="showEditModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-2xl w-full p-6" @click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Edit FAQ</h3>
                    <button @click="showEditModal = false" class="p-1 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="'/admin/faqs/' + editFaq.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                            <input type="text" name="category" x-model="editFaq.category" required list="category-list-edit" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <datalist id="category-list-edit">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Language <span class="text-red-500">*</span></label>
                                <select name="language" x-model="editFaq.language" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="en">English (EN)</option>
                                    <option value="sq">Albanian (SQ)</option>
                                    <option value="de">German (DE)</option>
                                    <option value="it">Italian (IT)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                                <input type="number" name="sort_order" x-model="editFaq.sort_order" min="0" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Question <span class="text-red-500">*</span></label>
                        <input type="text" name="question" x-model="editFaq.question" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Answer <span class="text-red-500">*</span></label>
                        <textarea name="answer" x-model="editFaq.answer" required rows="6" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition">
                            Update FAQ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function faqPage() {
    return {
        showCreateModal: false,
        showEditModal: false,
        editFaq: {
            id: null,
            category: '',
            question: '',
            answer: '',
            language: 'en',
            sort_order: 0
        },
        openEditModal(faq) {
            this.editFaq = {
                id: faq.id,
                category: faq.category || 'General',
                question: faq.question,
                answer: faq.answer,
                language: faq.language || 'en',
                sort_order: faq.sort_order || 0
            };
            this.showEditModal = true;
        }
    }
}
</script>
@endsection
