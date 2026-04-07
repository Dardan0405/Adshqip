<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('country_wise_bidding', function (Blueprint $table) {
            // Drop the old foreign key that references 'users' table
            $table->dropForeign('country_wise_bidding_advertiser_id_foreign');

            // Add the correct foreign key that references 'aq_users' table
            $table->foreign('advertiser_id')
                ->references('id')
                ->on('aq_users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('country_wise_bidding', function (Blueprint $table) {
            // Drop the new foreign key
            $table->dropForeign(['advertiser_id']);

            // Restore the old foreign key (for rollback purposes)
            $table->foreign('advertiser_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
};
