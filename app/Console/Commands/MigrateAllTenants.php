<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class MigrateAllTenants extends Command
{
    protected $signature = 'tenants:migrate-fresh';
    protected $description = 'Migrar todas las BDs de tenants desde cero';

    public function handle()
    {
        $tenants = Tenant::all();
        
        $this->info("Migrando {$tenants->count()} tenants...\n");

        foreach ($tenants as $tenant) {
            $dbName = $tenant->database()->getName();
            
            $this->line("Tenant: {$tenant->id} (DB: {$dbName})");
            
            try {
                // Recrear base de datos completa
                DB::statement("DROP DATABASE IF EXISTS `{$dbName}`");
                DB::statement("CREATE DATABASE `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                // Configurar conexión
                Config::set('database.connections.tenant.database', $dbName);
                DB::purge('tenant');
                DB::reconnect('tenant');
                
                // Ejecutar migraciones
                Artisan::call('migrate', [
                    '--database' => 'tenant',
                    '--path' => 'database/migrations/tenant',
                    '--force' => true,
                ]);
                
                // Contar tablas
                $tables = DB::connection('tenant')->select('SHOW TABLES');
                $this->info("  ✓ Migraciones ejecutadas (" . count($tables) . " tablas)");
                
            } catch (\Exception $e) {
                $this->error("  ✗ Error: " . $e->getMessage());
            }
        }
        
        $this->info("\n✓ Proceso completado");
        
        return 0;
    }
}
