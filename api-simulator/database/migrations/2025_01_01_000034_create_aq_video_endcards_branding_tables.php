<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_video_end_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertiser_id')->index('idx_advertiser')->comment('FK → aq_users (owner)');
            $table->string('name', 255)->comment('Template name');
            $table->enum('end_card_type', ['static_image','html','cta_button','product_feed','custom'])->default('cta_button');
            $table->string('image_url', 500)->nullable();
            $table->text('html_content')->nullable()->comment('Custom HTML (sanitized server-side)');
            $table->string('headline', 255)->nullable();
            $table->text('body_text')->nullable();
            $table->string('cta_text', 50)->nullable();
            $table->string('cta_url', 2000)->nullable();
            $table->char('cta_color', 7)->nullable();
            $table->string('logo_url', 500)->nullable();
            $table->char('background_color', 7)->default('#FFFFFF');
            $table->unsignedInteger('display_seconds')->default(10);
            $table->boolean('is_default')->default(false);
            $table->enum('status', ['active','inactive','archived'])->default('active')->index('idx_status');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('advertiser_id', 'fk_endcard_advertiser')->references('id')->on('aq_users')->onDelete('cascade');
        });

        Schema::create('aq_ad_end_card_assoc', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_id')->comment('FK → aq_ads');
            $table->unsignedBigInteger('end_card_id')->index('idx_endcard')->comment('FK → aq_video_end_cards');
            $table->integer('weight')->default(1)->comment('Rotation weight for A/B testing');
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->useCurrent();

            $table->unique(['ad_id', 'end_card_id'], 'uk_ad_endcard');
            $table->foreign('ad_id', 'fk_aec_ad')->references('id')->on('aq_ads')->onDelete('cascade');
            $table->foreign('end_card_id', 'fk_aec_endcard')->references('id')->on('aq_video_end_cards')->onDelete('cascade');
        });

        Schema::create('aq_video_branding_overlays', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertiser_id')->index('idx_advertiser')->comment('FK → aq_users (owner)');
            $table->string('name', 255)->comment('Preset name');
            $table->string('logo_url', 500)->comment('Logo watermark image URL');
            $table->enum('logo_position', ['top_left','top_right','bottom_left','bottom_right'])->default('bottom_right');
            $table->decimal('logo_opacity', 3, 2)->default(0.80);
            $table->unsignedTinyInteger('logo_size_percent')->default(15)->comment('Logo size as % of player width');
            $table->string('intro_video_url', 500)->nullable()->comment('Brand intro bumper video (3-5s)');
            $table->unsignedInteger('intro_duration_seconds')->nullable();
            $table->string('outro_video_url', 500)->nullable();
            $table->unsignedInteger('outro_duration_seconds')->nullable();
            $table->char('color_border', 7)->nullable();
            $table->boolean('is_default')->default(false);
            $table->enum('status', ['active','inactive','archived'])->default('active');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('advertiser_id', 'fk_branding_advertiser')->references('id')->on('aq_users')->onDelete('cascade');
        });

        Schema::create('aq_ad_branding_overlay_assoc', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_id');
            $table->unsignedBigInteger('overlay_id')->index('idx_overlay');
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->useCurrent();

            $table->unique(['ad_id', 'overlay_id'], 'uk_ad_overlay');
            $table->foreign('ad_id', 'fk_abo_ad')->references('id')->on('aq_ads')->onDelete('cascade');
            $table->foreign('overlay_id', 'fk_abo_overlay')->references('id')->on('aq_video_branding_overlays')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_ad_branding_overlay_assoc');
        Schema::dropIfExists('aq_video_branding_overlays');
        Schema::dropIfExists('aq_ad_end_card_assoc');
        Schema::dropIfExists('aq_video_end_cards');
    }
};
