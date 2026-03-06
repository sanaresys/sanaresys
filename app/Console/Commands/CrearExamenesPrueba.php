<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Examenes;
use App\Models\Pacientes;
use App\Models\Medico;

class CrearExamenesPrueba extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'examenes:crear-prueba';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear exámenes de prueba para testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Temporalmente desactivar el scope global
        $paciente = Pacientes::withoutGlobalScopes()->first();
        $medico = Medico::withoutGlobalScopes()->first();

        if (!$paciente) {
            $this->error('No hay pacientes en la base de datos');
            return;
        }

        if (!$medico) {
            $this->error('No hay médicos en la base de datos');
            return;
        }

        $this->info("Creando exámenes para el paciente: {$paciente->persona->nombre_completo}");

        $examenes = [
            [
                'tipo_examen' => 'Hemograma completo',
                'estado' => 'Solicitado',
                'observaciones' => 'Control de rutina - ayuno de 8 horas'
            ],
            [
                'tipo_examen' => 'Examen de orina',
                'estado' => 'Completado',
                'observaciones' => 'Primera orina de la mañana',
                'imagen_resultado' => 'ejemplo_resultado.jpg' // Archivo ficticio
            ],
            [
                'tipo_examen' => 'Radiografía de tórax',
                'estado' => 'Solicitado',
                'observaciones' => 'Sospecha de neumonía'
            ],
            [
                'tipo_examen' => 'Electrocardiograma',
                'estado' => 'No presentado',
                'observaciones' => 'Control cardiológico'
            ]
        ];

        foreach ($examenes as $examenData) {
            // Buscar una consulta existente para este paciente o crear una ficticia
            $consulta = \App\Models\Consulta::withoutGlobalScopes()->where('paciente_id', $paciente->id)->first();
            
            $examen = Examenes::create([
                'paciente_id' => $paciente->id,
                'medico_id' => $medico->id,
                'centro_id' => $paciente->centro_id ?? 1,
                'consulta_id' => $consulta?->id, // Puede ser null si no hay consulta
                'tipo_examen' => $examenData['tipo_examen'],
                'estado' => $examenData['estado'],
                'observaciones' => $examenData['observaciones'],
                'imagen_resultado' => $examenData['imagen_resultado'] ?? null,
                'fecha_completado' => $examenData['estado'] === 'Completado' ? now() : null,
            ]);

            $this->line("✓ Creado: {$examenData['tipo_examen']} - {$examenData['estado']}");
        }

        $this->info('Exámenes de prueba creados exitosamente!');
    }
}
