<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('aq_push_subscriptions')) {
            return;
        }

        Schema::create('aq_push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->text('endpoint');
            $table->string('endpoint_hash', 64)->unique(); // SHA-256 hash for uniqueness
            $table->string('p256dh_key', 500);
            $table->string('auth_token', 500);
            $table->string('user_agent', 500)->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable();

            $table->foreign('user_id')->references('id')->on('aq_users')->cascadeOnDelete();
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('aq_push_subscriptions')) {
            Schema::dropIfExists('aq_push_subscriptions');
        }
    }
};
