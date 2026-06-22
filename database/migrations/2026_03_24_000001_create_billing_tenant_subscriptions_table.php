<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_tenant_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('centro_id')
                ->unique()
                ->constrained('centros_medicos')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('clinic_registration_request_id')->nullable();
            $table->foreign('clinic_registration_request_id', 'bts_reg_req_fk')
                ->references('id')
                ->on('clinic_registration_requests')
                ->nullOnDelete();
            $table->string('status', 32)->default('pending')->index();
            $table->string('plan_code', 32);
            $table->string('billing_interval', 16)->default('monthly');
            $table->timestamp('anchor_at')->nullable();
            $table->timestamp('current_period_starts_at')->nullable();
            $table->timestamp('current_period_ends_at')->nullable();
            $table->timestamp('next_charge_at')->nullable()->index();
            $table->timestamp('grace_until')->nullable()->index();
            $table->boolean('cancel_at_period_end')->default(false);
            $table->timestamp('canceled_at')->nullable();
            $table->unsignedInteger('dunning_attempts')->default(0);
            $table->timestamp('last_successful_charge_at')->nullable();
            $table->timestamp('last_failed_charge_at')->nullable();
            $table->unsignedBigInteger('last_invoice_id')->nullable();
            $table->timestamp('consent_at')->nullable();
            $table->string('consent_text_version', 64)->nullable();
            $table->string('consent_ip', 45)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_tenant_subscriptions');
    }
};
