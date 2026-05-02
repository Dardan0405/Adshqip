<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_user_subscriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('aq_user_subscriptions', 'gateway_subscription_id')) {
                $table->string('gateway_subscription_id', 255)->nullable()->after('gateway_txn_id')->index('idx_sub_gateway_subscription');
            }

            if (! Schema::hasColumn('aq_user_subscriptions', 'gateway_customer_id')) {
                $table->string('gateway_customer_id', 255)->nullable()->after('gateway_subscription_id')->index('idx_sub_gateway_customer');
            }

            if (! Schema::hasColumn('aq_user_subscriptions', 'auto_renew')) {
                $table->boolean('auto_renew')->default(true)->after('payment_reference');
            }

            if (! Schema::hasColumn('aq_user_subscriptions', 'next_renewal_at')) {
                $table->dateTime('next_renewal_at')->nullable()->after('current_period_end');
            }

            if (! Schema::hasColumn('aq_user_subscriptions', 'last_renewed_at')) {
                $table->dateTime('last_renewed_at')->nullable()->after('next_renewal_at');
            }

            if (! Schema::hasColumn('aq_user_subscriptions', 'renewal_attempts')) {
                $table->unsignedSmallInteger('renewal_attempts')->default(0)->after('last_renewed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('aq_user_subscriptions', function (Blueprint $table) {
            foreach ([
                'renewal_attempts',
                'last_renewed_at',
                'next_renewal_at',
                'auto_renew',
                'gateway_customer_id',
                'gateway_subscription_id',
            ] as $column) {
                if (Schema::hasColumn('aq_user_subscriptions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
