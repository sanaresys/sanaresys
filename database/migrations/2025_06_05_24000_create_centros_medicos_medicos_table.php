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
        Schema::create('centros_medicos_medicos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medico_id');
            $table->foreign('medico_id')->references('id')->on('medicos');
            $table->unsignedBigInteger('centro_medico_id');
            $table->foreign('centro_medico_id')->references('id')->on('centros_medicos');
            $table->string('horario_entrada');
            $table->string('horario_salida');
            $table->unsignedBigInteger("centro_id")->nullable(); // ID del centro médico, puede ser nulo
            $table->foreign("centro_id")->references("id")->on("centros_medicos");

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
        Schema::dropIfExists('centros_medicos_medicos');
    }
};
