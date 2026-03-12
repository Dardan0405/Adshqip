<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_cookie_consents', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_id', 64)->index('idx_visitor')->comment('Anonymous visitor fingerprint');
            $table->unsignedBigInteger('user_id')->nullable()->index('idx_user');
            $table->enum('consent_type', ['accept_all','reject_non_essential','custom']);
            $table->boolean('essential_cookies')->default(true);
            $table->boolean('analytics_cookies')->default(false);
            $table->boolean('marketing_cookies')->default(false);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('aq_languages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 5)->unique('uk_code');
            $table->string('name', 50);
            $table->string('native_name', 50);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
        });

        Schema::create('aq_api_keys', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index('idx_user');
            $table->string('name', 100)->default('Default');
            $table->string('api_key', 64)->unique('uk_api_key');
            $table->string('api_secret_hash', 255);
            $table->json('permissions')->nullable()->comment('["read","write","admin"]');
            $table->integer('rate_limit_per_minute')->default(60);
            $table->json('allowed_ips')->nullable();
            $table->dateTime('last_used_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->enum('status', ['active','revoked'])->default('active');
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('user_id', 'fk_apikey_user')->references('id')->on('aq_users')->onDelete('cascade');
        });

        Schema::create('aq_mobile_devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index('idx_user');
            $table->string('device_token', 500)->comment('FCM/APNs push token');
            $table->enum('platform', ['ios','android'])->index('idx_platform');
            $table->string('device_model', 100)->nullable();
            $table->string('os_version', 20)->nullable();
            $table->string('app_version', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('last_active_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('user_id', 'fk_device_user')->references('id')->on('aq_users')->onDelete('cascade');
        });

        Schema::create('aq_careers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 255)->comment('old: job_category');
            $table->string('department', 100)->nullable();
            $table->string('location', 100)->default('Tirana, Albania');
            $table->enum('employment_type', ['full_time','part_time','contract','remote'])->default('full_time');
            $table->text('summary')->nullable()->comment('old: job_summary');
            $table->longText('description')->nullable()->comment('old: job_description');
            $table->text('requirements')->nullable();
            $table->string('salary_range', 100)->nullable();
            $table->boolean('is_published')->default(false)->index('idx_published');
            $table->dateTime('published_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('aq_platform_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('setting_key', 100)->unique('uk_key');
            $table->text('setting_value')->nullable();
            $table->enum('setting_type', ['string','integer','boolean','json','decimal'])->default('string');
            $table->string('category', 50)->default('general');
            $table->string('description', 255)->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_platform_settings');
        Schema::dropIfExists('aq_careers');
        Schema::dropIfExists('aq_mobile_devices');
        Schema::dropIfExists('aq_api_keys');
        Schema::dropIfExists('aq_languages');
        Schema::dropIfExists('aq_cookie_consents');
    }
};
