<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('aq_api_keys') || Schema::hasColumn('aq_api_keys', 'api_secret_encrypted')) {
            return;
        }

        Schema::table('aq_api_keys', function (Blueprint $table) {
            $table->text('api_secret_encrypted')->nullable()->after('api_secret_hash');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('aq_api_keys') || !Schema::hasColumn('aq_api_keys', 'api_secret_encrypted')) {
            return;
        }

        Schema::table('aq_api_keys', function (Blueprint $table) {
            $table->dropColumn('api_secret_encrypted');
        });
    }
};
