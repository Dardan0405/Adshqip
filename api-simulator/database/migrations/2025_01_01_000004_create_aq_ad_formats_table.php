<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_ad_formats', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 50)->unique('uk_slug')->comment('popunder, native_feed, interstitial, in_page_push, text_banner, native_video, rich_media');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->enum('category', ['high_impact', 'user_friendly', 'premium'])->default('user_friendly');
            $table->decimal('ecpm_avg', 8, 2)->nullable()->comment('Average eCPM shown on landing page');
            $table->decimal('fill_rate_avg', 5, 2)->nullable()->comment('Average fill rate %');
            $table->decimal('ctr_avg', 5, 2)->nullable();
            $table->boolean('supports_mobile')->default(true);
            $table->boolean('supports_desktop')->default(true);
            $table->boolean('supports_amp')->default(false);
            $table->boolean('gdpr_ready')->default(true);
            $table->decimal('performance_rating', 2, 1)->nullable()->comment('1.0-5.0 star rating');
            $table->boolean('is_new')->default(false)->comment('Rich Media has NEW badge');
            $table->enum('status', ['active', 'beta', 'deprecated'])->default('active');
            $table->integer('sort_order')->default(0);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_ad_formats');
    }
};
