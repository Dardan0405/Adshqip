<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE aq_users
            MODIFY role ENUM('admin', 'operational', 'advertiser', 'publisher', 'manager')
            NOT NULL DEFAULT 'advertiser'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE aq_users
            MODIFY role ENUM('admin', 'advertiser', 'publisher', 'manager')
            NOT NULL DEFAULT 'advertiser'
        ");
    }
};
