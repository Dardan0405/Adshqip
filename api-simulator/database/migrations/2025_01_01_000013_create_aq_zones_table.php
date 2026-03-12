<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_zones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->index('idx_site');
            $table->string('name', 255);
            $table->unsignedInteger('format_id')->nullable()->index('idx_format');
            $table->unsignedInteger('size_id')->nullable();
            $table->enum('placement', ['header','sidebar','content','footer','overlay','interstitial','push'])->default('content');
            $table->decimal('floor_price', 10, 4)->nullable()->comment('Minimum CPM');
            $table->enum('status', ['active','paused','archived'])->default('active');
            $table->text('ad_code')->nullable()->comment('Generated JS/HTML embed code');
            $table->boolean('is_deleted')->default(false);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('site_id', 'fk_zone_site')->references('id')->on('aq_sites')->onDelete('cascade');
            $table->foreign('format_id', 'fk_zone_format')->references('id')->on('aq_ad_formats')->onDelete('set null');
            $table->foreign('size_id', 'fk_zone_size')->references('id')->on('aq_ad_sizes')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_zones');
    }
};
