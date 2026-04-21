<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Models\TelegramMiniApp;
use App\Models\TelegramMiniAppSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    /**
     * Handle Telegram Bot Webhook (receives updates from Telegram)
     * This is called by Telegram when users interact with your bot
     */
    public function handleBotWebhook(Request $request)
    {
        // Verify webhook secret if configured
        $savedSecret = PlatformSetting::getValue('telegram_webhook_secret_encrypted');
        if ($savedSecret) {
            $providedSecret = $request->header('X-Telegram-Bot-Api-Secret-Token');
            if (!$providedSecret || !hash_equals(decrypt($savedSecret), $providedSecret)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }

        $update = $request->all();
        Log::info('Telegram webhook received', $update);

        // Handle different update types
        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        }

        if (isset($update['callback_query'])) {
            $this->handleCallbackQuery($update['callback_query']);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Handle Mini App Session Start
     * Called by your mini app when a user opens it
     */
    public function startSession(Request $request)
    {
        $validated = $request->validate([
            'mini_app_id' => ['required', 'exists:aq_telegram_mini_apps,id'],
            'init_data' => ['required', 'string'], // Telegram WebApp.initData
            'platform' => ['nullable', 'string', 'max:50'],
            'start_param' => ['nullable', 'string', 'max:255'],
        ]);

        $miniApp = TelegramMiniApp::findOrFail($validated['mini_app_id']);

        // Parse and validate initData from Telegram
        $initData = $this->parseInitData($validated['init_data']);
        if (!$initData || !$this->validateInitData($validated['init_data'], $miniApp)) {
            return response()->json(['error' => 'Invalid init data'], 400);
        }

        $telegramUser = $initData['user'] ?? null;
        if (!$telegramUser) {
            return response()->json(['error' => 'No user data'], 400);
        }

        // Create session record
        $session = TelegramMiniAppSession::create([
            'mini_app_id' => $miniApp->id,
            'telegram_user_id' => $telegramUser['id'] ?? null,
            'telegram_username' => $telegramUser['username'] ?? null,
            'telegram_first_name' => $telegramUser['first_name'] ?? null,
            'telegram_last_name' => $telegramUser['last_name'] ?? null,
            'telegram_language_code' => $telegramUser['language_code'] ?? null,
            'telegram_is_premium' => $telegramUser['is_premium'] ?? false,
            'auth_date' => isset($initData['auth_date']) ? now()->setTimestamp($initData['auth_date']) : now(),
            'init_data_hash' => $initData['hash'] ?? null,
            'query_id' => $initData['query_id'] ?? null,
            'platform' => $validated['platform'] ?? $request->header('User-Agent'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'start_param' => $validated['start_param'] ?? $initData['start_param'] ?? null,
        ]);

        // Update mini app stats
        $miniApp->increment('total_sessions');
        $miniApp->update(['last_active_at' => now()]);

        return response()->json([
            'success' => true,
            'session_id' => $session->id,
        ]);
    }

    /**
     * End a mini app session
     */
    public function endSession(Request $request)
    {
        $validated = $request->validate([
            'session_id' => ['required', 'exists:aq_telegram_mini_app_sessions,id'],
        ]);

        $session = TelegramMiniAppSession::findOrFail($validated['session_id']);

        if (!$session->ended_at) {
            $duration = now()->diffInSeconds($session->created_at);
            $session->update([
                'ended_at' => now(),
                'duration_seconds' => $duration,
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Track an event in a mini app session
     */
    public function trackEvent(Request $request)
    {
        $validated = $request->validate([
            'mini_app_id' => ['required', 'exists:aq_telegram_mini_apps,id'],
            'event_name' => ['required', 'string', 'max:100'],
            'event_data' => ['nullable', 'array'],
        ]);

        $miniApp = TelegramMiniApp::findOrFail($validated['mini_app_id']);
        $miniApp->increment('total_events');

        Log::info('Mini app event tracked', [
            'mini_app_id' => $miniApp->id,
            'event' => $validated['event_name'],
            'data' => $validated['event_data'] ?? [],
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Link Telegram account to existing user (Telegram Login Widget)
     * Called when user authenticates via Telegram Login Widget on your site
     */
    public function linkAccount(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'first_name' => ['required', 'string'],
            'last_name' => ['nullable', 'string'],
            'username' => ['nullable', 'string'],
            'photo_url' => ['nullable', 'url'],
            'auth_date' => ['required', 'integer'],
            'hash' => ['required', 'string'],
        ]);

        // Verify the data is from Telegram
        if (!$this->verifyTelegramLogin($validated)) {
            return response()->json(['error' => 'Invalid authentication'], 400);
        }

        // Check auth_date is not too old (1 day max)
        if (time() - $validated['auth_date'] > 86400) {
            return response()->json(['error' => 'Authentication expired'], 400);
        }

        // Get current authenticated user
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        // Link Telegram to user
        $user->update([
            'telegram_user_id' => $validated['id'],
            'telegram_username' => $validated['username'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Telegram account linked successfully',
        ]);
    }

    /**
     * Parse Telegram initData string into array
     */
    private function parseInitData(string $initData): array
    {
        $params = [];
        parse_str($initData, $params);

        if (isset($params['user'])) {
            $params['user'] = json_decode($params['user'], true);
        }

        return $params;
    }

    /**
     * Validate Telegram WebApp initData
     */
    private function validateInitData(string $initData, TelegramMiniApp $miniApp): bool
    {
        try {
            $botToken = decrypt($miniApp->bot_token_hash);
        } catch (\Exception $e) {
            return false;
        }

        $params = [];
        parse_str($initData, $params);

        if (!isset($params['hash'])) {
            return false;
        }

        $hash = $params['hash'];
        unset($params['hash']);

        ksort($params);
        $dataCheckString = collect($params)
            ->map(fn($value, $key) => "$key=$value")
            ->implode("\n");

        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
        $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

        return hash_equals($calculatedHash, $hash);
    }

    /**
     * Verify Telegram Login Widget data
     */
    private function verifyTelegramLogin(array $data): bool
    {
        $botTokenEncrypted = PlatformSetting::getValue('telegram_bot_token_encrypted');
        if (!$botTokenEncrypted) {
            return false;
        }

        try {
            $botToken = decrypt($botTokenEncrypted);
        } catch (\Exception $e) {
            return false;
        }

        $hash = $data['hash'];
        unset($data['hash']);

        ksort($data);
        $dataCheckString = collect($data)
            ->map(fn($value, $key) => "$key=$value")
            ->implode("\n");

        $secretKey = hash('sha256', $botToken, true);
        $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

        return hash_equals($calculatedHash, $hash);
    }

    private function handleMessage(array $message): void
    {
        // Handle incoming messages from Telegram bot
        Log::info('Telegram message received', $message);
    }

    private function handleCallbackQuery(array $callbackQuery): void
    {
        // Handle callback queries (inline button clicks)
        Log::info('Telegram callback query received', $callbackQuery);
    }
}
