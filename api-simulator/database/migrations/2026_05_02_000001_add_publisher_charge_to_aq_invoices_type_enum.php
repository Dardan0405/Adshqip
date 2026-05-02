<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE aq_invoices
            MODIFY type ENUM('advertiser_charge','publisher_payout','publisher_charge')
        ");
    }

    public function down(): void
    {
        DB::statement("
            UPDATE aq_invoices
            SET type = 'publisher_payout'
            WHERE type = 'publisher_charge'
        ");

        DB::statement("
            ALTER TABLE aq_invoices
            MODIFY type ENUM('advertiser_charge','publisher_payout')
        ");
    }
};
