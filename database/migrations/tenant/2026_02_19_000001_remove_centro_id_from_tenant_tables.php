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
        // Eliminar centro_id de tablas tenant ya que el contexto define el centro
        
        if (Schema::hasColumn('users', 'centro_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('centro_id');
            });
        }
        
        if (Schema::hasColumn('medicos', 'centro_id')) {
            Schema::table('medicos', function (Blueprint $table) {
                $table->dropColumn('centro_id');
            });
        }
        
        if (Schema::hasColumn('roles', 'centro_id')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn('centro_id');
            });
        }
        
        if (Schema::hasColumn('especialidads', 'centro_id')) {
            Schema::table('especialidads', function (Blueprint $table) {
                $table->dropColumn('centro_id');
            });
        }
        
        if (Schema::hasColumn('especialidad_medicos', 'centro_id')) {
            Schema::table('especialidad_medicos', function (Blueprint $table) {
                $table->dropColumn('centro_id');
            });
        }
        
        if (Schema::hasColumn('centros_medicos_medicos', 'centro_id')) {
            Schema::table('centros_medicos_medicos', function (Blueprint $table) {
                $table->dropColumn('centro_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar columnas si es necesario
        
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('centro_id')->nullable();
        });
        
        Schema::table('medicos', function (Blueprint $table) {
            $table->unsignedBigInteger('centro_id')->nullable();
        });
        
        Schema::table('roles', function (Blueprint $table) {
            $table->unsignedBigInteger('centro_id')->nullable();
        });
        
        Schema::table('especialidads', function (Blueprint $table) {
            $table->unsignedBigInteger('centro_id')->nullable();
        });
        
        Schema::table('especialidad_medicos', function (Blueprint $table) {
            $table->unsignedBigInteger('centro_id')->nullable();
        });
        
        Schema::table('centros_medicos_medicos', function (Blueprint $table) {
            $table->unsignedBigInteger('centro_id')->nullable();
        });
    }
};
