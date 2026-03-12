<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('centros_medicos', function (Blueprint $table) {
            if (! Schema::hasColumn('centros_medicos', 'billing_status')) {
                $table->string('billing_status', 32)->default('inactive')->after('tenancy_mode');
            }

            if (! Schema::hasColumn('centros_medicos', 'billing_plan_code')) {
                $table->string('billing_plan_code', 32)->nullable()->after('billing_status');
            }

            if (! Schema::hasColumn('centros_medicos', 'billing_renews_at')) {
                $table->timestamp('billing_renews_at')->nullable()->after('billing_plan_code');
            }

            if (! Schema::hasColumn('centros_medicos', 'billing_last_sync_at')) {
                $table->timestamp('billing_last_sync_at')->nullable()->after('billing_renews_at');
            }

            if (! Schema::hasColumn('centros_medicos', 'billing_override')) {
                $table->string('billing_override', 32)->nullable()->after('billing_last_sync_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('centros_medicos', function (Blueprint $table) {
            if (Schema::hasColumn('centros_medicos', 'billing_override')) {
                $table->dropColumn('billing_override');
            }

            if (Schema::hasColumn('centros_medicos', 'billing_last_sync_at')) {
                $table->dropColumn('billing_last_sync_at');
            }

            if (Schema::hasColumn('centros_medicos', 'billing_renews_at')) {
                $table->dropColumn('billing_renews_at');
            }

            if (Schema::hasColumn('centros_medicos', 'billing_plan_code')) {
                $table->dropColumn('billing_plan_code');
            }

            if (Schema::hasColumn('centros_medicos', 'billing_status')) {
                $table->dropColumn('billing_status');
            }
        });
    }
};
