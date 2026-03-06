<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class TestTenantConnection extends Command
{
    protected $signature = 'tenant:test {centro_id}';
    protected $description = 'Probar conexión a un tenant específico';

    public function handle()
    {
        $centroId = $this->argument('centro_id');
        
        $tenant = Tenant::where('centro_id', $centroId)->first();
        
        if (!$tenant) {
            $this->error("Tenant no encontrado para centro: {$centroId}");
            return 1;
        }

        $this->info("Inicializando tenant: {$tenant->id}");
        $this->info("Database: {$tenant->database()->getName()}");

        tenancy()->initialize($tenant);

        $this->info("\n📊 Tablas en la base de datos del tenant:");
        
        try {
            $tables = DB::select('SHOW TABLES');
            foreach ($tables as $table) {
                $tableName = array_values((array) $table)[0];
                $count = DB::table($tableName)->count();
                $this->line("  - {$tableName}: {$count} registros");
            }

            $this->info("\n✓ Conexión exitosa");
        } catch (\Exception $e) {
            $this->error("Error conectando al tenant: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
