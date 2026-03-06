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
        Schema::table('users', function (Blueprint $table) {

            // $table->unsignedBigInteger('nacionalidad_id')->nullable()->after('email');

            // $table->foreign('nacionalidad_id')
            //     ->references('id')
            //     ->on('nacionalidades')
            //     ->onDelete('set null');
            
        });

    }

    /**
     * Reverse the migrations. 
     */
    public function down(): void{
        Schema::table('users', function (Blueprint $table) {
           // $table->dropForeign(['nacionalidad_id']);
           // $table->dropColumn('nacionalidad_id');
        });
    }
};
