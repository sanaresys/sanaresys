<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Consulta;
use App\Models\Receta;

class CheckConsultaRecetas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:consulta-recetas {consulta_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar las recetas asociadas a una consulta específica';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $consultaId = $this->argument('consulta_id') ?? 1;

        try {
            // Buscar consulta sin scopes globales
            $consulta = Consulta::withoutGlobalScopes()->find($consultaId);

            if (!$consulta) {
                $this->error("Consulta con ID {$consultaId} no encontrada");
                return;
            }

            $this->info("=== INFORMACIÓN DE LA CONSULTA ===");
            $this->info("ID: {$consulta->id}");
            $this->info("Fecha: {$consulta->created_at}");
            $this->info("Diagnóstico: " . ($consulta->diagnostico ?: 'Sin diagnóstico'));

            // Buscar recetas asociadas sin scopes globales
            $recetas = Receta::withoutGlobalScopes()->where('consulta_id', $consultaId)->get();

            $this->info("\n=== RECETAS ASOCIADAS ===");

            if ($recetas->count() > 0) {
                foreach ($recetas as $index => $receta) {
                    $this->info("--- Receta #" . ($index + 1) . " ---");
                    $this->info("ID: {$receta->id}");
                    $this->info("Fecha: " . ($receta->fecha_receta ? $receta->fecha_receta->format('d/m/Y') : 'Sin fecha'));
                    $this->info("Medicamentos:");
                    $this->line("  " . ($receta->medicamentos ?: 'Sin medicamentos'));
                    $this->info("Indicaciones:");
                    $this->line("  " . ($receta->indicaciones ?: 'Sin indicaciones'));
                    $this->info("");
                }
            } else {
                $this->warn("No hay recetas asociadas a esta consulta");
            }

            // Mostrar todas las consultas disponibles
            $this->info("=== TODAS LAS CONSULTAS EN EL SISTEMA ===");
            $consultas = Consulta::withoutGlobalScopes()->get();

            foreach ($consultas as $c) {
                $recetasCount = Receta::withoutGlobalScopes()->where('consulta_id', $c->id)->count();
                $this->info("ID: {$c->id} | Recetas: {$recetasCount} | Fecha: {$c->created_at->format('d/m/Y')}");
            }

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
