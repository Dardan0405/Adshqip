<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_url_ad_reports', function (Blueprint $table) {
            $table->text('referrer_url')->nullable()->after('request_url');
        });
    }

    public function down(): void
    {
        Schema::table('aq_url_ad_reports', function (Blueprint $table) {
            $table->dropColumn('referrer_url');
        });
    }
};
