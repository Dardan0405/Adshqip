<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\TelegramWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-security-question', [AuthController::class, 'verifySecurityQuestion']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/me', [AuthController::class, 'me']);

Route::middleware('api.key')->get('/integration/ping', function (\Illuminate\Http\Request $request) {
    $apiKey = $request->attributes->get('api_key');

    return response()->json([
        'ok' => true,
        'message' => 'API key authentication works.',
        'key' => [
            'id' => $apiKey?->id,
            'name' => $apiKey?->name,
            'api_key' => $apiKey?->api_key,
            'permissions' => $apiKey?->permissions ?? [],
            'last_used_at' => optional($apiKey?->last_used_at)->toISOString(),
        ],
    ]);
})->name('api.integration.ping');

Route::middleware('api.key:read_reports')->get('/integration/reports-test', function (\Illuminate\Http\Request $request) {
    return response()->json([
        'ok' => true,
        'message' => 'read_reports permission accepted.',
        'key_name' => $request->attributes->get('api_key')?->name,
    ]);
})->name('api.integration.reports-test');

Route::post('/payments/stripe/webhook', [PaymentWebhookController::class, 'stripe'])->name('api.payments.stripe.webhook');
Route::post('/payments/bitpay/webhook', [PaymentWebhookController::class, 'bitpay'])->name('api.payments.bitpay.webhook');
Route::post('/payments/paypal/webhook', [PaymentWebhookController::class, 'paypal'])->name('api.payments.paypal.webhook');

// Telegram Integration API
Route::prefix('telegram')->group(function () {
    // Webhook from Telegram Bot API (no CSRF, no auth)
    Route::post('/webhook', [TelegramWebhookController::class, 'handleBotWebhook'])->name('api.telegram.webhook');

    // Mini App Session Tracking (CORS enabled for mini apps)
    Route::post('/session/start', [TelegramWebhookController::class, 'startSession'])->name('api.telegram.session.start');
    Route::post('/session/end', [TelegramWebhookController::class, 'endSession'])->name('api.telegram.session.end');
    Route::post('/event', [TelegramWebhookController::class, 'trackEvent'])->name('api.telegram.event');

    // Link Telegram to User Account (requires auth)
    Route::middleware('auth:sanctum')->post('/link-account', [TelegramWebhookController::class, 'linkAccount'])->name('api.telegram.link');
});
