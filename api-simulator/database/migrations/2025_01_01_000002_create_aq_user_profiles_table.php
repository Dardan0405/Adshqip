<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_user_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique('uk_user_id');
            $table->string('first_name', 100)->default('');
            $table->string('last_name', 100)->default('');
            $table->string('company_name', 255)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('address_line1', 255)->nullable();
            $table->string('address_line2', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state_region', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->char('country_code', 2)->default('AL')->index('idx_country');
            $table->string('vat_number', 50)->nullable();
            $table->string('website_url', 500)->nullable();
            $table->string('avatar_url', 500)->nullable();
            $table->text('bio')->nullable();
            $table->decimal('balance', 12, 4)->default(0.0000)->comment('old: dj_cur_balance');
            $table->string('currency', 3)->default('EUR');
            $table->enum('payment_method', ['paypal', 'wire_transfer', 'crypto', 'payoneer'])->nullable();
            $table->json('payment_details')->nullable();
            $table->boolean('is_default')->default(false)->comment('old: dj_is_default');
            $table->boolean('is_denied')->default(false)->comment('old: dj_is_denied');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('user_id', 'fk_profile_user')->references('id')->on('aq_users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_user_profiles');
    }
};
