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
        Schema::create('contratos_medicos', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('medico_id');
            $table->decimal('salario_quincenal', 10, 2);
            $table->decimal('salario_mensual', 10, 2);
            $table->decimal('porcentaje_servicio', 5, 2);
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->enum('activo', ['SI', 'NO']);
            $table->text('observaciones')->nullable();
            // centro_id removido - el contexto del tenant define el centro
            
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contratos_medicos');
    }
};