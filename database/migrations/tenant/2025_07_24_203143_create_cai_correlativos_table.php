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
        Schema::create('cai_correlativos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('autorizacion_id')->constrained('cai_autorizaciones');
            $table->integer('numero_correlativo')->nullable();
            $table->string('numero_factura');
            $table->timestamp('fecha_emision');
            $table->unsignedBigInteger('usuario_id'); // FK removida - users está en BD central
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
        Schema::dropIfExists('cai_correlativos');
    }
};

