<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_invoice_id')
                ->constrained('billing_invoices')
                ->cascadeOnDelete();
            $table->foreignId('billing_module_id')
                ->nullable()
                ->constrained('billing_modules')
                ->nullOnDelete();
            $table->string('item_type', 32)->index();
            $table->string('description', 255);
            $table->string('billing_interval', 16)->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_amount', 10, 2)->default(0);
            $table->decimal('amount', 10, 2)->default(0);
            $table->timestamp('period_starts_at')->nullable();
            $table->timestamp('period_ends_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_invoice_items');
    }
};
