<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_campaigns', function (Blueprint $table) {
            if (! Schema::hasColumn('aq_campaigns', 'admarket_enabled')) {
                $table->boolean('admarket_enabled')->default(false)->after('admin_approved')->index();
            }

            if (! Schema::hasColumn('aq_campaigns', 'admarket_status')) {
                $table->string('admarket_status', 30)->default('unlisted')->after('admarket_enabled')->index();
            }

            if (! Schema::hasColumn('aq_campaigns', 'admarket_notes')) {
                $table->text('admarket_notes')->nullable()->after('admarket_status');
            }

            if (! Schema::hasColumn('aq_campaigns', 'admarket_published_at')) {
                $table->dateTime('admarket_published_at')->nullable()->after('admarket_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('aq_campaigns', function (Blueprint $table) {
            if (Schema::hasColumn('aq_campaigns', 'admarket_published_at')) {
                $table->dropColumn('admarket_published_at');
            }

            if (Schema::hasColumn('aq_campaigns', 'admarket_notes')) {
                $table->dropColumn('admarket_notes');
            }

            if (Schema::hasColumn('aq_campaigns', 'admarket_status')) {
                $table->dropColumn('admarket_status');
            }

            if (Schema::hasColumn('aq_campaigns', 'admarket_enabled')) {
                $table->dropColumn('admarket_enabled');
            }
        });
    }
};
