<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_system_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->string('provider_type', 50)->default('other');
            $table->string('environment', 30)->default('production');
            $table->string('status', 30)->default('active');
            $table->string('base_url')->nullable();
            $table->string('webhook_url')->nullable();
            $table->string('auth_type', 40)->default('none');
            $table->string('api_key')->nullable();
            $table->text('api_secret')->nullable();
            $table->unsignedSmallInteger('priority')->default(100);
            $table->unsignedSmallInteger('timeout_seconds')->default(10);
            $table->json('config')->nullable();
            $table->string('last_check_status', 30)->nullable();
            $table->text('last_check_message')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['provider_type', 'status']);
            $table->index(['environment', 'status']);
            $table->index('priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_system_providers');
    }
};
