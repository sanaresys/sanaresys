<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('centro_id')
                ->nullable()
                ->constrained('centros_medicos')
                ->nullOnDelete();
            $table->foreignId('clinic_registration_request_id')
                ->nullable()
                ->constrained('clinic_registration_requests')
                ->nullOnDelete();
            $table->string('provider', 32)->default('paypal');
            $table->string('paypal_subscription_id')->unique();
            $table->string('paypal_plan_id')->nullable();
            $table->string('plan_code', 32)->nullable();
            $table->string('provider_status', 64)->nullable();
            $table->string('status', 32)->default('inactive')->index();
            $table->string('currency', 3)->default('USD');
            $table->decimal('amount', 10, 2)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('current_period_start_at')->nullable();
            $table->timestamp('current_period_end_at')->nullable();
            $table->timestamp('renews_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_subscriptions');
    }
};
