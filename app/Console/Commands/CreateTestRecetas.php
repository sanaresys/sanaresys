<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Consulta;
use App\Models\Receta;

class CreateTestRecetas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:test-recetas {consulta_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear recetas de prueba para una consulta';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $consultaId = $this->argument('consulta_id') ?? 1;

        try {
            $consulta = Consulta::withoutGlobalScopes()->find($consultaId);

            if (!$consulta) {
                $this->error("Consulta con ID {$consultaId} no encontrada");
                return;
            }

            $this->info("Creando recetas de prueba para la consulta #{$consultaId}");

            // Receta 1
            $receta1 = Receta::create([
                'medicamentos' => "Paracetamol 500mg - 1 tableta cada 8 horas\nIbuprofeno 400mg - 1 tableta cada 12 horas por 3 días\nAmoxicilina 500mg - 1 cápsula cada 8 horas por 7 días",
                'indicaciones' => "Tomar con alimentos para evitar molestias estomacales\nCompletar todo el tratamiento antibiótico\nRegresar si persisten los síntomas después de 3 días",
                'fecha_receta' => now()->subDays(1),
                'consulta_id' => $consultaId,
                'paciente_id' => $consulta->paciente_id,
                'medico_id' => $consulta->medico_id,
                'centro_id' => $consulta->centro_id,
            ]);

            // Receta 2
            $receta2 = Receta::create([
                'medicamentos' => "Omeprazol 20mg - 1 cápsula en ayunas por 14 días\nHioscina 10mg - 1 tableta cada 8 horas según dolor\nProbióticos - 1 sobre después de cada comida",
                'indicaciones' => "Tomar omeprazol 30 minutos antes del desayuno\nEvitar alimentos irritantes (picantes, ácidos)\nMantener dieta blanda por una semana\nControl en 15 días",
                'fecha_receta' => now(),
                'consulta_id' => $consultaId,
                'paciente_id' => $consulta->paciente_id,
                'medico_id' => $consulta->medico_id,
                'centro_id' => $consulta->centro_id,
            ]);

            // Receta 3
            $receta3 = Receta::create([
                'medicamentos' => "Loratadina 10mg - 1 tableta al día por 7 días\nCrema hidrocortisona 1% - aplicar 2 veces al día en áreas afectadas\nJabón neutro pH balanceado",
                'indicaciones' => "Evitar exposición al sol durante el tratamiento\nNo rascar las áreas afectadas\nUsar ropa de algodón\nAplicar crema después del baño\nRegresar en 1 semana si no hay mejoría",
                'fecha_receta' => now()->addHours(2),
                'consulta_id' => $consultaId,
                'paciente_id' => $consulta->paciente_id,
                'medico_id' => $consulta->medico_id,
                'centro_id' => $consulta->centro_id,
            ]);

            $this->info("✅ Se crearon 3 recetas de prueba exitosamente:");
            $this->info("- Receta #{$receta1->id}: Tratamiento para infección");
            $this->info("- Receta #{$receta2->id}: Tratamiento gastrointestinal");
            $this->info("- Receta #{$receta3->id}: Tratamiento dermatológico");

            $totalRecetas = Receta::withoutGlobalScopes()->where('consulta_id', $consultaId)->count();
            $this->info("\n📊 Total de recetas para esta consulta: {$totalRecetas}");

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
