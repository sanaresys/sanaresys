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
        Schema::create('cuentas_por_cobrars', function (Blueprint $table) {
            $table->id();

            $table->foreignId('factura_id')->constrained('facturas');
            $table->foreignId('pagos_factura_id')->nullable()->constrained('pagos_facturas');
            $table->decimal('saldo_pendiente', 12, 2);
            $table->date('fecha_vencimiento');
            $table->enum('estado_cuentas_por_cobrar', ['PENDIENTE','VENCIDA','PAGADA','PARCIAL','INCOBRABLE'])->default('PENDIENTE');
            // centro_id removido - el contexto del tenant define el centro

            /* logs */
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuentas_por_cobrars');
    }
};

