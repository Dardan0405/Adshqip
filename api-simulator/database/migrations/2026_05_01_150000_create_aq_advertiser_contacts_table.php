<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('aq_advertiser_contacts')) {
            return;
        }

        Schema::create('aq_advertiser_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index('idx_adv_contacts_user');
            $table->string('name', 150);
            $table->string('email', 255)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('company', 180)->nullable();
            $table->string('job_title', 150)->nullable();
            $table->enum('type', ['client', 'partner', 'agency', 'billing', 'technical', 'other'])->default('client')->index('idx_adv_contacts_type');
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active')->index('idx_adv_contacts_status');
            $table->boolean('is_primary')->default(false)->index('idx_adv_contacts_primary');
            $table->text('notes')->nullable();
            $table->dateTime('last_contacted_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('user_id', 'fk_adv_contacts_user')->references('id')->on('aq_users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_advertiser_contacts');
    }
};
