<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_users', function (Blueprint $table) {
            $table->unsignedBigInteger('account_manager_id')->nullable()->after('telegram_username');
            $table->foreign('account_manager_id')
                ->references('id')
                ->on('aq_users')
                ->nullOnDelete();
            $table->index('account_manager_id');
        });
    }

    public function down(): void
    {
        Schema::table('aq_users', function (Blueprint $table) {
            $table->dropForeign(['account_manager_id']);
            $table->dropIndex(['account_manager_id']);
            $table->dropColumn('account_manager_id');
        });
    }
};
