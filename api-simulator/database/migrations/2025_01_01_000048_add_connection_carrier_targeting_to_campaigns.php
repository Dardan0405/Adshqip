<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_campaigns', function (Blueprint $table) {
            $table->json('targeting_connection_type')->nullable()->after('targeting_city')->comment('Connection type targeting (wifi, 3g, 4g, 5g)');
            $table->json('targeting_carrier')->nullable()->after('targeting_connection_type')->comment('Mobile carrier targeting (array of carrier names)');
        });
    }

    public function down(): void
    {
        Schema::table('aq_campaigns', function (Blueprint $table) {
            $table->dropColumn(['targeting_connection_type', 'targeting_carrier']);
        });
    }
};
