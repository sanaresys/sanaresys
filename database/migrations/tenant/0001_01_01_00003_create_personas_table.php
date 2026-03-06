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
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->string ("primer_nombre");
            $table->string ("segundo_nombre")->nullable();
            $table->string ("primer_apellido");
            $table->string ("segundo_apellido")->nullable();
            $table->string("dni")->unique();
            $table->string("telefono");
            $table->text("direccion")->nullable();
            $table->enum("sexo",["M","F"]);
            $table->date("fecha_nacimiento");
            $table->unsignedBigInteger("nacionalidad_id");
            $table->foreign("nacionalidad_id")->references("id")->on("nacionalidades");
            // centro_id removido - el contexto del tenant define el centro
            $table->string("fotografia")->nullable();

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
        Schema::dropIfExists('personas');
    }
};


