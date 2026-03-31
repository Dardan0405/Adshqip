<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_campaigns', function (Blueprint $table) {
            $table->json('targeting_os_version')->nullable()->after('targeting_os')->comment('OS version targeting (array of {os, version} objects)');
            $table->json('targeting_browser_version')->nullable()->after('targeting_browser')->comment('Browser version targeting (array of {browser, version} objects)');
        });
    }

    public function down(): void
    {
        Schema::table('aq_campaigns', function (Blueprint $table) {
            $table->dropColumn(['targeting_os_version', 'targeting_browser_version']);
        });
    }
};
