@extends('layouts.advertiser')

@section('title', 'Pixel Tracker')

@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pixel Tracker</h1>
            <p class="text-sm text-gray-500 mt-1">Create and manage tracking pixels for your campaigns.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        @foreach([
            ['Total Pixels', number_format($totalPixels), 'text-gray-900'],
            ['HTML Pixels', number_format($htmlCount), 'text-blue-700'],
            ['S2S Pixels', number_format($s2sCount), 'text-violet-700'],
            ['Active', number_format($activeCount), 'text-emerald-700'],
        ] as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card[0] }}</div>
                <div class="mt-2 text-2xl font-bold {{ $card[2] }}">{{ $card[1] }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr,360px]">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <form method="GET" action="{{ route('advertiser.network.pixel-trackers') }}" class="flex flex-wrap items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search pixel..." class="px-3 py-2 rounded-lg border border-gray-200 text-sm">
                    <select name="type" class="px-3 py-2 rounded-lg border border-gray-200 text-sm">
                        <option value="">All Types</option>
                        <option value="html_pixel" @selected(request('type') === 'html_pixel')>HTML Pixel</option>
                        <option value="s2s_pixel" @selected(request('type') === 's2s_pixel')>S2S Pixel</option>
                        <option value="mobile_s2s" @selected(request('type') === 'mobile_s2s')>Mobile S2S</option>
                    </select>
                    <button class="px-4 py-2 rounded-lg bg-gray-900 text-sm font-medium text-white">Filter</button>
                    <a href="{{ route('advertiser.network.pixel-trackers') }}" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600">Reset</a>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/70 border-b border-gray-100">
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Name</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Type</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Category</th>
                            <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($pixels as $pixel)
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs text-gray-500">#{{ $pixel->id }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-gray-900">{{ $pixel->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $pixel->pixel_code }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ Str::headline($pixel->type) }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $pixel->category ?: '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <form method="POST" action="{{ route('advertiser.network.pixel-trackers.destroy', $pixel) }}" onsubmit="return confirm('Delete this pixel tracker?')" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-12 text-center text-sm text-gray-500">No pixel trackers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($pixels->hasPages())
                <div class="border-t border-gray-100 px-4 py-3">{{ $pixels->links() }}</div>
            @endif
        </div>

        <form method="POST" action="{{ route('advertiser.network.pixel-trackers.store') }}" class="bg-white rounded-xl border border-gray-200 p-4 h-max">
            @csrf
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Create Pixel Tracker</h2>
            <div class="space-y-3">
                <input name="name" placeholder="Pixel Name" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" required>
                <select name="type" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" required>
                    <option value="html_pixel">HTML Pixel</option>
                    <option value="s2s_pixel">S2S Pixel</option>
                    <option value="mobile_s2s">Mobile S2S</option>
                </select>
                <input name="pixel_goal" placeholder="Pixel Goal" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm">
                <input name="category" placeholder="Category" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm">
                <select name="status" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" required>
                    <option value="active">Active</option>
                    <option value="paused">Paused</option>
                    <option value="archived">Archived</option>
                </select>
                <textarea name="append_code" rows="3" placeholder="Append Code" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm"></textarea>
                <button class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Create Pixel</button>
            </div>
        </form>
    </div>
@endsection
