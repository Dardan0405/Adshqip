<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_platform_announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title', 160);
            $table->string('slug', 180)->unique();
            $table->string('audience', 40)->default('all')->index();
            $table->string('placement', 40)->default('dashboard')->index();
            $table->string('type', 40)->default('info')->index();
            $table->string('status', 40)->default('draft')->index();
            $table->string('summary', 500)->nullable();
            $table->longText('body');
            $table->string('cta_label', 80)->nullable();
            $table->string('cta_url', 500)->nullable();
            $table->dateTime('starts_at')->nullable()->index();
            $table->dateTime('ends_at')->nullable()->index();
            $table->dateTime('published_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->boolean('is_pinned')->default(false)->index();
            $table->unsignedInteger('notification_count')->default(0);
            $table->dateTime('last_notified_at')->nullable();
            $table->timestamps();

            $table->foreign('created_by', 'fk_platform_announcements_created_by')
                ->references('id')
                ->on('aq_users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_platform_announcements');
    }
};
