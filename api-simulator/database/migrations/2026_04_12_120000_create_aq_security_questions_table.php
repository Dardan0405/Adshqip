<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_security_questions', function (Blueprint $table) {
            $table->id();
            $table->string('question', 255);
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // Add security question fields to aq_users
        Schema::table('aq_users', function (Blueprint $table) {
            $table->unsignedBigInteger('security_question_id')->nullable()->after('two_factor_secret');
            $table->string('security_answer_hash', 255)->nullable()->after('security_question_id');

            $table->foreign('security_question_id')->references('id')->on('aq_security_questions')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('aq_users', function (Blueprint $table) {
            $table->dropForeign(['security_question_id']);
            $table->dropColumn(['security_question_id', 'security_answer_hash']);
        });

        Schema::dropIfExists('aq_security_questions');
    }
};
