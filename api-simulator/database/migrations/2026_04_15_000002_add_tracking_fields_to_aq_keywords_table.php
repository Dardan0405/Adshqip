<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_keywords', function (Blueprint $table) {
            $table->string('category', 100)->nullable()->after('keyword');
            $table->text('description')->nullable()->after('category');
            $table->unsignedBigInteger('match_count')->default(0)->after('status');
            $table->timestamp('last_matched_at')->nullable()->after('match_count');

            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::table('aq_keywords', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn(['category', 'description', 'match_count', 'last_matched_at']);
        });
    }
};
