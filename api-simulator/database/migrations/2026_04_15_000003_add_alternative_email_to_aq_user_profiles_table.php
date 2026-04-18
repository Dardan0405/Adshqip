<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_user_profiles', function (Blueprint $table) {
            $table->string('alternative_email', 255)->nullable()->after('last_name')->unique('uk_aq_user_profiles_alternative_email');
        });
    }

    public function down(): void
    {
        Schema::table('aq_user_profiles', function (Blueprint $table) {
            $table->dropUnique('uk_aq_user_profiles_alternative_email');
            $table->dropColumn('alternative_email');
        });
    }
};
