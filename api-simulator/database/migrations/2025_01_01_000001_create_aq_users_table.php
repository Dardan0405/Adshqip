<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_users', function (Blueprint $table) {
            $table->id();
            $table->string('email', 255)->unique('uk_email');
            $table->string('password_hash', 255);
            $table->enum('role', ['admin', 'advertiser', 'publisher', 'manager'])->default('advertiser')->index('idx_role');
            $table->enum('status', ['active', 'inactive', 'suspended', 'pending_verification', 'closed'])->default('pending_verification')->index('idx_status');
            $table->dateTime('email_verified_at')->nullable();
            $table->boolean('two_factor_enabled')->default(false)->comment('Landing page: 2FA security');
            $table->string('two_factor_secret', 255)->nullable();
            $table->string('preferred_language', 5)->default('en')->comment('EN, SQ, IT, DE from language selector');
            $table->enum('theme_preference', ['light', 'dark', 'system'])->default('system');
            $table->string('timezone', 50)->default('Europe/Tirane');
            $table->dateTime('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->enum('kyc_status', ['not_started', 'pending', 'in_review', 'approved', 'rejected', 'expired'])->default('not_started')->index('idx_kyc_status')->comment('Denormalized from aq_kyc_verifications');
            $table->enum('kyc_level', ['none', 'basic', 'standard', 'enhanced'])->default('none')->comment('Current verified KYC tier');
            $table->dateTime('kyc_verified_at')->nullable()->comment('When KYC was last approved');
            $table->bigInteger('telegram_user_id')->nullable()->unique('uk_telegram_user_id')->comment('Linked Telegram numeric user ID');
            $table->string('telegram_username', 100)->nullable()->comment('Linked Telegram @username');
            $table->dateTime('telegram_linked_at')->nullable()->comment('When Telegram account was linked');
            $table->string('referral_code', 32)->nullable()->unique('uk_referral_code')->comment("This user's own unique referral code");
            $table->unsignedBigInteger('referred_by')->nullable()->index('idx_referred_by')->comment('FK → aq_users: who referred this user');
            $table->dateTime('referred_at')->nullable()->comment('When the referral signup occurred');
            $table->boolean('is_deleted')->default(false);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('referred_by', 'fk_user_referred_by')->references('id')->on('aq_users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_users');
    }
};
