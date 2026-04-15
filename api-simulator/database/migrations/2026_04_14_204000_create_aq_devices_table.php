<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_name', 100);
            $table->string('device_value', 50);
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index('device_name');
            $table->index('device_value');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_devices');
    }
};
