<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_conversion_goals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertiser_id')->index();
            $table->unsignedBigInteger('pixel_tracker_id')->nullable()->index();
            $table->string('name', 255);
            $table->string('goal_key', 100)->index();
            $table->enum('goal_type', ['purchase', 'lead', 'signup', 'install', 'pageview', 'custom'])->default('purchase');
            $table->decimal('default_value', 12, 4)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->enum('counting_method', ['every', 'once_per_click', 'once_per_user'])->default('every');
            $table->unsignedSmallInteger('attribution_window_days')->default(30);
            $table->string('status', 20)->default('active')->index();
            $table->text('description')->nullable();
            $table->boolean('is_deleted')->default(false)->index();
            $table->timestamps();

            $table->unique(['advertiser_id', 'goal_key'], 'uq_goal_advertiser_key');
            $table->foreign('advertiser_id')->references('id')->on('aq_users')->cascadeOnDelete();
            $table->foreign('pixel_tracker_id')->references('id')->on('aq_pixel_trackers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_conversion_goals');
    }
};
