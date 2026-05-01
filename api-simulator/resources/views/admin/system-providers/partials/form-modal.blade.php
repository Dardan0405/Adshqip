@php
    $isEdit = $mode === 'edit';
@endphp

<div x-show="{{ $showProperty }}" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex min-h-screen items-center justify-center px-4 py-8">
        <div class="fixed inset-0 bg-black/50" @click="{{ $showProperty }} = false"></div>
        <div class="relative w-full max-w-4xl rounded-xl bg-white p-6 shadow-xl" @click.stop>
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $isEdit ? 'Edit System Provider' : 'Create System Provider' }}</h3>
                    <p class="mt-1 text-sm text-gray-500">Secrets are stored encrypted. Leave the secret blank when editing to keep the existing one.</p>
                </div>
                <button type="button" @click="{{ $showProperty }} = false" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" @if($isEdit) :action="'/admin/system-providers/' + editItem.id" @else action="{{ $action }}" @endif class="space-y-4">
                @csrf
                @if($method !== 'POST')
                    @method($method)
                @endif

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" @if($isEdit) x-model="editItem.name" @else value="{{ old('name') }}" @endif required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20" placeholder="Stripe, Google Analytics, SSP X">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Slug</label>
                        <input type="text" name="slug" @if($isEdit) x-model="editItem.slug" @else value="{{ old('slug') }}" @endif class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20" placeholder="Auto generated if empty">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Type <span class="text-red-500">*</span></label>
                        <select name="provider_type" @if($isEdit) x-model="editItem.provider_type" @endif required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                            @foreach($providerTypes as $value => $label)
                                <option value="{{ $value }}" @selected(old('provider_type', 'other') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Environment <span class="text-red-500">*</span></label>
                        <select name="environment" @if($isEdit) x-model="editItem.environment" @endif required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                            @foreach($environments as $value => $label)
                                <option value="{{ $value }}" @selected(old('environment', 'production') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Status <span class="text-red-500">*</span></label>
                        <select name="status" @if($isEdit) x-model="editItem.status" @endif required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                            <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                            <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Priority <span class="text-red-500">*</span></label>
                        <input type="number" name="priority" min="1" max="999" @if($isEdit) x-model="editItem.priority" @else value="{{ old('priority', 100) }}" @endif required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Timeout Seconds <span class="text-red-500">*</span></label>
                        <input type="number" name="timeout_seconds" min="1" max="60" @if($isEdit) x-model="editItem.timeout_seconds" @else value="{{ old('timeout_seconds', 10) }}" @endif required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Base URL</label>
                        <input type="url" name="base_url" @if($isEdit) x-model="editItem.base_url" @else value="{{ old('base_url') }}" @endif class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20" placeholder="https://api.provider.com">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Webhook URL</label>
                        <input type="url" name="webhook_url" @if($isEdit) x-model="editItem.webhook_url" @else value="{{ old('webhook_url') }}" @endif class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20" placeholder="https://provider.com/webhook">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Auth Type <span class="text-red-500">*</span></label>
                        <select name="auth_type" @if($isEdit) x-model="editItem.auth_type" @endif required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                            @foreach($authTypes as $value => $label)
                                <option value="{{ $value }}" @selected(old('auth_type', 'none') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">API Key</label>
                        <input type="text" name="api_key" @if($isEdit) x-model="editItem.api_key" @else value="{{ old('api_key') }}" @endif class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">API Secret</label>
                        <input type="password" name="api_secret" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20" placeholder="{{ $isEdit ? 'Leave blank to keep current secret' : '' }}">
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">JSON Config</label>
                    <textarea name="config_json" rows="5" @if($isEdit) x-model="editItem.config_json" @endif class="w-full rounded-lg border border-gray-200 px-3 py-2 font-mono text-xs focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20" placeholder='{"region":"eu","currency":"EUR"}'>{{ $isEdit ? '' : old('config_json') }}</textarea>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" rows="3" @if($isEdit) x-model="editItem.notes" @endif class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">{{ $isEdit ? '' : old('notes') }}</textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="{{ $showProperty }} = false" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200">Cancel</button>
                    <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-700">{{ $isEdit ? 'Save Provider' : 'Create Provider' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
