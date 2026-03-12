<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('type', ['success','warning','error','info','payment','campaign','system'])->default('info');
            $table->string('title', 255);
            $table->text('message')->nullable();
            $table->string('action_url', 500)->nullable();
            $table->boolean('is_read')->default(false);
            $table->dateTime('read_at')->nullable();
            $table->dateTime('created_at')->useCurrent()->index('idx_created');

            $table->index(['user_id', 'is_read'], 'idx_user_read');
            $table->foreign('user_id', 'fk_notif_user')->references('id')->on('aq_users')->onDelete('cascade');
        });

        Schema::create('aq_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique('uk_user');
            $table->boolean('email_campaign_updates')->default(true);
            $table->boolean('email_payment_alerts')->default(true);
            $table->boolean('email_fraud_alerts')->default(true);
            $table->boolean('email_newsletter')->default(true);
            $table->boolean('push_enabled')->default(true);
            $table->boolean('push_earnings')->default(true);
            $table->boolean('push_campaign_status')->default(true);
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('user_id', 'fk_notifsettings_user')->references('id')->on('aq_users')->onDelete('cascade');
        });

        Schema::create('aq_support_tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index('idx_user');
            $table->unsignedBigInteger('assigned_to')->nullable()->index('idx_assigned')->comment('Admin/manager user_id');
            $table->string('subject', 255);
            $table->enum('category', ['billing','technical','campaign','account','fraud','other'])->default('other');
            $table->enum('priority', ['low','medium','high','urgent'])->default('medium');
            $table->enum('status', ['open','in_progress','waiting_reply','resolved','closed'])->default('open')->index('idx_status');
            $table->dateTime('resolved_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('user_id', 'fk_ticket_user')->references('id')->on('aq_users')->onDelete('cascade');
        });

        Schema::create('aq_support_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id')->index('idx_ticket');
            $table->unsignedBigInteger('sender_id');
            $table->text('message');
            $table->string('attachment_url', 500)->nullable();
            $table->boolean('is_internal_note')->default(false);
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('ticket_id', 'fk_msg_ticket')->references('id')->on('aq_support_tickets')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_support_messages');
        Schema::dropIfExists('aq_support_tickets');
        Schema::dropIfExists('aq_notification_settings');
        Schema::dropIfExists('aq_notifications');
    }
};
