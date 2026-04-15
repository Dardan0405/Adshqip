<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_browsers', function (Blueprint $table) {
            $table->id();
            $table->string('browser_name', 100);
            $table->string('browser_code', 100);
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index('browser_name');
            $table->index('browser_code');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_browsers');
    }
};
