<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_mobile_capabilities', function (Blueprint $table) {
            $table->id();
            $table->string('capability_name', 100);
            $table->string('capability_value', 50);
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index('capability_name');
            $table->index('capability_value');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_mobile_capabilities');
    }
};
