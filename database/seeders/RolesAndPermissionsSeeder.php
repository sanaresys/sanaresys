<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos organizados usando firstOrCreate para evitar duplicados

        // VER
        Permission::firstOrCreate(['name' => 'ver personas']);
        Permission::firstOrCreate(['name' => 'ver nacionalidad']);
        Permission::firstOrCreate(['name' => 'ver usuario']);
        Permission::firstOrCreate(['name' => 'ver pacientes']);
        Permission::firstOrCreate(['name' => 'ver medicocentromedico']);
        Permission::firstOrCreate(['name' => 'ver centromedico']);
        Permission::firstOrCreate(['name' => 'ver enfermedades']);
        Permission::firstOrCreate(['name' => 'ver especialidad']);
        Permission::firstOrCreate(['name' => 'ver especialidadmedicos']);
        Permission::firstOrCreate(['name' => 'ver medicos']);
        Permission::firstOrCreate(['name' => 'ver enfermedades_pacientes']);
        Permission::firstOrCreate(['name' => 'ver recetas']);
        Permission::firstOrCreate(['name' => 'ver consultas']);
        Permission::firstOrCreate(['name' => 'ver contratomedico']);
        Permission::firstOrCreate(['name' => 'ver nomina']);
        Permission::firstOrCreate(['name' => 'ver detallenomina']);
        Permission::firstOrCreate(['name' => 'ver citas']); // NUEVO
        Permission::firstOrCreate(['name' => 'ver examenes']); // NUEVO EXÁMENES
        Permission::firstOrCreate(['name' => 'ver cai_correlativos']);
        Permission::firstOrCreate(['name' => 'ver cai_autorizaciones']);
        Permission::firstOrCreate(['name' => 'ver cuentas_por_cobrars']);
        Permission::firstOrCreate(['name' => 'ver servicio']);
        Permission::firstOrCreate(['name' => 'ver Impuesto']);
        Permission::firstOrCreate(['name' => 'ver factura']);
        Permission::firstOrCreate(['name' => 'ver descuento']);
        Permission::firstOrCreate(['name' => 'ver factura_detalles']);
        Permission::firstOrCreate(['name' => 'ver pagos_facturas']);
        Permission::firstOrCreate(['name' => 'gestionar modulos billing']);
        Permission::firstOrCreate(['name' => 'billing.manage']);
        Permission::firstOrCreate(['name' => 'billing.invoice.pay']);
        Permission::firstOrCreate(['name' => 'billing.cancellation.manage']);

        // CREAR
        Permission::firstOrCreate(['name' => 'crear personas']);
        Permission::firstOrCreate(['name' => 'crear nacionalidad']);
        Permission::firstOrCreate(['name' => 'crear usuario']);
        Permission::firstOrCreate(['name' => 'crear pacientes']);
        Permission::firstOrCreate(['name' => 'crear medicocentromedico']);
        Permission::firstOrCreate(['name' => 'crear centromedico']);
        Permission::firstOrCreate(['name' => 'crear enfermedades']);
        Permission::firstOrCreate(['name' => 'crear especialidad']);
        Permission::firstOrCreate(['name' => 'crear especialidadmedicos']);
        Permission::firstOrCreate(['name' => 'crear medicos']);
        Permission::firstOrCreate(['name' => 'crear enfermedades_pacientes']);
        Permission::firstOrCreate(['name' => 'crear recetas']);
        Permission::firstOrCreate(['name' => 'crear consultas']);
        Permission::firstOrCreate(['name' => 'crear contratomedico']);
        Permission::firstOrCreate(['name' => 'crear nomina']);
        Permission::firstOrCreate(['name' => 'crear detallenomina']);
        Permission::firstOrCreate(['name' => 'crear examenes']); // NUEVO EXÁMENES
        Permission::firstOrCreate(['name' => 'crear citas']); // NUEVO
        Permission::firstOrCreate(['name' => 'crear cai_correlativos']);
        Permission::firstOrCreate(['name' => 'crear cai_autorizaciones']);
        Permission::firstOrCreate(['name' => 'crear cuentas_por_cobrars']);
        Permission::firstOrCreate(['name' => 'crear servicio']);
        Permission::firstOrCreate(['name' => 'crear Impuesto']);
        Permission::firstOrCreate(['name' => 'crear factura']);
        Permission::firstOrCreate(['name' => 'crear descuento']);
        Permission::firstOrCreate(['name' => 'crear factura_detalles']);
        Permission::firstOrCreate(['name' => 'crear pagos_facturas']);


        // ACTUALIZAR
        Permission::firstOrCreate(['name' => 'actualizar personas']);
        Permission::firstOrCreate(['name' => 'actualizar nacionalidad']);
        Permission::firstOrCreate(['name' => 'actualizar usuario']);
        Permission::firstOrCreate(['name' => 'actualizar pacientes']);
        Permission::firstOrCreate(['name' => 'actualizar medicocentromedico']);
        Permission::firstOrCreate(['name' => 'actualizar centromedico']);
        Permission::firstOrCreate(['name' => 'actualizar enfermedades']);
        Permission::firstOrCreate(['name' => 'actualizar especialidad']);
        Permission::firstOrCreate(['name' => 'actualizar especialidadmedicos']);
        Permission::firstOrCreate(['name' => 'actualizar medicos']);
        Permission::firstOrCreate(['name' => 'actualizar enfermedades_pacientes']);
        Permission::firstOrCreate(['name' => 'actualizar recetas']);
        Permission::firstOrCreate(['name' => 'actualizar consultas']);
        Permission::firstOrCreate(['name' => 'actualizar contratomedico']);
        Permission::firstOrCreate(['name' => 'actualizar nomina']);
        Permission::firstOrCreate(['name' => 'actualizar detallenomina']);
        Permission::firstOrCreate(['name' => 'actualizar citas']); // NUEVO
        Permission::firstOrCreate(['name' => 'actualizar examenes']); // NUEVO EXÁMENES
        Permission::firstOrCreate(['name' => 'actualizar cai_correlativos']);
        Permission::firstOrCreate(['name' => 'actualizar cai_autorizaciones']);
        Permission::firstOrCreate(['name' => 'actualizar cuentas_por_cobrars']);
        Permission::firstOrCreate(['name' => 'actualizar servicio']);
        Permission::firstOrCreate(['name' => 'actualizar Impuesto']);
        Permission::firstOrCreate(['name' => 'actualizar factura']);
        Permission::firstOrCreate(['name' => 'actualizar descuento']);
        Permission::firstOrCreate(['name' => 'actualizar factura_detalles']);
        Permission::firstOrCreate(['name' => 'actualizar pagos_facturas']);


        // BORRAR
        Permission::firstOrCreate(['name' => 'borrar personas']);
        Permission::firstOrCreate(['name' => 'borrar nacionalidad']);
        Permission::firstOrCreate(['name' => 'borrar usuario']);
        Permission::firstOrCreate(['name' => 'borrar pacientes']);
        Permission::firstOrCreate(['name' => 'borrar centromedico']);
        Permission::firstOrCreate(['name' => 'borrar medicocentromedico']);
        Permission::firstOrCreate(['name' => 'borrar enfermedades']);
        Permission::firstOrCreate(['name' => 'borrar especialidad']);
        Permission::firstOrCreate(['name' => 'borrar especialidadmedicos']);
        Permission::firstOrCreate(['name' => 'borrar medicos']);
        Permission::firstOrCreate(['name' => 'borrar enfermedades_pacientes']);
        Permission::firstOrCreate(['name' => 'borrar recetas']);
        Permission::firstOrCreate(['name' => 'borrar consultas']);
        Permission::firstOrCreate(['name' => 'borrar contratomedico']);
        Permission::firstOrCreate(['name' => 'borrar nomina']);
        Permission::firstOrCreate(['name' => 'borrar detallenomina']);
        Permission::firstOrCreate(['name' => 'borrar citas']); // NUEVO
        Permission::firstOrCreate(['name' => 'borrar examenes']); // NUEVO EXÁMENES
        Permission::firstOrCreate(['name' => 'borrar cai_correlativos']);
        Permission::firstOrCreate(['name' => 'borrar cai_autorizaciones']);
        Permission::firstOrCreate(['name' => 'borrar cuentas_por_cobrars']);
        Permission::firstOrCreate(['name' => 'borrar servicio']);
        Permission::firstOrCreate(['name' => 'borrar Impuesto']);
        Permission::firstOrCreate(['name' => 'borrar factura']);
        Permission::firstOrCreate(['name' => 'borrar descuento']);
        Permission::firstOrCreate(['name' => 'borrar factura_detalles']);
        Permission::firstOrCreate(['name' => 'borrar pagos_facturas']);

        // Crear roles y asignar permisos
        $roleAdmin = Role::firstOrCreate(['name' => 'root']);
        $roleAdmin->givePermissionTo([
            // VER
            'ver personas', 'ver nacionalidad', 'ver usuario', 'ver pacientes', 'ver medicocentromedico', 'ver enfermedades', 'ver centromedico', 'ver especialidad', 'ver especialidadmedicos', 'ver medicos', 'ver enfermedades_pacientes', 'ver recetas', 'ver consultas', 'ver contratomedico', 'ver nomina', 'ver detallenomina', 'ver citas', 'ver examenes', 'ver cai_correlativos', 'ver cai_autorizaciones', 'ver cuentas_por_cobrars', 'ver servicio', 'ver Impuesto', 'ver factura', 'ver descuento', 'ver factura_detalles', 'ver pagos_facturas', 'gestionar modulos billing', 'billing.manage', 'billing.invoice.pay', 'billing.cancellation.manage',
                        // CREAR
            'crear personas', 'crear nacionalidad', 'crear usuario', 'crear pacientes', 'crear medicocentromedico', 'crear centromedico', 'crear enfermedades', 'crear especialidad', 'crear especialidadmedicos', 'crear medicos', 'crear enfermedades_pacientes', 'crear recetas', 'crear consultas', 'crear contratomedico', 'crear nomina', 'crear detallenomina', 'crear examenes', 'crear citas', 'crear cai_correlativos', 'crear cai_autorizaciones', 'crear cuentas_por_cobrars', 'crear servicio', 'crear Impuesto', 'crear factura', 'crear descuento', 'crear factura_detalles', 'crear pagos_facturas',
            // ACTUALIZAR
            'actualizar personas', 'actualizar nacionalidad', 'actualizar usuario', 'actualizar pacientes', 'actualizar medicocentromedico', 'actualizar centromedico', 'actualizar enfermedades', 'actualizar especialidad', 'actualizar especialidadmedicos', 'actualizar medicos', 'actualizar enfermedades_pacientes', 'actualizar recetas', 'actualizar consultas', 'actualizar contratomedico', 'actualizar nomina', 'actualizar detallenomina', 'actualizar citas', 'actualizar examenes', 'actualizar cai_correlativos', 'actualizar cai_autorizaciones', 'actualizar cuentas_por_cobrars', 'actualizar servicio', 'actualizar Impuesto', 'actualizar factura', 'actualizar descuento', 'actualizar factura_detalles', 'actualizar pagos_facturas',
            // BORRAR
            'borrar personas', 'borrar nacionalidad', 'borrar usuario', 'borrar pacientes', 'borrar centromedico', 'borrar medicocentromedico', 'borrar enfermedades', 'borrar especialidad', 'borrar especialidadmedicos', 'borrar medicos', 'borrar enfermedades_pacientes', 'borrar recetas', 'borrar consultas', 'borrar contratomedico', 'borrar nomina', 'borrar detallenomina', 'borrar citas', 'borrar examenes', 'borrar cai_correlativos', 'borrar cai_autorizaciones', 'borrar cuentas_por_cobrars', 'borrar servicio', 'borrar Impuesto', 'borrar factura', 'borrar descuento', 'borrar factura_detalles', 'borrar pagos_facturas'
        ]);

        $roleAdminCentro = Role::firstOrCreate(['name' => 'administrador']);
        $adminRestrictedPermissions = [
            // Catálogos y entidades reservadas al root/sistema central
            'ver personas', 'crear personas', 'actualizar personas', 'borrar personas',
            'ver nacionalidad', 'crear nacionalidad', 'actualizar nacionalidad', 'borrar nacionalidad',
            'ver centromedico', 'crear centromedico', 'actualizar centromedico', 'borrar centromedico',
            'ver medicocentromedico', 'crear medicocentromedico', 'actualizar medicocentromedico', 'borrar medicocentromedico',
            'crear especialidad', 'actualizar especialidad', 'borrar especialidad',
            'ver especialidadmedicos', 'crear especialidadmedicos', 'actualizar especialidadmedicos', 'borrar especialidadmedicos',
        ];

        $adminPermissions = Permission::query()
            ->whereNotIn('name', $adminRestrictedPermissions)
            ->pluck('name')
            ->all();

        $roleAdminCentro->syncPermissions($adminPermissions);

       

        $roleAdminMedicos = Role::firstOrCreate(['name' => 'medico']);
        $roleAdminMedicos->givePermissionTo(['crear pacientes', 'ver pacientes', 'actualizar pacientes', 'borrar pacientes',
            'crear consultas', 'ver consultas', 'actualizar consultas', 'borrar consultas',
            'crear recetas', 'ver recetas', 'actualizar recetas', 'borrar recetas',
            'crear examenes', 'ver examenes', 'actualizar examenes', 'borrar examenes', // Médicos pueden gestionar exámenes
            'crear citas', 'ver citas', 'actualizar citas', // NUEVO - Médicos pueden crear, ver y actualizar sus propias citas
            'ver contratomedico', // Permiso para ver sus contratos
        ]);
    }
}
