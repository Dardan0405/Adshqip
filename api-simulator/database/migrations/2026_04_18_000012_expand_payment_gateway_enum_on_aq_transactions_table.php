<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE aq_transactions
            MODIFY payment_gateway ENUM('stripe','paypal','coinbase','wire_transfer','manual','authorize','bitcoin') NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            UPDATE aq_transactions
            SET payment_gateway = 'wire_transfer'
            WHERE payment_gateway IN ('authorize','bitcoin')
        ");

        DB::statement("
            ALTER TABLE aq_transactions
            MODIFY payment_gateway ENUM('stripe','paypal','coinbase','wire_transfer','manual') NULL
        ");
    }
};
