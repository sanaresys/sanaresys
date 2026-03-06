<?php

namespace App\Filament\Resources\FacturaDetalles;

use App\Models\FacturaDetalle;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Infolists\Components\Concerns\HasState;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Support\Enums\FontWeight;

class FacturaDetallesTable
{
    public static function infolist(): array
    {
        return [
            Section::make('Información de la Factura')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextEntry::make('factura.numero_factura')
                                ->label('Número de Factura')
                                ->getStateUsing(function (FacturaDetalle $record): string {
                                    // Verificación segura: primero verificamos que existe la factura
                                    if (!$record->factura) {
                                        return 'Sin factura asignada';
                                    }
                                    
                                    // Si la factura existe, verificamos si usa CAI
                                    if ($record->factura->usa_cai && $record->factura->caiCorrelativo) {
                                        return $record->factura->caiCorrelativo->numero_factura ?? 'Sin número';
                                    }
                                    
                                    // Si no usa CAI o no tiene correlativo, generamos número sin CAI
                                    return $record->factura->generarNumeroSinCAI();
                                })
                                ->badge()
                                ->color('primary'),
                            
                            TextEntry::make('factura.cliente.nombre')
                                ->label('Cliente')
                                ->getStateUsing(function (FacturaDetalle $record): string {
                                    // Verificación en cadena para evitar errores null
                                    if (!$record->factura) {
                                        return 'Sin cliente';
                                    }
                                    
                                    if (!$record->factura->cliente) {
                                        return 'Cliente no asignado';
                                    }
                                    
                                    return $record->factura->cliente->nombre ?? 'Sin nombre';
                                })
                                ->icon('heroicon-o-user')
                                ->iconColor('success'),
                            
                            TextEntry::make('factura.fecha_emision')
                                ->label('Fecha de Emisión')
                                ->getStateUsing(function (FacturaDetalle $record): string {
                                    if (!$record->factura || !$record->factura->fecha_emision) {
                                        return 'Sin fecha';
                                    }
                                    
                                    return $record->factura->fecha_emision->format('d/m/Y');
                                })
                                ->icon('heroicon-o-calendar')
                                ->iconColor('info'),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('factura.estado')
                                ->label('Estado de Factura')
                                ->getStateUsing(function (FacturaDetalle $record): string {
                                    if (!$record->factura) {
                                        return 'Desconocido';
                                    }
                                    
                                    return match($record->factura->estado) {
                                        'pendiente' => 'Pendiente',
                                        'pagada' => 'Pagada',
                                        'anulada' => 'Anulada',
                                        'vencida' => 'Vencida',
                                        default => 'Sin estado'
                                    };
                                })
                                ->badge()
                                ->color(fn (FacturaDetalle $record): string => 
                                    match($record->factura?->estado) {
                                        'pendiente' => 'warning',
                                        'pagada' => 'success',
                                        'anulada' => 'danger',
                                        'vencida' => 'gray',
                                        default => 'secondary'
                                    }
                                ),
                            
                            TextEntry::make('factura.tipo_documento')
                                ->label('Tipo de Documento')
                                ->getStateUsing(function (FacturaDetalle $record): string {
                                    if (!$record->factura) {
                                        return 'Sin tipo';
                                    }
                                    
                                    return match($record->factura->tipo_documento) {
                                        'factura' => 'Factura',
                                        'credito_fiscal' => 'Crédito Fiscal',
                                        'nota_credito' => 'Nota de Crédito',
                                        'nota_debito' => 'Nota de Débito',
                                        default => 'Otro'
                                    };
                                })
                                ->badge()
                                ->color('info'),
                        ]),
                ]),
            
            Section::make('Detalles del Producto/Servicio')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextEntry::make('producto.nombre')
                                ->label('Producto/Servicio')
                                ->getStateUsing(function (FacturaDetalle $record): string {
                                    if (!$record->producto) {
                                        return $record->descripcion ?? 'Sin descripción';
                                    }
                                    
                                    return $record->producto->nombre ?? $record->descripcion ?? 'Sin nombre';
                                })
                                ->weight(FontWeight::Bold),
                            
                            TextEntry::make('cantidad')
                                ->label('Cantidad')
                                ->numeric()
                                ->suffix(' unidades'),
                            
                            TextEntry::make('precio_unitario')
                                ->label('Precio Unitario')
                                ->money('HNL'),
                        ]),
                    
                    Grid::make(3)
                        ->schema([
                            TextEntry::make('descuento')
                                ->label('Descuento')
                                ->getStateUsing(function (FacturaDetalle $record): string {
                                    $descuento = $record->descuento ?? 0;
                                    return number_format($descuento, 2) . '%';
                                }),
                            
                            TextEntry::make('impuesto')
                                ->label('Impuesto (ISV)')
                                ->getStateUsing(function (FacturaDetalle $record): string {
                                    $impuesto = $record->impuesto ?? 0;
                                    return number_format($impuesto, 2) . '%';
                                }),
                            
                            TextEntry::make('subtotal')
                                ->label('Subtotal')
                                ->getStateUsing(function (FacturaDetalle $record): string {
                                    $subtotal = $record->cantidad * $record->precio_unitario;
                                    $descuento = $subtotal * ($record->descuento / 100);
                                    $subtotalConDescuento = $subtotal - $descuento;
                                    $impuesto = $subtotalConDescuento * ($record->impuesto / 100);
                                    $total = $subtotalConDescuento + $impuesto;
                                    
                                    return 'L. ' . number_format($total, 2);
                                })
                                ->weight(FontWeight::Bold)
                                ->size(TextEntrySize::Large)
                                ->color('success'),
                        ]),
                ]),
            
            Section::make('Información CAI')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('factura.usa_cai')
                                ->label('Usa CAI')
                                ->getStateUsing(function (FacturaDetalle $record): string {
                                    if (!$record->factura) {
                                        return 'No';
                                    }
                                    
                                    return $record->factura->usa_cai ? 'Sí' : 'No';
                                })
                                ->badge()
                                ->color(fn (FacturaDetalle $record): string => 
                                    $record->factura?->usa_cai ? 'success' : 'gray'
                                ),
                            
                            TextEntry::make('factura.caiCorrelativo.cai')
                                ->label('Número CAI')
                                ->getStateUsing(function (FacturaDetalle $record): string {
                                    // Verificación segura en cadena
                                    if (!$record->factura) {
                                        return 'Sin CAI';
                                    }
                                    
                                    if (!$record->factura->usa_cai) {
                                        return 'No aplica';
                                    }
                                    
                                    if (!$record->factura->caiCorrelativo) {
                                        return 'CAI no asignado';
                                    }
                                    
                                    return $record->factura->caiCorrelativo->cai ?? 'Sin número CAI';
                                })
                                ->visible(fn (FacturaDetalle $record): bool => 
                                    $record->factura?->usa_cai ?? false
                                ),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('factura.caiCorrelativo.fecha_limite_emision')
                                ->label('Fecha Límite de Emisión')
                                ->getStateUsing(function (FacturaDetalle $record): string {
                                    if (!$record->factura || !$record->factura->caiCorrelativo) {
                                        return 'No disponible';
                                    }
                                    
                                    $fecha = $record->factura->caiCorrelativo->fecha_limite_emision;
                                    return $fecha ? $fecha->format('d/m/Y') : 'Sin fecha';
                                })
                                ->icon('heroicon-o-exclamation-triangle')
                                ->iconColor(function (FacturaDetalle $record): string {
                                    if (!$record->factura || !$record->factura->caiCorrelativo) {
                                        return 'gray';
                                    }
                                    
                                    $fecha = $record->factura->caiCorrelativo->fecha_limite_emision;
                                    if (!$fecha) {
                                        return 'gray';
                                    }
                                    
                                    return $fecha->isPast() ? 'danger' : 'success';
                                })
                                ->visible(fn (FacturaDetalle $record): bool => 
                                    $record->factura?->usa_cai ?? false
                                ),
                            
                            TextEntry::make('factura.caiCorrelativo.rango_autorizado')
                                ->label('Rango Autorizado')
                                ->getStateUsing(function (FacturaDetalle $record): string {
                                    if (!$record->factura || !$record->factura->caiCorrelativo) {
                                        return 'No disponible';
                                    }
                                    
                                    $cai = $record->factura->caiCorrelativo;
                                    $desde = $cai->rango_desde ?? 'N/A';
                                    $hasta = $cai->rango_hasta ?? 'N/A';
                                    
                                    return "Desde: {$desde} - Hasta: {$hasta}";
                                })
                                ->visible(fn (FacturaDetalle $record): bool => 
                                    $record->factura?->usa_cai ?? false
                                ),
                        ]),
                ])
                ->visible(fn (FacturaDetalle $record): bool => 
                    $record->factura?->usa_cai ?? false
                ),
            
            Section::make('Información Adicional')
                ->schema([
                    TextEntry::make('notas')
                        ->label('Notas')
                        ->getStateUsing(function (FacturaDetalle $record): string {
                            return $record->notas ?? 'Sin notas adicionales';
                        })
                        ->columnSpanFull(),
                    
                    Grid::make(3)
                        ->schema([
                            TextEntry::make('created_at')
                                ->label('Fecha de Creación')
                                ->dateTime('d/m/Y H:i')
                                ->icon('heroicon-o-clock'),
                            
                            TextEntry::make('updated_at')
                                ->label('Última Actualización')
                                ->dateTime('d/m/Y H:i')
                                ->icon('heroicon-o-arrow-path'),
                            
                            TextEntry::make('usuario.name')
                                ->label('Creado por')
                                ->getStateUsing(function (FacturaDetalle $record): string {
                                    return $record->usuario?->name ?? 'Sistema';
                                })
                                ->icon('heroicon-o-user-circle'),
                        ]),
                ]),
        ];
    }
    
    /**
     * Método auxiliar para obtener el query con relaciones cargadas
     */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return FacturaDetalle::query()
            ->with([
                'factura',
                'factura.cliente',
                'factura.caiCorrelativo',
                'producto',
                'usuario'
            ]);
    }
}