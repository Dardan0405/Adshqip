<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_zones', function (Blueprint $table) {
            if (! Schema::hasColumn('aq_zones', 'content_width_px')) {
                $table->integer('content_width_px')->nullable()->after('reload_time');
            }
            if (! Schema::hasColumn('aq_zones', 'content_height_px')) {
                $table->integer('content_height_px')->nullable()->after('content_width_px');
            }
            if (! Schema::hasColumn('aq_zones', 'top_offset_px')) {
                $table->integer('top_offset_px')->nullable()->after('content_height_px');
            }
            if (! Schema::hasColumn('aq_zones', 'right_offset_px')) {
                $table->integer('right_offset_px')->nullable()->after('top_offset_px');
            }
            if (! Schema::hasColumn('aq_zones', 'z_index_value')) {
                $table->integer('z_index_value')->nullable()->after('right_offset_px');
            }
            if (! Schema::hasColumn('aq_zones', 'is_fixed')) {
                $table->boolean('is_fixed')->default(false)->after('z_index_value');
            }
            if (! Schema::hasColumn('aq_zones', 'hide_side')) {
                $table->string('hide_side', 20)->nullable()->after('is_fixed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('aq_zones', function (Blueprint $table) {
            $drops = [
                'content_width_px',
                'content_height_px',
                'top_offset_px',
                'right_offset_px',
                'z_index_value',
                'is_fixed',
                'hide_side',
            ];

            $existing = array_filter($drops, fn ($column) => Schema::hasColumn('aq_zones', $column));
            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }
};
