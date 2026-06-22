<?php

namespace App\Services;

use App\Models\Centros_Medico;
use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TenantIdentityRenameService
{
    public function __construct(
        protected TenantIdentityService $identityService,
    ) {
    }

    public function rename(Centros_Medico $centro, string $nuevoNombre, bool $persistCentro = true): RenameResult
    {
        if (($centro->tenancy_mode ?? 'legacy') !== 'domain') {
            throw ValidationException::withMessages([
                'tenancy_mode' => 'Solo se puede renombrar identidad tenant en modo domain.',
            ]);
        }

        $tenant = Tenant::where('centro_id', $centro->id)->first();
        if (! $tenant) {
            throw ValidationException::withMessages([
                'tenant' => 'No existe un tenant asociado al centro.',
            ]);
        }

        $newSlug = $this->identityService->generateSlug($nuevoNombre);
        $oldSlug = (string) $centro->slug;

        if ($newSlug === $oldSlug) {
            $oldDomain = (string) ($tenant->getPrimaryDomain() ?? '');
            $oldDatabase = $tenant->database()->getName();

            return new RenameResult($oldSlug, $newSlug, $oldDomain, $oldDomain, $oldDatabase, $oldDatabase);
        }

        $this->identityService->validateSlugAvailable($newSlug, $centro->id);

        $oldDomain = (string) ($tenant->getPrimaryDomain() ?? '');
        $newDomain = $this->identityService->buildPrimaryDomain($newSlug);

        $oldDatabase = $tenant->database()->getName();
        $newDatabase = $newSlug;

        $maintenanceKey = "tenant:maintenance:{$tenant->id}";
        Cache::put($maintenanceKey, true, now()->addMinutes(20));

        $createdDomain = false;

        try {
            if (! $tenant->domains()->where('domain', $newDomain)->exists()) {
                $tenant->createDomain($newDomain);
                $createdDomain = true;
            }

            $this->renameDatabase($oldDatabase, $newDatabase);

            $tenant->forceFill([
                'tenancy_db_name' => $newDatabase,
                'tenancy_primary_domain' => $newDomain,
                'tenancy_mode' => 'domain',
            ]);
            $tenant->save();

            DB::purge((string) config('tenancy.tenant_connection', 'tenant'));

            if ($persistCentro) {
                $centro->slug = $newSlug;
                $centro->saveQuietly();
            } else {
                $centro->slug = $newSlug;
            }

            Log::info('Se renombro identidad tenant automaticamente.', [
                'centro_id' => $centro->id,
                'tenant_id' => $tenant->id,
                'old_domain' => $oldDomain,
                'new_domain' => $newDomain,
                'old_database' => $oldDatabase,
                'new_database' => $newDatabase,
            ]);
        } catch (\Throwable $e) {
            if ($createdDomain) {
                try {
                    $tenant->domains()->where('domain', $newDomain)->delete();
                } catch (\Throwable $domainRollbackError) {
                    Log::error('Error en rollback de alias de dominio durante rename.', [
                        'tenant_id' => $tenant->id,
                        'domain' => $newDomain,
                        'error' => $domainRollbackError->getMessage(),
                    ]);
                }
            }

            throw $e;
        } finally {
            Cache::forget($maintenanceKey);
        }

        return new RenameResult($oldSlug, $newSlug, $oldDomain, $newDomain, $oldDatabase, $newDatabase);
    }

    protected function renameDatabase(string $oldDatabase, string $newDatabase): void
    {
        if ($oldDatabase === $newDatabase) {
            return;
        }

        $connectionName = config('tenancy.database.central_connection', config('database.default'));
        $connection = DB::connection($connectionName);

        $oldExists = ! empty($connection->select(
            "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?",
            [$oldDatabase]
        ));
        if (! $oldExists) {
            throw ValidationException::withMessages([
                'database' => "La base de datos origen {$oldDatabase} no existe.",
            ]);
        }

        $newExists = ! empty($connection->select(
            "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?",
            [$newDatabase]
        ));
        if ($newExists) {
            throw ValidationException::withMessages([
                'database' => "La base de datos destino {$newDatabase} ya existe.",
            ]);
        }

        $connection->statement("CREATE DATABASE `{$newDatabase}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        $movedTables = [];

        try {
            $connection->statement('SET FOREIGN_KEY_CHECKS=0');

            $tables = $connection->select(
                "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE'",
                [$oldDatabase]
            );

            foreach ($tables as $table) {
                $tableName = str_replace('`', '``', (string) $table->TABLE_NAME);
                $connection->statement("RENAME TABLE `{$oldDatabase}`.`{$tableName}` TO `{$newDatabase}`.`{$tableName}`");
                $movedTables[] = $tableName;
            }

            $connection->statement('SET FOREIGN_KEY_CHECKS=1');
            $connection->statement("DROP DATABASE `{$oldDatabase}`");
        } catch (\Throwable $e) {
            $connection->statement('SET FOREIGN_KEY_CHECKS=1');

            foreach (array_reverse($movedTables) as $tableName) {
                try {
                    $connection->statement("RENAME TABLE `{$newDatabase}`.`{$tableName}` TO `{$oldDatabase}`.`{$tableName}`");
                } catch (\Throwable $rollbackError) {
                    Log::error('Error en rollback de renombrado de base de datos.', [
                        'table' => $tableName,
                        'old_database' => $oldDatabase,
                        'new_database' => $newDatabase,
                        'error' => $rollbackError->getMessage(),
                    ]);
                }
            }

            throw $e;
        }
    }
}
