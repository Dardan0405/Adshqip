<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aq_traffic_source_bidding', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('traffic_source_id');
            $table->decimal('bid_rate', 10, 2)->default(0.00);
            $table->enum('status', ['active', 'paused'])->default('active');
            $table->unsignedBigInteger('campaign_id');
            $table->string('campaign_type', 10)->default('network')->comment('network or direct');
            $table->timestamps();

            // Prevent duplicate source per campaign
            $table->unique(['campaign_id', 'traffic_source_id', 'campaign_type'], 'tsb_camp_source_unique');

            $table->foreign('traffic_source_id', 'fk_tsb_source')->references('id')->on('aq_traffic_sources')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aq_traffic_source_bidding');
    }
};
