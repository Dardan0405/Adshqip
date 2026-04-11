<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_transactions', function (Blueprint $table) {
            $table->index(['type', 'status', 'completed_at'], 'idx_txn_type_status_completed');
            $table->index(['user_id', 'type', 'completed_at'], 'idx_txn_user_type_completed');
            $table->index(['payment_gateway', 'status'], 'idx_txn_gateway_status');
        });
    }

    public function down(): void
    {
        Schema::table('aq_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_txn_type_status_completed');
            $table->dropIndex('idx_txn_user_type_completed');
            $table->dropIndex('idx_txn_gateway_status');
        });
    }
};
