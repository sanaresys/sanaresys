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
        Schema::create('nominas', function (Blueprint $table) {
            $table->id();
            $table->string('empresa');
            $table->integer('año');
            $table->integer('mes');
            $table->enum('tipo_pago', ['mensual', 'quincenal', 'semanal'])->default('mensual');
            $table->integer('quincena')->nullable(); // 1 = primera quincena, 2 = segunda quincena
            $table->text('descripcion')->nullable();
            $table->boolean('cerrada')->default(false);
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');
            // centro_id removido - el contexto del tenant define el centro
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // centro_id FK removido - el contexto del tenant define el centro
            // created_by y updated_by FK removidas - users está en BD central
            $table->index(['año', 'mes', 'empresa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nominas');
    }
};
