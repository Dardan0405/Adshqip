<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_admin_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('recipient_id');
            $table->string('subject', 255);
            $table->text('message');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->boolean('is_read')->default(false);
            $table->dateTime('read_at')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->dateTime('created_at')->useCurrent();

            $table->index(['recipient_id', 'is_read'], 'idx_recipient_read');
            $table->index(['sender_id'], 'idx_sender');
            $table->foreign('sender_id')->references('id')->on('aq_users')->onDelete('cascade');
            $table->foreign('recipient_id')->references('id')->on('aq_users')->onDelete('cascade');
        });

        // Add push notification columns to notification settings if not exists
        Schema::table('aq_notification_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('aq_notification_settings', 'push_subscription')) {
                $table->text('push_subscription')->nullable()->after('push_campaign_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('aq_notification_settings', function (Blueprint $table) {
            if (Schema::hasColumn('aq_notification_settings', 'push_subscription')) {
                $table->dropColumn('push_subscription');
            }
        });

        Schema::dropIfExists('aq_admin_messages');
    }
};
