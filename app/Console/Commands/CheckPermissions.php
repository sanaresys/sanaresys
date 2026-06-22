<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CheckPermissions extends Command
{
    protected $signature = 'check:permissions';
    protected $description = 'Verificar que los permisos y roles estén correctamente configurados';

    public function handle()
    {
        $this->info('=== VERIFICACIÓN DE PERMISOS Y ROLES ===');

        // 1. Verificar permisos CAI
        $this->info("\n1. Verificando permisos CAI...");
        $caiPermissions = [
            'crear cai_correlativos',
            'actualizar cai_correlativos', 
            'borrar cai_correlativos',
            'ver cai_correlativos',
            'crear cai_autorizaciones',
            'actualizar cai_autorizaciones',
            'borrar cai_autorizaciones',
            'ver cai_autorizaciones'
        ];

        foreach ($caiPermissions as $permission) {
            try {
                $perm = Permission::findByName($permission);
                $this->info("✅ {$permission}: EXISTE (ID: {$perm->id})");
            } catch (\Exception $e) {
                $this->error("❌ {$permission}: NO EXISTE");
            }
        }

        // 2. Verificar roles
        $this->info("\n2. Verificando roles...");
        $roles = ['root', 'administrador', 'medico'];
        foreach ($roles as $roleName) {
            try {
                $role = Role::findByName($roleName);
                $permCount = $role->permissions->count();
                $this->info("✅ Rol '{$roleName}': EXISTE con {$permCount} permisos");
            } catch (\Exception $e) {
                $this->error("❌ Rol '{$roleName}': NO EXISTE");
            }
        }

        // 3. Verificar permisos de exámenes
        $this->info("\n3. Verificando permisos de exámenes...");
        $examenPermissions = ['ver examenes', 'crear examenes', 'actualizar examenes', 'borrar examenes'];
        foreach ($examenPermissions as $permission) {
            try {
                $perm = Permission::findByName($permission);
                $this->info("✅ {$permission}: EXISTE");
            } catch (\Exception $e) {
                $this->error("❌ {$permission}: NO EXISTE");
            }
        }

        // 4. Verificar que root tiene permisos CAI
        $this->info("\n4. Verificando permisos del rol root...");
        try {
            $rootRole = Role::findByName('root');
            foreach ($caiPermissions as $permission) {
                if ($rootRole->hasPermissionTo($permission)) {
                    $this->info("✅ Root tiene: {$permission}");
                } else {
                    $this->error("❌ Root NO tiene: {$permission}");
                }
            }
        } catch (\Exception $e) {
            $this->error("❌ Error verificando rol root: " . $e->getMessage());
        }

        $this->info("\n=== VERIFICACIÓN COMPLETADA ===");
    }
}
