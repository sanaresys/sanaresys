<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_charge_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_invoice_id')
                ->constrained('billing_invoices')
                ->cascadeOnDelete();
            $table->foreignId('centro_id')
                ->nullable()
                ->constrained('centros_medicos')
                ->nullOnDelete();
            $table->unsignedBigInteger('clinic_registration_request_id')->nullable();
            $table->foreign('clinic_registration_request_id', 'bca_reg_req_fk')
                ->references('id')
                ->on('clinic_registration_requests')
                ->nullOnDelete();
            $table->foreignId('requested_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('context', 32)->default('invoice')->index();
            $table->string('provider', 32)->default('paypal');
            $table->unsignedInteger('attempt_number')->default(1);
            $table->string('status', 32)->default('created')->index();
            $table->string('currency', 3)->default('USD');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('paypal_order_id')->nullable()->unique();
            $table->string('paypal_capture_id')->nullable()->unique();
            $table->text('approve_url')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->string('failure_code', 120)->nullable();
            $table->text('failure_message')->nullable();
            $table->json('payload')->nullable();
            $table->json('capture_payload')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_charge_attempts');
    }
};
