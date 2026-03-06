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
        Schema::create('centros_medicos', function (Blueprint $table) {
            $table->id();
            $table->string("nombre_centro");
            $table->string("direccion");
            $table->string("telefono");
            $table->string("email")->nullable();
            $table->string("rtn")->unique();
            $table->string("fotografia")->nullable();
            
            //  campos de logs

            $table->integer("created_by")->nullable();
            $table->integer("updated_by")->nullable();
            $table->integer("deleted_by")->nullable();


            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('centros_medicos');
    }
};
