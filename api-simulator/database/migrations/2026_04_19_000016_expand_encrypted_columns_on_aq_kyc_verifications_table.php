<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE aq_kyc_verifications MODIFY id_number TEXT NULL');
        DB::statement('ALTER TABLE aq_kyc_verifications MODIFY business_registration_number TEXT NULL');
        DB::statement('ALTER TABLE aq_kyc_verifications MODIFY vat_number TEXT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE aq_kyc_verifications MODIFY id_number VARCHAR(100) NULL');
        DB::statement('ALTER TABLE aq_kyc_verifications MODIFY business_registration_number VARCHAR(100) NULL');
        DB::statement('ALTER TABLE aq_kyc_verifications MODIFY vat_number VARCHAR(50) NULL');
    }
};
