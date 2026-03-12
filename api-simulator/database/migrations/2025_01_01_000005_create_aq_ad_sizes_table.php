<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_ad_sizes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100)->comment('e.g. Leaderboard, Medium Rectangle');
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->unsignedInteger('format_id')->nullable()->index('idx_format');
            $table->boolean('is_responsive')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->dateTime('created_at')->useCurrent();

            $table->unique(['width', 'height'], 'uk_dimensions');
            $table->foreign('format_id', 'fk_size_format')->references('id')->on('aq_ad_formats')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_ad_sizes');
    }
};
