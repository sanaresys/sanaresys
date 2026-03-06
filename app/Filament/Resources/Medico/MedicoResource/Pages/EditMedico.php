<?php

namespace App\Filament\Resources\Medico\MedicoResource\Pages;

use App\Filament\Resources\Medico\MedicoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\Medico;
use App\Models\ContabilidadMedica\ContratoMedico;

class EditMedico extends EditRecord
{
    protected static string $resource = MedicoResource::class;
    protected static ?string $title = 'Editar Médico';

    protected function resolveRecord(int | string $key): Medico
    {
        // Cargar explícitamente el médico con sus relaciones
        return Medico::with(['persona', 'especialidades', 'contratoActivo', 'persona.user'])
            ->findOrFail($key);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Verificación adicional de que el registro está cargado correctamente
        if (!is_object($this->record)) {
            abort(500, 'El registro médico no se cargó correctamente');
        }

        // Cargar relaciones si no están cargadas
        $this->record->loadMissing(['persona', 'especialidades', 'contratoActivo', 'persona.user']);

        // Obtener datos del contrato activo
        $contrato = $this->record->contratoActivo;
        $usuario = $this->record->persona->user;
        $persona = $this->record->persona;

        if ($persona->fotografia) {
            // FileUpload espera solo el path relativo, no la URL completa
            $data['fotografia'] = $persona->fotografia;
        }

        return array_merge($data, [
            // Datos personales
            'primer_nombre' => $this->record->persona->primer_nombre ?? null,
            'segundo_nombre' => $this->record->persona->segundo_nombre ?? null,
            'primer_apellido' => $this->record->persona->primer_apellido ?? null,
            'segundo_apellido' => $this->record->persona->segundo_apellido ?? null,
            'dni' => $this->record->persona->dni ?? null,
            'telefono' => $this->record->persona->telefono ?? null,
            'direccion' => $this->record->persona->direccion ?? null,
            'sexo' => $this->record->persona->sexo ?? null,
            'fecha_nacimiento' => $this->record->persona->fecha_nacimiento ?? null,
            'nacionalidad_id' => $this->record->persona->nacionalidad_id ?? null,
            // 'persona.foto' eliminado, solo se usa 'fotografia'
            'especialidades' => $this->record->especialidades->pluck('id')->toArray(),
            
            // Datos del contrato
            'salario_quincenal' => $contrato->salario_quincenal ?? null,
            'salario_mensual' => $contrato->salario_mensual ?? null,
            'porcentaje_servicio' => $contrato->porcentaje_servicio ?? 0,
            'fecha_inicio' => $contrato->fecha_inicio ?? null,
            'fecha_fin' => $contrato->fecha_fin ?? null,
            'activo' => $contrato->activo ?? true,
            'observaciones_contrato' => $contrato->observaciones ?? null,
            
            // Datos del usuario (si existe)
            'crear_usuario' => $usuario ? true : false,
            'username' => $usuario?->name ?? null,
            'user_email' => $usuario?->email ?? null,
            'user_role' => $usuario?->roles?->first()?->name ?? 'medico',
            'user_active' => $usuario ? ($usuario->email_verified_at ? true : false) : false,
        ]);
    }

    protected function handleRecordUpdate($record, array $data): Medico
{
    DB::beginTransaction();

    try {
        // Validar duplicados de usuario solo si se van a cambiar
        $usuario = $record->persona->user;
        
        if (isset($data['username']) && !empty($data['username'])) {
            // Solo validar si el username cambió
            if (!$usuario || $usuario->name !== $data['username']) {
                $existingUser = \App\Models\User::where('name', $data['username']);
                if ($usuario) {
                    $existingUser->where('id', '!=', $usuario->id);
                }
                if ($existingUser->exists()) {
                    \Filament\Notifications\Notification::make()
                        ->title('Error de validación')
                        ->body('El nombre de usuario "' . $data['username'] . '" ya está en uso por otro usuario.')
                        ->danger()
                        ->send();
                    
                    DB::rollBack();
                    $this->halt(); // Detener el proceso sin error fatal
                    return $record;
                }
            }
        }

        if (isset($data['user_email']) && !empty($data['user_email'])) {
            // Solo validar si el email cambió
            if (!$usuario || $usuario->email !== $data['user_email']) {
                $existingEmail = \App\Models\User::where('email', $data['user_email']);
                if ($usuario) {
                    $existingEmail->where('id', '!=', $usuario->id);
                }
                if ($existingEmail->exists()) {
                    \Filament\Notifications\Notification::make()
                        ->title('Error de validación')
                        ->body('El email "' . $data['user_email'] . '" ya está en uso por otro usuario.')
                        ->danger()
                        ->send();
                    
                    DB::rollBack();
                    $this->halt(); // Detener el proceso sin error fatal
                    return $record;
                }
            }
        }
        // 1. Actualizar datos de la persona
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
        ];

        // Actualizar fotografía si se proporcionó
        if (isset($data['fotografia']) && $data['fotografia']) {
            $personaData['fotografia'] = $data['fotografia'];
        }

        $record->persona->update($personaData);

        // 2. Actualizar datos del médico
        $record->update([
            'numero_colegiacion' => $data['numero_colegiacion'],
            'horario_entrada' => $data['horario_entrada'],
            'horario_salida' => $data['horario_salida'],
        ]);

        // 3. Sincronizar especialidades
        if (array_key_exists('especialidades', $data)) {
            $record->especialidades()->sync($data['especialidades']);
        }

        // 4. Actualizar o crear contrato médico
        if (isset($data['salario_quincenal']) && isset($data['porcentaje_servicio'])) {
            $contratoData = [
                'salario_quincenal' => $data['salario_quincenal'],
                'salario_mensual' => $data['salario_quincenal'] * 2,
                'porcentaje_servicio' => $data['porcentaje_servicio'] ?? 0,
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin' => isset($data['fecha_fin']) && $data['fecha_fin'] ? $data['fecha_fin'] : null,
                'activo' => $data['activo'] ?? true,
                'observaciones' => $data['observaciones_contrato'] ?? null,
            ];

            if (Schema::hasColumn('contratos_medicos', 'centro_id')) {
                $contratoData['centro_id'] = $record->centro_id ?? tenancy()->tenant?->centro_id;
            }

            // Buscar contrato activo existente o crear uno nuevo
            $contrato = $record->contratoActivo;
            if ($contrato) {
                $contrato->update($contratoData);
            } else {
                $contratoData['medico_id'] = $record->id;
                ContratoMedico::create($contratoData);
            }
        }

        // 5. Manejar usuario de acceso - Solo si se proporcionaron datos de usuario
        $usuario = $record->persona->user;
        $crearUsuario = $data['crear_usuario'] ?? false;

        if ($crearUsuario && !$usuario) {
            // Crear nuevo usuario solo si se proporcionaron todos los datos requeridos
            if (isset($data['username']) && isset($data['user_email']) && isset($data['user_password']) &&
                !empty($data['username']) && !empty($data['user_email']) && !empty($data['user_password'])) {
                try {
                    $userData = [
                        'name' => $data['username'],
                        'email' => $data['user_email'],
                        'password' => Hash::make($data['user_password']),
                        'persona_id' => $record->persona->id,
                        'email_verified_at' => $data['user_active'] ? now() : null,
                    ];

                    if (Schema::hasColumn('users', 'centro_id')) {
                        $userData['centro_id'] = $record->centro_id ?? tenancy()->tenant?->centro_id;
                    }

                    $nuevoUsuario = \App\Models\User::create($userData);

                    // Asignar rol
                    $nuevoUsuario->assignRole($data['user_role'] ?? 'medico');
                    
                    Log::info("Usuario creado durante edición", [
                        'medico_id' => $record->id,
                        'user_id' => $nuevoUsuario->id,
                        'username' => $data['username']
                    ]);
                } catch (\Exception $e) {
                    Log::error("Error al crear usuario durante edición: " . $e->getMessage());
                }
            }
        } elseif ($usuario) {
            // Solo actualizar usuario existente si se proporcionaron cambios
            $updateData = [];
            $shouldUpdate = false;

            // Verificar si el nombre de usuario cambió
            if (isset($data['username']) && !empty($data['username']) && $data['username'] !== $usuario->name) {
                $updateData['name'] = $data['username'];
                $shouldUpdate = true;
            }

            // Verificar si el email cambió
            if (isset($data['user_email']) && !empty($data['user_email']) && $data['user_email'] !== $usuario->email) {
                $updateData['email'] = $data['user_email'];
                $shouldUpdate = true;
            }

            // Verificar si se cambió el estado activo
            if (isset($data['user_active'])) {
                $currentlyActive = $usuario->email_verified_at !== null;
                if ($data['user_active'] !== $currentlyActive) {
                    $updateData['email_verified_at'] = $data['user_active'] ? ($usuario->email_verified_at ?? now()) : null;
                    $shouldUpdate = true;
                }
            }

            // Solo actualizar contraseña si se proporciona una nueva
            if (isset($data['user_password']) && !empty($data['user_password'])) {
                $updateData['password'] = Hash::make($data['user_password']);
                $shouldUpdate = true;
            }

            // Solo actualizar si hay cambios
            if ($shouldUpdate && !empty($updateData)) {
                $usuario->update($updateData);
                
                Log::info("Usuario actualizado durante edición", [
                    'medico_id' => $record->id,
                    'user_id' => $usuario->id,
                    'changes' => array_keys($updateData)
                ]);
            }

            // Verificar si el rol cambió
            if (isset($data['user_role']) && !empty($data['user_role'])) {
                $currentRole = $usuario->roles->first()?->name;
                if ($data['user_role'] !== $currentRole) {
                    $usuario->syncRoles([$data['user_role']]);
                    Log::info("Rol de usuario actualizado", [
                        'user_id' => $usuario->id,
                        'old_role' => $currentRole,
                        'new_role' => $data['user_role']
                    ]);
                }
            }
        }

        DB::commit();
        return $record;

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error al actualizar médico: '.$e->getMessage());
        throw $e;
    }
}
    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('Guardar cambios')
                ->submit('save')
                ->icon('heroicon-o-check')
                ->color('primary'),
                
            Actions\Action::make('cancel')
                ->label('Cancelar')
                ->url($this->getResource()::getUrl('index'))
                ->icon('heroicon-o-x-mark')
->color('danger')
        ];
    }

    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
        return \Filament\Notifications\Notification::make()
            ->success()
            ->title('Médico actualizado')
            ->body('Los datos del médico y sus especialidades se han actualizado correctamente.');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
