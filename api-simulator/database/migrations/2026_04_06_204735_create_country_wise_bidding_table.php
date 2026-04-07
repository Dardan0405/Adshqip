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
        Schema::create('country_wise_bidding', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertiser_id')->constrained('aq_users')->onDelete('cascade');
            $table->foreignId('campaign_id')->nullable()->constrained('aq_campaigns')->onDelete('cascade');
            $table->enum('type', ['CPC', 'CPM', 'CPA', 'CPV'])->default('CPC');
            $table->string('country_code', 2);
            $table->decimal('bid_value', 10, 2)->default(0.00);
            $table->timestamps();

            // Index for faster lookups
            $table->index(['advertiser_id', 'campaign_id', 'country_code'], 'cwb_adv_camp_country_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_wise_bidding');
    }
};
