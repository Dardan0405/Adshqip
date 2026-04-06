<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_zones', function (Blueprint $table) {
            $table->string('format_key', 50)->nullable()->after('format_id')->comment('Hardcoded format key');
            $table->string('size_key', 50)->nullable()->after('size_id')->comment('Hardcoded size key');
        });
    }

    public function down(): void
    {
        Schema::table('aq_zones', function (Blueprint $table) {
            $table->dropColumn('format_key');
            $table->dropColumn('size_key');
        });
    }
};
