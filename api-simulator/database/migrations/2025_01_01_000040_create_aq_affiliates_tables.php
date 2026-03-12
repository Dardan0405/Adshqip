<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_affiliates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by')->index('idx_created_by')->comment('FK → aq_users (role=admin) who created this affiliate');
            $table->string('title', 255)->comment('Affiliate program title');
            $table->string('slug', 255)->unique('uk_slug')->comment('URL-safe slug');
            $table->text('description')->nullable();
            $table->string('short_description', 500)->nullable();
            $table->string('domain', 255)->index('idx_domain')->comment('Affiliate domain / website');
            $table->string('cover_image_url', 500)->nullable();
            // Status & visibility
            $table->enum('status', ['draft','active','paused','expired','archived'])->default('draft')->index('idx_status');
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            // Metadata
            $table->json('tags')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['is_featured', 'sort_order'], 'idx_featured');
            $table->foreign('created_by', 'fk_affiliate_created_by')->references('id')->on('aq_users')->onDelete('cascade');
        });

        Schema::create('aq_affiliate_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliate_id')->index('idx_affiliate');
            $table->string('image_url', 500)->comment('Image file URL');
            $table->string('alt_text', 255)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('affiliate_id', 'fk_afimg_affiliate')->references('id')->on('aq_affiliates')->onDelete('cascade');
        });

        Schema::create('aq_affiliate_countries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliate_id');
            $table->char('country_code', 2)->index('idx_country')->comment('ISO 3166-1 alpha-2 code');
            $table->dateTime('created_at')->useCurrent();

            $table->unique(['affiliate_id', 'country_code'], 'uk_affiliate_country');
            $table->foreign('affiliate_id', 'fk_afcountry_affiliate')->references('id')->on('aq_affiliates')->onDelete('cascade');
            $table->foreign('country_code', 'fk_afcountry_country')->references('iso_code')->on('aq_geo_countries')->onDelete('cascade');
        });

        Schema::create('aq_affiliate_ad_formats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliate_id');
            $table->unsignedInteger('format_id')->index('idx_format')->comment('FK → aq_ad_formats');
            $table->dateTime('created_at')->useCurrent();

            $table->unique(['affiliate_id', 'format_id'], 'uk_affiliate_format');
            $table->foreign('affiliate_id', 'fk_afformat_affiliate')->references('id')->on('aq_affiliates')->onDelete('cascade');
            $table->foreign('format_id', 'fk_afformat_format')->references('id')->on('aq_ad_formats')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_affiliate_ad_formats');
        Schema::dropIfExists('aq_affiliate_countries');
        Schema::dropIfExists('aq_affiliate_images');
        Schema::dropIfExists('aq_affiliates');
    }
};
