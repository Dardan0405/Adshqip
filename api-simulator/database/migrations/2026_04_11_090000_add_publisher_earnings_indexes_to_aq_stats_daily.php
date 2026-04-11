<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_stats_daily', function (Blueprint $table) {
            $table->index(['publisher_id', 'date'], 'idx_stats_daily_publisher_date');
            $table->index(['publisher_id', 'publisher_earnings'], 'idx_stats_daily_publisher_earnings');
        });
    }

    public function down(): void
    {
        Schema::table('aq_stats_daily', function (Blueprint $table) {
            $table->dropIndex('idx_stats_daily_publisher_date');
            $table->dropIndex('idx_stats_daily_publisher_earnings');
        });
    }
};
