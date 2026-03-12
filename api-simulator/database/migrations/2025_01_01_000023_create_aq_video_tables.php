<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_vast_events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('event_name', 50)->unique('uk_event')->comment('start, firstQuartile, midpoint, thirdQuartile, complete, skip, mute, unmute, pause, resume');
            $table->string('description', 255)->nullable();
            $table->boolean('is_trackable')->default(true);
            $table->dateTime('created_at')->useCurrent();
        });

        Schema::create('aq_video_tracking', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_id')->index('idx_ad');
            $table->unsignedBigInteger('impression_id')->nullable();
            $table->unsignedInteger('event_id')->index('idx_event');
            $table->string('viewer_id', 64);
            $table->unsignedTinyInteger('progress_percent')->nullable();
            $table->dateTime('created_at')->useCurrent()->index('idx_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_video_tracking');
        Schema::dropIfExists('aq_vast_events');
    }
};
