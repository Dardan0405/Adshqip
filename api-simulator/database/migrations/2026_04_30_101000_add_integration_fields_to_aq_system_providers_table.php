<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_system_providers', function (Blueprint $table) {
            $table->string('source', 60)->default('manual')->after('status');
            $table->string('source_key', 120)->nullable()->after('source');
            $table->timestamp('last_used_at')->nullable()->after('last_checked_at');

            $table->index(['source', 'source_key']);
            $table->index('last_used_at');
        });
    }

    public function down(): void
    {
        Schema::table('aq_system_providers', function (Blueprint $table) {
            $table->dropIndex(['source', 'source_key']);
            $table->dropIndex(['last_used_at']);
            $table->dropColumn(['source', 'source_key', 'last_used_at']);
        });
    }
};
