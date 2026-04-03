<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('aq_direct_campaign_clicks')) {
            return;
        }

        Schema::create('aq_direct_campaign_clicks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id')->index('idx_dc_click_campaign');
            $table->unsignedBigInteger('creative_id')->nullable()->index('idx_dc_click_creative');
            $table->string('viewer_id', 36)->nullable()->index('idx_dc_click_viewer');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->char('country_code', 2)->nullable();
            $table->enum('device_type', ['desktop', 'mobile', 'tablet'])->nullable();
            $table->boolean('is_unique')->default(true);
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('campaign_id', 'fk_dcc_campaign')
                ->references('id')->on('aq_direct_campaigns')
                ->onDelete('cascade');
            $table->foreign('creative_id', 'fk_dcc_creative')
                ->references('id')->on('aq_direct_campaign_creatives')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_direct_campaign_clicks');
    }
};
