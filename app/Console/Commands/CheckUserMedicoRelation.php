<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Medico;
use App\Models\Persona;

class CheckUserMedicoRelation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:user-medico {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar la relación entre usuario y médico';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id') ?? 1;

        $user = User::with(['persona', 'medico.persona'])->find($userId);

        if (!$user) {
            $this->error("Usuario con ID {$userId} no encontrado");
            return;
        }

        $this->info("=== INFORMACIÓN DEL USUARIO ===");
        $this->info("ID: {$user->id}");
        $this->info("Nombre: {$user->name}");
        $this->info("Email: {$user->email}");
        $this->info("Persona ID: " . ($user->persona_id ?? 'null'));

        if ($user->persona) {
            $this->info("=== INFORMACIÓN DE LA PERSONA ===");
            $this->info("Nombre completo: {$user->persona->nombre_completo}");
            $this->info("DNI: " . ($user->persona->dni ?? 'Sin DNI'));
        } else {
            $this->warn("El usuario NO tiene una persona asociada");
        }

        // Verificar relación directa con médico
        if ($user->medico) {
            $this->info("=== MÉDICO (Relación directa) ===");
            $this->info("Médico ID: {$user->medico->id}");
            if ($user->medico->persona) {
                $this->info("Nombre médico: {$user->medico->persona->nombre_completo}");
            }
        } else {
            $this->warn("El usuario NO tiene relación directa con médico");

            // Buscar médico por persona_id
            if ($user->persona_id) {
                try {
                    $medico = Medico::withoutGlobalScopes()->where('persona_id', $user->persona_id)->with('persona')->first();
                    if ($medico) {
                        $this->info("=== MÉDICO ENCONTRADO POR PERSONA_ID ===");
                        $this->info("Médico ID: {$medico->id}");
                        $this->info("Nombre: {$medico->persona->nombre_completo}");
                    } else {
                        $this->error("No se encontró registro de médico para esta persona");
                    }
                } catch (\Exception $e) {
                    $this->error("Error al buscar médico: " . $e->getMessage());
                }
            }
        }

        // Listar todos los médicos para referencia
        $this->info("=== TODOS LOS MÉDICOS EN EL SISTEMA ===");
        try {
            $medicos = Medico::withoutGlobalScopes()->with('persona')->get();
            foreach ($medicos as $medico) {
                $nombre = $medico->persona ? $medico->persona->nombre_completo : 'Sin persona';
                $this->info("ID: {$medico->id} | Persona ID: {$medico->persona_id} | Nombre: {$nombre}");
            }
        } catch (\Exception $e) {
            $this->error("Error al obtener médicos: " . $e->getMessage());
        }
    }
}
