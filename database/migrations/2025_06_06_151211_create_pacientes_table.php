<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    /**
     * Run the migrations.
     * 
     * 
     */
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            
            // Guardamos solo el ID de la persona (que está en BD central)
            // No podemos hacer FK entre bases de datos diferentes
            $table->unsignedBigInteger('persona_id');
            // $table->foreign('persona_id')->references('id')->on('personas'); // ← Removida FK
            
            $table->enum('grupo_sanguineo', ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-', 'No especificado'])->nullable();
            $table->string('contacto_emergencia')->nullable();
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
        Schema::dropIfExists('pacientes');
    }
};
