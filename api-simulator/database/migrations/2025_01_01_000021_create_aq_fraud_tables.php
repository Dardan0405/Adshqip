<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_fraud_events', function (Blueprint $table) {
            $table->id();
            $table->enum('event_type', ['click','impression'])->index('idx_type');
            $table->unsignedBigInteger('ad_id')->nullable();
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->string('viewer_id', 64)->index('idx_viewer');
            $table->string('fingerprint_id', 64)->nullable();
            $table->string('ip_address', 45)->index('idx_ip');
            $table->string('user_agent', 500)->nullable();
            $table->enum('fraud_reason', ['duplicate','bot','datacenter_ip','click_flood','impression_stacking','geo_mismatch','other']);
            $table->enum('severity', ['low','medium','high','critical'])->default('medium');
            $table->boolean('blocked')->default(true);
            $table->dateTime('created_at')->useCurrent()->index('idx_created');
        });

        Schema::create('aq_antifraud_rules', function (Blueprint $table) {
            $table->increments('id');
            $table->string('rule_name', 100);
            $table->enum('rule_type', ['impression_cap','click_cap','ip_blacklist','ua_blacklist','geo_block','fingerprint']);
            $table->integer('threshold_value')->nullable()->comment('old: imp_num');
            $table->integer('reset_period_seconds')->nullable()->comment('old: resettime');
            $table->enum('action', ['block','flag','throttle'])->default('block');
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('aq_publisher_fraud_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('publisher_id')->index('idx_publisher');
            $table->enum('record_type', ['allow','fraud']);
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('flagged_impressions')->default(0);
            $table->unsignedBigInteger('flagged_clicks')->default(0);
            $table->enum('action_taken', ['none','warning','suspended','banned'])->default('none');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('resolved_at')->nullable();

            $table->foreign('publisher_id', 'fk_fraud_publisher')->references('id')->on('aq_users')->onDelete('cascade');
        });

        Schema::create('aq_fraud_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('publisher_id')->index('idx_publisher');
            $table->unsignedBigInteger('fraud_record_id')->nullable()->index('idx_fraud_record');
            $table->enum('notification_type', ['email','in_app','sms'])->default('email');
            $table->string('subject', 255)->nullable();
            $table->text('message')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('fraud_record_id', 'fk_fraudnotif_record')->references('id')->on('aq_publisher_fraud_records')->onDelete('set null');
            $table->foreign('publisher_id', 'fk_fraudnotif_publisher')->references('id')->on('aq_users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_fraud_notifications');
        Schema::dropIfExists('aq_publisher_fraud_records');
        Schema::dropIfExists('aq_antifraud_rules');
        Schema::dropIfExists('aq_fraud_events');
    }
};
