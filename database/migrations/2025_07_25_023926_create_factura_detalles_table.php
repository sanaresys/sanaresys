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
        Schema::create('factura_detalles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('factura_id')->nullable()->constrained('facturas');
            $table->foreignId('servicio_id')->constrained('servicios');
            $table->foreignId('consulta_id')->nullable()->constrained('consultas');
            $table->unique(['consulta_id', 'servicio_id'], 'unique_consulta_servicio_temp');
            $table->integer('cantidad');
            $table->foreignId('descuento_id')->nullable()->constrained('descuentos');
            $table->decimal('subtotal', 12, 2);
            $table->foreignId('impuesto_id')->nullable()->constrained('impuestos');
            $table->decimal('impuesto_monto', 12, 2)->default(0);
            $table->decimal('descuento_monto', 12, 2)->default(0);
            $table->decimal('total_linea', 12, 2);
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
        Schema::dropIfExists('factura_detalles');
    }
};

