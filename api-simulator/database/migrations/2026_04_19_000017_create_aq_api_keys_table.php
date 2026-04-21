<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('aq_api_keys')) {
            return;
        }

        Schema::create('aq_api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('key_prefix', 32)->unique();
            $table->string('token_hash', 255);
            $table->json('scopes')->nullable();
            $table->enum('status', ['active', 'revoked'])->default('active')->index();
            $table->dateTime('last_used_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('created_by', 'fk_api_keys_created_by')
                ->references('id')
                ->on('aq_users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('aq_api_keys')) {
            Schema::dropIfExists('aq_api_keys');
        }
    }
};
