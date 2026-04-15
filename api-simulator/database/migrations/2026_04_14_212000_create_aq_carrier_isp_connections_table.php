<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_carrier_isp_connections', function (Blueprint $table) {
            $table->id();
            $table->string('carrier_name', 100);
            $table->string('start_ip', 45);
            $table->string('end_ip', 45);
            $table->string('country', 10);
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index('carrier_name');
            $table->index('country');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_carrier_isp_connections');
    }
};
