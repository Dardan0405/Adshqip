<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_campaigns', function (Blueprint $table) {
            $table->unsignedBigInteger('zone_id')->nullable()->after('group_id');
            $table->foreign('zone_id', 'fk_campaign_zone')
                ->references('id')
                ->on('aq_zones')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('aq_campaigns', function (Blueprint $table) {
            $table->dropForeign('fk_campaign_zone');
            $table->dropColumn('zone_id');
        });
    }
};
