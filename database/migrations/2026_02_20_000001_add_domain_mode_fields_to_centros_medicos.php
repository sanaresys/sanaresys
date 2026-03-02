<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('centros_medicos', function (Blueprint $table) {
            if (! Schema::hasColumn('centros_medicos', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('nombre_centro');
            }

            if (! Schema::hasColumn('centros_medicos', 'tenancy_mode')) {
                $table->string('tenancy_mode')->default('legacy')->after('slug');
            }

            if (! Schema::hasColumn('centros_medicos', 'onboarding_completed_at')) {
                $table->timestamp('onboarding_completed_at')->nullable()->after('tenancy_mode');
            }

            if (! Schema::hasColumn('centros_medicos', 'onboarding_current_step')) {
                $table->integer('onboarding_current_step')->default(0)->after('onboarding_completed_at');
            }

            if (! Schema::hasColumn('centros_medicos', 'onboarding_skipped_cai')) {
                $table->boolean('onboarding_skipped_cai')->default(false)->after('onboarding_current_step');
            }
        });
    }

    public function down(): void
    {
        Schema::table('centros_medicos', function (Blueprint $table) {
            if (Schema::hasColumn('centros_medicos', 'onboarding_skipped_cai')) {
                $table->dropColumn('onboarding_skipped_cai');
            }

            if (Schema::hasColumn('centros_medicos', 'onboarding_current_step')) {
                $table->dropColumn('onboarding_current_step');
            }

            if (Schema::hasColumn('centros_medicos', 'onboarding_completed_at')) {
                $table->dropColumn('onboarding_completed_at');
            }

            if (Schema::hasColumn('centros_medicos', 'tenancy_mode')) {
                $table->dropColumn('tenancy_mode');
            }

            if (Schema::hasColumn('centros_medicos', 'slug')) {
                $table->dropUnique('centros_medicos_slug_unique');
                $table->dropColumn('slug');
            }
        });
    }
};

