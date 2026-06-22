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
        Schema::create('descuentos', function (Blueprint $table) {
            $table->id();

            $table->string('nombre');
            $table->enum('tipo', ['PORCENTAJE', 'MONTO']);
            $table->decimal('valor', 10, 2);
            $table->date('aplica_desde');
            $table->date('aplica_hasta')->nullable();
            $table->enum('activo', ['SI', 'NO'])->default('SI');
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
        Schema::dropIfExists('descuentos');
    }
};

