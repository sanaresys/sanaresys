<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_notification_logs', function (Blueprint $table) {
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
            $table->foreign('billing_tenant_subscription_id', 'bnl_tenant_sub_fk')
                ->references('id')
                ->on('billing_tenant_subscriptions')
                ->nullOnDelete();
            $table->unsignedBigInteger('billing_module_subscription_id')->nullable();
            $table->foreign('billing_module_subscription_id', 'bnl_module_sub_fk')
                ->references('id')
                ->on('billing_module_subscriptions')
                ->nullOnDelete();
            $table->string('event_key', 64)->index();
            $table->string('channel', 32);
            $table->string('recipient', 190);
            $table->date('scheduled_for_date');
            $table->timestamp('sent_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(
                ['event_key', 'channel', 'recipient', 'scheduled_for_date'],
                'billing_notification_logs_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_notification_logs');
    }
};
