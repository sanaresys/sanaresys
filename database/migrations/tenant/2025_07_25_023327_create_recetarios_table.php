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
        Schema::create('recetarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medico_id');
            $table->unsignedBigInteger('consulta_id')->nullable();
            $table->foreign('consulta_id')->references('id')->on('consultas');
            // centro_id removido - el contexto del tenant define el centro
            
            // Campos básicos del recetario
            $table->boolean('tiene_recetario')->default(false); // Estado de activación del recetario
            $table->string('numero_recetario')->nullable();
            $table->text('observaciones_generales')->nullable();
            $table->enum('estado', ['activo', 'inactivo', 'vencido'])->default('activo');
            $table->date('fecha_emision')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            
            // Campos de diseño y personalización
            $table->string('logo')->nullable(); // Ruta del archivo de logo
            $table->string('color_primario', 7)->default('#2563eb'); // Color principal (hex)
            $table->string('color_secundario', 7)->default('#64748b'); // Color secundario (hex)
            $table->string('fuente_familia')->default('Arial'); // Familia de fuente
            $table->integer('fuente_tamano')->default(12); // Tamaño de fuente base
            $table->boolean('mostrar_logo')->default(true); // Mostrar/ocultar logo
            $table->boolean('mostrar_especialidades')->default(true); // Mostrar especialidades del médico
            $table->boolean('mostrar_telefono')->default(true); // Mostrar teléfono
            $table->boolean('mostrar_direccion')->default(true); // Mostrar dirección

            //campos de edicion
            $table->string('titulo')->nullable(); //  Dr, Dra, etc.
            $table->string('nombre_mostrar')->nullable(); // Nombre a mostrar en el recetario
            $table->string('telefono_mostrar')->nullable(); // Teléfono a mostrar en el recetario
        
            $table->timestamps();
            $table->softDeletes();
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
        Schema::dropIfExists('recetarios');
    }
};
