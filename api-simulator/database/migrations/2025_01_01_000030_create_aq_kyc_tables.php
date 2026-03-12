<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_kyc_verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index('idx_user');
            $table->enum('verification_level', ['basic','standard','enhanced'])->default('basic')->index('idx_level')->comment('Tiered KYC levels');
            $table->enum('status', ['not_started','pending','in_review','approved','rejected','expired'])->default('not_started')->index('idx_status');
            // Personal / business info snapshot
            $table->string('legal_first_name', 100)->nullable();
            $table->string('legal_last_name', 100)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->char('nationality', 2)->nullable()->comment('ISO 3166-1 alpha-2');
            $table->string('id_number', 100)->nullable()->comment('National ID / passport number (encrypted at app layer)');
            $table->enum('id_type', ['passport','national_id','drivers_license','residence_permit'])->nullable();
            $table->char('id_issuing_country', 2)->nullable();
            $table->date('id_expiry_date')->nullable();
            // Business KYC
            $table->string('business_name', 255)->nullable();
            $table->string('business_registration_number', 100)->nullable();
            $table->enum('business_type', ['individual','sole_proprietor','llc','corporation','partnership','non_profit'])->nullable();
            $table->char('business_country', 2)->nullable();
            $table->text('business_address')->nullable();
            $table->string('vat_number', 50)->nullable();
            // Review
            $table->unsignedBigInteger('reviewer_id')->nullable()->index('idx_reviewer')->comment('Admin who reviewed');
            $table->dateTime('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedInteger('rejection_count')->default(0)->comment('How many times rejected');
            $table->text('notes')->nullable()->comment('Internal admin notes');
            // Risk scoring
            $table->decimal('risk_score', 5, 2)->nullable()->comment('0-100, higher = riskier');
            $table->json('risk_flags')->nullable()->comment('e.g. ["pep","sanctions_match","high_risk_country"]');
            $table->boolean('aml_check_passed')->nullable()->comment('Anti-money-laundering check result');
            $table->boolean('sanctions_check_passed')->nullable();
            // Timestamps
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('expires_at')->nullable()->comment('KYC validity period');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('user_id', 'fk_kyc_user')->references('id')->on('aq_users')->onDelete('cascade');
            $table->foreign('reviewer_id', 'fk_kyc_reviewer')->references('id')->on('aq_users')->onDelete('set null');
        });

        Schema::create('aq_kyc_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kyc_id')->index('idx_kyc');
            $table->unsignedBigInteger('user_id')->index('idx_user');
            $table->enum('document_type', ['id_front','id_back','passport','selfie','selfie_with_id','proof_of_address','business_registration','tax_certificate','bank_statement','other'])->index('idx_type');
            $table->string('file_path', 500)->comment('Stored in secure / encrypted bucket');
            $table->string('file_name', 255)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('file_size_bytes')->nullable();
            $table->string('file_hash', 128)->nullable()->comment('SHA-256 for integrity');
            $table->enum('status', ['uploaded','verified','rejected','expired'])->default('uploaded')->index('idx_status');
            $table->string('rejection_reason', 500)->nullable();
            $table->unsignedBigInteger('verified_by')->nullable()->comment('Admin who verified this doc');
            $table->dateTime('verified_at')->nullable();
            $table->dateTime('expires_at')->nullable()->comment('Document expiry date');
            $table->json('metadata')->nullable()->comment('OCR results, MRZ data, etc.');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('kyc_id', 'fk_kycdoc_kyc')->references('id')->on('aq_kyc_verifications')->onDelete('cascade');
            $table->foreign('user_id', 'fk_kycdoc_user')->references('id')->on('aq_users')->onDelete('cascade');
            $table->foreign('verified_by', 'fk_kycdoc_verifier')->references('id')->on('aq_users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_kyc_documents');
        Schema::dropIfExists('aq_kyc_verifications');
    }
};
