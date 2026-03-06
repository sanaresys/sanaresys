<?php

namespace App\Filament\Resources\FacturaDetalles\FacturaDetallesResource\Pages;

use App\Filament\Resources\FacturaDetalles\FacturaDetallesResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use App\Models\FacturaDetalle;

class ViewFacturaDetalles extends ViewRecord
{
    protected static string $resource = FacturaDetallesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('volver_a_factura')
                ->label('Ver Factura Completa')
                ->icon('heroicon-m-arrow-left')
                ->url(fn (FacturaDetalle $record): string => 
                    route('filament.admin.resources.facturas.view', ['record' => $record->factura_id])
                )
                ->color('primary'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Información de la Factura
                Infolists\Components\Section::make('Información de la Factura')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('factura.numero_factura')
                                    ->label('Número de Factura')
                                    ->getStateUsing(function (FacturaDetalle $record): string {
                                        if ($record->factura->usa_cai && $record->factura->caiCorrelativo) {
                                            return $record->factura->caiCorrelativo->numero_factura;
                                        }
                                        return $record->factura->generarNumeroSinCAI();
                                    })
                                    ->badge()
                                    ->color('primary')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                    
                                Infolists\Components\TextEntry::make('factura.estado')
                                    ->label('Estado de la Factura')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'PENDIENTE' => 'warning',
                                        'PAGADA' => 'success',
                                        'PARCIAL' => 'info',
                                        'ANULADA' => 'danger',
                                    }),
                                    
                                Infolists\Components\TextEntry::make('factura.fecha_emision')
                                    ->label('Fecha de Emisión')
                                    ->date('d/m/Y'),
                            ]),
                            
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('factura.codigo_cai')
                                    ->label('Código CAI')
                                    ->getStateUsing(fn (FacturaDetalle $record): ?string => $record->factura->codigo_cai)
                                    ->placeholder('Sin CAI (Proforma)')
                                    ->badge()
                                    ->color('success'),
                                    
                                Infolists\Components\TextEntry::make('factura.centro.nombre_centro')
                                    ->label('Centro Médico')
                                    ->icon('heroicon-m-building-office-2'),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible(),

                // Información del Paciente y Médico
                Infolists\Components\Section::make('Datos del Paciente y Médico')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('factura.paciente.persona.nombre_completo')
                                    ->label('Paciente')
                                    ->icon('heroicon-m-user')
                                    ->weight('bold'),
                                    
                                Infolists\Components\TextEntry::make('factura.medico.persona.nombre_completo')
                                    ->label('Médico Tratante')
                                    ->icon('heroicon-m-user-circle')
                                    ->weight('bold'),
                            ]),
                            
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('factura.paciente.persona.identidad')
                                    ->label('Identidad del Paciente'),
                                    
                                Infolists\Components\TextEntry::make('factura.paciente.persona.telefono')
                                    ->label('Teléfono del Paciente')
                                    ->icon('heroicon-m-phone'),
                                    
                                Infolists\Components\TextEntry::make('factura.paciente.persona.email')
                                    ->label('Email del Paciente')
                                    ->icon('heroicon-m-envelope'),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible(),

                // Detalle del Servicio/Línea
                Infolists\Components\Section::make('Detalle del Servicio')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('servicio.nombre')
                                    ->label('Nombre del Servicio')
                                    ->weight('bold')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                    
                                Infolists\Components\TextEntry::make('servicio.codigo')
                                    ->label('Código del Servicio')
                                    ->badge()
                                    ->color('gray'),
                            ]),
                            
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('cantidad')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->weight('bold'),
                                    
                                Infolists\Components\TextEntry::make('precio_unitario')
                                    ->label('Precio Unitario')
                                    ->money('HNL')
                                    ->weight('bold'),
                                    
                                Infolists\Components\TextEntry::make('descuento_monto')
                                    ->label('Descuento')
                                    ->money('HNL')
                                    ->color('danger'),
                                    
                                Infolists\Components\TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->money('HNL')
                                    ->color('info')
                                    ->weight('bold'),
                            ]),
                            
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('impuesto_unitario')
                                    ->label('Impuesto Unitario')
                                    ->money('HNL')
                                    ->color('orange'),
                                    
                                Infolists\Components\TextEntry::make('impuesto_monto')
                                    ->label('Total Impuestos')
                                    ->money('HNL')
                                    ->color('orange')
                                    ->weight('bold'),
                                    
                                Infolists\Components\TextEntry::make('total_linea')
                                    ->label('TOTAL DE LÍNEA')
                                    ->money('HNL')
                                    ->color('success')
                                    ->weight('bold')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                            ]),
                    ])
                    ->columns(1),

                // Información del Servicio Adicional
                Infolists\Components\Section::make('Información Adicional del Servicio')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('servicio.descripcion')
                                    ->label('Descripción del Servicio')
                                    ->placeholder('Sin descripción')
                                    ->columnSpanFull(),
                                    
                                Infolists\Components\TextEntry::make('servicio.categoria')
                                    ->label('Categoría')
                                    ->placeholder('Sin categoría'),
                                    
                                Infolists\Components\TextEntry::make('servicio.impuesto.nombre')
                                    ->label('Tipo de Impuesto')
                                    ->placeholder('Sin impuesto'),
                            ]),
                            
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('servicio.impuesto.porcentaje')
                                    ->label('% Impuesto')
                                    ->suffix('%')
                                    ->placeholder('0%'),
                                    
                                Infolists\Components\TextEntry::make('servicio.estado')
                                    ->label('Estado del Servicio')
                                    ->badge()
                                    ->color(fn (?string $state): string => match ($state) {
                                        'ACTIVO' => 'success',
                                        'INACTIVO' => 'danger',
                                        default => 'gray',
                                    }),
                                    
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Fecha de Registro')
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible(),

                // Totales de la Factura Completa
                Infolists\Components\Section::make('Resumen de la Factura Completa')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('factura.subtotal')
                                    ->label('Subtotal de la Factura')
                                    ->money('HNL')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                    
                                Infolists\Components\TextEntry::make('factura.impuesto_total')
                                    ->label('Total Impuestos')
                                    ->money('HNL')
                                    ->color('orange'),
                            ]),
                            
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('factura.descuento.nombre')
                                    ->label('Descuento Aplicado')
                                    ->placeholder('Sin descuento'),
                                    
                                Infolists\Components\TextEntry::make('factura.descuento_total')
                                    ->label('Monto Descuento')
                                    ->money('HNL')
                                    ->color('danger'),
                            ]),
                            
                        Infolists\Components\TextEntry::make('factura.total')
                            ->label('TOTAL DE LA FACTURA')
                            ->money('HNL')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold')
                            ->color('success')
                            ->extraAttributes(['class' => 'text-center'])
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->collapsible(),

                // Estado de Pagos
                Infolists\Components\Section::make('Estado de Pagos')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('factura.total_pagado')
                                    ->label('Total Pagado')
                                    ->getStateUsing(fn (FacturaDetalle $record): float => $record->factura->montoPagado())
                                    ->money('HNL')
                                    ->color('success')
                                    ->weight('bold'),
                                    
                                Infolists\Components\TextEntry::make('factura.saldo_pendiente')
                                    ->label('Saldo Pendiente')
                                    ->getStateUsing(fn (FacturaDetalle $record): float => $record->factura->saldoPendiente())
                                    ->money('HNL')
                                    ->color(fn (float $state): string => $state > 0 ? 'danger' : 'success')
                                    ->weight('bold'),
                                    
                                Infolists\Components\TextEntry::make('factura.metodos_pago')
                                    ->label('Métodos de Pago')
                                    ->getStateUsing(function (FacturaDetalle $record): string {
                                        $pagos = $record->factura->pagos()->with('tipoPago')->get();
                                        
                                        if ($pagos->isEmpty()) {
                                            return 'Sin pagos registrados';
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
                                    ->html(),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible(),

                // Información de Auditoría
                Infolists\Components\Section::make('Información de Auditoría')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_by')
                                    ->label('Creado por')
                                    ->getStateUsing(function (FacturaDetalle $record): string {
                                        return $record->createdByUser->name ?? 'Sistema';
                                    }),
                                    
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Última modificación')
                                    ->dateTime('d/m/Y H:i'),
                                    
                                Infolists\Components\TextEntry::make('factura.observaciones')
                                    ->label('Observaciones de la Factura')
                                    ->placeholder('Sin observaciones'),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
