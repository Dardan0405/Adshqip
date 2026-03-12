<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_campaign_optimization', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id')->index('idx_campaign');
            $table->enum('tool', ['inline','spendguard','perf_stimulator','pacing_health'])->index('idx_tool')->comment('Which optimization tool triggered this event');
            // In-Line
            $table->unsignedBigInteger('inline_zone_id')->nullable()->comment('Zone that triggered the bid adjustment');
            $table->decimal('inline_bid_before', 10, 4)->nullable();
            $table->decimal('inline_bid_after', 10, 4)->nullable();
            $table->string('inline_adjustment_reason', 255)->nullable();
            // SpendGuard
            $table->decimal('spendguard_daily_budget', 12, 4)->nullable();
            $table->decimal('spendguard_spent_so_far', 12, 4)->nullable();
            $table->enum('spendguard_action', ['warning','soft_cap','hard_stop'])->nullable();
            // Performance Stimulator
            $table->unsignedBigInteger('perf_zone_id')->nullable();
            $table->enum('perf_metric', ['ctr','conversions','roas','ecpm'])->nullable();
            $table->decimal('perf_metric_value', 10, 4)->nullable();
            $table->decimal('perf_bid_boost_pct', 5, 2)->nullable();
            // Pacing Health Score
            $table->decimal('pacing_score', 5, 2)->nullable();
            $table->enum('pacing_status', ['healthy','under_pacing','over_pacing','critical'])->nullable();
            $table->decimal('pacing_budget_consumed_pct', 5, 2)->nullable();
            $table->decimal('pacing_time_elapsed_pct', 5, 2)->nullable();
            // Common
            $table->string('note', 500)->nullable();
            $table->dateTime('created_at')->useCurrent()->index('idx_created');

            $table->foreign('campaign_id', 'fk_opt_campaign')->references('id')->on('aq_direct_campaigns')->onDelete('cascade');
        });

        Schema::create('aq_account_deactivations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index('idx_user')->comment('Account that was affected');
            // What happened
            $table->enum('action', ['deactivated','suspended','reactivated','closed','pending_review'])->index('idx_action');
            $table->enum('previous_status', ['active','inactive','suspended','pending_verification','closed']);
            $table->enum('new_status', ['active','inactive','suspended','pending_verification','closed']);
            // Why
            $table->enum('reason_code', ['user_request','inactivity','payment_failure','fraud_detected','policy_violation','kyc_failed','duplicate_account','admin_manual','gdpr_erasure','other'])->default('user_request');
            $table->text('reason_detail')->nullable();
            // Who triggered it
            $table->enum('triggered_by', ['user','admin','system'])->default('user');
            $table->unsignedBigInteger('admin_user_id')->nullable()->comment('Admin user_id if triggered_by = admin');
            // Self-deactivation details
            $table->text('user_feedback')->nullable();
            $table->string('reactivation_token', 128)->nullable()->index('idx_reactivation_token');
            $table->dateTime('reactivation_token_expires_at')->nullable();
            $table->dateTime('reactivated_at')->nullable();
            // Scheduled closure
            $table->dateTime('scheduled_closure_at')->nullable();
            $table->boolean('data_deletion_requested')->default(false);
            $table->dateTime('data_deleted_at')->nullable();
            // Metadata
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->dateTime('created_at')->useCurrent()->index('idx_created');

            $table->foreign('user_id', 'fk_deact_user')->references('id')->on('aq_users')->onDelete('cascade');
            $table->foreign('admin_user_id', 'fk_deact_admin')->references('id')->on('aq_users')->onDelete('set null');
        });

        Schema::create('aq_two_factor_backup_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index('idx_user');
            $table->string('code_hash', 255)->comment('bcrypt hash of the 8-digit backup code');
            $table->boolean('used')->default(false)->index('idx_used');
            $table->dateTime('used_at')->nullable();
            $table->string('used_ip', 45)->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('user_id', 'fk_2fa_backup_user')->references('id')->on('aq_users')->onDelete('cascade');
        });

        Schema::create('aq_two_factor_challenges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index('idx_user');
            $table->enum('method', ['totp','backup_code','sms','email_otp'])->default('totp');
            $table->enum('result', ['success','failed','expired','locked_out'])->index('idx_result');
            $table->string('code_used', 10)->nullable();
            $table->unsignedTinyInteger('failure_count')->default(0);
            $table->dateTime('locked_until')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->dateTime('created_at')->useCurrent()->index('idx_created');

            $table->foreign('user_id', 'fk_2fa_challenge_user')->references('id')->on('aq_users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_two_factor_challenges');
        Schema::dropIfExists('aq_two_factor_backup_codes');
        Schema::dropIfExists('aq_account_deactivations');
        Schema::dropIfExists('aq_campaign_optimization');
    }
};
