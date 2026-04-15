<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_keywords', function (Blueprint $table) {
            $table->id();
            $table->string('keyword', 255);
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index('keyword');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_keywords');
    }
};
