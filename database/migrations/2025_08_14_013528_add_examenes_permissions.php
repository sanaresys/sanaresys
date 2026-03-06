<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear permisos de exámenes si no existen
        $permissions = [
            'ver examenes',
            'crear examenes',
            'actualizar examenes',
            'borrar examenes',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Asignar permisos a roles
        $rootRole = Role::where('name', 'root')->first();
        if ($rootRole) {
            $rootRole->givePermissionTo($permissions);
        }

        $adminRole = Role::where('name', 'administrador')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo(['ver examenes', 'actualizar examenes']);
        }

        $medicoRole = Role::where('name', 'medico')->first();
        if ($medicoRole) {
            $medicoRole->givePermissionTo($permissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover permisos de exámenes
        $permissions = [
            'ver examenes',
            'crear examenes',
            'actualizar examenes',
            'borrar examenes',
        ];

        foreach ($permissions as $permission) {
            Permission::where('name', $permission)->delete();
        }
    }
};
