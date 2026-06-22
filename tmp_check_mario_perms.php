<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenant = App\Models\Tenant::query()->whereHas('domains', fn($q) => $q->where('domain', 'clinica-mario.sanaresys.localhost'))->first();
if (! $tenant) { echo "tenant-not-found\n"; exit(1); }

tenancy()->initialize($tenant);
$user = App\Models\User::query()->where('email', 'mario@ejemplo.com')->first();
if (! $user) { echo "user-not-found\n"; tenancy()->end(); exit(1); }

$roleNames = $user->roles->pluck('name')->all();
$canCrearUsuario = $user->can('crear usuario') ? 'true' : 'false';
$canCrearMedicos = $user->can('crear medicos') ? 'true' : 'false';

$role = Spatie\Permission\Models\Role::query()->where('name','administrador')->first();
$roleHasCrearUsuario = $role && $role->hasPermissionTo('crear usuario') ? 'true' : 'false';
$rolePermCount = $role ? $role->permissions()->count() : 0;

echo 'user=' . $user->id . PHP_EOL;
echo 'roles=' . implode(',', $roleNames) . PHP_EOL;
echo 'can_crear_usuario=' . $canCrearUsuario . PHP_EOL;
echo 'can_crear_medicos=' . $canCrearMedicos . PHP_EOL;
echo 'role_has_crear_usuario=' . $roleHasCrearUsuario . PHP_EOL;
echo 'role_perm_count=' . $rolePermCount . PHP_EOL;

tenancy()->end();
