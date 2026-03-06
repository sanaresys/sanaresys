<?php

namespace App\Filament\Resources\Facturas\FacturasResource\Pages;

use App\Filament\Resources\Facturas\FacturasResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Actions;
use App\Models\Factura;
use App\Models\FacturaDetalle;

class ViewFacturas extends ViewRecord
{
    protected static string $resource = FacturasResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Cargar las relaciones necesarias para el infolist
        $this->record->load([
            'paciente.persona',
            'medico.persona', 
            'centro',
            'detalles.servicio',
            'caiCorrelativo.caiAutorizacion',
            'pagos'
        ]);
        
        return $data;
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Información General de la Factura
                Infolists\Components\Section::make('Información General')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('numero_factura')
                                    ->label('Número de Factura')
                                    ->getStateUsing(function (Factura $record): string {
                                        if ($record->usa_cai && $record->caiCorrelativo) {
                                            return $record->caiCorrelativo->numero_factura;
                                        }
                                        return $record->generarNumeroSinCAI();
                                    })
                                    ->badge()
                                    ->color('primary')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                    
                                Infolists\Components\TextEntry::make('codigo_cai')
                                    ->label('Código CAI')
                                    ->getStateUsing(fn (Factura $record): ?string => $record->codigo_cai)
                                    ->placeholder('Sin CAI')
                                    ->badge()
                                    ->color('success'),
                                    
                                Infolists\Components\TextEntry::make('estado')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'PENDIENTE' => 'warning',
                                        'PAGADA' => 'success',
                                        'PARCIAL' => 'info',
                                        'ANULADA' => 'danger',
                                    }),
                            ]),
                            
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('fecha_emision')
                                    ->label('Fecha de Emisión')
                                    ->date('d/m/Y'),
                                    
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Fecha de Creación')
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                    ])
                    ->columns(1),

                // Información del Paciente y Médico
                Infolists\Components\Section::make('Datos del Paciente y Médico')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('paciente.persona.nombre_completo')
                                    ->label('Paciente')
                                    ->icon('heroicon-m-user'),
                                    
                                Infolists\Components\TextEntry::make('medico.persona.nombre_completo')
                                    ->label('Médico Tratante')
                                    ->icon('heroicon-m-user-circle'),
                            ]),
                            
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('paciente.persona.identidad')
                                    ->label('Identidad del Paciente'),
                                    
                                Infolists\Components\TextEntry::make('centro.nombre_centro')
                                    ->label('Centro Médico')
                                    ->icon('heroicon-m-building-office-2'),
                            ]),
                    ])
                    ->columns(1),

                // Servicios y Detalles
                Infolists\Components\Section::make('Servicios Facturados')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('detalles')
                            ->label('')
                            ->schema([
                                Infolists\Components\Grid::make(4)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('servicio.nombre')
                                            ->label('Servicio'),
                                            
                                        Infolists\Components\TextEntry::make('cantidad')
                                            ->label('Cantidad')
                                            ->numeric(),
                                            
                                        Infolists\Components\TextEntry::make('precio_unitario')
                                            ->label('Precio Unitario')
                                            ->money('HNL'),
                                            
                                        Infolists\Components\TextEntry::make('subtotal')
                                            ->label('Subtotal')
                                            ->money('HNL')
                                            ->weight('bold'),
                                    ]),
                                    
                                Infolists\Components\Grid::make(2)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('impuesto_unitario')
                                            ->label('Impuesto')
                                            ->money('HNL'),
                                            
                                        Infolists\Components\TextEntry::make('total')
                                            ->label('Total')
                                            ->money('HNL')
                                            ->weight('bold')
                                            ->color('success'),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                // Totales y Descuentos
                Infolists\Components\Section::make('Resumen Financiero')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->money('HNL')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                    
                                Infolists\Components\TextEntry::make('impuesto_total')
                                    ->label('Impuestos (ISV)')
                                    ->money('HNL')
                                    ->color('orange'),
                            ]),
                            
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('descuento.nombre')
                                    ->label('Descuento Aplicado')
                                    ->placeholder('Sin descuento'),
                                    
                                Infolists\Components\TextEntry::make('descuento_total')
                                    ->label('Monto Descuento')
                                    ->money('HNL')
                                    ->color('danger'),
                            ]),
                            
                        Infolists\Components\Split::make([
                            Infolists\Components\TextEntry::make('total')
                                ->label('TOTAL A PAGAR')
                                ->money('HNL')
                                ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                ->weight('bold')
                                ->color('success')
                                ->extraAttributes(['class' => 'text-center']),
                        ])
                    ])
                    ->columns(1),

                // Información de Pagos
                Infolists\Components\Section::make('Historial de Pagos')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('pagos')
                            ->label('')
                            ->schema([
                                Infolists\Components\Grid::make(4)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('tipoPago.nombre')
                                            ->label('Método de Pago')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'Efectivo' => 'success',
                                                'Tarjeta de Crédito' => 'primary',
                                                'Tarjeta de Débito' => 'info',
                                                'Transferencia' => 'warning',
                                                default => 'gray',
                                            }),
                                            
                                        Infolists\Components\TextEntry::make('monto_recibido')
                                            ->label('Monto Recibido')
                                            ->money('HNL')
                                            ->weight('bold'),
                                            
                                        Infolists\Components\TextEntry::make('fecha_pago')
                                            ->label('Fecha de Pago')
                                            ->dateTime('d/m/Y H:i'),
                                            
                                        Infolists\Components\TextEntry::make('created_at')
                                            ->label('Registrado')
                                            ->dateTime('d/m/Y H:i')
                                            ->color('gray'),
                                    ]),
                            ])
                            ->columnSpanFull(),
                            
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('total_pagado')
                                    ->label('Total Pagado')
                                    ->getStateUsing(fn (Factura $record): float => $record->montoPagado())
                                    ->money('HNL')
                                    ->color('success')
                                    ->weight('bold'),
                                    
                                Infolists\Components\TextEntry::make('saldo_pendiente')
                                    ->label('Saldo Pendiente')
                                    ->getStateUsing(fn (Factura $record): float => $record->saldoPendiente())
                                    ->money('HNL')
                                    ->color(fn (float $state): string => $state > 0 ? 'danger' : 'success')
                                    ->weight('bold'),
                                    
                                Infolists\Components\TextEntry::make('cambio_calculado')
                                    ->label('Cambio')
                                    ->getStateUsing(function (Factura $record): float {
                                        $pagado = $record->montoPagado();
                                        $total = $record->total;
                                        return max(0, $pagado - $total);
                                    })
                                    ->money('HNL')
                                    ->color('warning'),
                            ]),
                    ])
                    ->columns(1),

                // Observaciones
                Infolists\Components\Section::make('Información Adicional')
                    ->schema([
                        Infolists\Components\TextEntry::make('observaciones')
                            ->label('Observaciones')
                            ->placeholder('Sin observaciones')
                            ->columnSpanFull(),
                            
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_by')
                                    ->label('Creado por')
                                    ->getStateUsing(function (Factura $record): string {
                                        return $record->createdByUser->name ?? 'Sistema';
                                    }),
                                    
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Última modificación')
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                    ])
                    ->columns(1),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
            Actions\Action::make('download_pdf')
                ->label('Descargar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->url(fn (): string => route('factura.pdf', $this->record))
                ->openUrlInNewTab(),
                
            Actions\Action::make('preview_pdf')
                ->label('Vista Previa PDF')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn (): string => route('factura.pdf.preview', $this->record))
                ->openUrlInNewTab(),
        ];
    }
}
