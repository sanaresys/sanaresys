<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_module_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('centro_id')
                ->constrained('centros_medicos')
                ->cascadeOnDelete();
            $table->foreignId('billing_module_id')
                ->constrained('billing_modules')
                ->cascadeOnDelete();
            $table->string('status', 32)->default('inactive')->index();
            $table->string('currency', 3)->default('USD');
            $table->decimal('amount', 10, 2)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('renews_at')->nullable();
            $table->timestamp('last_payment_at')->nullable();
            $table->timestamp('last_refund_at')->nullable();
            $table->string('last_paypal_order_id')->nullable()->index();
            $table->string('last_paypal_capture_id')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['centro_id', 'billing_module_id'], 'billing_module_subscriptions_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_module_subscriptions');
    }
};

