<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_geo_countries', function (Blueprint $table) {
            $table->increments('id');
            $table->char('iso_code', 2)->unique('uk_iso');
            $table->string('name', 100);
            $table->string('name_sq', 100)->nullable()->comment('Albanian name');
            $table->string('continent', 50)->nullable();
            $table->boolean('is_balkan')->default(false)->index('idx_balkan')->comment('Albania, Kosovo, North Macedonia, etc.');
            $table->enum('status', ['active', 'inactive'])->default('active');
        });

        Schema::create('aq_geo_regions', function (Blueprint $table) {
            $table->increments('id');
            $table->char('country_code', 2)->index('idx_country');
            $table->string('region_code', 10);
            $table->string('name', 100);

            $table->foreign('country_code', 'fk_region_country')->references('iso_code')->on('aq_geo_countries')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_geo_regions');
        Schema::dropIfExists('aq_geo_countries');
    }
};
