<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_advertiser_feedback', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index('idx_user');
            $table->enum('type', ['bug', 'feature_request', 'improvement', 'general'])->default('general')->index('idx_type');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->string('subject', 255);
            $table->text('message');
            $table->string('page_url', 500)->nullable();
            $table->enum('status', ['submitted', 'reviewed', 'planned', 'resolved', 'closed'])->default('submitted')->index('idx_status');
            $table->text('admin_response')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('user_id', 'fk_adv_feedback_user')->references('id')->on('aq_users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_advertiser_feedback');
    }
};
