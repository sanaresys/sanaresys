<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenants = App\Models\Tenant::query()->get();
echo 'Tenants: ' . $tenants->count() . PHP_EOL;

foreach ($tenants as $tenant) {
    try {
        tenancy()->initialize($tenant);
        app(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        app(Database\Seeders\RolesAndPermissionsSeeder::class)->run();
        app(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $permissionsCount = Spatie\Permission\Models\Permission::query()->count();
        $adminRole = Spatie\Permission\Models\Role::query()->where('name', 'administrador')->first();
        $adminPerms = $adminRole ? $adminRole->permissions()->count() : 0;
        echo "Synced {$tenant->id} | permissions={$permissionsCount} | admin_permissions={$adminPerms}" . PHP_EOL;
    } catch (Throwable $e) {
        echo "Error {$tenant->id}: {$e->getMessage()}" . PHP_EOL;
    } finally {
        tenancy()->end();
    }
}
