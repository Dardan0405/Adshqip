<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_telegram_mini_apps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index('idx_user')->comment('Owner (publisher or advertiser)');
            $table->string('app_name', 255)->comment('Display name inside Telegram');
            $table->string('app_short_name', 64)->unique('uk_app_short_name')->comment('Telegram bot menu button short_name');
            $table->string('bot_username', 100)->unique('uk_bot_username')->comment('@BotFather bot username');
            $table->string('bot_token_hash', 255)->comment('Encrypted bot token');
            $table->string('app_url', 500)->comment('HTTPS URL served as the mini app');
            $table->string('icon_url', 500)->nullable()->comment('Mini app icon / thumbnail');
            $table->text('description')->nullable();
            $table->enum('category', ['monetization','analytics','campaign_manager','ad_preview','custom'])->default('custom')->index('idx_category');
            $table->string('webhook_url', 500)->nullable()->comment('Incoming update webhook');
            $table->string('webhook_secret_hash', 255)->nullable();
            $table->json('allowed_origins')->nullable()->comment('Allowed web_app_data origins');
            $table->json('theme_params')->nullable()->comment('Telegram theme color overrides');
            $table->boolean('inline_mode_enabled')->default(false)->comment('Allow inline query integration');
            $table->boolean('payment_enabled')->default(false)->comment('Telegram Payments API enabled');
            $table->string('payment_provider_token_hash', 255)->nullable()->comment('Encrypted Stripe/etc. provider token');
            $table->boolean('menu_button_enabled')->default(true)->comment('Show as bot menu button');
            $table->boolean('expand_on_open')->default(false)->comment('Open in expanded mode');
            $table->enum('status', ['draft','pending_review','active','suspended','archived'])->default('draft')->index('idx_status');
            $table->boolean('admin_approved')->default(false);
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('total_sessions')->default(0)->comment('Denormalized session counter');
            $table->unsignedBigInteger('total_events')->default(0)->comment('Denormalized event counter');
            $table->dateTime('last_active_at')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('user_id', 'fk_tma_user')->references('id')->on('aq_users')->onDelete('cascade');
        });

        Schema::create('aq_telegram_mini_app_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mini_app_id')->index('idx_mini_app');
            $table->bigInteger('telegram_user_id')->index('idx_tg_user')->comment('Telegram numeric user ID');
            $table->string('telegram_username', 100)->nullable();
            $table->string('telegram_first_name', 100)->nullable();
            $table->string('telegram_last_name', 100)->nullable();
            $table->string('telegram_language_code', 10)->nullable();
            $table->boolean('telegram_is_premium')->default(false);
            $table->dateTime('auth_date')->comment('initData auth_date');
            $table->string('init_data_hash', 255)->comment('Validated initData hash');
            $table->string('query_id', 64)->nullable()->comment('web_app_query_id for answerWebAppQuery');
            $table->string('platform', 30)->nullable()->comment('tdesktop, android, ios, web, etc.');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('start_param', 255)->nullable()->comment('Deep-link start parameter');
            $table->unsignedBigInteger('referrer_mini_app_id')->nullable()->index('idx_referrer')->comment('If opened via another mini app');
            $table->unsignedInteger('duration_seconds')->nullable()->comment('Session duration (updated on close)');
            $table->dateTime('created_at')->useCurrent()->index('idx_created');
            $table->dateTime('ended_at')->nullable();

            $table->foreign('mini_app_id', 'fk_tmas_app')->references('id')->on('aq_telegram_mini_apps')->onDelete('cascade');
            $table->foreign('referrer_mini_app_id', 'fk_tmas_referrer')->references('id')->on('aq_telegram_mini_apps')->onDelete('set null');
        });

        Schema::create('aq_telegram_mini_app_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mini_app_id')->index('idx_mini_app');
            $table->unsignedBigInteger('session_id')->nullable()->index('idx_session');
            $table->bigInteger('telegram_user_id')->index('idx_tg_user');
            $table->enum('event_type', ['page_view','ad_impression','ad_click','purchase','custom'])->default('custom')->index('idx_event_type');
            $table->string('event_name', 100)->comment('e.g. banner_viewed, cta_clicked, checkout_complete');
            $table->json('event_data')->nullable()->comment('Arbitrary payload');
            $table->decimal('revenue', 12, 4)->nullable()->comment('Revenue attributed to this event');
            $table->string('currency', 3)->default('EUR');
            $table->dateTime('created_at')->useCurrent()->index('idx_created');

            $table->foreign('mini_app_id', 'fk_tmae_app')->references('id')->on('aq_telegram_mini_apps')->onDelete('cascade');
            $table->foreign('session_id', 'fk_tmae_session')->references('id')->on('aq_telegram_mini_app_sessions')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_telegram_mini_app_events');
        Schema::dropIfExists('aq_telegram_mini_app_sessions');
        Schema::dropIfExists('aq_telegram_mini_apps');
    }
};
