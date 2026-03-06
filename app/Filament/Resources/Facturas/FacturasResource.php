<?php

namespace App\Filament\Resources\Facturas;

use App\Filament\Resources\Facturas\FacturasResource\Pages;
use App\Filament\Resources\Facturas\FacturasResource\RelationManagers;
use App\Models\Factura;
use App\Models\FacturaDetalle;
use App\Models\TipoPago;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class FacturasResource extends Resource
{
    protected static ?string $model = Factura::class;
    protected static ?string $slug = 'facturas';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static function esFacturaSoloLectura(?Factura $record): bool
    {
        return $record && $record->estado === 'PAGADA';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('consulta_id'),
                Forms\Components\Hidden::make('paciente_id'),
                Forms\Components\Hidden::make('medico_id'),
                Forms\Components\Hidden::make('cita_id'),
                Forms\Components\Hidden::make('subtotal'),
                Forms\Components\Hidden::make('impuesto_total'),
                Forms\Components\Hidden::make('descuento_total'),
                Forms\Components\Hidden::make('total'),
                Forms\Components\Hidden::make('descuento_id'),
                Forms\Components\Hidden::make('fecha_emision')
                    ->default(now()),
                Forms\Components\Hidden::make('estado')
                    ->default('PENDIENTE'),
                Forms\Components\Hidden::make('created_by')
                    ->default(Auth::id()),
                Forms\Components\Hidden::make('centro_id')
                    ->default(Auth::user()->centro_id ?? 1),
                Forms\Components\Hidden::make('usa_cai')
                    ->default(false),

                Forms\Components\Section::make('Pago')
                    ->schema([
                        // RESUMEN DE TOTALES ARRIBA - CAMPOS CALCULADOS REACTIVOS
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('total_a_pagar_display')
                                    ->label('Total a Pagar')
                                    ->prefix('L.')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->live()
                                    ->afterStateHydrated(function (TextInput $component, $state, callable $get) {
                                        $totalAPagar = (float) ($get('total') ?? 0);
                                        $component->state(number_format($totalAPagar, 2));
                                    })
                                    ->extraAttributes(['class' => 'font-bold text-lg text-blue-600']),
                                    
                                Forms\Components\TextInput::make('total_pagado')
                                    ->label('Total Pagado')
                                    ->prefix('L.')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->live()
                                    ->afterStateHydrated(function (TextInput $component, $state, callable $get) {
                                        $pagos = $get('pagos') ?? [];
                                        $totalPagado = 0;
                                        
                                        foreach ($pagos as $pago) {
                                            if (isset($pago['monto_recibido']) && !empty($pago['monto_recibido'])) {
                                                $totalPagado += (float) $pago['monto_recibido'];
                                            }
                                        }
                                        
                                        $component->state(number_format($totalPagado, 2));
                                    })
                                    ->extraAttributes(['class' => 'font-bold text-green-600']),
                                    
                                Forms\Components\TextInput::make('cambio')
                                    ->label('Cambio a Devolver')
                                    ->prefix('L.')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->live()
                                    ->afterStateHydrated(function (TextInput $component, $state, callable $get) {
                                        $totalAPagar = (float) ($get('total') ?? 0);
                                        $pagos = $get('pagos') ?? [];
                                        $totalPagado = 0;
                                        
                                        foreach ($pagos as $pago) {
                                            if (isset($pago['monto_recibido']) && !empty($pago['monto_recibido'])) {
                                                $totalPagado += (float) $pago['monto_recibido'];
                                            }
                                        }
                                        
                                        $cambio = max(0, $totalPagado - $totalAPagar);
                                        $component->state(number_format($cambio, 2));
                                    })
                                    ->extraAttributes(['class' => 'font-bold text-orange-600']),
                                    
                                Forms\Components\TextInput::make('saldo_pendiente')
                                    ->label('Saldo Pendiente')
                                    ->prefix('L.')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->live()
                                    ->afterStateHydrated(function (TextInput $component, $state, callable $get) {
                                        $totalAPagar = (float) ($get('total') ?? 0);
                                        $pagos = $get('pagos') ?? [];
                                        $totalPagado = 0;
                                        
                                        foreach ($pagos as $pago) {
                                            if (isset($pago['monto_recibido']) && !empty($pago['monto_recibido'])) {
                                                $totalPagado += (float) $pago['monto_recibido'];
                                            }
                                        }
                                        
                                        $saldoPendiente = max(0, $totalAPagar - $totalPagado);
                                        $component->state(number_format($saldoPendiente, 2));
                                    })
                                    ->extraAttributes(['class' => 'font-bold text-red-600']),
                            ])
                            ->columnSpanFull(),

                        // BOTÓN DE PAGO RÁPIDO
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('pago_completo')
                                ->label('Pagar Total Completo')
                                ->icon('heroicon-m-banknotes')
                                ->color('success')
                                ->action(function (callable $set, callable $get) {
                                    $totalAPagar = (float) ($get('total') ?? 0);
                                    
                                    // Configurar pago completo en efectivo
                                    $set('pagos', [
                                        [
                                            'tipo_pago_id' => 1, // Efectivo
                                            'monto_recibido' => $totalAPagar,
                                        ]
                                    ]);
                                    
                                    $set('estado', 'PAGADA');
                                })
                                ->visible(function (callable $get, ?Factura $record) {
                                    $totalAPagar = (float) ($get('total') ?? 0);
                                    $pagos = $get('pagos') ?? [];
                                    $totalPagado = 0;
                                    
                                    foreach ($pagos as $pago) {
                                        if (isset($pago['monto_recibido']) && !empty($pago['monto_recibido'])) {
                                            $totalPagado += (float) $pago['monto_recibido'];
                                        }
                                    }
                                    
                                    return $totalPagado < $totalAPagar;
                                })
                        ])->columnSpanFull(),

                        // ✅ CAMBIADO: Repeater SIN relationship para evitar conflictos
                        Repeater::make('pagos')
                            ->label('Métodos de Pago')
                            ->dehydrated()
                            ->defaultItems(3)
                            ->addActionLabel('Agregar Método de Pago')
                            
                            // ✅ VALORES POR DEFECTO SIMPLIFICADOS
                            ->default([
                                ['tipo_pago_id' => 1, 'monto_recibido' => ''], // Efectivo
                                ['tipo_pago_id' => 2, 'monto_recibido' => ''], // Tarjeta
                                ['tipo_pago_id' => 3, 'monto_recibido' => ''], // POS
                            ])
                            
                            ->collapsed(false)
                            
                            ->schema([
                                Select::make('tipo_pago_id')
                                    ->label('Tipo de Pago')
                                    ->options(TipoPago::pluck('nombre', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->placeholder('Seleccionar método'),

                                TextInput::make('monto_recibido')
                                    ->label('Monto Recibido')
                                    ->numeric()
                                    ->prefix('L.')
                                    ->placeholder('0.00')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Recalcular totales cuando cambia cualquier monto
                                        $pagos = $get('../../pagos') ?? [];
                                        $totalPagado = 0;
                                        
                                        foreach ($pagos as $pago) {
                                            if (isset($pago['monto_recibido']) && !empty($pago['monto_recibido'])) {
                                                $totalPagado += (float) $pago['monto_recibido'];
                                            }
                                        }
                                        
                                        $totalAPagar = (float) ($get('../../total') ?? 0);
                                        $saldoPendiente = max(0, $totalAPagar - $totalPagado);
                                        $cambio = max(0, $totalPagado - $totalAPagar);
                                        
                                        // Actualizar campos reactivos
                                        $set('../../total_pagado', number_format($totalPagado, 2));
                                        $set('../../saldo_pendiente', number_format($saldoPendiente, 2));
                                        $set('../../cambio', number_format($cambio, 2));
                                        
                                        // Actualizar estado de la factura
                                        if ($saldoPendiente == 0 && $totalAPagar > 0) {
                                            $set('../../estado', 'PAGADA');
                                        } elseif ($saldoPendiente > 0 && $totalPagado > 0) {
                                            $set('../../estado', 'PARCIAL');
                                        } else {
                                            $set('../../estado', 'PENDIENTE');
                                        }
                                    })
                                    ->columnSpan(1),
                                    
                                // Campos ocultos - VERSIÓN MEJORADA Y MÁS AGRESIVA
                                Forms\Components\Hidden::make('paciente_id')
                                    ->default(function (callable $get) {
                                        // Estrategia múltiple para obtener paciente_id
                                        
                                        // 1. Del formulario principal
                                        $pacienteId = $get('../../paciente_id');
                                        if ($pacienteId && $pacienteId !== '?') {
                                            return $pacienteId;
                                        }
                                        
                                        // 2. De la consulta desde URL
                                        $consultaId = request()->get('consulta_id') ?? $get('../../consulta_id');
                                        if ($consultaId) {
                                            try {
                                                $consulta = \App\Models\Consulta::find($consultaId);
                                                if ($consulta && $consulta->paciente_id) {
                                                    return $consulta->paciente_id;
                                                }
                                            } catch (\Exception $e) {
                                                // Si hay error, continuar con otros métodos
                                            }
                                        }
                                        
                                        // 3. Si estamos editando, del record actual
                                        if (request()->route('record')) {
                                            try {
                                                $factura = \App\Models\Factura::find(request()->route('record'));
                                                if ($factura && $factura->paciente_id) {
                                                    return $factura->paciente_id;
                                                }
                                            } catch (\Exception $e) {
                                                // Si hay error, continuar
                                            }
                                        }
                                        
                                        return null;
                                    })
                                    ->live()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Forzar la propagación del paciente_id si se actualiza
                                        if ($state && $state !== '?') {
                                            Log::info('Paciente ID actualizado en campo hidden', ['paciente_id' => $state]);
                                        }
                                    }),
                                    
                                Forms\Components\Hidden::make('centro_id')
                                    ->default(Auth::user()->centro_id),
                                    
                                
                                Forms\Components\Hidden::make('fecha_pago')
                                    ->default(now()),
                                    
                                Forms\Components\Hidden::make('created_by')
                                    ->default(Auth::id()),
                                    
                                Forms\Components\Hidden::make('monto_devolucion')
                                    ->default(0),
                            ])
                            ->columns(2)
                            ->addActionLabel('Agregar método de pago')
                            ->deletable()
                            ->reorderable(false)
                            ->maxItems(5)
                            ->minItems(0)
                            ->live()
                            ->dehydrated()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Recalcular totales cuando cambia el estado del repeater (agregar/eliminar)
                                $pagos = $state ?? [];
                                $totalPagado = 0;
                                
                                foreach ($pagos as $pago) {
                                    if (isset($pago['monto_recibido']) && !empty($pago['monto_recibido'])) {
                                        $totalPagado += (float) $pago['monto_recibido'];
                                    }
                                }
                                
                                $totalAPagar = (float) ($get('total') ?? 0);
                                $saldoPendiente = max(0, $totalAPagar - $totalPagado);
                                $cambio = max(0, $totalPagado - $totalAPagar);
                                
                                // Actualizar campos reactivos
                                $set('total_pagado', number_format($totalPagado, 2));
                                $set('saldo_pendiente', number_format($saldoPendiente, 2));
                                $set('cambio', number_format($cambio, 2));
                                
                                // Actualizar estado de la factura
                                if ($saldoPendiente == 0 && $totalAPagar > 0) {
                                    $set('estado', 'PAGADA');
                                } elseif ($saldoPendiente > 0 && $totalPagado > 0) {
                                    $set('estado', 'PARCIAL');
                                } else {
                                    $set('estado', 'PENDIENTE');
                                }
                            })
                            ->disabled(fn (?Factura $record) => self::esFacturaSoloLectura($record)),
                    ])
                    ->columns(1),
            ])
            ->live();
    }

    // ... resto del código table() igual ...

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_factura')
                    ->label('Número')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (Factura $record): string => 
                        $record->usa_cai ? 'success' : 'warning'
                    )
                    ->formatStateUsing(function (Factura $record): string {
                        if ($record->usa_cai && $record->caiCorrelativo) {
                            return $record->caiCorrelativo->numero_factura;
                        }
                        return $record->generarNumeroSinCAI();
                    })
                    ->description(fn (Factura $record): ?string => 
                        $record->usa_cai ? 'Factura Fiscal' : 'Recibo/Proforma'
                    ),

                TextColumn::make('cai_codigo')
                    ->label('CAI')
                    ->getStateUsing(fn (Factura $record): ?string => $record->codigo_cai)
                    ->placeholder('Sin CAI')
                    ->limit(15)
                    ->tooltip(fn (Factura $record): ?string => $record->codigo_cai)
                    ->badge()
                    ->color('primary')
                    ->toggleable(),
                    
                TextColumn::make('paciente.persona.nombre_completo')
                    ->label('Paciente')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('medico.persona.nombre_completo')
                    ->label('Médico')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('fecha_emision')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('HNL')
                    ->alignEnd()
                    ->sortable(),
                    
                TextColumn::make('impuesto_total')
                    ->label('Impuestos')
                    ->money('HNL')
                    ->alignEnd()
                    ->color('orange')
                    ->toggleable(),

                TextColumn::make('descuento_total')
                    ->label('Descuento')
                    ->money('HNL')
                    ->alignEnd()
                    ->color('danger')
                    ->toggleable(),
                    
                TextColumn::make('total')
                    ->label('Total')
                    ->money('HNL')
                    ->alignEnd()
                    ->sortable()
                    ->weight('bold')
                    ->color('success')
                    ->description(function (Factura $record): string {
                        $pagado = $record->montoPagado();
                        $saldo = $record->saldoPendiente();
                        
                        if ($saldo <= 0) {
                            return 'Totalmente pagada';
                        } elseif ($pagado > 0) {
                            return 'Saldo: L. ' . number_format($saldo, 2);
                        }
                        
                        return 'Sin pagos';
                    }),
                    
                TextColumn::make('metodos_pago')
                    ->label('Métodos de Pago')
                    ->getStateUsing(function (Factura $record): string {
                        $pagos = $record->pagos()->with('tipoPago')->get();
                        
                        if ($pagos->isEmpty()) {
                            return 'Sin pagos';
                        }
                        
                        $resumen = [];
                        foreach ($pagos as $pago) {
                            $tipo = $pago->tipoPago->nombre ?? 'N/A';
                            $monto = $pago->monto_recibido;
                            
                            if (!isset($resumen[$tipo])) {
                                $resumen[$tipo] = 0;
                            }
                            $resumen[$tipo] += $monto;
                        }
                        
                        $lineas = [];
                        foreach ($resumen as $tipo => $monto) {
                            $lineas[] = "{$tipo}: L. " . number_format($monto, 2);
                        }
                        
                        return implode("\n", $lineas);
                    })
                    ->html()
                    ->wrap()
                    ->extraAttributes(['style' => 'white-space: pre-line; font-size: 0.8em;'])
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PENDIENTE' => 'warning',
                        'PAGADA' => 'success',
                        'PARCIAL' => 'info',
                        'ANULADA' => 'danger',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'PENDIENTE' => 'heroicon-m-clock',
                        'PAGADA' => 'heroicon-m-check-circle',
                        'PARCIAL' => 'heroicon-m-currency-dollar',
                        'ANULADA' => 'heroicon-m-x-circle',
                    }),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'PENDIENTE' => 'Pendiente',
                        'PAGADA' => 'Pagada',
                        'PARCIAL' => 'Parcial',
                        'ANULADA' => 'Anulada',
                    ]),
                    
                Tables\Filters\SelectFilter::make('descuento_id')
                    ->label('Con descuento')
                    ->relationship('descuento', 'nombre')
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->url(fn (Factura $record): string => route('factura.pdf', $record))
                    ->openUrlInNewTab(),
                    
                Tables\Actions\Action::make('preview_pdf')
                    ->label('Vista Previa')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (Factura $record): string => route('factura.pdf.preview', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('download_pdfs')
                        ->label('Descargar PDFs')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $facturaIds = $records->pluck('id')->toArray();
                            
                            // Redirigir a la URL con los IDs como parámetros GET
                            $url = route('facturas.pdf.lote') . '?' . http_build_query(['factura_ids' => $facturaIds]);
                            
                            return redirect($url);
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFacturas::route('/'),
            'create' => Pages\CreateFacturas::route('/create'),
            'edit' => Pages\EditFacturas::route('/{record}/edit'),
            'view' => Pages\ViewFacturas::route('/{record}'),
        ];
    }
}