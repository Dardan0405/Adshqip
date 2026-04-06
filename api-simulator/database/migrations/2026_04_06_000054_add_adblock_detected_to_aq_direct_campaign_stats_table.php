<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_direct_campaign_stats', function (Blueprint $table) {
            $table->unsignedBigInteger('adblock_detected')->default(0)->after('viewable_impressions');
        });
    }

    public function down(): void
    {
        Schema::table('aq_direct_campaign_stats', function (Blueprint $table) {
            $table->dropColumn('adblock_detected');
        });
    }
};
