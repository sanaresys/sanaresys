<?php
/**
 * REGISTRO COMPLETO: Nuevo Centro Médico + Usuario
 * Simula el flujo: Registro → Login → Onboarding
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Centros_Medico;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

echo "\n╔══════════════════════════════════════════════════════════════════╗\n";
echo "║  REGISTRO COMPLETO: Nuevo Centro + Usuario                      ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

// Datos del nuevo centro
$datosCentro = [
    'nombre' => 'Clínica San Rafael',
    'rtn' => '08011999012345',
    'telefono' => '+504 2234-5678',
    'email' => 'info@clinicasanrafael.hn',
    'direccion' => 'Colonia Kennedy, Tegucigalpa',
];

// Datos del usuario administrador
$datosUsuario = [
    'name' => 'Dr. Juan Pérez',
    'email' => 'juan.perez@clinicasanrafael.hn',
    'password' => 'password123',
];

echo "📋 DATOS DEL NUEVO REGISTRO:\n";
echo "────────────────────────────────────────────────────────────────\n";
echo "Centro:   {$datosCentro['nombre']}\n";
echo "RTN:      {$datosCentro['rtn']}\n";
echo "Email:    {$datosCentro['email']}\n";
echo "Usuario:  {$datosUsuario['name']}\n";
echo "Email:    {$datosUsuario['email']}\n";
echo "Password: {$datosUsuario['password']}\n\n";

// Verificar si ya existe
$usuarioExistente = User::on('mysql')->where('email', $datosUsuario['email'])->first();
if ($usuarioExistente) {
    echo "⚠️  Este usuario ya existe. ¿Deseas eliminarlo y crear uno nuevo? (S/N)\n";
    echo "   Presiona CTRL+C para cancelar o espera 5 segundos para continuar...\n\n";
    sleep(5);
    
    echo "🗑️  Eliminando usuario existente...\n";
    
    // Buscar centro del usuario
    if ($usuarioExistente->centro_id) {
        $centroViejo = Centros_Medico::on('mysql')->find($usuarioExistente->centro_id);
        if ($centroViejo) {
            echo "🗑️  Eliminando centro: {$centroViejo->nombre}\n";
            
            // Eliminar tenant si existe
            $tenant = \App\Models\Tenant::where('centro_id', $centroViejo->id)->first();
            if ($tenant) {
                echo "🗑️  Eliminando tenant y base de datos: {$tenant->id}\n";
                try {
                    DB::statement("DROP DATABASE IF EXISTS `{$tenant->id}`");
                    $tenant->delete();
                } catch (\Exception $e) {
                    echo "⚠️  Error eliminando tenant: {$e->getMessage()}\n";
                }
            }
            
            $centroViejo->delete();
        }
    }
    
    $usuarioExistente->delete();
    echo "✅ Usuario existente eliminado\n\n";
}

try {
    DB::beginTransaction();
    
    echo "🏥 PASO 1: Crear Centro Médico\n";
    echo "────────────────────────────────────────────────────────────────\n";
    
    $centro = Centros_Medico::on('mysql')->create([
        'nombre' => $datosCentro['nombre'],
        'rtn' => $datosCentro['rtn'],
        'telefono' => $datosCentro['telefono'],
        'email' => $datosCentro['email'],
        'direccion' => $datosCentro['direccion'],
        'onboarding_current_step' => 0,
        'onboarding_completed_at' => null,
        'onboarding_skipped_cai' => false,
    ]);
    
    echo "✅ Centro creado: ID {$centro->id}\n";
    echo "   Onboarding: Pendiente (step 0)\n\n";
    
    echo "👤 PASO 2: Crear Usuario Administrador\n";
    echo "────────────────────────────────────────────────────────────────\n";
    
    $usuario = User::on('mysql')->create([
        'name' => $datosUsuario['name'],
        'email' => $datosUsuario['email'],
        'password' => Hash::make($datosUsuario['password']),
        'centro_id' => $centro->id,
        'email_verified_at' => now(),
    ]);
    
    echo "✅ Usuario creado: ID {$usuario->id}\n";
    echo "   Centro asociado: {$centro->nombre}\n\n";
    
    DB::commit();
    
    echo "╔══════════════════════════════════════════════════════════════════╗\n";
    echo "║  ✅ REGISTRO COMPLETADO EXITOSAMENTE                            ║\n";
    echo "╚══════════════════════════════════════════════════════════════════╝\n\n";
    
    echo "🎯 AHORA PUEDES PROBAR EL FLUJO COMPLETO:\n";
    echo "────────────────────────────────────────────────────────────────\n\n";
    
    echo "1️⃣  Ve a: http://localhost:8000/admin/login\n\n";
    
    echo "2️⃣  Inicia sesión con:\n";
    echo "   📧 Email:    {$datosUsuario['email']}\n";
    echo "   🔒 Password: {$datosUsuario['password']}\n\n";
    
    echo "3️⃣  Deberías ser redirigido automáticamente a:\n";
    echo "   🎉 http://localhost:8000/onboarding/welcome\n\n";
    
    echo "4️⃣  Completa el wizard:\n";
    echo "   ✅ Paso 1: Información básica\n";
    echo "   ✅ Paso 2: Configuración CAI\n";
    echo "   ✅ Paso 3: Servicios iniciales\n";
    echo "   ✅ Paso 4: Completar onboarding\n\n";
    
    echo "╔══════════════════════════════════════════════════════════════════╗\n";
    echo "║  📊 ESTADO ACTUAL                                               ║\n";
    echo "╚══════════════════════════════════════════════════════════════════╝\n\n";
    
    echo "Centro Médico:\n";
    echo "  • ID: {$centro->id}\n";
    echo "  • Nombre: {$centro->nombre}\n";
    echo "  • RTN: {$centro->rtn}\n";
    echo "  • Onboarding: ⏳ Pendiente (step 0)\n\n";
    
    echo "Usuario:\n";
    echo "  • ID: {$usuario->id}\n";
    echo "  • Nombre: {$usuario->name}\n";
    echo "  • Email: {$usuario->email}\n";
    echo "  • Centro: {$centro->nombre}\n\n";
    
    echo "Tenant:\n";
    echo "  • Estado: ⏳ Será creado durante el onboarding\n";
    echo "  • ID esperado: centro_{$centro->id}\n";
    echo "  • Base de datos: Se creará automáticamente en paso 2\n\n";
    
    echo "╔══════════════════════════════════════════════════════════════════╗\n";
    echo "║  🎓 QUÉ ESPERAR                                                 ║\n";
    echo "╚══════════════════════════════════════════════════════════════════╝\n\n";
    
    echo "📍 DESPUÉS DEL LOGIN:\n";
    echo "   1. LoginResponse detecta que onboarding_current_step = 0\n";
    echo "   2. Redirige automáticamente a /onboarding/welcome\n";
    echo "   3. Middleware RequireOnboarding permite acceso a rutas onboarding\n\n";
    
    echo "📍 DURANTE EL ONBOARDING:\n";
    echo "   • Paso 2: Se crea tenant automáticamente si no existe\n";
    echo "   • Se crea base de datos: centro_{$centro->id}\n";
    echo "   • Se ejecutan migraciones del tenant\n";
    echo "   • Se guarda configuración CAI\n\n";
    
    echo "📍 DESPUÉS DE COMPLETAR:\n";
    echo "   • onboarding_completed_at se marca con fecha/hora\n";
    echo "   • Redirige al dashboard principal\n";
    echo "   • Usuario puede usar el sistema normalmente\n\n";
    
    echo "✅ LISTO PARA PROBAR!\n\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    
    echo "\n❌ ERROR AL CREAR REGISTRO:\n";
    echo "────────────────────────────────────────────────────────────────\n";
    echo "Error: {$e->getMessage()}\n\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n\n";
    
    exit(1);
}
