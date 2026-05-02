<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('aq_invoices', 'type')) {
                return;
            }

            DB::statement("ALTER TABLE aq_invoices MODIFY type ENUM('advertiser_charge','publisher_payout','publisher_charge','subscription_charge')");
        });

        Schema::table('aq_user_subscriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('aq_user_subscriptions', 'status')) {
                return;
            }

            DB::statement("ALTER TABLE aq_user_subscriptions MODIFY status ENUM('pending','active','cancelled','expired','trial')");

            if (! Schema::hasColumn('aq_user_subscriptions', 'invoice_id')) {
                $table->unsignedBigInteger('invoice_id')->nullable()->after('plan_id');
            }

            if (! Schema::hasColumn('aq_user_subscriptions', 'payment_gateway')) {
                $table->string('payment_gateway', 50)->nullable()->after('billing_cycle');
            }

            if (! Schema::hasColumn('aq_user_subscriptions', 'gateway_txn_id')) {
                $table->string('gateway_txn_id', 255)->nullable()->after('payment_gateway');
            }

            if (! Schema::hasColumn('aq_user_subscriptions', 'gateway_status')) {
                $table->string('gateway_status', 50)->nullable()->after('gateway_txn_id');
            }

            if (! Schema::hasColumn('aq_user_subscriptions', 'gateway_response')) {
                $table->json('gateway_response')->nullable()->after('gateway_status');
            }

            if (! Schema::hasColumn('aq_user_subscriptions', 'payment_reference')) {
                $table->string('payment_reference', 255)->nullable()->after('gateway_response');
            }
        });

        Schema::table('aq_user_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('aq_user_subscriptions', 'invoice_id')) {
                $table->foreign('invoice_id', 'fk_sub_invoice')->references('id')->on('aq_invoices')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('aq_user_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('aq_user_subscriptions', 'invoice_id')) {
                $table->dropForeign('fk_sub_invoice');
                $table->dropColumn('invoice_id');
            }

            foreach (['payment_reference', 'gateway_response', 'gateway_status', 'gateway_txn_id', 'payment_gateway'] as $column) {
                if (Schema::hasColumn('aq_user_subscriptions', $column)) {
                    $table->dropColumn($column);
                }
            }

            DB::statement("ALTER TABLE aq_user_subscriptions MODIFY status ENUM('active','cancelled','expired','trial')");
        });

        DB::statement("ALTER TABLE aq_invoices MODIFY type ENUM('advertiser_charge','publisher_payout','publisher_charge')");
    }
};
