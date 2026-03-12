<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index('idx_user');
            $table->string('token', 255)->unique('uk_token');
            $table->string('ip_address', 45);
            $table->string('user_agent', 500)->nullable();
            $table->string('browser', 50)->nullable();
            $table->string('os', 50)->nullable();
            $table->enum('device_type', ['desktop', 'mobile', 'tablet'])->nullable();
            $table->dateTime('expires_at')->index('idx_expires');
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('user_id', 'fk_session_user')->references('id')->on('aq_users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_sessions');
    }
};
