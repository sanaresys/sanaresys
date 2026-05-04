<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_override_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('centro_id')
                ->constrained('centros_medicos')
                ->cascadeOnDelete();
            $table->foreignId('performed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('old_override', 32)->nullable();
            $table->string('new_override', 32)->nullable();
            $table->text('reason');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_override_audits');
    }
};
