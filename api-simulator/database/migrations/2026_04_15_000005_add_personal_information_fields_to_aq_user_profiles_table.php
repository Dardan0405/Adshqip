<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_user_profiles', function (Blueprint $table) {
            $table->string('gender', 30)->nullable()->after('last_name');
            $table->date('date_of_birth')->nullable()->after('gender');
            $table->string('mobile_number', 30)->nullable()->after('date_of_birth');
        });
    }

    public function down(): void
    {
        Schema::table('aq_user_profiles', function (Blueprint $table) {
            $table->dropColumn(['gender', 'date_of_birth', 'mobile_number']);
        });
    }
};
