<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('aq_case_studies')) {
            return;
        }

        Schema::table('aq_case_studies', function (Blueprint $table) {
            if (!Schema::hasColumn('aq_case_studies', 'slug')) {
                $table->string('slug', 80)->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('aq_case_studies', 'title')) {
                $table->string('title', 160)->after('slug');
            }
            if (!Schema::hasColumn('aq_case_studies', 'audience_type')) {
                $table->enum('audience_type', ['publisher', 'advertiser', 'both'])->default('both')->after('title');
            }
            if (!Schema::hasColumn('aq_case_studies', 'industry')) {
                $table->string('industry', 100)->nullable()->after('audience_type');
            }
            if (!Schema::hasColumn('aq_case_studies', 'metric_value')) {
                $table->string('metric_value', 40)->default('')->after('industry');
            }
            if (!Schema::hasColumn('aq_case_studies', 'metric_label')) {
                $table->string('metric_label', 80)->nullable()->after('metric_value');
            }
            if (!Schema::hasColumn('aq_case_studies', 'description')) {
                $table->text('description')->after('metric_label');
            }
            if (!Schema::hasColumn('aq_case_studies', 'company_name')) {
                $table->string('company_name', 120)->default('')->after('description');
            }
            if (!Schema::hasColumn('aq_case_studies', 'logo_url')) {
                $table->string('logo_url', 500)->nullable()->after('company_name');
            }
            if (!Schema::hasColumn('aq_case_studies', 'accent_color')) {
                $table->string('accent_color', 20)->default('#e11d48')->after('logo_url');
            }
            if (!Schema::hasColumn('aq_case_studies', 'chart_type')) {
                $table->enum('chart_type', ['comparison', 'line', 'bar'])->default('comparison')->after('accent_color');
            }
            if (!Schema::hasColumn('aq_case_studies', 'before_label')) {
                $table->string('before_label', 60)->nullable()->after('chart_type');
            }
            if (!Schema::hasColumn('aq_case_studies', 'before_value')) {
                $table->string('before_value', 60)->nullable()->after('before_label');
            }
            if (!Schema::hasColumn('aq_case_studies', 'after_label')) {
                $table->string('after_label', 60)->nullable()->after('before_value');
            }
            if (!Schema::hasColumn('aq_case_studies', 'after_value')) {
                $table->string('after_value', 60)->nullable()->after('after_label');
            }
            if (!Schema::hasColumn('aq_case_studies', 'cta_url')) {
                $table->string('cta_url', 500)->nullable()->after('after_value');
            }
            if (!Schema::hasColumn('aq_case_studies', 'is_published')) {
                $table->boolean('is_published')->default(true)->after('cta_url');
            }
            if (!Schema::hasColumn('aq_case_studies', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_published');
            }
            if (!Schema::hasColumn('aq_case_studies', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('aq_case_studies', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Intentionally non-destructive: this migration upgrades an existing table.
    }
};
