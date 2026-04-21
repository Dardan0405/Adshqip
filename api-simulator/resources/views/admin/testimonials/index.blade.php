@extends('layouts.admin')

@section('title', 'Testimonials')

@section('content')
<div class="space-y-6" x-data="testimonialPage()">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Testimonials</h1>
            <p class="text-sm text-gray-500 mt-1">Manage customer testimonials displayed on the website</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="showCreateModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Testimonial
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
        <form method="GET" action="{{ route('admin.testimonials') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, company, or quote..." class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="w-40">
                <label class="block text-xs font-medium text-gray-600 mb-1">Featured</label>
                <select name="featured" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All</option>
                    <option value="1" {{ request('featured') === '1' ? 'selected' : '' }}>Featured Only</option>
                    <option value="0" {{ request('featured') === '0' ? 'selected' : '' }}>Not Featured</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                    Search
                </button>
                <a href="{{ route('admin.testimonials') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Testimonials Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($testimonials as $testimonial)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative">
            {{-- Status Badges --}}
            <div class="absolute top-4 right-4 flex items-center gap-2">
                @if($testimonial->is_featured)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                    Featured
                </span>
                @endif
                @if($testimonial->is_published)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Published</span>
                @else
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Draft</span>
                @endif
            </div>

            {{-- Author Info --}}
            <div class="flex items-center gap-4 mb-4">
                @if($testimonial->author_avatar_url)
                <img src="{{ $testimonial->author_avatar_url }}" alt="{{ $testimonial->author_name }}" class="w-14 h-14 rounded-full object-cover border-2 border-brand-100">
                @else
                <div class="w-14 h-14 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-semibold text-lg">
                    {{ strtoupper(substr($testimonial->author_name, 0, 2)) }}
                </div>
                @endif
                <div>
                    <h3 class="font-semibold text-gray-900">{{ $testimonial->author_name }}</h3>
                    @if($testimonial->author_title || $testimonial->author_company)
                    <p class="text-sm text-gray-500">
                        {{ $testimonial->author_title }}{{ $testimonial->author_title && $testimonial->author_company ? ', ' : '' }}{{ $testimonial->author_company }}
                    </p>
                    @endif
                </div>
            </div>

            {{-- Rating --}}
            @if($testimonial->rating)
            <div class="flex items-center gap-1 mb-3">
                @for($i = 1; $i <= 5; $i++)
                <svg class="w-4 h-4 {{ $i <= $testimonial->rating ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                </svg>
                @endfor
            </div>
            @endif

            {{-- Quote --}}
            <blockquote class="text-gray-600 text-sm italic line-clamp-4 mb-4">"{{ $testimonial->quote }}"</blockquote>

            {{-- Actions --}}
            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <span class="text-xs text-gray-400">Order: {{ $testimonial->sort_order }}</span>
                <div class="flex items-center gap-1">
                    {{-- Edit --}}
                    <button @click="openEditModal({{ json_encode($testimonial) }})" class="p-1.5 rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-600 transition" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>

                    {{-- Toggle Featured --}}
                    <form method="POST" action="{{ route('admin.testimonials.toggle-featured', $testimonial->id) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="p-1.5 rounded-lg hover:bg-amber-50 {{ $testimonial->is_featured ? 'text-amber-500' : 'text-gray-400' }} hover:text-amber-600 transition" title="{{ $testimonial->is_featured ? 'Remove from featured' : 'Mark as featured' }}">
                            <svg class="w-4 h-4" fill="{{ $testimonial->is_featured ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        </button>
                    </form>

                    {{-- Publish/Unpublish --}}
                    @if($testimonial->is_published)
                    <form method="POST" action="{{ route('admin.testimonials.unpublish', $testimonial->id) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="p-1.5 rounded-lg hover:bg-yellow-50 text-gray-400 hover:text-yellow-600 transition" title="Unpublish">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('admin.testimonials.publish', $testimonial->id) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="p-1.5 rounded-lg hover:bg-green-50 text-gray-400 hover:text-green-600 transition" title="Publish">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </form>
                    @endif

                    {{-- Delete --}}
                    <form method="POST" action="{{ route('admin.testimonials.destroy', $testimonial->id) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600 transition" title="Delete" onclick="return confirm('Are you sure you want to delete this testimonial?')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-4 py-12 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                No testimonials found. Create your first testimonial to get started.
            </div>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($testimonials->hasPages())
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-4 py-3">
        {{ $testimonials->links() }}
    </div>
    @endif

    {{-- Create Modal --}}
    <div x-show="showCreateModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-2xl w-full p-6" @click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Add Testimonial</h3>
                    <button @click="showCreateModal = false" class="p-1 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.testimonials.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Author Name <span class="text-red-500">*</span></label>
                            <input type="text" name="author_name" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="John Doe">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title/Position</label>
                            <input type="text" name="author_title" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Marketing Director">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                            <input type="text" name="author_company" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Acme Inc.">
                        </div>
                        <div x-data="{ previewUrl: null }">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Avatar</label>
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-gray-100 border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden">
                                    <template x-if="previewUrl">
                                        <img :src="previewUrl" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!previewUrl">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </template>
                                </div>
                                <label class="flex-1 cursor-pointer">
                                    <input type="file" name="author_avatar" accept="image/*" class="hidden" @change="previewUrl = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                                    <span class="inline-flex items-center gap-2 px-3 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Choose File
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quote <span class="text-red-500">*</span></label>
                        <textarea name="quote" required rows="4" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Write the testimonial quote..."></textarea>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                            <select name="rating" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="5">5 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="2">2 Stars</option>
                                <option value="1">1 Star</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                            <input type="number" name="sort_order" value="0" min="0" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_featured" value="1" class="w-4 h-4 text-brand-600 border-gray-300 rounded focus:ring-brand-500">
                                <span class="text-sm text-gray-700">Featured</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition">
                            Create Testimonial
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
                    <h3 class="text-lg font-semibold text-gray-900">Edit Testimonial</h3>
                    <button @click="showEditModal = false" class="p-1 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="'/admin/testimonials/' + editTestimonial.id" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Author Name <span class="text-red-500">*</span></label>
                            <input type="text" name="author_name" x-model="editTestimonial.author_name" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title/Position</label>
                            <input type="text" name="author_title" x-model="editTestimonial.author_title" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                            <input type="text" name="author_company" x-model="editTestimonial.author_company" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div x-data="{ editPreviewUrl: null }" x-init="$watch('editTestimonial.author_avatar_url', val => editPreviewUrl = val)">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Avatar</label>
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-gray-100 border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden">
                                    <template x-if="editPreviewUrl">
                                        <img :src="editPreviewUrl" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!editPreviewUrl">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </template>
                                </div>
                                <label class="flex-1 cursor-pointer">
                                    <input type="file" name="author_avatar" accept="image/*" class="hidden" @change="editPreviewUrl = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : editTestimonial.author_avatar_url">
                                    <span class="inline-flex items-center gap-2 px-3 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Change File
                                    </span>
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 mt-1" x-show="editTestimonial.author_avatar_url">Current avatar will be kept if no new file selected</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quote <span class="text-red-500">*</span></label>
                        <textarea name="quote" x-model="editTestimonial.quote" required rows="4" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                            <select name="rating" x-model="editTestimonial.rating" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="5">5 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="2">2 Stars</option>
                                <option value="1">1 Star</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                            <input type="number" name="sort_order" x-model="editTestimonial.sort_order" min="0" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_featured" value="1" x-bind:checked="editTestimonial.is_featured" class="w-4 h-4 text-brand-600 border-gray-300 rounded focus:ring-brand-500">
                                <span class="text-sm text-gray-700">Featured</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition">
                            Update Testimonial
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function testimonialPage() {
    return {
        showCreateModal: false,
        showEditModal: false,
        editTestimonial: {
            id: null,
            author_name: '',
            author_title: '',
            author_company: '',
            author_avatar_url: '',
            quote: '',
            rating: 5,
            is_featured: false,
            sort_order: 0
        },
        openEditModal(testimonial) {
            this.editTestimonial = {
                id: testimonial.id,
                author_name: testimonial.author_name || '',
                author_title: testimonial.author_title || '',
                author_company: testimonial.author_company || '',
                author_avatar_url: testimonial.author_avatar_url || '',
                quote: testimonial.quote || '',
                rating: testimonial.rating || 5,
                is_featured: testimonial.is_featured || false,
                sort_order: testimonial.sort_order || 0
            };
            this.showEditModal = true;
        }
    }
}
</script>
@endsection
