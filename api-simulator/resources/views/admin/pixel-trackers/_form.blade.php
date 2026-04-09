{{-- Advertiser --}}
<div>
    <label class="block text-xs font-medium text-gray-600 mb-1">Advertiser <span class="text-red-500">*</span></label>
    <select name="advertiser_id" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
        <option value="">Select Advertiser</option>
        @foreach($advertisers as $adv)
            @php
                $name = trim(($adv->profile->first_name ?? '') . ' ' . ($adv->profile->last_name ?? ''));
                if (!$name) $name = $adv->email;
            @endphp
            <option value="{{ $adv->id }}" {{ old('advertiser_id') == $adv->id ? 'selected' : '' }}>{{ $name }}</option>
        @endforeach
    </select>
    @error('advertiser_id')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

{{-- Pixel Name --}}
<div>
    <label class="block text-xs font-medium text-gray-600 mb-1">Pixel Name <span class="text-red-500">*</span></label>
    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Purchase Conversion Pixel"
           class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
    @error('name')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

{{-- Type --}}
<div>
    <label class="block text-xs font-medium text-gray-600 mb-2">Type <span class="text-red-500">*</span></label>
    <div class="grid grid-cols-3 gap-2">
        <label class="flex items-center gap-2 px-3 py-2.5 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 transition-colors">
            <input type="radio" name="type" value="html_pixel" {{ old('type', 'html_pixel') === 'html_pixel' ? 'checked' : '' }}
                   class="w-4 h-4 text-brand-600 border-gray-300 focus:ring-brand-500">
            <span class="text-sm font-medium text-gray-700">HTML Pixel</span>
        </label>
        <label class="flex items-center gap-2 px-3 py-2.5 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 transition-colors">
            <input type="radio" name="type" value="s2s_pixel" {{ old('type') === 's2s_pixel' ? 'checked' : '' }}
                   class="w-4 h-4 text-brand-600 border-gray-300 focus:ring-brand-500">
            <span class="text-sm font-medium text-gray-700">S2S Pixel</span>
        </label>
        <label class="flex items-center gap-2 px-3 py-2.5 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 transition-colors">
            <input type="radio" name="type" value="mobile_s2s" {{ old('type') === 'mobile_s2s' ? 'checked' : '' }}
                   class="w-4 h-4 text-brand-600 border-gray-300 focus:ring-brand-500">
            <span class="text-sm font-medium text-gray-700">Mobile S2S</span>
        </label>
    </div>
    @error('type')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

<div class="grid grid-cols-2 gap-4">
    {{-- Pixel Goal --}}
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Pixel Goal</label>
        <select name="pixel_goal" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            <option value="">Select Goal</option>
            <option value="conversion" {{ old('pixel_goal') === 'conversion' ? 'selected' : '' }}>Conversion</option>
            <option value="lead" {{ old('pixel_goal') === 'lead' ? 'selected' : '' }}>Lead</option>
            <option value="purchase" {{ old('pixel_goal') === 'purchase' ? 'selected' : '' }}>Purchase</option>
            <option value="signup" {{ old('pixel_goal') === 'signup' ? 'selected' : '' }}>Sign Up</option>
            <option value="pageview" {{ old('pixel_goal') === 'pageview' ? 'selected' : '' }}>Page View</option>
            <option value="add_to_cart" {{ old('pixel_goal') === 'add_to_cart' ? 'selected' : '' }}>Add to Cart</option>
            <option value="install" {{ old('pixel_goal') === 'install' ? 'selected' : '' }}>App Install</option>
            <option value="custom" {{ old('pixel_goal') === 'custom' ? 'selected' : '' }}>Custom</option>
        </select>
    </div>

    {{-- Status --}}
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Status <span class="text-red-500">*</span></label>
        <select name="status" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="paused" {{ old('status') === 'paused' ? 'selected' : '' }}>Paused</option>
            <option value="archived" {{ old('status') === 'archived' ? 'selected' : '' }}>Archived</option>
        </select>
    </div>
</div>

{{-- Category --}}
<div>
    <label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
    <input type="text" name="category" value="{{ old('category') }}" placeholder="e.g. E-commerce, Lead Gen, App Install..."
           class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
</div>

{{-- Append Code --}}
<div>
    <label class="block text-xs font-medium text-gray-600 mb-1">Append Code</label>
    <textarea name="append_code" rows="4" placeholder="Paste HTML/JS code to append to the pixel..."
              class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">{{ old('append_code') }}</textarea>
    <p class="mt-1 text-xs text-gray-400">Optional code to be appended when the pixel fires.</p>
</div>
