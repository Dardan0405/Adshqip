<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('aq_case_studies')) {
            return;
        }

        Schema::create('aq_case_studies', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->string('title', 160);
            $table->enum('audience_type', ['publisher', 'advertiser', 'both'])->default('both');
            $table->string('industry', 100)->nullable();
            $table->string('metric_value', 40);
            $table->string('metric_label', 80)->nullable();
            $table->text('description');
            $table->string('company_name', 120);
            $table->string('logo_url', 500)->nullable();
            $table->string('accent_color', 20)->default('#e11d48');
            $table->enum('chart_type', ['comparison', 'line', 'bar'])->default('comparison');
            $table->string('before_label', 60)->nullable();
            $table->string('before_value', 60)->nullable();
            $table->string('after_label', 60)->nullable();
            $table->string('after_value', 60)->nullable();
            $table->string('cta_url', 500)->nullable();
            $table->boolean('is_published')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_case_studies');
    }
};
