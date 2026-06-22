<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Centros_Medico;
use App\Models\User;

echo "\n========================================\n";
echo "   ESTADO DEL SISTEMA - ONBOARDING\n";
echo "========================================\n\n";

// Verificar centros sin onboarding
$centrosSinOnboarding = Centros_Medico::on('mysql')
    ->whereNull('onboarding_completed_at')
    ->get();

echo "📊 Centros sin onboarding completado: " . $centrosSinOnboarding->count() . "\n\n";

if ($centrosSinOnboarding->count() > 0) {
    echo "Lista de centros disponibles para testing:\n";
    echo "-------------------------------------------\n";
    foreach ($centrosSinOnboarding as $centro) {
        echo sprintf(
            "  🏥 ID: %-3d | %-30s | RTN: %s\n",
            $centro->id,
            $centro->nombre ?? '(Sin nombre)',
            $centro->rtn ?? '(Sin RTN)'
        );
        
        // Verificar si hay usuarios para este centro
        $usuarios = User::on('mysql')->where('centro_id', $centro->id)->count();
        echo "     👥 Usuarios asociados: $usuarios\n";
        
        if ($usuarios > 0) {
            $primerUsuario = User::on('mysql')
                ->where('centro_id', $centro->id)
                ->first();
            echo "     📧 Email ejemplo: {$primerUsuario->email}\n";
        }
        echo "\n";
    }
} else {
    echo "⚠️  No hay centros sin onboarding en el sistema.\n";
    echo "   Necesitas crear un centro de prueba primero.\n\n";
}

// Verificar centros CON onboarding completado
$centrosCompletos = Centros_Medico::on('mysql')
    ->whereNotNull('onboarding_completed_at')
    ->count();

echo "✅ Centros con onboarding completado: $centrosCompletos\n\n";

// Verificar middleware registrado
$middlewareAlias = config('app.middleware_aliases');
if (isset($middlewareAlias['require.onboarding'])) {
    echo "✅ Middleware 'require.onboarding' registrado correctamente\n";
} else {
    echo "❌ Middleware 'require.onboarding' NO está registrado\n";
}

echo "\n========================================\n";
echo "   PRÓXIMOS PASOS PARA TESTING\n";
echo "========================================\n\n";

if ($centrosSinOnboarding->count() > 0) {
    $centroPrueba = $centrosSinOnboarding->first();
    $usuario = User::on('mysql')->where('centro_id', $centroPrueba->id)->first();
    
    if ($usuario) {
        echo "✅ LISTO PARA PROBAR:\n\n";
        echo "1. Inicia el servidor de desarrollo:\n";
        echo "   php artisan serve\n\n";
        echo "2. Abre el navegador en:\n";
        echo "   http://localhost:8000/admin/login\n\n";
        echo "3. Ingresa con estas credenciales:\n";
        echo "   Email: {$usuario->email}\n";
        echo "   Password: (la contraseña del usuario)\n\n";
        echo "4. El sistema debe redirigirte automáticamente a:\n";
        echo "   http://localhost:8000/onboarding\n\n";
        echo "5. Completa el wizard de 5 pasos\n\n";
    } else {
        echo "⚠️  Necesitas crear un usuario para el centro ID {$centroPrueba->id}\n\n";
        echo "Ejecuta esto para crear un usuario de prueba:\n";
        echo "php artisan tinker\n";
        echo "\$user = new App\\Models\\User();\n";
        echo "\$user->name = 'Admin Prueba';\n";
        echo "\$user->email = 'admin@prueba.com';\n";
        echo "\$user->password = bcrypt('password');\n";
        echo "\$user->centro_id = {$centroPrueba->id};\n";
        echo "\$user->save();\n\n";
    }
} else {
    echo "⚠️  Primero crea un centro de prueba:\n\n";
    echo "Opción 1 - Crear centro desde Filament:\n";
    echo "1. php artisan serve\n";
    echo "2. Login como root\n";
    echo "3. Crear nuevo centro médico\n\n";
    echo "Opción 2 - Crear desde consola:\n";
    echo "php artisan tinker\n";
    echo "\$centro = App\\Models\\Centros_Medico::create([\n";
    echo "  'nombre' => 'Clínica Prueba Onboarding',\n";
    echo "  'rtn' => '12345678901234',\n";
    echo "  'tenancy_mode' => 'domain'\n";
    echo "]);\n\n";
}

echo "========================================\n\n";
