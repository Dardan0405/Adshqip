<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_connection_types', function (Blueprint $table) {
            $table->id();
            $table->string('connection_name', 100);
            $table->string('connection_value', 50);
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index('connection_name');
            $table->index('connection_value');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_connection_types');
    }
};
