<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('clinic_registration_requests', 'rtn')
            || ! Schema::hasColumn('centros_medicos', 'rtn')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE `clinic_registration_requests` MODIFY `rtn` VARCHAR(100) NULL');
        DB::statement('ALTER TABLE `centros_medicos` MODIFY `rtn` VARCHAR(255) NULL');
    }

    public function down(): void
    {
        if (! Schema::hasColumn('clinic_registration_requests', 'rtn')
            || ! Schema::hasColumn('centros_medicos', 'rtn')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::table('clinic_registration_requests')
            ->whereNull('rtn')
            ->update(['rtn' => '']);

        DB::table('centros_medicos')
            ->whereNull('rtn')
            ->update([
                'rtn' => DB::raw("CONCAT('RTN-MIG-', id)"),
            ]);

        DB::statement('ALTER TABLE `clinic_registration_requests` MODIFY `rtn` VARCHAR(100) NOT NULL');
        DB::statement('ALTER TABLE `centros_medicos` MODIFY `rtn` VARCHAR(255) NOT NULL');
    }
};
