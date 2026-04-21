<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Models\TelegramMiniApp;
use App\Models\TelegramMiniAppSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TelegramIntegrationController extends Controller
{
    public function index()
    {
        $miniApps = TelegramMiniApp::query()
            ->with('owner')
            ->where('is_deleted', false)
            ->latest('created_at')
            ->paginate(15);

        $summary = [
            'mini_apps' => TelegramMiniApp::where('is_deleted', false)->count(),
            'active' => TelegramMiniApp::where('is_deleted', false)->where('status', 'active')->count(),
            'sessions' => TelegramMiniAppSession::count(),
            'linked_users' => \App\Models\User::whereNotNull('telegram_user_id')->count(),
        ];

        return view('admin.telegram-integration.index', [
            'miniApps' => $miniApps,
            'summary' => $summary,
            'owners' => User::query()
                ->whereIn('role', ['advertiser', 'publisher'])
                ->where('is_deleted', false)
                ->orderBy('email')
                ->get(['id', 'email', 'role']),
            'settings' => [
                'telegram_bot_username' => PlatformSetting::getValue('telegram_bot_username', ''),
                'telegram_webhook_url' => PlatformSetting::getValue('telegram_webhook_url', ''),
                'telegram_notifications_enabled' => (bool) PlatformSetting::getValue('telegram_notifications_enabled', false),
                'telegram_login_enabled' => (bool) PlatformSetting::getValue('telegram_login_enabled', false),
                'telegram_bot_token_saved' => filled(PlatformSetting::getValue('telegram_bot_token_encrypted')),
                'telegram_webhook_secret_saved' => filled(PlatformSetting::getValue('telegram_webhook_secret_encrypted')),
            ],
        ]);
    }

    public function storeMiniApp(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:aq_users,id'],
            'app_name' => ['required', 'string', 'max:255'],
            'app_short_name' => ['required', 'string', 'max:64', Rule::unique('aq_telegram_mini_apps', 'app_short_name')],
            'bot_username' => ['required', 'string', 'max:100', Rule::unique('aq_telegram_mini_apps', 'bot_username')],
            'bot_token' => ['required', 'string', 'max:255'],
            'app_url' => ['required', 'url', 'max:500'],
            'icon_url' => ['nullable', 'url', 'max:500'],
            'description' => ['nullable', 'string'],
            'category' => ['required', Rule::in(['monetization', 'analytics', 'campaign_manager', 'ad_preview', 'custom'])],
            'webhook_url' => ['nullable', 'url', 'max:500'],
            'webhook_secret' => ['nullable', 'string', 'max:255'],
            'allowed_origins' => ['nullable', 'string', 'max:2000'],
            'inline_mode_enabled' => ['nullable', 'boolean'],
            'payment_enabled' => ['nullable', 'boolean'],
            'payment_provider_token' => ['nullable', 'string', 'max:255'],
            'menu_button_enabled' => ['nullable', 'boolean'],
            'expand_on_open' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['draft', 'pending_review', 'active', 'suspended', 'archived'])],
        ]);

        $allowedOrigins = collect(preg_split('/[\r\n,]+/', (string) ($validated['allowed_origins'] ?? ''), -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($origin) => trim($origin))
            ->filter()
            ->values()
            ->all();

        TelegramMiniApp::create([
            'user_id' => $validated['user_id'],
            'app_name' => trim($validated['app_name']),
            'app_short_name' => trim($validated['app_short_name']),
            'bot_username' => ltrim(trim($validated['bot_username']), '@'),
            'bot_token_hash' => encrypt(trim($validated['bot_token'])),
            'app_url' => trim($validated['app_url']),
            'icon_url' => filled($validated['icon_url'] ?? null) ? trim((string) $validated['icon_url']) : null,
            'description' => filled($validated['description'] ?? null) ? trim((string) $validated['description']) : null,
            'category' => $validated['category'],
            'webhook_url' => filled($validated['webhook_url'] ?? null) ? trim((string) $validated['webhook_url']) : null,
            'webhook_secret_hash' => filled($validated['webhook_secret'] ?? null) ? encrypt(trim((string) $validated['webhook_secret'])) : null,
            'allowed_origins' => $allowedOrigins === [] ? null : $allowedOrigins,
            'inline_mode_enabled' => $request->boolean('inline_mode_enabled'),
            'payment_enabled' => $request->boolean('payment_enabled'),
            'payment_provider_token_hash' => filled($validated['payment_provider_token'] ?? null) ? encrypt(trim((string) $validated['payment_provider_token'])) : null,
            'menu_button_enabled' => $request->boolean('menu_button_enabled'),
            'expand_on_open' => $request->boolean('expand_on_open'),
            'status' => $validated['status'],
            'admin_approved' => $validated['status'] === 'active',
        ]);

        return redirect()->route('admin.telegram-integration')->with('success', 'Telegram mini app created successfully.');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'telegram_bot_username' => ['nullable', 'string', 'max:100'],
            'telegram_bot_token' => ['nullable', 'string', 'max:255'],
            'telegram_webhook_url' => ['nullable', 'url', 'max:500'],
            'telegram_webhook_secret' => ['nullable', 'string', 'max:255'],
            'telegram_notifications_enabled' => ['nullable', 'boolean'],
            'telegram_login_enabled' => ['nullable', 'boolean'],
        ]);

        PlatformSetting::setValue('telegram_bot_username', trim((string) ($validated['telegram_bot_username'] ?? '')), 'string', 'telegram', 'Telegram bot username', $request->user()?->id);
        PlatformSetting::setValue('telegram_webhook_url', trim((string) ($validated['telegram_webhook_url'] ?? '')), 'string', 'telegram', 'Telegram webhook URL', $request->user()?->id);
        PlatformSetting::setValue('telegram_notifications_enabled', $request->boolean('telegram_notifications_enabled'), 'boolean', 'telegram', 'Enable Telegram notifications', $request->user()?->id);
        PlatformSetting::setValue('telegram_login_enabled', $request->boolean('telegram_login_enabled'), 'boolean', 'telegram', 'Enable Telegram login', $request->user()?->id);

        if (filled($validated['telegram_bot_token'] ?? null)) {
            PlatformSetting::setValue('telegram_bot_token_encrypted', encrypt(trim((string) $validated['telegram_bot_token'])), 'string', 'telegram', 'Encrypted Telegram bot token', $request->user()?->id);
        }

        if (filled($validated['telegram_webhook_secret'] ?? null)) {
            PlatformSetting::setValue('telegram_webhook_secret_encrypted', encrypt(trim((string) $validated['telegram_webhook_secret'])), 'string', 'telegram', 'Encrypted Telegram webhook secret', $request->user()?->id);
        }

        return redirect()->route('admin.telegram-integration')->with('success', 'Telegram integration updated successfully.');
    }

    public function activate(TelegramMiniApp $telegramMiniApp)
    {
        $telegramMiniApp->update([
            'status' => 'active',
            'admin_approved' => true,
            'rejection_reason' => null,
        ]);

        return redirect()->route('admin.telegram-integration')->with('success', 'Telegram mini app activated successfully.');
    }

    public function suspend(Request $request, TelegramMiniApp $telegramMiniApp)
    {
        $telegramMiniApp->update([
            'status' => 'suspended',
            'rejection_reason' => trim((string) $request->input('rejection_reason', 'Suspended by admin')),
        ]);

        return redirect()->route('admin.telegram-integration')->with('success', 'Telegram mini app suspended successfully.');
    }
}
