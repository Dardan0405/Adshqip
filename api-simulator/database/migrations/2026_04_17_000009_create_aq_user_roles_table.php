<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_user_roles', function (Blueprint $table) {
            $table->id();
            $table->string('role_key', 80)->unique();
            $table->string('role_name', 120);
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        DB::table('aq_user_roles')->insert([
            [
                'role_key' => 'admin',
                'role_name' => 'Admin',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_key' => 'operational',
                'role_name' => 'Operational',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_key' => 'advertiser_manager',
                'role_name' => 'Advertiser Manager',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_key' => 'publisher_manager',
                'role_name' => 'Publisher Manager',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_user_roles');
    }
};
