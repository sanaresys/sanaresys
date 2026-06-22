<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$rootUser = User::on('mysql')->where('email', 'root@example.com')->first();

if ($rootUser) {
    echo "\n🔍 Verificando usuario root...\n\n";
    echo "Email: {$rootUser->email}\n";
    echo "Centro ID: {$rootUser->centro_id}\n";
    echo "Tiene rol 'root': " . ($rootUser->hasRole('root') ? '✅ Sí' : '❌ No') . "\n";
    
    if (!$rootUser->hasRole('root')) {
        echo "\n⚠️  Este usuario NO tiene rol 'root', será redirigido al onboarding.\n";
        echo "   Usa el usuario admin@testing.com para probar el onboarding.\n";
    }
} else {
    echo "Usuario root no encontrado.\n";
}

echo "\n";
