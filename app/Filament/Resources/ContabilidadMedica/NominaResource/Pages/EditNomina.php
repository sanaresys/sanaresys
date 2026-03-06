<?php

namespace App\Filament\Resources\ContabilidadMedica\NominaResource\Pages;

use App\Filament\Resources\ContabilidadMedica\NominaResource;
use App\Models\ContabilidadMedica\DetalleNomina;
use App\Models\Medico;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class EditNomina extends EditRecord
{
    protected static string $resource = NominaResource::class;
    
    protected static string $view = 'filament.resources.contabilidad-medica.nomina-resource.pages.create-nomina';

    public $medicosSeleccionados = [];

    public function mount(int | string $record): void
    {
        parent::mount($record);
        $this->loadMedicosFromRecord();
    }

    protected function loadMedicosFromRecord(): void
    {
        $user = Auth::user();
        $centroId = $user ? $user->centro_id : null;
        
        // Usar la relación optimizada para obtener solo médicos con contratos activos
        $query = Medico::with(['persona', 'contratoActivo'])
            ->whereHas('contratosActivos'); // Solo médicos con contratos activos
        
        if ($centroId) {
            $query->where('centro_id', $centroId);
        }
        
        $todosMedicos = $query->get()
            ->filter(function ($medico) {
                // Filtrar solo médicos que tengan persona asociada
                return $medico->persona && $medico->persona->nombre_completo;
            });

        // Obtener los detalles de nómina existentes
        $detallesExistentes = $this->record->detalles()->with('medico.persona')->get();
        
        $this->medicosSeleccionados = $todosMedicos->map(function ($medico) use ($detallesExistentes) {
            $contrato = $medico->contratoActivo;
            $salarioBase = $contrato ? (float) $contrato->salario_mensual : 0;
            
            // Buscar si este médico ya está en la nómina
            $detalleExistente = $detallesExistentes->firstWhere('medico_id', $medico->id);
            
            if ($detalleExistente) {
                return [
                    'id' => $medico->id,
                    'nombre' => $medico->persona->nombre_completo,
                    'salario_base' => (float) $detalleExistente->salario_base,
                    'deducciones' => (float) $detalleExistente->deducciones,
                    'percepciones' => (float) $detalleExistente->percepciones,
                    'total' => (float) $detalleExistente->total_pagar,
                    'seleccionado' => true,
                ];
            } else {
                return [
                    'id' => $medico->id,
                    'nombre' => $medico->persona->nombre_completo,
                    'salario_base' => $salarioBase,
                    'deducciones' => 0.0,
                    'percepciones' => 0.0,
                    'total' => $salarioBase,
                    'seleccionado' => false,
                ];
            }
        })->values()->toArray();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información General')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('empresa')
                                    ->label('Centro Médico')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('año')
                                    ->label('Año')
                                    ->required()
                                    ->numeric(),

                                Select::make('mes')
                                    ->label('Mes')
                                    ->options([
                                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
                                    ])
                                    ->required(),

                                Select::make('tipo_pago')
                                    ->label('Tipo de Pago')
                                    ->options([
                                        'mensual' => 'Mensual',
                                        'quincenal' => 'Quincenal',
                                        'semanal' => 'Semanal',
                                    ])
                                    ->required(),
                            ]),

                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function toggleSeleccionTodos(): void
    {
        $todosSeleccionados = collect($this->medicosSeleccionados)->every(fn($medico) => $medico['seleccionado']);
        
        foreach ($this->medicosSeleccionados as $index => $medico) {
            $this->medicosSeleccionados[$index]['seleccionado'] = !$todosSeleccionados;
        }
    }

    public function deseleccionarTodos(): void
    {
        foreach ($this->medicosSeleccionados as $index => $medico) {
            $this->medicosSeleccionados[$index]['seleccionado'] = false;
        }
    }

    public function updatedMedicosSeleccionados($value, $key): void
    {
        // Separar el key para obtener índice y campo
        $parts = explode('.', $key);
        $index = $parts[0];
        $campo = $parts[1] ?? '';
        
        // Si el campo actualizado es 'seleccionado' y está marcado como true
        if ($campo === 'seleccionado' && $value === true) {
            // Calcular automáticamente las comisiones para este médico
            $this->calcularComisionesParaMedico($index);
        }
        
        // Recalcular totales cuando cambian los valores
        if (strpos($key, 'deducciones') !== false || strpos($key, 'percepciones') !== false || strpos($key, 'salario_base') !== false) {
            $salario = (float) ($this->medicosSeleccionados[$index]['salario_base'] ?? 0);
            $deducciones = (float) ($this->medicosSeleccionados[$index]['deducciones'] ?? 0);
            $percepciones = (float) ($this->medicosSeleccionados[$index]['percepciones'] ?? 0);
            
            $this->medicosSeleccionados[$index]['total'] = $salario + $percepciones - $deducciones;
        }
    }
    
    /**
     * Calcula las comisiones solo para un médico específico
     */
    protected function calcularComisionesParaMedico($index): void
    {
        $medico = $this->medicosSeleccionados[$index] ?? null;
        if (!$medico) {
            return;
        }
        
        $comisionService = app(\App\Services\ComisionMedicoService::class);
        $formData = $this->form->getState();
        $año = $formData['año'] ?? date('Y');
        $mes = $formData['mes'] ?? date('n');
        
        // Determinar si es quincenal
        $quincena = null;
        if (($formData['tipo_pago'] ?? null) === 'quincenal') {
            $quincena = $formData['quincena'] ?? 1;
        }
        
        // Calcular comisión para este médico
        $resultado = $comisionService->calcularComision(
            $medico['id'],
            $año,
            $mes,
            $quincena
        );
        
        if ($resultado['total_comision'] > 0) {
            // Agregar la comisión como percepción
            $this->medicosSeleccionados[$index]['percepciones'] = (float) ($this->medicosSeleccionados[$index]['percepciones'] ?? 0) + $resultado['total_comision'];
            
            // Recalcular total
            $salarioBase = (float) ($this->medicosSeleccionados[$index]['salario_base'] ?? 0);
            $percepciones = (float) ($this->medicosSeleccionados[$index]['percepciones'] ?? 0);
            $deducciones = (float) ($this->medicosSeleccionados[$index]['deducciones'] ?? 0);
            
            $this->medicosSeleccionados[$index]['total'] = $salarioBase + $percepciones - $deducciones;
            
            // Generar detalle para mostrar
            $nombreMedico = $medico['nombre'];
            $totalFacturado = number_format($resultado['total_facturado'], 2);
            $porcentaje = $resultado['porcentaje_servicio'];
            $comision = number_format($resultado['total_comision'], 2);
            
            // Agregar información de comisión como detalle
            if (!isset($this->medicosSeleccionados[$index]['percepciones_detalle'])) {
                $this->medicosSeleccionados[$index]['percepciones_detalle'] = '';
            }
            
            $this->medicosSeleccionados[$index]['percepciones_detalle'] .= 
                "Comisión por servicios: L. {$comision} " .
                "({$porcentaje}% de L. {$totalFacturado})\n";
                
            // Mostrar una notificación discreta
            \Filament\Notifications\Notification::make()
                ->title('Comisión calculada')
                ->body("Se ha calculado la comisión para {$nombreMedico}.")
                ->success()
                ->send();
        }
    }

    protected function handleRecordUpdate($record, array $data): \App\Models\ContabilidadMedica\Nomina
    {
        // Validar que haya médicos seleccionados
        $medicosSeleccionados = array_filter($this->medicosSeleccionados, fn($medico) => $medico['seleccionado']);
        
        if (empty($medicosSeleccionados)) {
            Notification::make()
                ->title('Error')
                ->body('Debe seleccionar al menos un médico para la nómina.')
                ->danger()
                ->send();
            $this->halt();
        }

        // Actualizar los datos de la nómina
        $record->update($data);

        // Eliminar detalles existentes
        $record->detalles()->delete();

        // Crear nuevos detalles
        foreach ($medicosSeleccionados as $medico) {
            $detalleData = [
                'nomina_id' => $record->id,
                'medico_id' => $medico['id'],
                'medico_nombre' => $medico['nombre'],
                'salario_base' => (float) ($medico['salario_base'] ?? 0),
                'deducciones' => (float) ($medico['deducciones'] ?? 0),
                'percepciones' => (float) ($medico['percepciones'] ?? 0),
                'total_pagar' => (float) ($medico['total'] ?? 0),
                'centro_id' => $record->centro_id,
                'percepciones_detalle' => $medico['percepciones_detalle'] ?? null,
                'deducciones_detalle' => $medico['deducciones_detalle'] ?? null,
            ];
            
            DetalleNomina::create($detalleData);
        }

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => !$this->record->cerrada),
        ];
    }
    
    /**
     * Calcula comisiones para todos los médicos seleccionados
     * NOTA: Esta función se mantiene para uso interno, pero ya no es accesible desde la interfaz
     * ya que las comisiones se calculan automáticamente al seleccionar un médico
     */
    public function calcularComisiones(): void
    {
        $comisionService = app(\App\Services\ComisionMedicoService::class);
        $año = $this->record->año;
        $mes = $this->record->mes;
        
        // Determinar si es quincenal
        $quincena = null;
        if ($this->record->tipo_pago === 'quincenal') {
            $quincena = $this->record->quincena ?? 1;
        }
        
        $medicosActualizados = 0;
        
        foreach ($this->medicosSeleccionados as $index => $medico) {
            if ($medico['seleccionado']) {
                // Calcular comisión para este médico
                $resultado = $comisionService->calcularComision(
                    $medico['id'],
                    $año,
                    $mes,
                    $quincena
                );
                
                if ($resultado['total_comision'] > 0) {
                    // Agregar la comisión como percepción
                    $this->medicosSeleccionados[$index]['percepciones'] = (float) ($this->medicosSeleccionados[$index]['percepciones'] ?? 0) + $resultado['total_comision'];
                    
                    // Recalcular total
                    $salarioBase = (float) ($this->medicosSeleccionados[$index]['salario_base'] ?? 0);
                    $percepciones = (float) ($this->medicosSeleccionados[$index]['percepciones'] ?? 0);
                    $deducciones = (float) ($this->medicosSeleccionados[$index]['deducciones'] ?? 0);
                    
                    $this->medicosSeleccionados[$index]['total'] = $salarioBase + $percepciones - $deducciones;
                    
                    $medicosActualizados++;
                    
                    // Generar detalle para mostrar
                    $nombreMedico = $medico['nombre'];
                    $totalFacturado = number_format($resultado['total_facturado'], 2);
                    $porcentaje = $resultado['porcentaje_servicio'];
                    $comision = number_format($resultado['total_comision'], 2);
                    
                    // Agregar información de comisión como detalle
                    if (!isset($this->medicosSeleccionados[$index]['percepciones_detalle'])) {
                        $this->medicosSeleccionados[$index]['percepciones_detalle'] = '';
                    }
                    
                    $this->medicosSeleccionados[$index]['percepciones_detalle'] .= 
                        "Comisión por servicios: L. {$comision} " .
                        "({$porcentaje}% de L. {$totalFacturado})\n";
                }
            }
        }
        
        if ($medicosActualizados > 0) {
            \Filament\Notifications\Notification::make()
                ->title('Comisiones calculadas')
                ->body("Se han calculado comisiones para {$medicosActualizados} médicos.")
                ->success()
                ->send();
        } else {
            \Filament\Notifications\Notification::make()
                ->title('Sin comisiones')
                ->body('No se encontraron comisiones para los médicos seleccionados en este período.')
                ->warning()
                ->send();
        }
    }

    protected function authorizeAccess(): void
    {
        parent::authorizeAccess();
        
        if ($this->record->cerrada) {
            $this->redirect(route('filament.admin.resources.contabilidad-medica.nominas.view', $this->record));
            
            \Filament\Notifications\Notification::make()
                ->title('Nómina cerrada')
                ->body('Esta nómina está cerrada y no puede ser editada.')
                ->warning()
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Guardar cambios')
                ->submit('save')
                ->keyBindings(['mod+s'])
                ->color('primary'),
            \Filament\Actions\Action::make('cancel')
                ->label('Cancelar')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),
        ];
    }

    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
        return Notification::make()
            ->title('Nómina actualizada')
            ->body('La nómina se ha actualizado exitosamente.')
            ->success();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Actualiza automáticamente las bonificaciones cada 30 segundos
     */
    public function actualizarBonificacionesAutomaticamente(): void
    {
        // Solo actualizar si la nómina no está cerrada
        if ($this->record->cerrada) {
            return;
        }
        
        $comisionService = app(\App\Services\ComisionMedicoService::class);
        $año = $this->record->año;
        $mes = $this->record->mes;
        
        // Determinar si es quincenal
        $quincena = null;
        if ($this->record->tipo_pago === 'quincenal') {
            $quincena = $this->record->quincena ?? 1;
        }
        
        $actualizacionesRealizadas = false;
        
        foreach ($this->medicosSeleccionados as $index => $medico) {
            if ($medico['seleccionado']) {
                // Calcular comisión actual para este médico
                $resultado = $comisionService->calcularComision(
                    $medico['id'],
                    $año,
                    $mes,
                    $quincena
                );
                
                // Verificar si hay cambios en la comisión
                $comisionActual = $resultado['total_comision'];
                $percepcionesActuales = (float) ($medico['percepciones'] ?? 0);
                
                // Si hay diferencia, actualizar
                if ($comisionActual != $percepcionesActuales && $comisionActual > 0) {
                    $this->medicosSeleccionados[$index]['percepciones'] = $comisionActual;
                    
                    // Recalcular total
                    $salarioBase = (float) ($medico['salario_base'] ?? 0);
                    $deducciones = (float) ($medico['deducciones'] ?? 0);
                    
                    $this->medicosSeleccionados[$index]['total'] = $salarioBase + $comisionActual - $deducciones;
                    
                    $actualizacionesRealizadas = true;
                }
            }
        }
        
        if ($actualizacionesRealizadas) {
            // Mostrar notificación
            \Filament\Notifications\Notification::make()
                ->title('Bonificaciones actualizadas')
                ->body('Se han actualizado las bonificaciones automáticamente.')
                ->success()
                ->send();
        }
    }
}