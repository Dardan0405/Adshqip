<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_newsletters', function (Blueprint $table) {
            $table->id();
            $table->string('email', 255)->unique('uk_email');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('source', ['landing_page','dashboard','api','import'])->default('landing_page');
            $table->enum('status', ['subscribed','unsubscribed','bounced'])->default('subscribed')->index('idx_status');
            $table->dateTime('subscribed_at')->useCurrent();
            $table->dateTime('unsubscribed_at')->nullable();
        });

        Schema::create('aq_faq', function (Blueprint $table) {
            $table->increments('id');
            $table->string('category', 100)->default('General');
            $table->text('question');
            $table->text('answer');
            $table->string('language', 5)->default('en');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['language', 'is_published'], 'idx_lang_published');
        });

        Schema::create('aq_testimonials', function (Blueprint $table) {
            $table->increments('id');
            $table->string('author_name', 100);
            $table->string('author_title', 100)->nullable();
            $table->string('author_company', 100)->nullable();
            $table->string('author_avatar_url', 500)->nullable();
            $table->text('quote');
            $table->unsignedTinyInteger('rating')->nullable()->comment('1-5 stars');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->integer('sort_order')->default(0);
            $table->dateTime('created_at')->useCurrent();
        });

        Schema::create('aq_case_studies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 255);
            $table->string('slug', 255)->unique('uk_slug');
            $table->string('client_name', 100);
            $table->string('client_logo_url', 500)->nullable();
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->string('metric_impressions', 50)->nullable()->comment('e.g. 1.8B+');
            $table->string('metric_revenue_increase', 50)->nullable()->comment('e.g. +340%');
            $table->string('metric_ctr', 50)->nullable();
            $table->string('metric_custom_label', 100)->nullable();
            $table->string('metric_custom_value', 50)->nullable();
            $table->boolean('is_published')->default(false);
            $table->dateTime('published_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('aq_trusted_publishers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('logo_url', 500)->nullable();
            $table->string('website_url', 500)->nullable();
            $table->boolean('is_featured')->default(true);
            $table->integer('sort_order')->default(0);
            $table->dateTime('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_trusted_publishers');
        Schema::dropIfExists('aq_case_studies');
        Schema::dropIfExists('aq_testimonials');
        Schema::dropIfExists('aq_faq');
        Schema::dropIfExists('aq_newsletters');
    }
};
