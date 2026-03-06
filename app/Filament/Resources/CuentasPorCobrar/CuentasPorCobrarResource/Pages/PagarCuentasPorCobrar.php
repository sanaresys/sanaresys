<?php

namespace App\Filament\Resources\CuentasPorCobrar\CuentasPorCobrarResource\Pages;

use App\Filament\Resources\CuentasPorCobrar\CuentasPorCobrarResource;
use App\Models\CuentasPorCobrar;
use App\Models\Factura;
use App\Models\Pagos_Factura;
use App\Models\TipoPago;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class PagarCuentasPorCobrar extends Page
{
    protected static string $resource = CuentasPorCobrarResource::class;
    protected static string $view = 'filament.resources.cuentas-por-cobrar.pages.pagar-cuentas-por-cobrar';

    public ?array $data = [];
    public ?CuentasPorCobrar $cuentaPorCobrar = null;
    public ?Factura $factura = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('buscar_factura')
                ->label('Buscar por Número de Factura')
                ->icon('heroicon-o-magnifying-glass')
                ->form([
                    TextInput::make('numero_factura')
                        ->label('Número de Factura')
                        ->placeholder('Ej: 001-001-01-00000001 o PROF-123')
                        ->required()
                        ->helperText('Ingrese el número completo de la factura'),
                ])
                ->action(function (array $data): void {
                    $numeroFactura = $data['numero_factura'];
                    
                    // Buscar factura por número CAI o por ID de proforma
                    $factura = null;
                    
                    if (str_starts_with($numeroFactura, 'PROF-')) {
                        // Es una proforma
                        $id = str_replace('PROF-', '', $numeroFactura);
                        $factura = Factura::where('id', $id)->where('usa_cai', false)->first();
                    } else {
                        // Es una factura CAI
                        $factura = Factura::whereHas('caiCorrelativo', function ($query) use ($numeroFactura) {
                            $query->where('numero_factura', $numeroFactura);
                        })->first();
                    }
                    
                    if (!$factura) {
                        Notification::make()
                            ->title('Factura no encontrada')
                            ->body("No se encontró ninguna factura con el número: {$numeroFactura}")
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    // Verificar saldo pendiente
                    $saldoPendiente = $factura->saldoPendiente();
                    if ($saldoPendiente <= 0) {
                        Notification::make()
                            ->title('Factura ya pagada')
                            ->body("Esta factura no tiene saldo pendiente. Total pagado: L." . number_format($factura->montoPagado(), 2))
                            ->warning()
                            ->send();
                        return;
                    }
                    
                    // Buscar o crear cuenta por cobrar asociada
                    $cuentaPorCobrar = CuentasPorCobrar::where('factura_id', $factura->id)->first();
                    
                    if (!$cuentaPorCobrar) {
                        // Crear cuenta por cobrar si no existe
                        $montoPagado = $factura->montoPagado();
                        $estadoCuenta = ($montoPagado > 0) ? 'PARCIAL' : 'PENDIENTE';
                        
                        $cuentaPorCobrar = CuentasPorCobrar::create([
                            'factura_id' => $factura->id,
                            'saldo_pendiente' => $saldoPendiente,
                            'fecha_vencimiento' => now()->addDays(30),
                            'centro_id' => $factura->centro_id,
                            'estado_cuentas_por_cobrar' => $estadoCuenta,
                            'created_by' => Auth::id(),
                        ]);
                        
                        Notification::make()
                            ->title('Cuenta por cobrar creada')
                            ->body("Se creó automáticamente una cuenta por cobrar para esta factura.")
                            ->info()
                            ->send();
                    }
                    
                    $this->factura = $factura;
                    $this->cuentaPorCobrar = $cuentaPorCobrar;
                    
                    // Pre-llenar el formulario
                    $this->form->fill([
                        'monto_a_pagar' => $saldoPendiente,
                        'fecha_pago' => now()->toDateString(),
                    ]);
                    
                    Notification::make()
                        ->title('Factura encontrada')
                        ->body("Factura encontrada. Saldo pendiente: L." . number_format($saldoPendiente, 2))
                        ->success()
                        ->send();
                }),
                
            Actions\Action::make('buscar_por_paciente')
                ->label('Buscar por Paciente')
                ->icon('heroicon-o-user')
                ->form([
                    Select::make('paciente_id')
                        ->label('Paciente')
                        ->options(function () {
                            return \App\Models\Pacientes::with('persona')
                                ->get()
                                ->mapWithKeys(function ($paciente) {
                                    return [$paciente->id => $paciente->persona->nombre_completo ?? 'Sin nombre'];
                                });
                        })
                        ->searchable()
                        ->required()
                        ->helperText('Seleccione el paciente para ver sus facturas pendientes'),
                ])
                ->action(function (array $data): void {
                    $pacienteId = $data['paciente_id'];
                    
                    // Buscar facturas pendientes del paciente
                    $facturasPendientes = Factura::where('paciente_id', $pacienteId)
                        ->with(['cuentasPorCobrar', 'paciente.persona'])
                        ->whereIn('estado', ['PENDIENTE', 'PARCIAL'])
                        ->get()
                        ->filter(function ($factura) {
                            return $factura->saldoPendiente() > 0;
                        });
                    
                    if ($facturasPendientes->isEmpty()) {
                        Notification::make()
                            ->title('Sin facturas pendientes')
                            ->body("Este paciente no tiene facturas con saldo pendiente.")
                            ->warning()
                            ->send();
                        return;
                    }
                    
                    // Mostrar lista de facturas pendientes
                    $paciente = $facturasPendientes->first()->paciente;
                    $totalPendiente = $facturasPendientes->sum(function ($f) { return $f->saldoPendiente(); });
                    
                    $mensaje = "Paciente: {$paciente->persona->nombre_completo}\n\n";
                    $mensaje .= "Facturas pendientes:\n";
                    
                    foreach ($facturasPendientes->take(5) as $factura) {
                        $numero = $factura->usa_cai && $factura->caiCorrelativo 
                            ? $factura->caiCorrelativo->numero_factura
                            : "PROF-{$factura->id}";
                        $saldo = $factura->saldoPendiente();
                        $mensaje .= "• {$numero}: L." . number_format($saldo, 2) . "\n";
                    }
                    
                    if ($facturasPendientes->count() > 5) {
                        $mensaje .= "... y " . ($facturasPendientes->count() - 5) . " más\n";
                    }
                    
                    $mensaje .= "\nTotal pendiente: L." . number_format($totalPendiente, 2);
                    
                    Notification::make()
                        ->title('Facturas pendientes encontradas')
                        ->body($mensaje)
                        ->info()
                        ->persistent()
                        ->send();
                }),
        ];
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Section::make('Información de la Cuenta por Cobrar')
                    ->schema([
                        Placeholder::make('info_factura')
                            ->label('Factura')
                            ->content(function () {
                                if (!$this->factura) {
                                    return 'Busque una factura usando el botón "Buscar por Número de Factura"';
                                }
                                
                                $numero = $this->factura->usa_cai && $this->factura->caiCorrelativo 
                                    ? $this->factura->caiCorrelativo->numero_factura
                                    : "PROF-{$this->factura->id}";
                                
                                return "Factura: {$numero} | Paciente: {$this->factura->paciente->persona->nombre_completo}";
                            }),
                            
                        Placeholder::make('info_montos')
                            ->label('Montos')
                            ->content(function () {
                                if (!$this->factura) {
                                    return '-';
                                }
                                
                                $total = $this->factura->total;
                                $pagado = $this->factura->montoPagado();
                                $pendiente = $this->factura->saldoPendiente();
                                
                                return "Total: L." . number_format($total, 2) . 
                                       " | Pagado: L." . number_format($pagado, 2) . 
                                       " | Pendiente: L." . number_format($pendiente, 2);
                            })
                            ->visible(fn () => $this->factura !== null),
                    ])
                    ->visible(fn () => $this->factura !== null),
                    
                Section::make('Procesar Pago')
                    ->schema([
                        Select::make('tipo_pago_id')
                            ->label('Tipo de Pago')
                            ->options(TipoPago::all()->pluck('nombre', 'id'))
                            ->required()
                            ->searchable(),
                            
                        TextInput::make('monto_a_pagar')
                            ->label('Monto a Pagar')
                            ->prefix('L.')
                            ->numeric()
                            ->step(0.01)
                            ->required()
                            ->rules([
                                'min:0.01',
                                function ($attribute, $value, $fail) {
                                    if ($this->cuentaPorCobrar && $value > $this->cuentaPorCobrar->saldo_pendiente) {
                                        $fail("El monto no puede ser mayor al saldo pendiente de L." . number_format($this->cuentaPorCobrar->saldo_pendiente, 2));
                                    }
                                },
                            ])
                            ->helperText(function () {
                                return $this->cuentaPorCobrar 
                                    ? "Saldo pendiente: L." . number_format($this->cuentaPorCobrar->saldo_pendiente, 2)
                                    : '';
                            }),
                            
                        DatePicker::make('fecha_pago')
                            ->label('Fecha de Pago')
                            ->default(now())
                            ->required(),
                    ])
                    ->visible(fn () => $this->cuentaPorCobrar !== null),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('procesar_pago')
                ->label('Procesar Pago')
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->disabled(fn () => !$this->cuentaPorCobrar)
                ->action(function (): void {
                    $data = $this->form->getState();
                    
                    if (!$this->factura || !$this->cuentaPorCobrar) {
                        Notification::make()
                            ->title('Error')
                            ->body('Debe buscar una factura primero.')
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    // Crear el pago
                    Pagos_Factura::create([
                        'factura_id' => $this->factura->id,
                        'paciente_id' => $this->factura->paciente_id,
                        'centro_id' => $this->factura->centro_id,
                        'tipo_pago_id' => $data['tipo_pago_id'],
                        'monto_recibido' => $data['monto_a_pagar'],
                        'monto_devolucion' => 0,
                        'fecha_pago' => $data['fecha_pago'],
                        'created_by' => Auth::id(),
                    ]);
                    
                    // La actualización del estado se hace automáticamente en el observer del modelo Pagos_Factura
                    
                    $this->factura->refresh();
                    $this->cuentaPorCobrar->refresh();
                    
                    $nuevoSaldo = $this->cuentaPorCobrar->saldo_pendiente;
                    $estado = $this->cuentaPorCobrar->estado_cuentas_por_cobrar;
                    
                    Notification::make()
                        ->title('Pago procesado exitosamente')
                        ->body("Pago de L." . number_format($data['monto_a_pagar'], 2) . " procesado. " .
                               ($nuevoSaldo > 0 ? "Saldo restante: L." . number_format($nuevoSaldo, 2) : "Cuenta totalmente pagada."))
                        ->success()
                        ->send();
                    
                    // Limpiar formulario si la cuenta está pagada
                    if ($estado === 'PAGADA') {
                        $this->factura = null;
                        $this->cuentaPorCobrar = null;
                        $this->form->fill([]);
                    } else {
                        // Actualizar monto sugerido
                        $this->form->fill([
                            'monto_a_pagar' => $nuevoSaldo,
                            'fecha_pago' => now()->toDateString(),
                        ]);
                    }
                })
                ->requiresConfirmation()
                ->modalHeading('Confirmar Pago')
                ->modalDescription(function () {
                    $data = $this->form->getState();
                    if (!isset($data['monto_a_pagar'])) return 'Confirme el pago';
                    
                    return "¿Está seguro de procesar un pago de L." . number_format($data['monto_a_pagar'], 2) . "?";
                }),
        ];
    }
}
