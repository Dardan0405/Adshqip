<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_campaign_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('advertiser_id')->nullable()->index('idx_advertiser');
            $table->enum('status', ['active', 'paused', 'archived'])->default('active');
            $table->integer('campaign_count')->default(0)->comment('Number of campaigns in this group');
            $table->decimal('total_budget', 12, 4)->nullable()->comment('Combined budget across all campaigns');
            $table->string('color', 20)->nullable()->comment('UI color for group badge');
            $table->boolean('is_deleted')->default(false);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('advertiser_id', 'fk_group_advertiser')->references('id')->on('aq_users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_campaign_groups');
    }
};
