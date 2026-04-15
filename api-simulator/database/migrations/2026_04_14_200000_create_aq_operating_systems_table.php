<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_operating_systems', function (Blueprint $table) {
            $table->id();
            $table->string('os_name', 100);
            $table->string('os_value', 100);
            $table->json('devices');
            $table->timestamps();

            $table->index('os_name');
            $table->index('os_value');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_operating_systems');
    }
};
