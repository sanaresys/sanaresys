<?php

namespace App\Filament\Resources\PagosFacturas;

use App\Filament\Resources\PagosFacturas\PagosFacturasResource\Pages;
use App\Filament\Resources\PagosFacturas\PagosFacturasResource\RelationManagers;
use App\Models\PagosFactura;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PagosFacturasResource extends Resource
{
    protected static ?string $model = PagosFactura::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Gestión de Facturación';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('factura_id')
                    ->label('Factura')
                    ->options(function () {
                        return \App\Models\Factura::query()
                            ->with(['caiCorrelativo'])
                            ->latest()
                            ->get()
                            ->mapWithKeys(function ($factura) {
                                $numero = $factura->usa_cai && $factura->caiCorrelativo 
                                    ? $factura->caiCorrelativo->numero_factura
                                    : $factura->generarNumeroSinCAI();
                                return [$factura->id => $numero];
                            });
                    })
                    ->searchable()
                    ->required(),

                Select::make('tipo_pago_id')
                    ->label('Tipo de Pago')
                    ->relationship('tipoPago', 'nombre')
                    ->required(),

                Forms\Components\TextInput::make('monto_recibido')
                    ->label('Monto Recibido')
                    ->numeric()
                    ->required()
                    ->prefix('L.'),

                Forms\Components\DatePicker::make('fecha_pago')
                    ->label('Fecha de Pago')
                    ->default(now())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('factura_numero')
                    ->label('Factura')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(function (PagosFactura $record): string {
                        if ($record->factura->usa_cai && $record->factura->caiCorrelativo) {
                            return $record->factura->caiCorrelativo->numero_factura;
                        }
                        return $record->factura->generarNumeroSinCAI();
                    })
                    ->badge()
                    ->color('primary'),
                    
                TextColumn::make('tipoPago.nombre')
                    ->label('Tipo de Pago')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Efectivo' => 'success',
                        'Tarjeta de Crédito' => 'primary',
                        'Tarjeta de Débito' => 'info',
                        'Transferencia' => 'warning',
                        default => 'gray',
                    }),
                    
                TextColumn::make('monto_recibido')
                    ->label('Monto Recibido')
                    ->money('HNL')
                    ->alignEnd()
                    ->weight('bold'),
                    
                TextColumn::make('fecha_pago')
                    ->label('Fecha de Pago')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                    
                TextColumn::make('factura.total')
                    ->label('Total Factura')
                    ->money('HNL')
                    ->alignEnd()
                    ->color('gray'),
                    
                TextColumn::make('pagos_de_factura')
                    ->label('Resumen de Pagos')
                    ->getStateUsing(function (PagosFactura $record): string {
                        // Obtener todos los pagos de esta factura
                        $todosPagos = PagosFactura::where('factura_id', $record->factura_id)
                            ->with('tipoPago')
                            ->get();
                            
                        $resumen = [];
                        $totalPagado = 0;
                        
                        foreach ($todosPagos as $pago) {
                            $tipo = $pago->tipoPago->nombre ?? 'N/A';
                            $monto = $pago->monto_recibido;
                            $totalPagado += $monto;
                            
                            if (!isset($resumen[$tipo])) {
                                $resumen[$tipo] = 0;
                            }
                            $resumen[$tipo] += $monto;
                        }
                        
                        $lineas = [];
                        foreach ($resumen as $tipo => $monto) {
                            $lineas[] = "{$tipo}: L. " . number_format($monto, 2);
                        }
                        
                        $resultado = implode(' | ', $lineas);
                        $resultado .= "\nTotal: L. " . number_format($totalPagado, 2);
                        
                        return $resultado;
                    })
                    ->html()
                    ->wrap()
                    ->extraAttributes(['style' => 'white-space: pre-line; font-size: 0.85em;'])
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('fecha_pago', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('tipo_pago_id')
                    ->label('Tipo de Pago')
                    ->relationship('tipoPago', 'nombre'),
                    
                Tables\Filters\Filter::make('fecha_pago')
                    ->form([
                        Forms\Components\DatePicker::make('desde')->label('Desde'),
                        Forms\Components\DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['desde'], fn (Builder $query, $date): Builder => $query->whereDate('fecha_pago', '>=', $date))
                            ->when($data['hasta'], fn (Builder $query, $date): Builder => $query->whereDate('fecha_pago', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->groups([
                Tables\Grouping\Group::make('factura.numero_factura')
                    ->label('Por Factura')
                    ->collapsible(),
                Tables\Grouping\Group::make('tipoPago.nombre')
                    ->label('Por Tipo de Pago')
                    ->collapsible(),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPagosFacturas::route('/'),
            'create' => Pages\CreatePagosFacturas::route('/create'),
            'edit' => Pages\EditPagosFacturas::route('/{record}/edit'),
            //'view'   => Pages\ViewPagosFacturas::route('/{record}'),
        ];
    }
}
