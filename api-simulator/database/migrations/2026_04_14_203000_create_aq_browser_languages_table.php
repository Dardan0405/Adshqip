<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_browser_languages', function (Blueprint $table) {
            $table->id();
            $table->string('language_name', 100);
            $table->string('language_value', 20);
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index('language_name');
            $table->index('language_value');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_browser_languages');
    }
};
