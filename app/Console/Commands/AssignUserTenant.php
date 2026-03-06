<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Centros_Medico;
use App\Models\Tenant;

class AssignUserTenant extends Command
{
    protected $signature = 'tenant:assign-user {user_id} {centro_id}';
    protected $description = 'Asigna un usuario a un centro médico específico';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $centroId = $this->argument('centro_id');

        $user = User::find($userId);
        $centro = Centros_Medico::find($centroId);

        if (!$user) {
            $this->error("Usuario con ID {$userId} no encontrado.");
            return 1;
        }

        if (!$centro) {
            $this->error("Centro médico con ID {$centroId} no encontrado.");
            return 1;
        }

        // Verificar que existe el tenant
        $tenant = Tenant::where('centro_id', $centroId)->first();
        if (!$tenant) {
            $this->error("No existe un tenant para el centro médico {$centro->nombre_centro}.");
            return 1;
        }

        // Asignar el centro al usuario
        $user->centro_id = $centroId;
        $user->save();

        $this->info("Usuario {$user->name} asignado al centro {$centro->nombre_centro} exitosamente.");
        return 0;
    }
}
