<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_stats_daily', function (Blueprint $table) {
            $table->unsignedBigInteger('unique_impressions')->default(0)->after('impressions');
            $table->unsignedBigInteger('unique_clicks')->default(0)->after('clicks');
        });
    }

    public function down(): void
    {
        Schema::table('aq_stats_daily', function (Blueprint $table) {
            $table->dropColumn(['unique_impressions', 'unique_clicks']);
        });
    }
};
