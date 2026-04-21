@extends('layouts.admin')
@section('title', 'Telegram Integration')
@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="flex flex-col gap-2 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Telegram Integration</h1>
        <p class="text-sm text-gray-500">Manage Telegram bot settings and review mini apps connected to the platform.</p>
    </div>

    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 mb-6">
        @foreach([
            ['label' => 'Mini Apps', 'value' => number_format($summary['mini_apps'])],
            ['label' => 'Active Apps', 'value' => number_format($summary['active'])],
            ['label' => 'Tracked Sessions', 'value' => number_format($summary['sessions'])],
            ['label' => 'Linked Users', 'value' => number_format($summary['linked_users'])],
        ] as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-[420px,1fr]">
        <div class="rounded-xl border border-gray-200 bg-white p-5 h-fit">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Bot Settings</h2>
            <form method="POST" action="{{ route('admin.telegram-integration.update') }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Bot Username</label>
                    <input type="text" name="telegram_bot_username" value="{{ $settings['telegram_bot_username'] }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="@adshqip_bot">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Bot Token</label>
                    <input type="text" name="telegram_bot_token" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="{{ $settings['telegram_bot_token_saved'] ? 'Saved - fill only to replace' : 'Paste bot token' }}">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Webhook URL</label>
                    <input type="url" name="telegram_webhook_url" value="{{ $settings['telegram_webhook_url'] }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="https://example.com/telegram/webhook">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Webhook Secret</label>
                    <input type="text" name="telegram_webhook_secret" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="{{ $settings['telegram_webhook_secret_saved'] ? 'Saved - fill only to replace' : 'Optional webhook secret' }}">
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="telegram_notifications_enabled" value="1" @checked($settings['telegram_notifications_enabled']) class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    Enable Telegram notifications
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="telegram_login_enabled" value="1" @checked($settings['telegram_login_enabled']) class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    Enable Telegram login
                </label>
                <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Save Telegram Settings</button>
            </form>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white overflow-x-auto" x-data="{ showCreateModal: false }">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Mini Apps</h2>
                <button @click="showCreateModal = true" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Mini App
                </button>
            </div>

            {{-- Create Mini App Modal --}}
            <div x-show="showCreateModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto" style="display: none;">
                <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto" @click.stop>
                    <div class="sticky top-0 bg-white border-b border-gray-100 px-6 py-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Add Telegram Mini App</h3>
                        <button @click="showCreateModal = false" class="p-1 rounded-lg hover:bg-gray-100 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('admin.telegram-integration.store-mini-app') }}" class="p-6 space-y-4">
                        @csrf
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Owner (User) <span class="text-red-500">*</span></label>
                                <select name="user_id" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                                    <option value="">Select user...</option>
                                    @foreach($owners as $owner)
                                        <option value="{{ $owner->id }}">{{ $owner->email }} ({{ $owner->role }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Category <span class="text-red-500">*</span></label>
                                <select name="category" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                                    <option value="monetization">Monetization</option>
                                    <option value="analytics">Analytics</option>
                                    <option value="campaign_manager">Campaign Manager</option>
                                    <option value="ad_preview">Ad Preview</option>
                                    <option value="custom">Custom</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">App Name <span class="text-red-500">*</span></label>
                                <input type="text" name="app_name" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="My Mini App">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Short Name <span class="text-red-500">*</span></label>
                                <input type="text" name="app_short_name" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="myapp">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Bot Username <span class="text-red-500">*</span></label>
                                <input type="text" name="bot_username" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="@mybot">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Bot Token <span class="text-red-500">*</span></label>
                                <input type="text" name="bot_token" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="123456:ABC-DEF...">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">App URL <span class="text-red-500">*</span></label>
                            <input type="url" name="app_url" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="https://myapp.example.com">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Icon URL</label>
                                <input type="url" name="icon_url" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="https://example.com/icon.png">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Status <span class="text-red-500">*</span></label>
                                <select name="status" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                                    <option value="draft">Draft</option>
                                    <option value="pending_review">Pending Review</option>
                                    <option value="active">Active</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Description</label>
                            <textarea name="description" rows="2" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Optional description..."></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Webhook URL</label>
                                <input type="url" name="webhook_url" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="https://example.com/webhook">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Webhook Secret</label>
                                <input type="text" name="webhook_secret" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Optional secret">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Allowed Origins (one per line)</label>
                            <textarea name="allowed_origins" rows="2" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="https://example.com&#10;https://app.example.com"></textarea>
                        </div>
                        <div class="flex flex-wrap gap-4">
                            <label class="flex items-center gap-2 text-sm text-gray-600">
                                <input type="checkbox" name="inline_mode_enabled" value="1" class="rounded border-gray-300 text-brand-600">
                                Inline Mode
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-600">
                                <input type="checkbox" name="payment_enabled" value="1" class="rounded border-gray-300 text-brand-600">
                                Payments
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-600">
                                <input type="checkbox" name="menu_button_enabled" value="1" class="rounded border-gray-300 text-brand-600">
                                Menu Button
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-600">
                                <input type="checkbox" name="expand_on_open" value="1" class="rounded border-gray-300 text-brand-600">
                                Expand on Open
                            </label>
                        </div>
                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                            <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200">Cancel</button>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700">Create Mini App</button>
                        </div>
                    </form>
                </div>
            </div>

            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Mini App</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Owner</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Bot</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Sessions</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Events</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($miniApps as $miniApp)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 font-semibold text-gray-900">#{{ $miniApp->id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $miniApp->app_name }}</div>
                                <div class="text-xs text-gray-400">{{ $miniApp->app_short_name }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $miniApp->owner?->email }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ '@' . ltrim($miniApp->bot_username, '@') }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-700">{{ number_format($miniApp->total_sessions) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-700">{{ number_format($miniApp->total_events) }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $miniApp->status === 'active' ? 'bg-emerald-100 text-emerald-700' : ($miniApp->status === 'suspended' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700') }}">{{ ucfirst(str_replace('_', ' ', $miniApp->status)) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($miniApp->status === 'active')
                                    <form method="POST" action="{{ route('admin.telegram-integration.suspend', $miniApp) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">Suspend</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.telegram-integration.activate', $miniApp) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">Activate</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-12 text-center text-sm text-gray-500">No Telegram mini apps found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($miniApps->hasPages())
                <div class="border-t border-gray-100 px-4 py-3">{{ $miniApps->links() }}</div>
            @endif
        </div>
    </div>
@endsection
