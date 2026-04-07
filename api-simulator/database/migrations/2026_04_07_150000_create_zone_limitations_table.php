<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zone_limitations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertiser_id');
            $table->string('name');
            $table->enum('type', ['adblock_whitelist', 'adblock_blacklist']);
            $table->json('zone_ids')->nullable();
            $table->timestamps();

            $table->foreign('advertiser_id')->references('id')->on('aq_users')->onDelete('cascade');
            $table->index(['advertiser_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zone_limitations');
    }
};
