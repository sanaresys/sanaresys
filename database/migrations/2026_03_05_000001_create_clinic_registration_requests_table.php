<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_registration_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('status', 40)->index();

            $table->string('nombre_centro');
            $table->string('slug', 63);
            $table->string('direccion');
            $table->string('telefono', 50);
            $table->string('rtn', 100)->index();
            $table->string('owner_name');
            $table->string('owner_email')->index();
            $table->text('password_encrypted')->nullable();

            $table->timestamp('verification_sent_at')->nullable();
            $table->timestamp('verification_expires_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('provisioned_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            $table->unsignedInteger('resend_count')->default(0);

            $table->foreignId('centro_id')
                ->nullable()
                ->constrained('centros_medicos')
                ->nullOnDelete();

            $table->string('tenant_id')->nullable()->index();
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->nullOnDelete();

            $table->string('primary_domain')->nullable();
            $table->text('onboarding_redirect_url')->nullable();
            $table->string('failure_code', 100)->nullable();
            $table->text('failure_message')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_registration_requests');
    }
};

