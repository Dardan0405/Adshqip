<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_campaigns', function (Blueprint $table) {
            $table->string('s2s_postback_url', 2000)->nullable()->after('targeting_ip_exclude');
            $table->boolean('s2s_enabled')->default(false)->after('s2s_postback_url');
        });
    }

    public function down(): void
    {
        Schema::table('aq_campaigns', function (Blueprint $table) {
            $table->dropColumn(['s2s_postback_url', 's2s_enabled']);
        });
    }
};
