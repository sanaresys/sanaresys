<?php

namespace App\Filament\Resources\Consultas\ConsultasResource\Pages;

use App\Filament\Resources\Consultas\ConsultasResource;
use App\Models\Consulta;
use App\Models\Servicio;
use App\Models\FacturaDetalle;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Notifications\Notification;
use App\Filament\Resources\Facturas\FacturasResource;
use Illuminate\Support\Facades\Auth;

class ManageServiciosConsulta extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = ConsultasResource::class;
    protected static string $relationship = 'servicios'; // ← Agregar esto
    protected static string $view = 'filament.resources.consultas.pages.manage-servicios-consulta';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('regresar')
                ->label('Regresar a Consulta')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn() => ConsultasResource::getUrl('view', ['record' => $this->record->id]))
                ->button(),
                
            Actions\Action::make('agregar_servicios')
                ->label('Agregar Servicios')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->form([
                    Repeater::make('servicios_data')
                        ->label('Servicios a Agregar')
                        ->schema([
                            Forms\Components\Select::make('servicio_id')
                                ->label('Servicio')
                                ->options(function () {
                                    return Servicio::all()->mapWithKeys(function ($servicio) {
                                        return [$servicio->id => $servicio->nombre . ' - L.' . number_format($servicio->precio_unitario, 2)];
                                    });
                                })
                                ->searchable()
                                ->preload()
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    if ($state) {
                                        $servicio = Servicio::find($state);
                                        if ($servicio) {
                                            $cantidad = (int) ($get('cantidad') ?? 1);
                                            $total = $servicio->precio_unitario * $cantidad;
                                            $set('total_linea', number_format($total, 2, '.', ''));
                                        }
                                    }
                                    
                                    // Solo verificar duplicados en el formulario actual (no en la BD)
                                    $serviciosData = $get('../../servicios_data') ?? [];
                                    $serviciosSeleccionados = array_filter(
                                        array_column($serviciosData, 'servicio_id'),
                                        fn($id) => !is_null($id)
                                    );
                                    
                                    $repetidos = array_count_values($serviciosSeleccionados);
                                    if (isset($repetidos[$state]) && $repetidos[$state] > 1) {
                                        Notification::make()
                                            ->title('Servicio duplicado en el formulario')
                                            ->body('No puede seleccionar el mismo servicio más de una vez en este formulario.')
                                            ->danger()
                                            ->send();
                                        $set('servicio_id', null);
                                        $set('total_linea', 0);
                                    }
                                    
                                    // NOTA: La verificación de BD se hace al enviar el formulario
                                }),
                                
                            Forms\Components\TextInput::make('cantidad')
                                ->label('Cantidad')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $servicioId = $get('servicio_id');
                                    if ($servicioId) {
                                        $servicio = Servicio::find($servicioId);
                                        if ($servicio) {
                                            $cantidad = (int) ($state ?? 1);
                                            $total = $servicio->precio_unitario * $cantidad;
                                            $set('total_linea', number_format($total, 2, '.', ''));
                                        }
                                    }
                                }),
                                
                            Forms\Components\TextInput::make('total_linea')
                                ->label('Total')
                                ->numeric()
                                ->prefix('L.')
                                ->step(0.01)
                                ->disabled()
                                ->dehydrated(true)
                                ->helperText('Cantidad × Precio del Servicio'),
                        ])
                        ->columns(3)
                        ->defaultItems(1)
                        ->addActionLabel('Agregar Otro Servicio')
                        ->itemLabel(fn (array $state): ?string => 
                            isset($state['servicio_id']) ? 
                            (Servicio::find($state['servicio_id'])?->nombre ?? 'Nuevo Servicio') : 
                            'Nuevo Servicio'
                        )
                        ->collapsible()
                        ->cloneable()
                        ->reorderable()
                        ->deleteAction(
                            fn (Forms\Components\Actions\Action $action) => $action
                                ->requiresConfirmation()
                                ->modalDescription('¿Estás seguro de que deseas eliminar este servicio?')
                        )
                        ->minItems(1)
                        ->columnSpanFull()
                ])
                ->action(function (array $data): void {
                    try {
                        $serviciosData = $data['servicios_data'] ?? [];
                        
                        $serviciosCreados = 0;
                        $serviciosDuplicados = 0;
                        $errores = [];
                        
                        foreach ($serviciosData as $servicioData) {
                            if (!empty($servicioData['servicio_id'])) {
                                try {
                                    // VERIFICAR SI YA EXISTE EL SERVICIO PARA ESTA CONSULTA
                                    $existeDetalle = FacturaDetalle::where('consulta_id', $this->record->id)
                                        ->where('servicio_id', $servicioData['servicio_id'])
                                        ->whereNull('factura_id')
                                        ->exists();
                                    
                                    if ($existeDetalle) {
                                        $serviciosDuplicados++;
                                        continue; // Saltar este servicio
                                    }
                                    
                                    $servicio = Servicio::with('impuesto')->find($servicioData['servicio_id']);
                                    if (!$servicio) {
                                        $errores[] = "Servicio con ID {$servicioData['servicio_id']} no encontrado.";
                                        continue;
                                    }
                                        $cantidad = (int) ($servicioData['cantidad'] ?? 1);
                                        $subtotal = $servicio->precio_unitario * $cantidad;
                                        
                                        // Calcular impuesto
                                        $impuesto_monto = 0;
                                        if ($servicio->es_exonerado !== 'SI' && $servicio->impuesto) {
                                            $impuesto_monto = ($subtotal * $servicio->impuesto->porcentaje) / 100;
                                        }
                                        
                                        $total_linea = $subtotal + $impuesto_monto;
                                        
                                        // Usar try-catch para cada inserción individual
                                        try {
                                            FacturaDetalle::create([
                                                'consulta_id' => $this->record->id,
                                                'servicio_id' => $servicioData['servicio_id'],
                                                'cantidad' => $cantidad,
                                                'subtotal' => $subtotal,
                                                'impuesto_id' => $servicio->impuesto?->id,
                                                'impuesto_monto' => $impuesto_monto,
                                                'descuento_monto' => 0,
                                                'total_linea' => $total_linea,
                                                'centro_id' => $this->record->centro_id,
                                                'created_by' => Auth::id(),
                                            ]);
                                            
                                            $serviciosCreados++;
                                        } catch (\Illuminate\Database\QueryException $e) {
                                            // Si es un error de duplicado, contarlo como duplicado
                                            if (str_contains($e->getMessage(), 'unique_consulta_servicio_temp')) {
                                                $serviciosDuplicados++;
                                            } else {
                                                $errores[] = "Error al agregar servicio {$servicio->nombre}: " . $e->getMessage();
                                            }
                                        }
                                } catch (\Exception $e) {
                                    $errores[] = "Error procesando servicio: " . $e->getMessage();
                                }
                            }
                        }
                        
                        // Construir mensaje de resultado
                        $mensaje = "{$serviciosCreados} servicio(s) agregado(s) a la consulta.";
                        if ($serviciosDuplicados > 0) {
                            $mensaje .= " {$serviciosDuplicados} servicio(s) se omitieron por estar duplicados.";
                        }
                        if (!empty($errores)) {
                            $mensaje .= " Errores: " . implode(', ', $errores);
                        }
                        
                        $tipo = empty($errores) ? 'success' : 'warning';
                        
                        Notification::make()
                            ->title('Servicios procesados')
                            ->body($mensaje)
                            ->$tipo()
                            ->send();

                        // AGREGAR ESTA LÍNEA DESPUÉS DE LA NOTIFICACIÓN
                        $this->dispatch('refresh-totales');
                            
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error general')
                            ->body('Error al procesar servicios: ' . $e->getMessage())
                            ->danger()
                            ->send();
                            
                        $this->dispatch('refresh-totales');
                    }
                    
                })
                ->modalHeading('Agregar Servicios a la Consulta')
                ->modalSubmitActionLabel('Agregar Servicios')
                ->modalWidth('5xl')
                ->extraModalFooterActions(fn () => [
                    Actions\Action::make('cancel')
                        ->label('Cancelar')
                        ->color('gray')
                        ->action(fn () => null)
                ]),
                
            Actions\Action::make('crear_factura')
                ->label('Continuar a Facturación')
                ->icon('heroicon-o-arrow-right')
                ->color('primary')
                ->action(function () {
                    $subtotal = $this->getServiciosSubtotal();
                    $impuestoTotal = $this->getServiciosImpuesto();
                    
                    // Crear la URL base con paciente_id incluido
                    $url = "/admin/facturas/create?consulta_id={$this->record->id}&paciente_id={$this->record->paciente_id}&subtotal={$subtotal}&impuesto_total={$impuestoTotal}";
                    
                    // Redirect usando JavaScript para leer el sessionStorage y el estado del CAI específicos de esta consulta
                    $this->js(<<<JS
                        const consultaId = '{$this->record->id}';
                        const descuentoKey = 'selected_descuento_' + consultaId;
                        const caiKey = 'cai_toggle_state_' + consultaId;
                        
                        const descuentoData = sessionStorage.getItem(descuentoKey);
                        const caiToggle = document.getElementById('emitir_cai_toggle');
                        let url = '{$url}';
                        
                        // Agregar información del descuento si existe
                        if (descuentoData) {
                            const descuento = JSON.parse(descuentoData);
                            url += '&descuento_id=' + descuento.id + '&descuento_total=' + descuento.monto_calculado;
                            // Guardar nombre del descuento para conservarlo
                            url += '&descuento_nombre=' + encodeURIComponent(descuento.nombre);
                        }
                        
                        // Agregar información del CAI si está activado
                        if (caiToggle && caiToggle.checked) {
                            url += '&usa_cai=1';
                        } else {
                            url += '&usa_cai=0';
                        }
                        
                        window.location.href = url;
                    JS);
                })
                ->visible(function () {
                    return FacturaDetalle::where('consulta_id', $this->record->id)
                        ->whereNull('factura_id')
                        ->exists();
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                FacturaDetalle::query()
                    ->where('consulta_id', $this->record->id)
                    ->whereNull('factura_id')
                    ->with(['servicio.impuesto'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('servicio.nombre')
                    ->label('Servicio')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                    
                Tables\Columns\TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->alignCenter()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('servicio.precio_unitario')
                    ->label('Precio Unit.')
                    ->money('HNL')
                    ->alignEnd()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('HNL')
                    ->alignEnd()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('impuesto_monto')
                    ->label('Impuesto')
                    ->money('HNL')
                    ->alignEnd()
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        $porcentaje = $record->servicio->impuesto?->porcentaje ?? 0;
                        $monto = $record->impuesto_monto ?? 0;
                        return number_format($monto, 2) . ($porcentaje > 0 ? " ({$porcentaje}%)" : '');
                    }),
                    
                Tables\Columns\TextColumn::make('total_linea')
                    ->label('Total')
                    ->money('HNL')
                    ->alignEnd()
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\Select::make('servicio_id')
                            ->label('Servicio')
                            ->options(function () {
                                return Servicio::all()->mapWithKeys(function ($servicio) {
                                    return [$servicio->id => $servicio->nombre . ' - L.' . number_format($servicio->precio_unitario, 2)];
                                });
                            })
                            ->searchable()
                            ->required(),
                            
                        Forms\Components\TextInput::make('cantidad')
                            ->label('Cantidad')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $servicio = Servicio::find($data['servicio_id']);
                        if ($servicio) {
                            $cantidad = (int) ($data['cantidad'] ?? 1);
                            $subtotal = $servicio->precio_unitario * $cantidad;
                            
                            // Calcular impuesto
                            $impuesto_monto = 0;
                            if ($servicio->es_exonerado !== 'SI' && $servicio->impuesto) {
                                $impuesto_monto = ($subtotal * $servicio->impuesto->porcentaje) / 100;
                            }
                            
                            $total_linea = $subtotal + $impuesto_monto;
                            
                            $data['subtotal'] = $subtotal;
                            $data['impuesto_id'] = $servicio->impuesto?->id;
                            $data['impuesto_monto'] = $impuesto_monto;
                            $data['descuento_monto'] = 0;
                            $data['total_linea'] = $total_linea;
                        }
                        
                        return $data;
                    })
                    ->before(function (array $data, $record) {
                        // Verificar que no se está cambiando a un servicio que ya existe
                        $existeOtroDetalle = FacturaDetalle::where('consulta_id', $this->record->id)
                            ->where('servicio_id', $data['servicio_id'])
                            ->where('id', '!=', $record->id) // Excluir el registro actual
                            ->whereNull('factura_id')
                            ->exists();
                        
                        if ($existeOtroDetalle) {
                            Notification::make()
                                ->title('Servicio duplicado')
                                ->body('Este servicio ya está agregado a la consulta. No se puede duplicar.')
                                ->danger()
                                ->send();
                            
                            // Cancelar la edición
                            $this->halt();
                        }
                    })
                    ->using(function (array $data, $record): array {
                        try {
                            return $record->update($data);
                        } catch (\Illuminate\Database\QueryException $e) {
                            if (str_contains($e->getMessage(), 'unique_consulta_servicio_temp')) {
                                Notification::make()
                                    ->title('Error de duplicado')
                                    ->body('No se puede agregar un servicio duplicado a la consulta.')
                                    ->danger()
                                    ->send();
                                $this->halt();
                            }
                            throw $e;
                        }
                    })
                    ->after(function () {
                        Notification::make()
                            ->title('Servicio actualizado')
                            ->body('El servicio se ha actualizado correctamente.')
                            ->success()
                            ->send();
                    }),
                    
                Tables\Actions\DeleteAction::make()
                    ->after(function () {
                        Notification::make()
                            ->title('Servicio eliminado')
                            ->body('El subtotal se ha actualizado automáticamente')
                            ->warning()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(function () {
                            Notification::make()
                                ->title('Servicios eliminados')
                                ->body('Los servicios han sido eliminados correctamente')
                                ->warning()
                                ->send();
                        }),
                ]),
            ])
            ->emptyStateHeading('No hay servicios agregados')
            ->emptyStateDescription('Agrega servicios a esta consulta usando el botón "Agregar Servicios"')
            ->emptyStateIcon('heroicon-o-plus-circle')
            ->contentFooter(function () {
                $subtotal = $this->getServiciosSubtotal();
                $impuesto = $this->getServiciosImpuesto();
                $total = $this->getServiciosTotal();
                $cantidad = $this->getCantidadServicios();
                
                if ($cantidad === 0) {
                    return null;
                }
                
                return view('filament.resources.consultas.table-footer', [
                    'cantidad' => $cantidad,
                    'subtotal' => $subtotal,
                    'impuesto' => $impuesto,
                    'total' => $total
                ]);
            });
    }

    public function mount(int|string $record): void
    {
            $this->record = Consulta::with([
                'paciente.persona',
                'medico.persona',
            ])->findOrFail($record);
    
    }

    public function getServiciosTotal(): float
    {
        return \App\Models\FacturaDetalle::where('consulta_id', $this->record->id)
            ->whereNull('factura_id')          // ← solo los que aún no tienen factura
            ->sum('total_linea');              // campo DECIMAL(12,2)
    }

    public function getServiciosSubtotal(): float
    {
        return \App\Models\FacturaDetalle::where('consulta_id', $this->record->id)
            ->whereNull('factura_id')
            ->sum('subtotal');
    }

    public function getServiciosImpuesto(): float
    {
        return \App\Models\FacturaDetalle::where('consulta_id', $this->record->id)
            ->whereNull('factura_id')
            ->sum('impuesto_monto');
    }

    /* Si también muestras la cantidad de líneas */
    public function getCantidadServicios(): int
    {
        return \App\Models\FacturaDetalle::where('consulta_id', $this->record->id)
            ->whereNull('factura_id')
            ->count();
    }
}