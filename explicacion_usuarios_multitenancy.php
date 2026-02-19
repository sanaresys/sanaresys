<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Tenant;
use App\Models\Centros_Medico;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "╔═══════════════════════════════════════════════════════════════════╗" . PHP_EOL;
echo "║   GESTIÓN DE USUARIOS EN SISTEMA MULTI-TENANT                  ║" . PHP_EOL;
echo "╚═══════════════════════════════════════════════════════════════════╝" . PHP_EOL . PHP_EOL;

// 1. Verificar usuarios en BD CENTRAL
echo "═══════════════════════════════════════════════════════════════════" . PHP_EOL;
echo "1. USUARIOS EN BASE DE DATOS CENTRAL (db_clinica)" . PHP_EOL;
echo "═══════════════════════════════════════════════════════════════════" . PHP_EOL;

// Asegurarse de estar en contexto central
tenancy()->end();

$usuariosCentral = DB::connection('mysql')->table('users')->get();
echo "   Total usuarios en central: " . $usuariosCentral->count() . PHP_EOL;

foreach ($usuariosCentral as $user) {
    echo "   - ID: {$user->id} | {$user->name} | {$user->email} | Centro: " . ($user->centro_id ?? 'NULL') . PHP_EOL;
}

echo PHP_EOL;

// 2. Verificar usuarios en cada TENANT
echo "═══════════════════════════════════════════════════════════════════" . PHP_EOL;
echo "2. USUARIOS EN BASES DE DATOS TENANT" . PHP_EOL;
echo "═══════════════════════════════════════════════════════════════════" . PHP_EOL;

$tenants = Tenant::all();

foreach ($tenants as $tenant) {
    tenancy()->initialize($tenant);
    
    $usuariosTenant = DB::connection('tenant')->table('users')->get();
    
    echo "   📂 Tenant: {$tenant->id} (Centro ID: {$tenant->centro_id})" . PHP_EOL;
    echo "      Total usuarios: " . $usuariosTenant->count() . PHP_EOL;
    
    foreach ($usuariosTenant as $user) {
        echo "      - ID: {$user->id} | {$user->name} | {$user->email} | Centro: " . ($user->centro_id ?? 'NULL') . PHP_EOL;
    }
    
    echo PHP_EOL;
    
    tenancy()->end();
}

// 3. Explicación del sistema actual
echo "═══════════════════════════════════════════════════════════════════" . PHP_EOL;
echo "3. CÓMO FUNCIONA ACTUALMENTE" . PHP_EOL;
echo "═══════════════════════════════════════════════════════════════════" . PHP_EOL;
echo PHP_EOL;

echo "📌 ARQUITECTURA ACTUAL:" . PHP_EOL;
echo "   • Usuario ROOT debe estar en BD CENTRAL" . PHP_EOL;
echo "   • Usuario ROOT tiene acceso a todos los centros" . PHP_EOL;
echo "   • Usuarios de centros específicos están en BD TENANT" . PHP_EOL;
echo "   • Cada centro tiene su propia BD con sus usuarios" . PHP_EOL;
echo PHP_EOL;

echo "🔄 FLUJO CORRECTO:" . PHP_EOL;
echo "   1. Usuario ROOT se autentica contra BD CENTRAL" . PHP_EOL;
echo "   2. ROOT selecciona un centro para administrar" . PHP_EOL;
echo "   3. Sistema inicializa contexto tenant de ese centro" . PHP_EOL;
echo "   4. ROOT crea usuario en la BD del TENANT activo" . PHP_EOL;
echo "   5. Usuario queda asignado con centro_id del tenant" . PHP_EOL;
echo PHP_EOL;

echo "⚠️ IMPORTANTE:" . PHP_EOL;
echo "   • Al crear usuario, debes estar en contexto del tenant correcto" . PHP_EOL;
echo "   • El centro_id se asigna automáticamente por el contexto" . PHP_EOL;
echo "   • Usuarios de centros NO deben estar en BD central" . PHP_EOL;
echo PHP_EOL;

// 4. Ejemplo de código para crear usuario correctamente
echo "═══════════════════════════════════════════════════════════════════" . PHP_EOL;
echo "4. EJEMPLO DE CÓDIGO PARA CREAR USUARIO" . PHP_EOL;
echo "═══════════════════════════════════════════════════════════════════" . PHP_EOL;
echo PHP_EOL;

echo "```php" . PHP_EOL;
echo "// En tu controlador o panel de Filament" . PHP_EOL;
echo PHP_EOL;
echo "// 1. Obtener el centro seleccionado" . PHP_EOL;
echo "\$centroId = \$request->input('centro_id');" . PHP_EOL;
echo PHP_EOL;
echo "// 2. Inicializar el tenant de ese centro" . PHP_EOL;
echo "\$tenant = Tenant::where('centro_id', \$centroId)->first();" . PHP_EOL;
echo "tenancy()->initialize(\$tenant);" . PHP_EOL;
echo PHP_EOL;
echo "// 3. Crear el usuario (automáticamente se crea en la BD tenant)" . PHP_EOL;
echo "\$usuario = User::create([" . PHP_EOL;
echo "    'name' => \$request->name," . PHP_EOL;
echo "    'email' => \$request->email," . PHP_EOL;
echo "    'password' => Hash::make(\$request->password)," . PHP_EOL;
echo "    'centro_id' => \$centroId," . PHP_EOL;
echo "]);" . PHP_EOL;
echo PHP_EOL;
echo "// 4. Asignar rol (los roles están en la BD del tenant)" . PHP_EOL;
echo "\$usuario->assignRole('admin'); // o 'medico', 'recepcionista', etc." . PHP_EOL;
echo PHP_EOL;
echo "// 5. Finalizar contexto tenant" . PHP_EOL;
echo "tenancy()->end();" . PHP_EOL;
echo "```" . PHP_EOL;
echo PHP_EOL;

echo "╔═══════════════════════════════════════════════════════════════════╗" . PHP_EOL;
echo "║   ✅ EL USUARIO QUEDA REGISTRADO EN LA BD DEL TENANT            ║" . PHP_EOL;
echo "╚═══════════════════════════════════════════════════════════════════╝" . PHP_EOL;
