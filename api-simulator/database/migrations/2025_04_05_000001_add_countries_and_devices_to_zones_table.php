<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_zones', function (Blueprint $table) {
            $table->json('target_countries')->nullable()->after('reload_time')->comment('JSON array of country codes, null = all');
            $table->json('target_devices')->nullable()->after('target_countries')->comment('JSON array of device types, null = all');
        });
    }

    public function down(): void
    {
        Schema::table('aq_zones', function (Blueprint $table) {
            $table->dropColumn(['target_countries', 'target_devices']);
        });
    }
};
