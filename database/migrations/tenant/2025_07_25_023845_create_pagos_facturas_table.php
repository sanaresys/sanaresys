<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pagos_facturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas');
            $table->foreignId('paciente_id')->constrained('pacientes');
            // centro_id removido - el contexto del tenant define el centro
            $table->foreignId('tipo_pago_id')->constrained('tipo_pagos');   // ajusta si tu tabla se llama distinto
            $table->decimal('monto_recibido', 12, 2);
            $table->decimal('monto_devolucion', 12, 2)->default(0);
            $table->timestamp('fecha_pago')->nullable();

            /* logs */
            $table->timestamps();      // created_at & updated_at
            $table->softDeletes();     // deleted_at
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_facturas');
    }
};

