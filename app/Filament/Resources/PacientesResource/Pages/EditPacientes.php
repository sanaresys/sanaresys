<?php

namespace App\Filament\Resources\PacientesResource\Pages;

use App\Filament\Resources\PacientesResource;
use App\Models\Persona;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class EditPacientes extends EditRecord
{
    protected static string $resource = PacientesResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $persona = $this->record->persona;
        
        if ($persona) {
            $data['primer_nombre'] = $persona->primer_nombre;
            $data['segundo_nombre'] = $persona->segundo_nombre;
            $data['primer_apellido'] = $persona->primer_apellido;
            $data['segundo_apellido'] = $persona->segundo_apellido;
            $data['dni'] = $persona->dni;
            $data['telefono'] = $persona->telefono;
            $data['direccion'] = $persona->direccion;
            $data['sexo'] = $persona->sexo;
            $data['fecha_nacimiento'] = $persona->fecha_nacimiento;
            $data['nacionalidad_id'] = $persona->nacionalidad_id;
            
            // Manejo mejorado de la fotografía
            if ($persona->fotografia) {
                // Asegurar que la ruta es correcta
                $data['fotografia'] = $persona->fotografia;
            }
        }

        // Obtener TODAS las enfermedades del paciente
        $enfermedadesData = [];
        if ($this->record->enfermedades->isNotEmpty()) {
            foreach ($this->record->enfermedades as $enfermedad) {
                $pivot = $enfermedad->pivot;
                
                // Extraer el año de la fecha de diagnóstico
                $anoDiagnostico = $pivot->fecha_diagnostico ? 
                    date('Y', strtotime($pivot->fecha_diagnostico)) : 
                    date('Y');
                
                $enfermedadesData[] = [
                    'enfermedad_id' => $enfermedad->id,
                    'ano_diagnostico' => $anoDiagnostico,
                    'tratamiento' => $pivot->tratamiento,
                ];
            }
        }
        
        $data['enfermedades_data'] = $enfermedadesData;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        DB::beginTransaction();

        try {
            // Preparar datos de la persona
            $personaData = [
                'primer_nombre' => $data['primer_nombre'],
                'segundo_nombre' => $data['segundo_nombre'],
                'primer_apellido' => $data['primer_apellido'],
                'segundo_apellido' => $data['segundo_apellido'],
                'telefono' => $data['telefono'],
                'direccion' => $data['direccion'],
                'sexo' => $data['sexo'],
                'fecha_nacimiento' => $data['fecha_nacimiento'],
                'nacionalidad_id' => $data['nacionalidad_id'],
                'updated_by' => Auth::id(),
            ];

            // Solo actualizar la fotografía si se proporcionó una nueva
            if (isset($data['fotografia']) && $data['fotografia']) {
                $personaData['fotografia'] = $data['fotografia'];
            }

            // 1. Actualizar datos de la persona
            $record->persona->update($personaData);

            // 2. Actualizar el paciente
            $record->update([
                'grupo_sanguineo' => $data['grupo_sanguineo'],
                'contacto_emergencia' => $data['contacto_emergencia'],
            ]);

            // 3. Sincronizar enfermedades - SISTEMA DE MÚLTIPLES ENFERMEDADES SIN DUPLICADOS
            $record->enfermedades()->detach();
            
            if (isset($data['enfermedades_data']) && is_array($data['enfermedades_data'])) {
                $enfermedadesSeleccionadas = [];
                
                foreach ($data['enfermedades_data'] as $enfermedadData) {
                    if (isset($enfermedadData['enfermedad_id'])) {
                        // Verificar duplicados
                        if (in_array($enfermedadData['enfermedad_id'], $enfermedadesSeleccionadas)) {
                            continue; // Saltar enfermedad duplicada
                        }
                        
                        $enfermedadesSeleccionadas[] = $enfermedadData['enfermedad_id'];
                        
                        // Convertir año a fecha completa (1 de enero del año)
                        $fechaDiagnostico = $enfermedadData['ano_diagnostico'] . '-01-01';
                        
                        $record->enfermedades()->attach($enfermedadData['enfermedad_id'], [
                            'fecha_diagnostico' => $fechaDiagnostico,
                            'tratamiento' => $enfermedadData['tratamiento'] ?? null,
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();
            return $record;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Paciente actualizado exitosamente';
    }
}