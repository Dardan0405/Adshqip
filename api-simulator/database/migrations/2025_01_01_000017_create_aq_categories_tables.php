<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('parent_id')->nullable()->index('idx_parent');
            $table->string('name', 100);
            $table->string('slug', 100)->unique('uk_slug');
            $table->string('iab_code', 20)->nullable()->comment('IAB category code for RTB');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('parent_id', 'fk_cat_parent')->references('id')->on('aq_categories')->onDelete('set null');
        });

        Schema::create('aq_campaign_category', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedInteger('category_id');
            $table->primary(['campaign_id', 'category_id']);
            $table->foreign('campaign_id', 'fk_cc_campaign')->references('id')->on('aq_campaigns')->onDelete('cascade');
            $table->foreign('category_id', 'fk_cc_category')->references('id')->on('aq_categories')->onDelete('cascade');
        });

        Schema::create('aq_site_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('site_id');
            $table->unsignedInteger('category_id');
            $table->primary(['site_id', 'category_id']);
            $table->foreign('site_id', 'fk_sc_site')->references('id')->on('aq_sites')->onDelete('cascade');
            $table->foreign('category_id', 'fk_sc_category')->references('id')->on('aq_categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_site_categories');
        Schema::dropIfExists('aq_campaign_category');
        Schema::dropIfExists('aq_categories');
    }
};
