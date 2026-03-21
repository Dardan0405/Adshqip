<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_campaigns', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->nullable()->after('advertiser_id')->index('idx_group');
            $table->foreign('group_id', 'fk_campaign_group')->references('id')->on('aq_campaign_groups')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('aq_campaigns', function (Blueprint $table) {
            $table->dropForeign('fk_campaign_group');
            $table->dropColumn('group_id');
        });
    }
};
