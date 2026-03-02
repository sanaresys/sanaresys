<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Centros_Medico;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "\n========================================\n";
echo "   CREAR USUARIO DE PRUEBA\n";
echo "========================================\n\n";

// Obtener el centro sin onboarding
$centro = Centros_Medico::on('mysql')
    ->whereNull('onboarding_completed_at')
    ->first();

if (!$centro) {
    echo "❌ No hay centros sin onboarding en el sistema.\n";
    exit(1);
}

echo "🏥 Centro encontrado:\n";
echo "   ID: {$centro->id}\n";
echo "   Nombre: {$centro->nombre}\n";
echo "   RTN: {$centro->rtn}\n\n";

// Verificar si ya existe un usuario admin de prueba (no root)
$userExistente = User::on('mysql')
    ->where('centro_id', $centro->id)
    ->where('email', 'admin@testing.com')
    ->first();

if ($userExistente) {
    echo "✅ Usuario de prueba ya existe:\n";
    echo "   Email: admin@testing.com\n";
    echo "   Password: password123\n\n";
} else {
    // Crear usuario de prueba
    $user = new User();
    $user->setConnection('mysql');
    $user->name = 'Admin Testing';
    $user->email = 'admin@testing.com';
    $user->password = Hash::make('password123');
    $user->centro_id = $centro->id;
    $user->save();
    
    echo "✅ Usuario de prueba creado exitosamente:\n";
    echo "   Email: admin@testing.com\n";
    echo "   Password: password123\n\n";
}

echo "========================================\n";
echo "   INSTRUCCIONES PARA TESTING\n";
echo "========================================\n\n";

echo "1️⃣  Inicia el servidor (en una nueva terminal):\n";
echo "    cd c:\\Users\\DELL\\Documents\\EclipciClini\\sanaresys\n";
echo "    php artisan serve\n\n";

echo "2️⃣  Abre el navegador en:\n";
echo "    http://localhost:8000/admin/login\n\n";

echo "3️⃣  Ingresa estas credenciales:\n";
echo "    📧 Email: admin@testing.com\n";
echo "    🔑 Password: password123\n\n";

echo "4️⃣  El sistema debe:\n";
echo "    ✓ Autenticarte correctamente\n";
echo "    ✓ Detectar onboarding pendiente\n";
echo "    ✓ Redirigirte a: http://localhost:8000/onboarding\n\n";

echo "5️⃣  Completa el wizard:\n";
echo "    • Paso 0: Bienvenida (click en 'Comenzar')\n";
echo "    • Paso 1: Datos del centro\n";
echo "    • Paso 2: CAI fiscal (o 'Configurar después')\n";
echo "    • Paso 3: Servicios médicos\n";
echo "    • Paso 4: Completado (click en 'Ir al Dashboard')\n\n";

echo "6️⃣  Verificar widget:\n";
echo "    • Deberías ver el widget de checklist en el dashboard\n";
echo "    • Muestra tareas opcionales post-onboarding\n\n";

echo "========================================\n";
echo "   DATOS DE PRUEBA SUGERIDOS\n";
echo "========================================\n\n";

echo "📋 PASO 1 - Datos del Centro:\n";
echo "   Nombre: {$centro->nombre}\n";
echo "   RTN: {$centro->rtn}\n";
echo "   Dirección: Av. Principal #123, Tegucigalpa\n";
echo "   Teléfono: 2222-3333\n";
echo "   Email: info@{$centro->id}clinica.com\n\n";

echo "🧾 PASO 2 - CAI:\n";
echo "   Código: A1B2-C3D4-E5F6-G7H8-I9J0\n";
echo "   Rango inicial: 1\n";
echo "   Rango final: 1000\n";
echo "   Fecha límite: 2026-12-31\n\n";

echo "💼 PASO 3 - Servicios:\n";
echo "   Servicio 1: Consulta General | \$500.00\n";
echo "   Servicio 2: Laboratorio Básico | \$350.00\n";
echo "   Servicio 3: Rayos X | \$800.00\n\n";

echo "========================================\n\n";

echo "💡 TIP: Si quieres volver a probar, ejecuta esto para resetear:\n";
echo "   UPDATE centros_medicos SET onboarding_completed_at = NULL WHERE id = {$centro->id};\n\n";
