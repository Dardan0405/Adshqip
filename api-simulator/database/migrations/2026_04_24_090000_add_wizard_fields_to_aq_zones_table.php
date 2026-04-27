<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_zones', function (Blueprint $table) {
            if (! Schema::hasColumn('aq_zones', 'choose_type')) {
                $table->string('choose_type')->default('web')->after('site_id');
            }

            if (! Schema::hasColumn('aq_zones', 'mobile_app_id')) {
                $table->unsignedBigInteger('mobile_app_id')->nullable()->after('choose_type');
            }

            if (! Schema::hasColumn('aq_zones', 'zone_type')) {
                $table->string('zone_type')->nullable()->after('size_key');
            }

            if (! Schema::hasColumn('aq_zones', 'passback')) {
                $table->text('passback')->nullable()->after('floor_price');
            }

            if (! Schema::hasColumn('aq_zones', 'image_width')) {
                $table->unsignedInteger('image_width')->nullable()->after('passback');
            }

            if (! Schema::hasColumn('aq_zones', 'image_height')) {
                $table->unsignedInteger('image_height')->nullable()->after('image_width');
            }

            if (! Schema::hasColumn('aq_zones', 'html_template')) {
                $table->longText('html_template')->nullable()->after('image_height');
            }

            if (! Schema::hasColumn('aq_zones', 'custom_css')) {
                $table->longText('custom_css')->nullable()->after('html_template');
            }

            if (! Schema::hasColumn('aq_zones', 'bg_color')) {
                $table->string('bg_color')->nullable()->after('custom_css');
            }

            if (! Schema::hasColumn('aq_zones', 'sponsored_prefix')) {
                $table->string('sponsored_prefix')->nullable()->after('bg_color');
            }

            if (! Schema::hasColumn('aq_zones', 'css_path')) {
                $table->string('css_path')->nullable()->after('sponsored_prefix');
            }

            if (! Schema::hasColumn('aq_zones', 'inline_video')) {
                $table->boolean('inline_video')->default(false)->after('css_path');
            }
        });

        if (Schema::hasColumn('aq_zones', 'site_id')) {
            $driver = Schema::getConnection()->getDriverName();

            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE aq_zones MODIFY site_id BIGINT UNSIGNED NULL');
            }
        }
    }

    public function down(): void
    {
        Schema::table('aq_zones', function (Blueprint $table) {
            $columns = [
                'inline_video',
                'css_path',
                'sponsored_prefix',
                'bg_color',
                'custom_css',
                'html_template',
                'image_height',
                'image_width',
                'passback',
                'zone_type',
                'mobile_app_id',
                'choose_type',
            ];

            $existing = array_values(array_filter($columns, fn (string $column) => Schema::hasColumn('aq_zones', $column)));

            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }
};
