<?php

namespace App\Console\Commands;

use App\Models\BillingOverrideAudit;
use App\Models\BillingSubscription;
use App\Models\BillingWebhookEvent;
use App\Models\Centros_Medico;
use App\Models\ClinicRegistrationRequest;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetClinicsCommand extends Command
{
    protected $signature = 'tenancy:reset-clinics
                            {--force : Ejecuta el borrado real (sin --force solo dry-run)}';

    protected $description = 'Limpia centros/tenants/solicitudes/billing para reinicio controlado, preservando usuarios y roles base.';

    public function handle(): int
    {
        $connection = (string) config('tenancy.central_connection', config('database.default', 'mysql'));
        $database = (string) DB::connection($connection)->getDatabaseName();
        $force = (bool) $this->option('force');

        $centros = Centros_Medico::query()->orderBy('id')->get(['id', 'nombre_centro', 'tenancy_mode']);
        $centroIds = $centros->pluck('id')->map(fn ($id) => (int) $id)->values();

        $tenants = Tenant::query()->whereIn('centro_id', $centroIds)->get(['id', 'centro_id']);
        $tenantIds = $tenants->pluck('id')->map(fn ($id) => (string) $id)->values();
        $registrationIds = Schema::connection($connection)->hasTable('clinic_registration_requests')
            ? ClinicRegistrationRequest::query()->pluck('id')->values()
            : collect();

        $tablesByCentroId = $this->discoverTablesWithColumn($connection, $database, 'centro_id')
            ->reject(fn (string $table) => in_array($table, [
                'centros_medicos',
                'tenants',
                'clinic_registration_requests',
                'billing_subscriptions',
                'billing_override_audits',
                'users',
            ], true))
            ->values()
            ->all();

        $tablesByCentroMedicoId = $this->discoverTablesWithColumn($connection, $database, 'centro_medico_id')
            ->reject(fn (string $table) => in_array($table, ['centros_medicos'], true))
            ->values()
            ->all();

        $deleteByCentroIdOrder = $this->resolveDeleteOrder($connection, $database, $tablesByCentroId);
        $deleteByCentroMedicoOrder = array_reverse($tablesByCentroMedicoId);

        $this->line("Conexion central: {$connection}");
        $this->line("Base central: {$database}");
        $this->newLine();
        $this->line('Resumen de borrado objetivo:');
        $this->line(' - centros_medicos: ' . $centros->count());
        $this->line(' - tenants: ' . $tenants->count());
        $this->line(' - clinic_registration_requests: ' . (Schema::connection($connection)->hasTable('clinic_registration_requests')
            ? ClinicRegistrationRequest::query()->count()
            : 0));
        $this->line(' - billing_subscriptions: ' . (Schema::connection($connection)->hasTable('billing_subscriptions')
            ? BillingSubscription::query()->count()
            : 0));
        $this->line(' - billing_webhook_events: ' . (Schema::connection($connection)->hasTable('billing_webhook_events')
            ? BillingWebhookEvent::query()->count()
            : 0));
        $this->line(' - billing_override_audits: ' . (Schema::connection($connection)->hasTable('billing_override_audits')
            ? BillingOverrideAudit::query()->count()
            : 0));
        $this->line(' - users preservados (solo se limpia users.centro_id)');
        $this->newLine();

        if ($centros->isNotEmpty()) {
            $this->table(
                ['Centro ID', 'Nombre', 'Modo'],
                $centros->map(fn ($c) => [$c->id, (string) $c->nombre_centro, (string) $c->tenancy_mode])->all()
            );
        }

        if (! $force) {
            $this->warn('Dry-run completado. No se borraron datos.');
            $this->line('Ejecuta con --force para aplicar la limpieza real.');
            return self::SUCCESS;
        }

        if ($centros->isEmpty()) {
            $this->info('No hay centros para limpiar.');
            return self::SUCCESS;
        }

        DB::connection($connection)
            ->table('users')
            ->whereIn('centro_id', $centroIds)
            ->update(['centro_id' => null]);

        foreach ($deleteByCentroMedicoOrder as $table) {
            DB::connection($connection)
                ->table($table)
                ->whereIn('centro_medico_id', $centroIds)
                ->delete();
        }

        foreach ($deleteByCentroIdOrder as $table) {
            DB::connection($connection)
                ->table($table)
                ->whereIn('centro_id', $centroIds)
                ->delete();
        }

        if (Schema::connection($connection)->hasTable('tenant_user_impersonation_tokens')) {
            DB::connection($connection)
                ->table('tenant_user_impersonation_tokens')
                ->whereIn('tenant_id', $tenantIds)
                ->delete();
        }

        if (Schema::connection($connection)->hasTable('billing_override_audits')) {
            BillingOverrideAudit::query()->whereIn('centro_id', $centroIds)->delete();
        }

        if (Schema::connection($connection)->hasTable('billing_subscriptions')) {
            BillingSubscription::query()->whereIn('centro_id', $centroIds)->delete();
            BillingSubscription::query()->whereIn('clinic_registration_request_id', $registrationIds)->delete();
        }

        if (Schema::connection($connection)->hasTable('billing_webhook_events')) {
            BillingWebhookEvent::query()->delete();
        }

        if (Schema::connection($connection)->hasTable('clinic_registration_requests')) {
            ClinicRegistrationRequest::query()->delete();
        }

        DB::connection($connection)
            ->table('domains')
            ->whereIn('tenant_id', $tenantIds)
            ->delete();

        Tenant::query()->whereIn('id', $tenantIds)->delete();
        Centros_Medico::query()->whereIn('id', $centroIds)->forceDelete();

        $this->info('Reset de clinicas completado.');
        $this->line('Usuarios/roles base preservados.');

        return self::SUCCESS;
    }

    /**
     * @return Collection<int, string>
     */
    protected function discoverTablesWithColumn(string $connection, string $database, string $column): Collection
    {
        $rows = DB::connection($connection)->select(
            "SELECT TABLE_NAME
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = ?
               AND COLUMN_NAME = ?",
            [$database, $column]
        );

        return collect($rows)
            ->map(fn ($row) => (string) $row->TABLE_NAME)
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * @param array<int, string> $tables
     * @return array<int, string>
     */
    protected function resolveDeleteOrder(string $connection, string $database, array $tables): array
    {
        if ($tables === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($tables), '?'));
        $sql = "SELECT TABLE_NAME AS child_table, REFERENCED_TABLE_NAME AS parent_table
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = ?
                  AND REFERENCED_TABLE_SCHEMA = ?
                  AND REFERENCED_TABLE_NAME IS NOT NULL
                  AND TABLE_NAME IN ({$placeholders})
                  AND REFERENCED_TABLE_NAME IN ({$placeholders})";

        $bindings = array_merge([$database, $database], $tables, $tables);
        $rows = DB::connection($connection)->select($sql, $bindings);

        $childrenByParent = [];
        $inDegree = array_fill_keys($tables, 0);

        foreach ($rows as $row) {
            $parent = (string) $row->parent_table;
            $child = (string) $row->child_table;

            if (! isset($childrenByParent[$parent])) {
                $childrenByParent[$parent] = [];
            }

            if (! in_array($child, $childrenByParent[$parent], true)) {
                $childrenByParent[$parent][] = $child;
                $inDegree[$child]++;
            }
        }

        $queue = collect($tables)->filter(fn (string $table) => $inDegree[$table] === 0)->values()->all();
        $topological = [];

        while ($queue !== []) {
            $node = array_shift($queue);
            $topological[] = $node;

            foreach ($childrenByParent[$node] ?? [] as $child) {
                $inDegree[$child]--;
                if ($inDegree[$child] === 0) {
                    $queue[] = $child;
                }
            }
        }

        if (count($topological) !== count($tables)) {
            $missing = array_values(array_diff($tables, $topological));
            $topological = array_merge($topological, $missing);
        }

        return array_reverse($topological);
    }
}
