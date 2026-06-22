<?php

require_once 'vendor/autoload.php';

// Test específico para verificar los permisos CAI corregidos
echo "=== VERIFICACIÓN DE PERMISOS CAI CORREGIDOS ===\n";

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

echo "\n1. Verificando permisos CAI específicos...\n";
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
        echo "✅ {$permission}: EXISTE (ID: {$perm->id})\n";
    } catch (Exception $e) {
        echo "❌ {$permission}: NO EXISTE - {$e->getMessage()}\n";
    }
}

echo "\n2. Verificando que el rol 'root' tiene los permisos CAI...\n";
try {
    $rootRole = Role::findByName('root');
    echo "Rol 'root' encontrado con {$rootRole->permissions->count()} permisos totales\n";
    
    foreach ($caiPermissions as $permission) {
        if ($rootRole->hasPermissionTo($permission)) {
            echo "✅ Root tiene: {$permission}\n";
        } else {
            echo "❌ Root NO tiene: {$permission}\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error verificando rol root: " . $e->getMessage() . "\n";
}

echo "\n3. Conteo de permisos por rol...\n";
$roles = ['root', 'administrador', 'medico'];
foreach ($roles as $roleName) {
    try {
        $role = Role::findByName($roleName);
        echo "✅ Rol '{$roleName}': {$role->permissions->count()} permisos\n";
    } catch (Exception $e) {
        echo "❌ Rol '{$roleName}': ERROR - {$e->getMessage()}\n";
    }
}

echo "\n4. Verificando permisos de exámenes específicos...\n";
$examenPermissions = ['ver examenes', 'crear examenes', 'actualizar examenes', 'borrar examenes'];
foreach ($examenPermissions as $permission) {
    try {
        $perm = Permission::findByName($permission);
        echo "✅ {$permission}: EXISTE\n";
    } catch (Exception $e) {
        echo "❌ {$permission}: NO EXISTE\n";
    }
}

echo "\n=== RESULTADO FINAL ===\n";
echo "Si todos los permisos CAI muestran ✅, el problema está resuelto!\n";
echo "Si hay algún ❌, revisar el seeder y ejecutar nuevamente.\n";
