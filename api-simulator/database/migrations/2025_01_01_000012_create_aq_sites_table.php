<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_sites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('publisher_id')->index('idx_publisher');
            $table->string('name', 255);
            $table->string('domain', 255)->index('idx_domain');
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable();
            $table->string('language', 5)->default('sq');
            $table->unsignedBigInteger('monthly_pageviews')->nullable();
            $table->enum('status', ['active','pending_review','rejected','suspended'])->default('pending_review');
            $table->boolean('is_deleted')->default(false);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('publisher_id', 'fk_site_publisher')->references('id')->on('aq_users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_sites');
    }
};
