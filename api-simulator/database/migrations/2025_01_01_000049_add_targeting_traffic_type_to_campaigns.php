<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_campaigns', function (Blueprint $table) {
            $table->string('targeting_traffic_type')->nullable()->default('all')->after('targeting_language');
        });
    }

    public function down(): void
    {
        Schema::table('aq_campaigns', function (Blueprint $table) {
            $table->dropColumn('targeting_traffic_type');
        });
    }
};
