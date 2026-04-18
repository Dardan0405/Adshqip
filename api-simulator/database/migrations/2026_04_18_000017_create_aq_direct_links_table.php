<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_direct_links', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->enum('publisher_scope', ['all', 'selected'])->default('all');
            $table->json('publisher_ids')->nullable();
            $table->enum('adblock_scope', ['all', 'selected'])->default('all');
            $table->json('zone_ids')->nullable();
            $table->string('link_code', 32)->unique();
            $table->string('destination_url', 2048)->nullable();
            $table->unsignedInteger('click_count')->default(0);
            $table->unsignedInteger('view_count')->default(0);
            $table->enum('status', ['active', 'paused', 'expired'])->default('active')->index();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')
                ->references('id')
                ->on('aq_users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_direct_links');
    }
};
