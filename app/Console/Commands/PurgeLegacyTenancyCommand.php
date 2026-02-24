<?php

namespace App\Console\Commands;

use App\Models\Centros_Medico;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PurgeLegacyTenancyCommand extends Command
{
    protected $signature = 'tenancy:purge-legacy
                            {--force : Ejecuta el borrado real (sin --force solo dry-run)}';

    protected $description = 'Elimina centros/tenants legacy y sus datos centrales relacionados por centro_id.';

    public function handle(): int
    {
        $connection = (string) config('tenancy.central_connection', config('database.default', 'mysql'));
        $database = (string) DB::connection($connection)->getDatabaseName();
        $force = (bool) $this->option('force');

        $legacyCentros = Centros_Medico::query()
            ->where('tenancy_mode', 'legacy')
            ->orderBy('id')
            ->get(['id', 'nombre_centro', 'tenancy_mode']);

        if ($legacyCentros->isEmpty()) {
            $this->info('No hay centros con tenancy_mode=legacy. No hay nada que purgar.');
            return self::SUCCESS;
        }

        $legacyCentroIds = $legacyCentros->pluck('id')->map(fn ($id) => (int) $id)->values();
        $legacyTenants = Tenant::query()
            ->whereIn('centro_id', $legacyCentroIds)
            ->orderBy('id')
            ->get();

        $legacyDatabases = $legacyTenants
            ->map(fn (Tenant $tenant) => (string) $tenant->getDatabaseName())
            ->filter()
            ->unique()
            ->values();

        $tablesWithCentroId = $this->discoverCentralTablesWithCentroId($connection, $database);
        $deleteOrder = $this->resolveDeleteOrder($connection, $database, $tablesWithCentroId);
        $tableImpact = $this->countRowsByTable($connection, $deleteOrder, $legacyCentroIds);

        $this->line("Conexion central: {$connection}");
        $this->line("Base central: {$database}");
        $this->newLine();

        $this->table(
            ['Centro ID', 'Nombre', 'Modo'],
            $legacyCentros->map(fn ($c) => [$c->id, (string) $c->nombre_centro, (string) $c->tenancy_mode])->all()
        );

        $this->newLine();
        $this->table(
            ['Tenant ID', 'Centro ID', 'DB esperada', 'Dominio primario'],
            $legacyTenants->map(function (Tenant $tenant) {
                return [
                    $tenant->id,
                    $tenant->centro_id,
                    $tenant->getDatabaseName(),
                    $tenant->getPrimaryDomain() ?? '-',
                ];
            })->all()
        );

        $this->newLine();
        $this->table(
            ['Tabla central (centro_id)', 'Registros candidatos'],
            collect($tableImpact)->map(fn (int $count, string $table) => [$table, $count])->all()
        );

        if (! $force) {
            $this->warn('Dry-run completado. No se borraron datos.');
            $this->line('Ejecuta con --force para aplicar la purga real.');
            return self::SUCCESS;
        }

        $this->warn('Modo --force activo: iniciando purga legacy definitiva.');

        foreach ($deleteOrder as $table) {
            $deleted = DB::connection($connection)
                ->table($table)
                ->whereIn('centro_id', $legacyCentroIds)
                ->delete();

            $this->line(" - {$table}: {$deleted} eliminados");
        }

        foreach ($legacyTenants as $tenant) {
            $tenantId = (string) $tenant->id;
            $dbName = (string) $tenant->getDatabaseName();
            $tenant->delete();
            $this->line(" - tenant {$tenantId} eliminado (db: {$dbName})");
        }

        foreach ($legacyCentros as $centro) {
            $centroId = (int) $centro->id;
            $centroName = (string) $centro->nombre_centro;
            $centro->forceDelete();
            $this->line(" - centro {$centroId} eliminado ({$centroName})");
        }

        $remainingLegacyCentros = Centros_Medico::query()
            ->where('tenancy_mode', 'legacy')
            ->count();

        $existingLegacyDbSchemas = $this->findExistingSchemas($connection, $legacyDatabases->all());

        $this->newLine();
        $this->line("Verificacion final: centros legacy restantes = {$remainingLegacyCentros}");
        $this->line('Verificacion final: DBs legacy restantes = ' . ($existingLegacyDbSchemas->isEmpty()
            ? '0'
            : $existingLegacyDbSchemas->implode(', ')));

        if ($remainingLegacyCentros > 0 || $existingLegacyDbSchemas->isNotEmpty()) {
            $this->error('La purga no quedo completa. Revisa logs/constraints para terminar limpieza.');
            return self::FAILURE;
        }

        $this->info('Purga legacy completada correctamente.');
        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    protected function discoverCentralTablesWithCentroId(string $connection, string $database): array
    {
        $rows = DB::connection($connection)->select(
            "SELECT TABLE_NAME
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = ?
               AND COLUMN_NAME = 'centro_id'",
            [$database]
        );

        $tables = collect($rows)
            ->map(fn ($row) => (string) $row->TABLE_NAME)
            ->filter()
            ->reject(fn (string $table) => in_array($table, ['centros_medicos', 'tenants'], true))
            ->unique()
            ->values()
            ->all();

        return $tables;
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

    /**
     * @param array<int, string> $tables
     * @param Collection<int, int> $legacyCentroIds
     * @return array<string, int>
     */
    protected function countRowsByTable(string $connection, array $tables, Collection $legacyCentroIds): array
    {
        $impact = [];

        foreach ($tables as $table) {
            $impact[$table] = (int) DB::connection($connection)
                ->table($table)
                ->whereIn('centro_id', $legacyCentroIds)
                ->count();
        }

        return $impact;
    }

    /**
     * @param array<int, string> $schemas
     * @return Collection<int, string>
     */
    protected function findExistingSchemas(string $connection, array $schemas): Collection
    {
        $schemas = array_values(array_filter(array_map('strval', $schemas)));
        if ($schemas === []) {
            return collect();
        }

        $placeholders = implode(',', array_fill(0, count($schemas), '?'));
        $sql = "SELECT SCHEMA_NAME
                FROM INFORMATION_SCHEMA.SCHEMATA
                WHERE SCHEMA_NAME IN ({$placeholders})";

        $rows = DB::connection($connection)->select($sql, $schemas);

        return collect($rows)
            ->map(fn ($row) => (string) $row->SCHEMA_NAME)
            ->filter()
            ->values();
    }
}
