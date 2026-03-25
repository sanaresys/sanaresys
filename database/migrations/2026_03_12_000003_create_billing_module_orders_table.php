<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_module_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('centro_id')
                ->constrained('centros_medicos')
                ->cascadeOnDelete();
            $table->foreignId('billing_module_id')
                ->constrained('billing_modules')
                ->cascadeOnDelete();
            $table->foreignId('billing_module_subscription_id')
                ->nullable()
                ->constrained('billing_module_subscriptions')
                ->nullOnDelete();
            $table->foreignId('requested_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('provider', 32)->default('paypal');
            $table->string('paypal_order_id')->unique();
            $table->string('paypal_capture_id')->nullable()->unique();
            $table->string('custom_id')->nullable()->index();
            $table->string('status', 32)->default('created')->index();
            $table->string('currency', 3)->default('USD');
            $table->decimal('amount', 10, 2);
            $table->text('approve_url')->nullable();
            $table->text('return_url')->nullable();
            $table->text('cancel_url')->nullable();
            $table->timestamp('order_created_at')->nullable();
            $table->timestamp('order_approved_at')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->json('payload')->nullable();
            $table->json('capture_payload')->nullable();
            $table->timestamps();

            $table->index(['centro_id', 'billing_module_id', 'status'], 'billing_module_orders_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_module_orders');
    }
};

