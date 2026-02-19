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
        Schema::create('detalle_nominas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nomina_id')->constrained('nominas')->onDelete('cascade');
            $table->string('medico_nombre');
            $table->decimal('salario_base', 10, 2);
            $table->decimal('deducciones', 10, 2)->default(0);
            $table->decimal('percepciones', 10, 2)->default(0);
            $table->decimal('total_pagar', 10, 2);
            $table->text('deducciones_detalle')->nullable();
            $table->text('percepciones_detalle')->nullable();
            // centro_id removido - el contexto del tenant define el centro
            $table->timestamps();
            $table->softDeletes();
            
            // centro_id FK removido - el contexto del tenant define el centro
            // medico_id index removido - columna no existe
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_nominas');
    }
};
