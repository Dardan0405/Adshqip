<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_direct_campaign_targeting', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id')->index('idx_campaign');
            $table->enum('targeting_type', ['geo_country','geo_region','geo_city','device','browser','os','language','carrier','connection_type','domain_whitelist','domain_blacklist','category','keyword','mail_domain','audience_segment','ip_range','retargeting','distribution_network','oem']);
            $table->enum('match_mode', ['include','exclude'])->default('include');
            $table->json('target_values')->comment('Array of values');
            $table->integer('priority')->default(0)->comment('Evaluation order');
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('targeting_type', 'idx_type');
            $table->foreign('campaign_id', 'fk_dct_campaign')->references('id')->on('aq_direct_campaigns')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_direct_campaign_targeting');
    }
};
