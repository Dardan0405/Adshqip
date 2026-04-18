<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_display_screens', function (Blueprint $table) {
            $table->id();
            $table->string('screen_name', 120);
            $table->string('value', 120)->unique();
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->enum('status', ['active', 'blocked'])->default('active')->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_display_screens');
    }
};
