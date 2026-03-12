<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('slug', 100)->unique('uk_slug');
            $table->string('color', 7)->default('#6366f1')->comment('Hex color for UI badge');
            $table->string('description', 500)->nullable();
            $table->string('tag_group', 50)->nullable()->index('idx_group')->comment('e.g. performance, compliance, device, content');
            $table->boolean('is_system')->default(false)->comment('System-managed vs user-created');
            $table->enum('status', ['active','inactive'])->default('active')->index('idx_status');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('aq_ad_format_tags', function (Blueprint $table) {
            $table->unsignedInteger('format_id');
            $table->unsignedInteger('tag_id')->index('idx_tag');
            $table->dateTime('created_at')->useCurrent();
            $table->primary(['format_id', 'tag_id']);
            $table->foreign('format_id', 'fk_aft_format')->references('id')->on('aq_ad_formats')->onDelete('cascade');
            $table->foreign('tag_id', 'fk_aft_tag')->references('id')->on('aq_tags')->onDelete('cascade');
        });

        Schema::create('aq_referral_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_id')->index('idx_referrer')->comment('User who created the link');
            $table->string('code', 32)->unique('uk_code')->comment('Unique referral code (URL-safe)');
            $table->string('slug', 100)->nullable()->unique('uk_slug')->comment('Optional vanity slug');
            $table->enum('target_role', ['advertiser','publisher','any'])->default('any')->index('idx_target_role');
            $table->string('campaign_name', 255)->nullable()->comment('Internal label for tracking campaigns');
            $table->string('landing_url', 500)->nullable()->comment('Custom landing page override');
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable();
            // Commission structure
            $table->enum('commission_type', ['percentage','flat'])->default('percentage');
            $table->decimal('commission_rate', 8, 4)->default(5.0000);
            $table->unsignedInteger('commission_duration_days')->default(365)->nullable();
            $table->decimal('max_commission_per_referral', 12, 4)->nullable();
            // Counters
            $table->unsignedBigInteger('total_clicks')->default(0);
            $table->unsignedInteger('total_signups')->default(0);
            $table->unsignedInteger('total_qualified')->default(0);
            $table->decimal('total_earned', 12, 4)->default(0.0000);
            $table->enum('status', ['active','paused','expired','revoked'])->default('active')->index('idx_status');
            $table->dateTime('expires_at')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('referrer_id', 'fk_reflink_user')->references('id')->on('aq_users')->onDelete('cascade');
        });

        Schema::create('aq_referral_conversions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('link_id')->index('idx_link');
            $table->unsignedBigInteger('referrer_id')->index('idx_referrer');
            $table->unsignedBigInteger('referred_user_id')->unique('uk_referred_user')->comment('The new user who signed up');
            $table->enum('referred_role', ['advertiser','publisher']);
            // Attribution
            $table->string('click_ip', 45)->nullable();
            $table->string('click_user_agent', 500)->nullable();
            $table->string('click_referer', 2000)->nullable();
            $table->string('signup_ip', 45)->nullable();
            $table->string('cookie_id', 64)->nullable()->comment('First-party attribution cookie');
            // Qualification
            $table->boolean('is_qualified')->default(false)->index('idx_qualified');
            $table->dateTime('qualified_at')->nullable();
            $table->decimal('qualification_threshold', 12, 4)->nullable();
            // Commission tracking
            $table->decimal('commission_earned', 12, 4)->default(0.0000);
            $table->string('commission_currency', 3)->default('EUR');
            $table->dateTime('commission_ends_at')->nullable();
            $table->enum('status', ['pending','active','qualified','expired','fraudulent'])->default('pending')->index('idx_status');
            $table->json('fraud_flags')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('link_id', 'fk_refconv_link')->references('id')->on('aq_referral_links')->onDelete('cascade');
            $table->foreign('referrer_id', 'fk_refconv_referrer')->references('id')->on('aq_users')->onDelete('cascade');
            $table->foreign('referred_user_id', 'fk_refconv_referred')->references('id')->on('aq_users')->onDelete('cascade');
        });

        Schema::create('aq_referral_payouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_id')->index('idx_referrer');
            $table->decimal('amount', 12, 4);
            $table->string('currency', 3)->default('EUR');
            $table->enum('payment_method', ['balance_credit','paypal','wire_transfer','crypto','payoneer'])->default('balance_credit');
            $table->string('payment_reference', 255)->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedInteger('conversions_count')->default(0);
            $table->enum('status', ['pending','processing','completed','failed','cancelled'])->default('pending')->index('idx_status');
            $table->dateTime('processed_at')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['period_start', 'period_end'], 'idx_period');
            $table->foreign('referrer_id', 'fk_refpayout_user')->references('id')->on('aq_users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_referral_payouts');
        Schema::dropIfExists('aq_referral_conversions');
        Schema::dropIfExists('aq_referral_links');
        Schema::dropIfExists('aq_ad_format_tags');
        Schema::dropIfExists('aq_tags');
    }
};
