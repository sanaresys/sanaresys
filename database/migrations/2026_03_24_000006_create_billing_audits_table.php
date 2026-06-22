<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('centro_id')
                ->nullable()
                ->constrained('centros_medicos')
                ->nullOnDelete();
            $table->foreignId('billing_invoice_id')
                ->nullable()
                ->constrained('billing_invoices')
                ->nullOnDelete();
            $table->unsignedBigInteger('billing_tenant_subscription_id')->nullable();
            $table->foreign('billing_tenant_subscription_id', 'ba_tenant_sub_fk')
                ->references('id')
                ->on('billing_tenant_subscriptions')
                ->nullOnDelete();
            $table->unsignedBigInteger('billing_module_subscription_id')->nullable();
            $table->foreign('billing_module_subscription_id', 'ba_module_sub_fk')
                ->references('id')
                ->on('billing_module_subscriptions')
                ->nullOnDelete();
            $table->foreignId('actor_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('actor_type', 32)->default('system');
            $table->string('event_type', 64)->index();
            $table->text('reason')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_audits');
    }
};
