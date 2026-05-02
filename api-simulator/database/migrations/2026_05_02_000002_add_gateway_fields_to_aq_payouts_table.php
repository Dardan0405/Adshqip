<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_payouts', function (Blueprint $table) {
            if (! Schema::hasColumn('aq_payouts', 'payment_provider')) {
                $table->string('payment_provider', 50)->nullable()->after('payment_method');
            }

            if (! Schema::hasColumn('aq_payouts', 'gateway_reference')) {
                $table->string('gateway_reference', 255)->nullable()->after('payment_reference');
            }

            if (! Schema::hasColumn('aq_payouts', 'gateway_response')) {
                $table->json('gateway_response')->nullable()->after('gateway_reference');
            }
        });
    }

    public function down(): void
    {
        Schema::table('aq_payouts', function (Blueprint $table) {
            if (Schema::hasColumn('aq_payouts', 'gateway_response')) {
                $table->dropColumn('gateway_response');
            }

            if (Schema::hasColumn('aq_payouts', 'gateway_reference')) {
                $table->dropColumn('gateway_reference');
            }

            if (Schema::hasColumn('aq_payouts', 'payment_provider')) {
                $table->dropColumn('payment_provider');
            }
        });
    }
};
