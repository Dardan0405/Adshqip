<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_ad_creatives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_id')->index('idx_ad');
            $table->string('file_path', 500)->comment('old: file_path');
            $table->enum('file_type', ['image', 'video', 'html5', 'gif'])->default('image');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('file_size_bytes')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->integer('duration_seconds')->nullable()->comment('For video ads');
            $table->string('alt_text', 255)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('ad_id', 'fk_creative_ad')->references('id')->on('aq_ads')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_ad_creatives');
    }
};
