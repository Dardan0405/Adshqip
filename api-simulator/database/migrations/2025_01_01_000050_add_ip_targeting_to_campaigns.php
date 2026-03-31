<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_campaigns', function (Blueprint $table) {
            $table->json('targeting_ip_include')->nullable()->after('targeting_traffic_type');
            $table->json('targeting_ip_exclude')->nullable()->after('targeting_ip_include');
        });
    }

    public function down(): void
    {
        Schema::table('aq_campaigns', function (Blueprint $table) {
            $table->dropColumn(['targeting_ip_include', 'targeting_ip_exclude']);
        });
    }
};
