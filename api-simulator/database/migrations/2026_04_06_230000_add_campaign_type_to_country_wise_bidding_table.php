<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('country_wise_bidding', function (Blueprint $table) {
            $table->string('campaign_type', 20)->nullable()->after('campaign_id');
        });

        DB::table('country_wise_bidding')
            ->whereNotNull('campaign_id')
            ->update(['campaign_type' => 'network']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('country_wise_bidding', function (Blueprint $table) {
            $table->dropColumn('campaign_type');
        });
    }
};
