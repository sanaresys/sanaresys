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
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('medico_id');
            $table->unsignedBigInteger('paciente_id');
            $table->foreign('paciente_id')->references('id')->on('pacientes');

            $table->date('fecha');
            $table->time('hora');
            $table->text('motivo', 255)->nullable();
            $table->enum('estado', ['Pendiente', 'Confirmado', 'Cancelado', 'Realizado']);
            // centro_id removido - el contexto del tenant define el centro

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
        Schema::dropIfExists('citas');
    }
};
