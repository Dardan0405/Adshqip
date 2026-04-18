<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_ad_creatives', function (Blueprint $table) {
            if (! Schema::hasColumn('aq_ad_creatives', 'display_screen_id')) {
                $table->unsignedBigInteger('display_screen_id')->nullable()->after('ad_id')->index('idx_creative_display_screen');
                $table->foreign('display_screen_id', 'fk_creative_display_screen')
                    ->references('id')
                    ->on('aq_display_screens')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('aq_ad_creatives', function (Blueprint $table) {
            if (Schema::hasColumn('aq_ad_creatives', 'display_screen_id')) {
                $table->dropForeign('fk_creative_display_screen');
                $table->dropIndex('idx_creative_display_screen');
                $table->dropColumn('display_screen_id');
            }
        });
    }
};
