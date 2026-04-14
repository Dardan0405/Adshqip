<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_request_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('email')->nullable();
            $table->string('name', 255);
            $table->enum('frequency', ['one', 'daily', 'weekly', 'monthly'])->default('one');
            $table->enum('report_type', ['summarized', 'detailed'])->default('summarized');
            $table->enum('file_format', ['csv', 'xls'])->default('csv');
            $table->enum('display_by', ['cumulative', 'hour', 'month', 'year'])->default('cumulative');
            $table->enum('environment', ['all', 'desktop', 'mobile', 'tablet'])->default('all');
            $table->json('metrics');
            $table->json('dimensions')->nullable();
            $table->json('filters')->nullable();
            $table->enum('status', ['active', 'paused'])->default('active');
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id', 'fk_request_reports_user')
                ->references('id')
                ->on('aq_users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_request_reports');
    }
};
