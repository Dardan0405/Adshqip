<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_advertiser_team_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_advertiser_id')->index('idx_owner');
            $table->unsignedBigInteger('user_id')->nullable()->index('idx_user');
            $table->string('email', 255)->index('idx_email');
            $table->string('name', 255)->nullable();
            $table->enum('team_role', ['owner', 'admin', 'manager', 'analyst', 'billing', 'viewer'])->default('viewer');
            $table->json('permissions')->nullable();
            $table->enum('status', ['pending', 'active', 'disabled'])->default('pending')->index('idx_status');
            $table->unsignedBigInteger('invited_by')->nullable()->index('idx_invited_by');
            $table->dateTime('accepted_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['owner_advertiser_id', 'email'], 'uk_owner_email');
            $table->foreign('owner_advertiser_id', 'fk_adv_team_owner')->references('id')->on('aq_users')->onDelete('cascade');
            $table->foreign('user_id', 'fk_adv_team_user')->references('id')->on('aq_users')->onDelete('set null');
            $table->foreign('invited_by', 'fk_adv_team_inviter')->references('id')->on('aq_users')->onDelete('set null');
        });

        Schema::create('aq_advertiser_team_invitations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_advertiser_id')->index('idx_owner');
            $table->unsignedBigInteger('member_id')->nullable()->index('idx_member');
            $table->string('email', 255)->index('idx_email');
            $table->string('name', 255)->nullable();
            $table->enum('team_role', ['admin', 'manager', 'analyst', 'billing', 'viewer'])->default('viewer');
            $table->json('permissions')->nullable();
            $table->string('token', 80)->unique('uk_token');
            $table->enum('status', ['pending', 'accepted', 'revoked', 'expired'])->default('pending')->index('idx_status');
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('accepted_at')->nullable();
            $table->unsignedBigInteger('invited_by')->nullable()->index('idx_invited_by');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('owner_advertiser_id', 'fk_adv_invite_owner')->references('id')->on('aq_users')->onDelete('cascade');
            $table->foreign('member_id', 'fk_adv_invite_member')->references('id')->on('aq_advertiser_team_members')->onDelete('set null');
            $table->foreign('invited_by', 'fk_adv_invite_inviter')->references('id')->on('aq_users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_advertiser_team_invitations');
        Schema::dropIfExists('aq_advertiser_team_members');
    }
};
