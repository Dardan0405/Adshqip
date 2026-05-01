<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('aq_api_docs')) {
            return;
        }

        Schema::create('aq_api_docs', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 100)->unique();
            $table->string('title', 160);
            $table->string('category', 80)->default('General');
            $table->string('http_method', 10)->default('GET');
            $table->string('endpoint_path', 255);
            $table->boolean('auth_required')->default(true);
            $table->string('required_permission', 120)->nullable();
            $table->text('description')->nullable();
            $table->text('headers_example')->nullable();
            $table->longText('request_example')->nullable();
            $table->longText('response_example')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_published')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_api_docs');
    }
};
