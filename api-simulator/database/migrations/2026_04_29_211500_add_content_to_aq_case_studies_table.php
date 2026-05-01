<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('aq_case_studies') || Schema::hasColumn('aq_case_studies', 'content')) {
            return;
        }

        Schema::table('aq_case_studies', function (Blueprint $table) {
            $table->longText('content')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('aq_case_studies') || !Schema::hasColumn('aq_case_studies', 'content')) {
            return;
        }

        Schema::table('aq_case_studies', function (Blueprint $table) {
            $table->dropColumn('content');
        });
    }
};
