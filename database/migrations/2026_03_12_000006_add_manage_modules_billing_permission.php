<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('roles') || ! Schema::hasTable('role_has_permissions')) {
            return;
        }

        $permissionName = 'gestionar modulos billing';
        $guardName = 'web';

        $permissionId = DB::table('permissions')
            ->where('name', $permissionName)
            ->where('guard_name', $guardName)
            ->value('id');

        if (! $permissionId) {
            $permissionId = DB::table('permissions')->insertGetId([
                'name' => $permissionName,
                'guard_name' => $guardName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $roleIds = DB::table('roles')
            ->whereIn('name', ['root', 'administrador'])
            ->where('guard_name', $guardName)
            ->pluck('id')
            ->all();

        foreach ($roleIds as $roleId) {
            $exists = DB::table('role_has_permissions')
                ->where('role_id', $roleId)
                ->where('permission_id', $permissionId)
                ->exists();

            if (! $exists) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('role_has_permissions')) {
            return;
        }

        $permissionId = DB::table('permissions')
            ->where('name', 'gestionar modulos billing')
            ->where('guard_name', 'web')
            ->value('id');

        if (! $permissionId) {
            return;
        }

        DB::table('role_has_permissions')
            ->where('permission_id', $permissionId)
            ->delete();

        DB::table('permissions')
            ->where('id', $permissionId)
            ->delete();
    }
};

