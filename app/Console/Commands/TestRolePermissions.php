<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Medico;
use App\Models\Centros_Medico;
use App\Models\Persona;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class TestRolePermissions extends Command
{
    protected $signature = 'test:role-permissions {--create-users : Create test users}';
    protected $description = 'Test role-based permissions system';

    public function handle()
    {
        $this->info('=== TEST DE PERMISOS POR ROLES ===');
        $this->newLine();

        // 1. Verificar usuarios y roles
        $this->info('1. VERIFICANDO USUARIOS POR ROL:');
        
        $users = User::with('roles')->get();
        foreach ($users as $user) {
            $roles = $user->roles->pluck('name')->join(', ');
            $this->line("- {$user->name}: {$roles}");
        }
        $this->newLine();

        // 2. Verificar centros médicos
        $this->info('2. CENTROS MÉDICOS DISPONIBLES:');
        $centros = Centros_Medico::all();
        foreach ($centros as $centro) {
            $this->line("- {$centro->nombre} (ID: {$centro->id})");
        }
        $this->newLine();

        // 3. Verificar médicos (sin tenant scoping)
        $this->info('3. MÉDICOS DISPONIBLES:');
        try {
            $medicos = Medico::withoutGlobalScopes()->with('persona')->get();
            foreach ($medicos as $medico) {
                $nombre = $medico->persona->primer_nombre . ' ' . $medico->persona->primer_apellido;
                $this->line("- {$nombre} (ID: {$medico->id}, Centro: {$medico->centro_id})");
            }
        } catch (\Exception $e) {
            $this->error("Error al cargar médicos: " . $e->getMessage());
        }
        $this->newLine();

        // 4. Crear usuarios de prueba si se solicita
        if ($this->option('create-users')) {
            $this->info('4. CREANDO USUARIOS DE PRUEBA:');
            $this->createTestUsers();
        }

        $this->newLine();
        $this->info('=== FIN DEL TEST ===');
    }

    private function createTestUsers()
    {
        // Crear administrador si no existe
        $admin = User::whereHas('roles', function($q) {
            $q->where('name', 'administrador');
        })->first();
        
        if (!$admin) {
            $admin = User::create([
                'name' => 'admin_test',
                'email' => 'admin@test.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $admin->assignRole('administrador');
            $this->line("✓ Usuario administrador creado: {$admin->name}");
        } else {
            $this->line("- Administrador ya existe: {$admin->name}");
        }

        // Crear médico si hay médicos disponibles
        $medico_record = Medico::withoutGlobalScopes()->first();
        if ($medico_record) {
            $medico_user = User::whereHas('roles', function($q) {
                $q->where('name', 'medico');
            })->first();
            
            if (!$medico_user) {
                $medico_user = User::create([
                    'name' => 'medico_test',
                    'email' => 'medico@test.com',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'medico_id' => $medico_record->id,
                ]);
                $medico_user->assignRole('medico');
                $this->line("✓ Usuario médico creado: {$medico_user->name}");
            } else {
                $this->line("- Médico ya existe: {$medico_user->name}");
            }
        } else {
            $this->line("- No hay registros de médicos para crear usuario médico");
        }
    }
}
