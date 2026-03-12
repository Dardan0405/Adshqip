<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_zone_ad_assoc', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zone_id');
            $table->unsignedBigInteger('ad_id');
            $table->integer('priority')->default(0);
            $table->dateTime('created_at')->useCurrent();

            $table->unique(['zone_id', 'ad_id'], 'uk_zone_ad');
            $table->foreign('zone_id', 'fk_assoc_zone')->references('id')->on('aq_zones')->onDelete('cascade');
            $table->foreign('ad_id', 'fk_assoc_ad')->references('id')->on('aq_ads')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_zone_ad_assoc');
    }
};
