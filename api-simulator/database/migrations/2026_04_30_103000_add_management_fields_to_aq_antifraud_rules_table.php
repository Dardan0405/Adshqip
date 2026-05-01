<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_antifraud_rules', function (Blueprint $table) {
            if (! Schema::hasColumn('aq_antifraud_rules', 'match_value')) {
                $table->text('match_value')->nullable()->after('rule_type');
            }
            if (! Schema::hasColumn('aq_antifraud_rules', 'severity')) {
                $table->string('severity', 20)->default('medium')->after('action');
            }
            if (! Schema::hasColumn('aq_antifraud_rules', 'description')) {
                $table->text('description')->nullable()->after('severity');
            }
            if (! Schema::hasColumn('aq_antifraud_rules', 'last_triggered_at')) {
                $table->timestamp('last_triggered_at')->nullable()->after('is_active');
            }
            if (! Schema::hasColumn('aq_antifraud_rules', 'triggered_count')) {
                $table->unsignedBigInteger('triggered_count')->default(0)->after('last_triggered_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('aq_antifraud_rules', function (Blueprint $table) {
            $columns = ['match_value', 'severity', 'description', 'last_triggered_at', 'triggered_count'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('aq_antifraud_rules', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
