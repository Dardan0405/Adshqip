<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_users', function (Blueprint $table) {
            $table->json('two_factor_verification_options')->nullable()->after('two_factor_secret');
            $table->json('two_factor_token_types')->nullable()->after('two_factor_verification_options');
            $table->string('two_factor_phone', 30)->nullable()->after('two_factor_token_types');
            $table->string('two_factor_email', 255)->nullable()->after('two_factor_phone');
            $table->string('two_factor_backup_question', 255)->nullable()->after('two_factor_email');
            $table->string('two_factor_backup_answer_hash', 255)->nullable()->after('two_factor_backup_question');
            $table->string('two_factor_trusted_ip', 45)->nullable()->after('two_factor_backup_answer_hash');
            $table->string('two_factor_trusted_subnet', 64)->nullable()->after('two_factor_trusted_ip');
            $table->string('two_factor_trusted_browser', 100)->nullable()->after('two_factor_trusted_subnet');
            $table->string('two_factor_trusted_os', 100)->nullable()->after('two_factor_trusted_browser');
            $table->string('two_factor_trusted_user_agent_hash', 64)->nullable()->after('two_factor_trusted_os');
        });
    }

    public function down(): void
    {
        Schema::table('aq_users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_verification_options',
                'two_factor_token_types',
                'two_factor_phone',
                'two_factor_email',
                'two_factor_backup_question',
                'two_factor_backup_answer_hash',
                'two_factor_trusted_ip',
                'two_factor_trusted_subnet',
                'two_factor_trusted_browser',
                'two_factor_trusted_os',
                'two_factor_trusted_user_agent_hash',
            ]);
        });
    }
};
