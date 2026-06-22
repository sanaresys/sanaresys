<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'billing.manage',
            'billing.invoice.pay',
            'billing.cancellation.manage',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::where('name', 'administrador')->where('guard_name', 'web')->first();
        if ($admin) {
            $admin->givePermissionTo($permissions);
        }

        $root = Role::where('name', 'root')->where('guard_name', 'web')->first();
        if ($root) {
            $root->givePermissionTo($permissions);
        }
    }

    public function down(): void
    {
        $permissions = [
            'billing.manage',
            'billing.invoice.pay',
            'billing.cancellation.manage',
        ];

        foreach ($permissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)
                ->where('guard_name', 'web')
                ->first();

            if ($permission) {
                $permission->delete();
            }
        }
    }
};
