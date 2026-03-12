<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_clip_campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->enum('campaign_source', ['rtb','direct'])->default('rtb');
            // Creative constraints
            $table->unsignedInteger('min_duration_seconds')->default(5);
            $table->unsignedInteger('max_duration_seconds')->default(60);
            $table->json('allowed_aspect_ratios')->nullable()->comment('Default: ["9:16"]');
            $table->boolean('require_sound')->default(true);
            $table->boolean('require_captions')->default(false);
            $table->boolean('auto_generate_captions')->default(true);
            $table->string('caption_language', 5)->default('sq');
            // Playback behavior
            $table->enum('autoplay_mode', ['always','wifi_only','never'])->default('always');
            $table->enum('sound_default', ['on','off','follow_device'])->default('on');
            $table->boolean('loop_enabled')->default(true);
            $table->unsignedInteger('max_loop_count')->nullable();
            // Swipe-up / CTA
            $table->enum('swipe_up_style', ['text_button','pill','gradient_bar','animated_arrow','custom'])->default('pill');
            $table->char('swipe_up_color', 7)->nullable();
            $table->enum('swipe_up_animation', ['none','pulse','slide_up','bounce'])->default('pulse');
            // Engagement features
            $table->boolean('polls_enabled')->default(false);
            $table->boolean('shoppable_enabled')->default(false);
            $table->boolean('stickers_enabled')->default(true);
            $table->string('hashtag_challenge', 255)->nullable();
            $table->text('hashtag_challenge_description')->nullable();
            // Delivery settings
            $table->enum('placement_type', ['in_feed','stories','discovery','pre_roll','all'])->default('in_feed')->index('idx_placement');
            $table->unsignedInteger('frequency_cap_per_user')->default(3);
            $table->unsignedInteger('viewability_threshold_pct')->default(50);
            $table->unsignedInteger('view_counted_at_seconds')->default(2);
            // Brand safety
            $table->enum('content_rating', ['G','PG','PG13','R'])->default('PG');
            $table->json('sensitive_categories_blocked')->nullable();
            // Performance
            $table->enum('optimization_goal', ['views','engagement','swipe_ups','conversions','reach'])->default('views')->index('idx_optimization');
            $table->decimal('target_completion_rate_pct', 5, 2)->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['campaign_id', 'campaign_source'], 'uk_campaign');
        });

        Schema::create('aq_clip_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->enum('category', ['product_showcase','testimonial','unboxing','tutorial','behind_the_scenes','announcement','sale_promo','challenge','storytelling','custom'])->default('product_showcase')->index('idx_category');
            $table->string('thumbnail_url', 500)->nullable();
            $table->string('preview_video_url', 500)->nullable();
            // Template structure
            $table->enum('aspect_ratio', ['9:16','4:5','1:1'])->default('9:16');
            $table->unsignedInteger('duration_seconds')->default(15);
            $table->unsignedInteger('scene_count')->default(1);
            $table->json('scene_config')->nullable();
            $table->enum('transition_style', ['cut','fade','slide_left','slide_up','zoom','glitch','none'])->default('cut');
            // Text & overlay presets
            $table->json('text_preset')->nullable();
            $table->json('cta_preset')->nullable();
            $table->json('sticker_presets')->nullable();
            // Music
            $table->string('suggested_music_genre', 50)->nullable();
            $table->json('suggested_music_bpm_range')->nullable();
            // Metadata
            $table->boolean('is_premium')->default(false);
            $table->unsignedBigInteger('usage_count')->default(0)->index('idx_usage');
            $table->decimal('avg_completion_rate', 5, 2)->nullable();
            $table->decimal('avg_ctr', 8, 4)->nullable();
            $table->boolean('is_active')->default(true)->index('idx_active');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('aq_clip_music_library', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255)->comment('Track title');
            $table->string('artist', 255)->nullable();
            $table->string('album', 255)->nullable();
            $table->enum('genre', ['pop','electronic','hip_hop','rock','indie','acoustic','cinematic','lofi','upbeat','chill','dramatic','ambient','folk','latin','balkan','custom'])->default('pop')->index('idx_genre');
            $table->enum('mood', ['happy','energetic','calm','dramatic','inspirational','funny','romantic','dark','neutral'])->default('energetic')->index('idx_mood');
            $table->unsignedInteger('bpm')->nullable()->index('idx_bpm');
            $table->unsignedInteger('duration_seconds');
            $table->string('audio_url', 500)->comment('CDN URL to the audio file');
            $table->string('preview_url', 500)->nullable();
            $table->string('waveform_url', 500)->nullable();
            // Licensing
            $table->enum('license_type', ['royalty_free','creative_commons','licensed','original','public_domain'])->default('royalty_free')->index('idx_license');
            $table->string('license_holder', 255)->nullable();
            $table->date('license_expires_at')->nullable();
            $table->json('usage_rights')->nullable();
            // Metadata
            $table->json('tags')->nullable();
            $table->boolean('is_trending')->default(false)->index('idx_trending');
            $table->unsignedBigInteger('usage_count')->default(0);
            $table->boolean('is_explicit')->default(false);
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->useCurrent();
        });

        Schema::create('aq_clip_interactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_id')->nullable()->index('idx_ad')->comment('FK → aq_ads');
            $table->unsignedBigInteger('campaign_id')->comment('FK');
            $table->enum('campaign_source', ['rtb','direct'])->default('rtb');
            $table->unsignedBigInteger('impression_id')->nullable()->index('idx_impression');
            // Interaction type
            $table->enum('interaction_type', ['view_start','view_25pct','view_50pct','view_75pct','view_complete','view_loop','swipe_up','tap_cta','tap_product','share','save','like','poll_vote','sound_on','sound_off','caption_toggle','long_press','screenshot'])->index('idx_type');
            $table->string('interaction_value', 500)->nullable();
            // Watch metrics
            $table->decimal('watch_duration_seconds', 6, 2)->nullable();
            $table->unsignedInteger('clip_total_seconds')->nullable();
            $table->unsignedInteger('loop_number')->default(1);
            $table->boolean('sound_was_on')->nullable();
            // Visitor context
            $table->string('cookie_id', 255)->nullable();
            $table->enum('device_type', ['mobile','tablet','desktop'])->nullable();
            $table->string('os', 50)->nullable();
            $table->char('country_code', 2)->nullable();
            $table->enum('placement', ['in_feed','stories','discovery','pre_roll'])->nullable();
            $table->dateTime('created_at')->useCurrent()->index('idx_created');

            $table->index(['campaign_id', 'campaign_source'], 'idx_campaign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_clip_interactions');
        Schema::dropIfExists('aq_clip_music_library');
        Schema::dropIfExists('aq_clip_templates');
        Schema::dropIfExists('aq_clip_campaigns');
    }
};
