<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_ads', function (Blueprint $table) {
            $table->unsignedTinyInteger('weight')->default(5)->after('admin_approved')->comment('Ad weight/priority 1-10, higher = more impressions');
        });
    }

    public function down(): void
    {
        Schema::table('aq_ads', function (Blueprint $table) {
            $table->dropColumn('weight');
        });
    }
};
