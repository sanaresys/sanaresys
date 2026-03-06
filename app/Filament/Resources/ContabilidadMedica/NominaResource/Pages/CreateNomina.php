<?php

namespace App\Filament\Resources\ContabilidadMedica\NominaResource\Pages;

use App\Filament\Resources\ContabilidadMedica\NominaResource;
use App\Models\ContabilidadMedica\DetalleNomina;
use App\Models\Medico;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Checkbox;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class CreateNomina extends CreateRecord
{
    protected static string $resource = NominaResource::class;

    protected static string $view = 'filament.resources.contabilidad-medica.nomina-resource.pages.create-nomina';

    public $medicosSeleccionados = [];

    public function mount(): void
    {
        parent::mount();
        $this->loadMedicos();
    }

    protected function loadMedicos(): void
    {
        $user = Auth::user();
        $centroId = $user ? $user->centro_id : null;
        
        // Usar la relación optimizada para obtener solo médicos con contratos activos
        $query = Medico::with(['persona', 'contratoActivo'])
            ->whereHas('contratosActivos'); // Solo médicos con contratos activos
        
        // Filtrar por centro médico del usuario si existe
        if ($centroId) {
            $query->where('centro_id', $centroId);
        }
        
        $this->medicosSeleccionados = $query->get()
            ->filter(function ($medico) {
                // Filtrar solo médicos que tengan persona asociada
                return $medico->persona && $medico->persona->nombre_completo;
            })
            ->map(function ($medico) {
                $contrato = $medico->contratoActivo;
                $salario = $contrato ? $contrato->salario_mensual : 0;
                
                return [
                    'id' => $medico->id,
                    'nombre' => $medico->persona->nombre_completo,
                    'salario_base' => $salario,
                    'deducciones' => 0,
                    'percepciones' => 0,
                    'total' => $salario,
                    'seleccionado' => false,
                ];
            })
            ->values() // Reindexar el array
            ->toArray();
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
                                    ->default(function () {
                                        $user = Auth::user();
                                        if ($user && $user->centro) {
                                            return $user->centro->nombre_centro;
                                        }
                                        return 'Centro Médico';
                                    })
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('año')
                                    ->label('Año')
                                    ->required()
                                    ->numeric()
                                    ->default(date('Y')),

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
                                    ->required()
                                    ->default('mensual')
                                    ->live()
                                    ->afterStateUpdated(function ($state) {
                                        $this->actualizarSalariosSegunTipo($state);
                                    }),

                                Select::make('quincena')
                                    ->label('Quincena')
                                    ->options([
                                        1 => 'Primera Quincena',
                                        2 => 'Segunda Quincena',
                                    ])
                                    ->required()
                                    ->visible(fn($get) => $get('tipo_pago') === 'quincenal'),
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

    public function actualizarSalariosSegunTipo($tipoPago): void
    {
        foreach ($this->medicosSeleccionados as $index => $medico) {
            $contrato = Medico::find($medico['id'])->contratoActivo;
            $salarioMensual = $contrato ? $contrato->salario_mensual : 0;
            
            switch ($tipoPago) {
                case 'quincenal':
                    $salarioCalculado = $salarioMensual / 2;
                    break;
                case 'semanal':
                    $salarioCalculado = $salarioMensual / 4;
                    break;
                default: // mensual
                    $salarioCalculado = $salarioMensual;
                    break;
            }
            
            $this->medicosSeleccionados[$index]['salario_base'] = $salarioCalculado;
            $this->medicosSeleccionados[$index]['total'] = $salarioCalculado + $medico['percepciones'] - $medico['deducciones'];
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
            $salario = floatval($this->medicosSeleccionados[$index]['salario_base'] ?? 0);
            $deducciones = floatval($this->medicosSeleccionados[$index]['deducciones'] ?? 0);
            $percepciones = floatval($this->medicosSeleccionados[$index]['percepciones'] ?? 0);
            
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
            $this->medicosSeleccionados[$index]['percepciones'] += $resultado['total_comision'];
            
            // Recalcular total
            $this->medicosSeleccionados[$index]['total'] = 
                $this->medicosSeleccionados[$index]['salario_base'] + 
                $this->medicosSeleccionados[$index]['percepciones'] - 
                $this->medicosSeleccionados[$index]['deducciones'];
            
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

    public function create(bool $another = false): void
    {
        $data = $this->form->getState();

        // Validar que haya médicos seleccionados
        $medicosSeleccionados = array_filter($this->medicosSeleccionados, fn($medico) => $medico['seleccionado']);
        
        if (empty($medicosSeleccionados)) {
            Notification::make()
                ->title('Error')
                ->body('Debe seleccionar al menos un médico para crear la nómina.')
                ->danger()
                ->send();
            return;
        }

        // Crear la nómina
        $user = Auth::user();
        $data['centro_id'] = $user ? $user->centro_id : null;
        $nomina = $this->getModel()::create($data);

        // Crear los detalles de nómina
        foreach ($medicosSeleccionados as $medico) {
            $detalleData = [
                'nomina_id' => $nomina->id,
                'medico_id' => $medico['id'],
                'medico_nombre' => $medico['nombre'],
                'salario_base' => $medico['salario_base'],
                'deducciones' => $medico['deducciones'],
                'percepciones' => $medico['percepciones'],
                'total_pagar' => $medico['total'],
                'centro_id' => $nomina->centro_id,
                'percepciones_detalle' => $medico['percepciones_detalle'] ?? null,
                'deducciones_detalle' => $medico['deducciones_detalle'] ?? null,
            ];
            
            // Asegurar que no haya campos problemáticos
            unset($detalleData['created_by'], $detalleData['updated_by'], $detalleData['deleted_by']);
            
            DetalleNomina::create($detalleData);
        }

        Notification::make()
            ->title('Nómina creada')
            ->body('La nómina se ha creado exitosamente.')
            ->success()
            ->send();

        $this->redirect($this->getRedirectUrl());
    }

    /**
     * Calcula comisiones para todos los médicos seleccionados
     * NOTA: Esta función se mantiene para uso interno, pero ya no es accesible desde la interfaz
     * ya que las comisiones se calculan automáticamente al seleccionar un médico
     */
    public function calcularComisiones(): void
    {
        $comisionService = app(\App\Services\ComisionMedicoService::class);
        $formData = $this->form->getState();
        $año = $formData['año'] ?? date('Y');
        $mes = $formData['mes'] ?? date('n');
        
        // Determinar si es quincenal
        $quincena = null;
        if (($formData['tipo_pago'] ?? null) === 'quincenal') {
            $quincena = $formData['quincena'] ?? 1;
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
                    $this->medicosSeleccionados[$index]['percepciones'] += $resultado['total_comision'];
                    
                    // Recalcular total
                    $this->medicosSeleccionados[$index]['total'] = 
                        $this->medicosSeleccionados[$index]['salario_base'] + 
                        $this->medicosSeleccionados[$index]['percepciones'] - 
                        $this->medicosSeleccionados[$index]['deducciones'];
                    
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
    
    protected function getHeaderActions(): array
    {
        // Ya no necesitamos botones de acción para las comisiones
        // porque estas se calculan automáticamente al seleccionar un médico
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
