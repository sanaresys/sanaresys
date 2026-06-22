<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('centros_medicos') || ! Schema::hasTable('clinic_registration_requests')) {
            return;
        }

        if (! Schema::hasColumn('centros_medicos', 'email')
            || ! Schema::hasColumn('clinic_registration_requests', 'owner_email')
            || ! Schema::hasColumn('clinic_registration_requests', 'centro_id')) {
            return;
        }

        $latestByCentro = DB::connection('mysql')
            ->table('clinic_registration_requests')
            ->selectRaw('MAX(id) as id, centro_id')
            ->whereNotNull('centro_id')
            ->groupBy('centro_id');

        $rows = DB::connection('mysql')
            ->table('centros_medicos as c')
            ->joinSub($latestByCentro, 'latest', function ($join): void {
                $join->on('latest.centro_id', '=', 'c.id');
            })
            ->join('clinic_registration_requests as r', 'r.id', '=', 'latest.id')
            ->where(function ($query): void {
                $query->whereNull('c.email')
                    ->orWhere('c.email', '=', '');
            })
            ->whereNotNull('r.owner_email')
            ->where('r.owner_email', '!=', '')
            ->select(['c.id as centro_id', 'r.owner_email'])
            ->get();

        foreach ($rows as $row) {
            DB::connection('mysql')
                ->table('centros_medicos')
                ->where('id', (int) $row->centro_id)
                ->update([
                    'email' => strtolower((string) $row->owner_email),
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        // No-op: este backfill no revierte datos para evitar perdida accidental.
    }
};

