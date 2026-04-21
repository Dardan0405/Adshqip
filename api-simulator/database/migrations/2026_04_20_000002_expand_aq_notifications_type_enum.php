<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE aq_notifications MODIFY type ENUM('success','warning','error','info','payment','campaign','system','push','broadcast') NOT NULL DEFAULT 'info'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE aq_notifications MODIFY type ENUM('success','warning','error','info','payment','campaign','system') NOT NULL DEFAULT 'info'");
    }
};
