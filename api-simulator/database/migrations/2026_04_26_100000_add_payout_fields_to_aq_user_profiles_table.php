<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_user_profiles', function (Blueprint $table) {
            $table->string('payout_method', 50)->nullable()->after('payment_details');
            $table->json('payout_details')->nullable()->after('payout_method');
        });
    }

    public function down(): void
    {
        Schema::table('aq_user_profiles', function (Blueprint $table) {
            $table->dropColumn(['payout_method', 'payout_details']);
        });
    }
};
