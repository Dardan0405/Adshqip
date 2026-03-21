<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_pixel_trackers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('advertiser_id')->nullable()->index('idx_advertiser');
            $table->enum('type', ['conversion', 'pageview', 'event', 'custom'])->default('conversion');
            $table->string('pixel_code', 32)->unique()->comment('Unique pixel identifier/code');
            $table->text('tracking_url')->nullable()->comment('Full tracking URL');
            $table->json('parameters')->nullable()->comment('Custom tracking parameters');
            $table->boolean('is_active')->default(true);
            $table->integer('fire_count')->default(0)->comment('Number of times pixel has fired');
            $table->dateTime('last_fired_at')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('advertiser_id', 'fk_pixel_advertiser')->references('id')->on('aq_users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_pixel_trackers');
    }
};
