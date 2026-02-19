<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Tenant;
use App\Models\Centros_Medico;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

echo "═══════════════════════════════════════════════════════════════════" . PHP_EOL;
echo "EJEMPLO: CREAR USUARIO ADMIN PARA UN CENTRO" . PHP_EOL;
echo "═══════════════════════════════════════════════════════════════════" . PHP_EOL . PHP_EOL;

// 1. Seleccionar el centro
$centroId = 1;
$centro = Centros_Medico::find($centroId);

if (!$centro) {
    echo "❌ Centro no encontrado" . PHP_EOL;
    exit(1);
}

echo "1. Centro seleccionado:" . PHP_EOL;
echo "   ID: {$centro->id}" . PHP_EOL;
echo "   Nombre: {$centro->nombre_centro}" . PHP_EOL;
echo PHP_EOL;

// 2. Obtener el tenant de ese centro
$tenant = Tenant::where('centro_id', $centroId)->first();

if (!$tenant) {
    echo "❌ Tenant no encontrado para este centro" . PHP_EOL;
    exit(1);
}

echo "2. Tenant encontrado: {$tenant->id}" . PHP_EOL;
echo PHP_EOL;

// 3. Inicializar el contexto del tenant
echo "3. Inicializando contexto del tenant..." . PHP_EOL;
tenancy()->initialize($tenant);
echo "   ✓ Contexto inicializado - ahora operamos en BD: " . DB::connection('tenant')->getDatabaseName() . PHP_EOL;
echo PHP_EOL;

// 4. Verificar si el usuario ya existe
$email = 'admin.centro1@clinica.hn';
$usuarioExistente = DB::connection('tenant')->table('users')->where('email', $email)->first();

if ($usuarioExistente) {
    echo "⚠️ El usuario {$email} ya existe en este tenant" . PHP_EOL;
    echo "   ID: {$usuarioExistente->id}" . PHP_EOL;
    echo "   Nombre: {$usuarioExistente->name}" . PHP_EOL;
    tenancy()->end();
    exit(0);
}

// 5. Crear el usuario en la BD del tenant
echo "4. Creando usuario administrador..." . PHP_EOL;

try {
    $usuario = User::create([
        'name' => 'Admin Centro 1',
        'email' => $email,
        'password' => Hash::make('password123'),
        'centro_id' => $centroId,
    ]);
    
    echo "   ✓ Usuario creado exitosamente" . PHP_EOL;
    echo "   ID: {$usuario->id}" . PHP_EOL;
    echo "   Nombre: {$usuario->name}" . PHP_EOL;
    echo "   Email: {$usuario->email}" . PHP_EOL;
    echo "   Centro ID: {$usuario->centro_id}" . PHP_EOL;
    echo PHP_EOL;
    
    // 6. Asignar rol de admin (si existe)
    echo "5. Asignando rol de administrador..." . PHP_EOL;
    
    // Verificar si el rol admin existe en el tenant
    $rolAdmin = Role::where('name', 'admin')->first();
    
    if ($rolAdmin) {
        $usuario->assignRole('admin');
        echo "   ✓ Rol 'admin' asignado" . PHP_EOL;
    } else {
        echo "   ⚠️ Rol 'admin' no existe en este tenant" . PHP_EOL;
        echo "   ℹ️ Creando rol admin..." . PHP_EOL;
        
        $rolAdmin = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $usuario->assignRole('admin');
        echo "   ✓ Rol 'admin' creado y asignado" . PHP_EOL;
    }
    
} catch (\Exception $e) {
    echo "   ❌ Error al crear usuario: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL;

// 7. Finalizar contexto tenant
tenancy()->end();

// 8. Verificar resultado
echo "═══════════════════════════════════════════════════════════════════" . PHP_EOL;
echo "VERIFICACIÓN FINAL" . PHP_EOL;
echo "═══════════════════════════════════════════════════════════════════" . PHP_EOL . PHP_EOL;

// Volver a inicializar para verificar
tenancy()->initialize($tenant);

$usuarioCreado = DB::connection('tenant')->table('users')
    ->where('email', $email)
    ->first();

if ($usuarioCreado) {
    echo "✅ Usuario confirmado en BD del tenant:" . PHP_EOL;
    echo "   Base de datos: " . DB::connection('tenant')->getDatabaseName() . PHP_EOL;
    echo "   ID: {$usuarioCreado->id}" . PHP_EOL;
    echo "   Nombre: {$usuarioCreado->name}" . PHP_EOL;
    echo "   Email: {$usuarioCreado->email}" . PHP_EOL;
    echo "   Centro ID: {$usuarioCreado->centro_id}" . PHP_EOL;
    echo PHP_EOL;
    
    echo "📊 Total usuarios en este tenant: " . DB::connection('tenant')->table('users')->count() . PHP_EOL;
}

tenancy()->end();

echo PHP_EOL;
echo "╔═══════════════════════════════════════════════════════════════════╗" . PHP_EOL;
echo "║   SÍ, el usuario queda asignado al centro en su BD tenant      ║" . PHP_EOL;
echo "╚═══════════════════════════════════════════════════════════════════╝" . PHP_EOL;
