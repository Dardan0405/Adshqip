<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_payouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index('idx_user');
            $table->decimal('amount', 12, 4);
            $table->string('currency', 3)->default('EUR');
            $table->enum('payment_method', ['paypal','wire_transfer','crypto','payoneer']);
            $table->string('payment_reference', 255)->nullable();
            $table->enum('status', ['pending','processing','completed','failed','cancelled'])->default('pending')->index('idx_status');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('processed_at')->nullable();
            $table->dateTime('created_at')->useCurrent()->index('idx_created');
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('user_id', 'fk_payout_user')->references('id')->on('aq_users')->onDelete('cascade');
        });

        Schema::create('aq_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index('idx_user');
            $table->string('invoice_number', 50)->unique('uk_invoice_number');
            $table->enum('type', ['advertiser_charge','publisher_payout']);
            $table->decimal('amount', 12, 4);
            $table->decimal('tax_amount', 12, 4)->default(0.0000);
            $table->decimal('total_amount', 12, 4);
            $table->string('currency', 3)->default('EUR');
            $table->enum('status', ['draft','sent','paid','overdue','cancelled'])->default('draft');
            $table->date('due_date')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->string('pdf_url', 500)->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('user_id', 'fk_invoice_user')->references('id')->on('aq_users')->onDelete('cascade');
        });

        Schema::create('aq_pricing_plans', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 50)->unique('uk_slug');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->enum('target_audience', ['advertiser','publisher','both'])->default('both');
            $table->decimal('price_monthly', 10, 2)->nullable()->comment('NULL = custom/contact us');
            $table->decimal('price_yearly', 10, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->json('features')->comment('List of included features');
            $table->unsignedBigInteger('impressions_limit')->nullable();
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_enterprise')->default(false);
            $table->enum('status', ['active','inactive'])->default('active');
            $table->integer('sort_order')->default(0);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('aq_user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index('idx_user');
            $table->unsignedInteger('plan_id')->index('idx_plan');
            $table->enum('billing_cycle', ['monthly','yearly'])->default('monthly');
            $table->enum('status', ['active','cancelled','expired','trial'])->default('active');
            $table->dateTime('trial_ends_at')->nullable();
            $table->date('current_period_start');
            $table->date('current_period_end');
            $table->dateTime('cancelled_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('user_id', 'fk_sub_user')->references('id')->on('aq_users')->onDelete('cascade');
            $table->foreign('plan_id', 'fk_sub_plan')->references('id')->on('aq_pricing_plans')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_user_subscriptions');
        Schema::dropIfExists('aq_pricing_plans');
        Schema::dropIfExists('aq_invoices');
        Schema::dropIfExists('aq_payouts');
    }
};
