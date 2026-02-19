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
        Schema::create('examenes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paciente_id');
            $table->foreign('paciente_id')->references('id')->on('pacientes');
            $table->unsignedBigInteger('consulta_id');
            $table->foreign('consulta_id')->references('id')->on('consultas');
            $table->unsignedBigInteger('medico_id');
            // centro_id removido - el contexto del tenant define el centro

            $table->string('tipo_examen'); // Texto libre: "Examen de orina", "Hemograma completo", etc.
            $table->text('observaciones')->nullable(); // Observaciones opcionales del médico
            $table->enum('estado', ['Solicitado', 'Completado', 'No presentado'])->default('Solicitado');
            $table->string('imagen_resultado')->nullable(); // Ruta del archivo subido (cambio de url_archivo)
            $table->timestamp('fecha_completado')->nullable(); // Cuando se sube la imagen
            $table->date('fecha_resultado'); // Mantener campo original

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
        Schema::dropIfExists('examenes');
    }
};
