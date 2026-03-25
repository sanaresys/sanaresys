<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('centro_id')
                ->nullable()
                ->constrained('centros_medicos')
                ->nullOnDelete();
            $table->foreignId('clinic_registration_request_id')
                ->nullable()
                ->constrained('clinic_registration_requests')
                ->nullOnDelete();
            $table->string('kind', 32)->default('renewal')->index();
            $table->string('status', 32)->default('open')->index();
            $table->string('currency', 3)->default('USD');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->timestamp('due_at')->nullable()->index();
            $table->timestamp('grace_until')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('billing_starts_at')->nullable();
            $table->timestamp('billing_ends_at')->nullable();
            $table->timestamp('billing_renews_at')->nullable();
            $table->timestamp('last_notified_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['centro_id', 'status'], 'billing_invoices_centro_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_invoices');
    }
};
