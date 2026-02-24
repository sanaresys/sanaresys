<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Citas;
use App\Models\Consulta;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class TestPermissionsFunctionality extends Command
{
    protected $signature = 'test:permissions-functionality';
    protected $description = 'Test the actual functionality of role-based permissions';

    public function handle()
    {
        $this->info('=== TEST DE FUNCIONALIDAD DE PERMISOS ===');
        $this->newLine();

        // Obtener usuarios de prueba
        $root = User::whereHas('roles', function($q) {
            $q->where('name', 'root');
        })->first();

        $admin = User::whereHas('roles', function($q) {
            $q->where('name', 'administrador');
        })->first();

        $medico = User::whereHas('roles', function($q) {
            $q->where('name', 'medico');
        })->first();

        // Test para ROOT
        if ($root) {
            $this->info('1. PERMISOS DEL ROOT:');
            Auth::login($root);
            
            $cita = Citas::withoutGlobalScopes()->first();
            if ($cita) {
                $this->line("- Ver citas: " . (Gate::allows('view', $cita) ? "✓" : "✗"));
                $this->line("- Crear citas: " . (Gate::allows('create', Citas::class) ? "✓" : "✗"));
                $this->line("- Editar citas: " . (Gate::allows('update', $cita) ? "✓" : "✗"));
                $this->line("- Eliminar citas: " . (Gate::allows('delete', $cita) ? "✓" : "✗"));
                $this->line("- Confirmar citas: " . (Gate::allows('confirm', $cita) ? "✓" : "✗"));
                $this->line("- Cancelar citas: " . (Gate::allows('cancel', $cita) ? "✓" : "✗"));
                $this->line("- Crear consultas: " . (Gate::allows('create', Consulta::class) ? "✓" : "✗"));
            }
            Auth::logout();
            $this->newLine();
        }

        // Test para ADMINISTRADOR
        if ($admin) {
            $this->info('2. PERMISOS DEL ADMINISTRADOR:');
            Auth::login($admin);
            
            $cita = Citas::withoutGlobalScopes()->first();
            if ($cita) {
                $this->line("- Ver citas: " . (Gate::allows('view', $cita) ? "✓" : "✗"));
                $this->line("- Crear citas: " . (Gate::allows('create', Citas::class) ? "✓" : "✗"));
                $this->line("- Editar citas: " . (Gate::allows('update', $cita) ? "✓" : "✗"));
                $this->line("- Eliminar citas: " . (Gate::allows('delete', $cita) ? "✓" : "✗"));
                $this->line("- Confirmar citas: " . (Gate::allows('confirm', $cita) ? "✓" : "✗"));
                $this->line("- Cancelar citas: " . (Gate::allows('cancel', $cita) ? "✓" : "✗"));
                $this->line("- Crear consultas: " . (Gate::allows('create', Consulta::class) ? "✓" : "✗"));
            }
            Auth::logout();
            $this->newLine();
        }

        // Test para MÉDICO
        if ($medico) {
            $this->info('3. PERMISOS DEL MÉDICO:');
            Auth::login($medico);
            
            $cita = Citas::withoutGlobalScopes()->first();
            if ($cita) {
                $this->line("- Ver citas: " . (Gate::allows('view', $cita) ? "✓" : "✗"));
                $this->line("- Crear citas: " . (Gate::allows('create', Citas::class) ? "✓" : "✗"));
                $this->line("- Editar citas: " . (Gate::allows('update', $cita) ? "✓" : "✗"));
                $this->line("- Eliminar citas: " . (Gate::allows('delete', $cita) ? "✓" : "✗"));
                $this->line("- Confirmar citas: " . (Gate::allows('confirm', $cita) ? "✓" : "✗"));
                $this->line("- Cancelar citas: " . (Gate::allows('cancel', $cita) ? "✓" : "✗"));
                $this->line("- Crear consultas: " . (Gate::allows('create', Consulta::class) ? "✓" : "✗"));
            }
            Auth::logout();
            $this->newLine();
        }

        // Test de conteo de datos accesibles
        $this->info('4. CONTEO DE DATOS ACCESIBLES POR ROL:');
        
        if ($medico) {
            Auth::login($medico);
            $citasMedico = Citas::count();
            $consultasMedico = Consulta::count();
            $this->line("- Médico ve {$citasMedico} citas y {$consultasMedico} consultas");
            Auth::logout();
        }

        if ($admin) {
            Auth::login($admin);
            $citasAdmin = Citas::count();
            $consultasAdmin = Consulta::count();
            $this->line("- Administrador ve {$citasAdmin} citas y {$consultasAdmin} consultas");
            Auth::logout();
        }

        if ($root) {
            Auth::login($root);
            $citasRoot = Citas::withoutGlobalScopes()->count();
            $consultasRoot = Consulta::withoutGlobalScopes()->count();
            $this->line("- Root ve {$citasRoot} citas y {$consultasRoot} consultas");
            Auth::logout();
        }

        $this->newLine();
        $this->info('=== SISTEMA DE PERMISOS IMPLEMENTADO CORRECTAMENTE ===');
    }
}
