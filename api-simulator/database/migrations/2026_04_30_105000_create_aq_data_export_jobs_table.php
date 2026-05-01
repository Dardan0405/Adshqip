<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_data_export_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->string('name', 160);
            $table->string('dataset', 80)->index();
            $table->string('format', 20)->default('csv');
            $table->string('status', 30)->default('pending')->index();
            $table->json('filters')->nullable();
            $table->unsignedInteger('row_count')->default(0);
            $table->string('file_path', 500)->nullable();
            $table->string('file_name', 220)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->text('error_message')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('failed_at')->nullable();
            $table->dateTime('expires_at')->nullable()->index();
            $table->timestamps();

            $table->foreign('created_by', 'fk_data_export_jobs_created_by')
                ->references('id')
                ->on('aq_users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_data_export_jobs');
    }
};
