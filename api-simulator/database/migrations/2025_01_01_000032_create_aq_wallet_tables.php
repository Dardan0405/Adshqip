<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_saved_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index('idx_user');
            $table->string('label', 100)->default('My Card')->comment('User-friendly name');
            $table->enum('type', ['credit_card','debit_card','paypal','crypto_wallet','wire_transfer']);
            $table->enum('gateway', ['stripe','paypal','coinbase','manual'])->default('stripe')->index('idx_gateway');
            $table->string('gateway_customer_id', 255)->nullable()->comment('Stripe customer ID / PayPal payer ID');
            $table->string('gateway_payment_method_id', 255)->comment('Tokenized payment method ID from gateway');
            $table->string('card_brand', 20)->nullable()->comment('visa, mastercard, amex, etc.');
            $table->char('card_last4', 4)->nullable();
            $table->unsignedTinyInteger('card_exp_month')->nullable();
            $table->unsignedSmallInteger('card_exp_year')->nullable();
            $table->char('billing_country', 2)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('user_id', 'fk_spm_user')->references('id')->on('aq_users')->onDelete('cascade');
        });

        Schema::create('aq_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index('idx_user');
            $table->enum('type', ['deposit','withdrawal','ad_spend','refund','adjustment','welcome_bonus','referral_credit'])->index('idx_type');
            $table->decimal('amount', 12, 4)->comment('Positive = credit, negative = debit');
            $table->string('currency', 3)->default('EUR');
            $table->decimal('balance_before', 12, 4);
            $table->decimal('balance_after', 12, 4);
            // Payment gateway details
            $table->unsignedBigInteger('payment_method_id')->nullable()->comment('FK → aq_saved_payment_methods');
            $table->enum('payment_gateway', ['stripe','paypal','coinbase','wire_transfer','manual'])->nullable();
            $table->string('gateway_txn_id', 255)->nullable()->index('idx_gateway_txn')->comment('External gateway transaction/charge ID');
            $table->enum('gateway_status', ['pending','processing','confirmed','failed','refunded','cancelled'])->nullable();
            $table->json('gateway_response')->nullable()->comment('Raw gateway webhook/response payload');
            // Metadata
            $table->unsignedBigInteger('campaign_id')->nullable()->index('idx_campaign')->comment('For ad_spend transactions');
            $table->unsignedBigInteger('invoice_id')->nullable()->comment('Linked invoice if generated');
            $table->string('description', 500)->nullable()->comment('Human-readable description');
            $table->string('admin_note', 500)->nullable()->comment('Internal note for manual adjustments');
            $table->unsignedBigInteger('initiated_by')->nullable()->comment('Admin user_id for manual adjustments');
            $table->string('ip_address', 45)->nullable();
            $table->enum('status', ['pending','completed','failed','reversed'])->default('pending')->index('idx_status');
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('created_at')->useCurrent()->index('idx_created');
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('user_id', 'fk_txn_user')->references('id')->on('aq_users')->onDelete('cascade');
            $table->foreign('payment_method_id', 'fk_txn_payment_method')->references('id')->on('aq_saved_payment_methods')->onDelete('set null');
            $table->foreign('invoice_id', 'fk_txn_invoice')->references('id')->on('aq_invoices')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_transactions');
        Schema::dropIfExists('aq_saved_payment_methods');
    }
};
