<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_pixel_trackers', function (Blueprint $table) {
            $table->string('pixel_goal', 100)->nullable()->after('type');
            $table->string('category', 100)->nullable()->after('pixel_goal');
            $table->text('append_code')->nullable()->after('tracking_url');
            $table->string('status', 20)->default('active')->after('is_active');
        });

        // Update type column to support new pixel types
        DB::statement("ALTER TABLE aq_pixel_trackers MODIFY COLUMN type VARCHAR(50) NOT NULL DEFAULT 'html_pixel'");
    }

    public function down(): void
    {
        Schema::table('aq_pixel_trackers', function (Blueprint $table) {
            $table->dropColumn(['pixel_goal', 'category', 'append_code', 'status']);
        });

        DB::statement("ALTER TABLE aq_pixel_trackers MODIFY COLUMN type ENUM('conversion','pageview','event','custom') NOT NULL DEFAULT 'conversion'");
    }
};
