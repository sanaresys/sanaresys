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
        Schema::create('cai_autorizaciones', function (Blueprint $table) {
            $table->id();

            $table->string('rtn')->unique();
            $table->string('cai_codigo')->unique();
            $table->integer('cantidad');
            $table->unsignedBigInteger('rango_inicial');
            $table->unsignedBigInteger('rango_final');
            $table->unsignedBigInteger('numero_actual')->nullable();
            $table->date('fecha_limite');
            $table->enum('estado', ['ACTIVA','VENCIDA','AGOTADA','ANULADA'])->default('ACTIVA');
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
        Schema::dropIfExists('cai_autorizaciones');
    }
};

