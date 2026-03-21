<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_campaigns', function (Blueprint $table) {
            $table->unsignedBigInteger('pixel_tracker_id')->nullable()->after('format_id')->comment('FK → aq_pixel_trackers');
            $table->foreign('pixel_tracker_id', 'fk_campaign_pixel')->references('id')->on('aq_pixel_trackers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('aq_campaigns', function (Blueprint $table) {
            $table->dropForeign('fk_campaign_pixel');
            $table->dropColumn('pixel_tracker_id');
        });
    }
};
