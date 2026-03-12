<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_ads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id')->index('idx_campaign');
            $table->string('name', 255)->comment('old: description');
            $table->enum('ad_type', ['image','html','video','text','rich_media','native','vast','motion','motion_studio','carousel','app_promotion','adshqipai_ad_maker','adshqipai_motion','adshqipai_motion_prompt','clip'])->default('image')->index('idx_type')->comment('old: storagetype');
            $table->enum('status', ['active','paused','pending_review','rejected','archived'])->default('pending_review')->index('idx_status');
            $table->string('destination_url', 2000)->comment('old: url');
            $table->string('display_url', 500)->nullable();
            $table->string('headline', 255)->nullable()->comment('For native/text ads');
            $table->string('headline_dki', 255)->nullable()->comment('DKI template');
            $table->string('headline_dki_default', 255)->nullable()->comment('Fallback text when no keyword matches');
            $table->text('body_text')->nullable()->comment('old: bannertext');
            $table->text('body_text_dki')->nullable()->comment('DKI-enabled body copy');
            $table->string('call_to_action', 50)->nullable()->comment('e.g. Shiko Tani, Mëso më shumë');
            $table->string('sponsored_label', 50)->default('sponsored')->comment('From native feed demo');
            // Campaign branding
            $table->string('brand_name', 100)->nullable()->comment('Advertiser brand name shown on ad');
            $table->string('brand_logo_url', 500)->nullable()->comment('Brand logo image URL');
            $table->string('brand_tagline', 255)->nullable()->comment('Short brand slogan shown below headline');
            $table->char('brand_color_primary', 7)->nullable()->comment('Hex color e.g. #FF5733');
            $table->char('brand_color_secondary', 7)->nullable()->comment('Secondary hex color');
            // Click-to-Watch (CTW) video settings
            $table->boolean('ctw_enabled')->default(false)->comment('Enable Click-to-Watch');
            $table->string('ctw_thumbnail_url', 500)->nullable()->comment('Thumbnail image shown before user clicks to watch');
            $table->unsignedInteger('ctw_min_watch_seconds')->default(5)->comment('Min seconds user must watch');
            $table->unsignedInteger('ctw_skip_after_seconds')->nullable()->comment('Allow skip after N seconds');
            $table->boolean('ctw_autoplay')->default(false)->comment('0 = user must click, 1 = autoplay muted');
            $table->boolean('ctw_muted_autoplay')->default(true)->comment('Start muted when autoplaying');
            // Video branding overlay
            $table->string('video_brand_logo_url', 500)->nullable()->comment('Logo watermark overlaid on video player');
            $table->enum('video_brand_logo_position', ['top_left','top_right','bottom_left','bottom_right'])->default('bottom_right');
            $table->decimal('video_brand_logo_opacity', 3, 2)->default(0.80)->comment('Logo opacity 0.00-1.00');
            $table->string('video_brand_intro_url', 500)->nullable()->comment('Short brand intro bumper video URL');
            $table->unsignedInteger('video_brand_intro_duration')->nullable()->comment('Intro bumper duration in seconds');
            // End Card settings
            $table->boolean('end_card_enabled')->default(false)->comment('Show end card after video completes');
            $table->enum('end_card_type', ['static_image','html','cta_button','product_feed','custom'])->default('cta_button');
            $table->string('end_card_image_url', 500)->nullable();
            $table->text('end_card_html')->nullable()->comment('Custom HTML for end card');
            $table->string('end_card_headline', 255)->nullable();
            $table->text('end_card_body')->nullable();
            $table->string('end_card_cta_text', 50)->nullable();
            $table->string('end_card_cta_url', 2000)->nullable();
            $table->char('end_card_cta_color', 7)->nullable();
            $table->unsignedInteger('end_card_display_seconds')->default(10);
            $table->string('end_card_logo_url', 500)->nullable();
            // Clip Ad settings
            $table->boolean('clip_enabled')->default(false)->comment('This ad is a Clip');
            $table->string('clip_video_url', 500)->nullable();
            $table->string('clip_thumbnail_url', 500)->nullable();
            $table->unsignedInteger('clip_duration_seconds')->nullable();
            $table->enum('clip_aspect_ratio', ['9:16','4:5','1:1'])->default('9:16');
            $table->enum('clip_sound_default', ['on','off'])->default('on');
            $table->boolean('clip_autoplay')->default(true);
            $table->boolean('clip_loop')->default(true);
            $table->boolean('clip_swipe_up_enabled')->default(true);
            $table->string('clip_swipe_up_text', 100)->default('Shiko Më Shumë');
            $table->string('clip_swipe_up_url', 2000)->nullable();
            $table->text('clip_caption')->nullable();
            $table->json('clip_hashtags')->nullable();
            $table->unsignedBigInteger('clip_music_track_id')->nullable()->comment('FK → aq_clip_music_library');
            $table->json('clip_sticker_overlays')->nullable();
            $table->json('clip_text_overlays')->nullable();
            $table->json('clip_interactive_poll')->nullable();
            $table->json('clip_shoppable_products')->nullable();
            // adshqipAI
            $table->enum('adshqipai_type', ['none','ad_maker','motion','motion_prompt'])->default('none');
            $table->text('adshqipai_prompt')->nullable();
            $table->string('adshqipai_style', 100)->nullable();
            $table->string('adshqipai_motion_template', 100)->nullable();
            $table->string('adshqipai_generated_asset_url', 500)->nullable();
            $table->string('adshqipai_generation_id', 128)->nullable();
            $table->string('adshqipai_model_version', 50)->nullable();
            $table->boolean('adshqipai_is_edited')->default(false);
            $table->boolean('admin_approved')->default(false)->comment('old: dj_admin_approve');
            $table->boolean('is_deleted')->default(false);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('campaign_id', 'fk_ad_campaign')->references('id')->on('aq_campaigns')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_ads');
    }
};
