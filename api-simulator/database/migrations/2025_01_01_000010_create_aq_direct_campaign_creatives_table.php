<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_direct_campaign_creatives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id')->index('idx_campaign');
            $table->string('variant_label', 50)->default('A')->index('idx_variant')->comment('A, B, C… for A/B testing');
            $table->enum('creative_type', ['image','html','video','text','rich_media','native','vast','clip'])->default('image');
            $table->string('file_path', 500)->nullable();
            $table->enum('file_type', ['image','video','html5','gif'])->default('image');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('file_size_bytes')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->integer('duration_seconds')->nullable()->comment('For video creatives');
            $table->string('alt_text', 255)->nullable();
            $table->string('headline', 255)->nullable()->comment('Override campaign-level headline');
            $table->text('body_text')->nullable()->comment('Override campaign-level body');
            $table->string('call_to_action', 50)->nullable();
            $table->string('destination_url', 2000)->nullable()->comment('Override campaign-level URL');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_winner')->default(false)->comment('A/B test winner flag');
            $table->enum('status', ['active','paused','pending_review','rejected','archived'])->default('pending_review')->index('idx_status');
            $table->boolean('admin_approved')->default(false);
            $table->unsignedBigInteger('impressions')->default(0)->comment('Running count for A/B split');
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedInteger('conversions')->default(0);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('campaign_id', 'fk_dcc_campaign')->references('id')->on('aq_direct_campaigns')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_direct_campaign_creatives');
    }
};
