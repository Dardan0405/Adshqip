<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_ad_creatives', function (Blueprint $table) {
            $table->string('video_url', 2000)->nullable()->after('file_path')
                ->comment('External video URL for video ads (YouTube, direct mp4, etc.)');
            $table->string('thumbnail_path', 500)->nullable()->after('alt_text')
                ->comment('Video thumbnail image path');
        });
    }

    public function down(): void
    {
        Schema::table('aq_ad_creatives', function (Blueprint $table) {
            $table->dropColumn(['video_url', 'thumbnail_path']);
        });
    }
};
