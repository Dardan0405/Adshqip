<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_zones', function (Blueprint $table) {
            $table->integer('target_age_min')->nullable()->after('size_key');
            $table->integer('target_age_max')->nullable()->after('target_age_min');
            $table->string('target_gender', 20)->nullable()->after('target_age_max');
            $table->string('target_color', 50)->nullable()->after('target_gender');
            $table->integer('target_height_min')->nullable()->after('target_color');
            $table->integer('target_height_max')->nullable()->after('target_height_min');
            $table->integer('target_weight_min')->nullable()->after('target_height_max');
            $table->integer('target_weight_max')->nullable()->after('target_weight_min');
            $table->integer('frequency_views')->nullable()->after('target_weight_max');
            $table->boolean('auto_reload')->default(false)->after('frequency_views');
            $table->integer('reload_time')->nullable()->after('auto_reload');
        });
    }

    public function down(): void
    {
        Schema::table('aq_zones', function (Blueprint $table) {
            $table->dropColumn([
                'target_age_min',
                'target_age_max',
                'target_gender',
                'target_color',
                'target_height_min',
                'target_height_max',
                'target_weight_min',
                'target_weight_max',
                'frequency_views',
                'auto_reload',
                'reload_time',
            ]);
        });
    }
};
