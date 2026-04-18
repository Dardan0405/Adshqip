<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_cpm_geo_settings', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 2)->unique();
            $table->string('country_name', 120);
            $table->decimal('cpm_value', 12, 4);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('country_name', 'idx_cpm_geo_country_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_cpm_geo_settings');
    }
};
