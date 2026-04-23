<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\TelegramWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/me', [AuthController::class, 'me']);

Route::post('/payments/stripe/webhook', [PaymentWebhookController::class, 'stripe'])->name('api.payments.stripe.webhook');
Route::post('/payments/bitpay/webhook', [PaymentWebhookController::class, 'bitpay'])->name('api.payments.bitpay.webhook');

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
