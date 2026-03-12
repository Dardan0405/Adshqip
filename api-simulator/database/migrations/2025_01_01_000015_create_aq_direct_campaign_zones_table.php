<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_direct_campaign_zones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('zone_id')->index('idx_zone');
            $table->integer('priority')->default(0);
            $table->decimal('floor_price_override', 10, 4)->nullable()->comment('Override zone floor for this deal');
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->useCurrent();

            $table->unique(['campaign_id', 'zone_id'], 'uk_campaign_zone');
            $table->foreign('campaign_id', 'fk_dcz_campaign')->references('id')->on('aq_direct_campaigns')->onDelete('cascade');
            $table->foreign('zone_id', 'fk_dcz_zone')->references('id')->on('aq_zones')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_direct_campaign_zones');
    }
};
