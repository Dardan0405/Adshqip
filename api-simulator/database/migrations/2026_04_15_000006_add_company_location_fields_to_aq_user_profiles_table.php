<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_user_profiles', function (Blueprint $table) {
            $table->string('company_address_line1', 255)->nullable()->after('company_name');
            $table->string('company_address_line2', 255)->nullable()->after('company_address_line1');
            $table->string('company_city', 100)->nullable()->after('company_address_line2');
            $table->string('company_state_region', 100)->nullable()->after('company_city');
            $table->char('company_country_code', 2)->nullable()->after('company_state_region');
        });
    }

    public function down(): void
    {
        Schema::table('aq_user_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'company_address_line1',
                'company_address_line2',
                'company_city',
                'company_state_region',
                'company_country_code',
            ]);
        });
    }
};
