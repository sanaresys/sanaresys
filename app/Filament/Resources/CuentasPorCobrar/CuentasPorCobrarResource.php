<?php

namespace App\Filament\Resources\CuentasPorCobrar;

use App\Filament\Resources\CuentasPorCobrar\CuentasPorCobrarResource\Pages;
use App\Filament\Resources\CuentasPorCobrar\CuentasPorCobrarResource\RelationManagers;
use App\Models\CuentasPorCobrar;
use CurlHandle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CuentasPorCobrarResource extends Resource
{
    protected static ?string $model = CuentasPorCobrar::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';
    protected static ?string $navigationGroup = 'Gestión de Facturación';
    protected static ?string $navigationLabel = 'Cuentas Pendientes';
    protected static ?string $modelLabel = 'Cuenta por Cobrar';
    protected static ?string $pluralModelLabel = 'Cuentas por Cobrar Pendientes';
    
    // Configurar la consulta base para mostrar solo cuentas pendientes
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('saldo_pendiente', '>', 0)
            ->whereIn('estado_cuentas_por_cobrar', ['PENDIENTE', 'PARCIAL', 'VENCIDA']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Cuenta por Cobrar')
                    ->schema([
                        Forms\Components\Select::make('factura_id')
                            ->label('Factura')
                            ->options(function () {
                                return \App\Models\Factura::with(['caiCorrelativo', 'paciente.persona'])
                                    ->get()
                                    ->mapWithKeys(function ($factura) {
                                        $numero = $factura->usa_cai && $factura->caiCorrelativo 
                                            ? $factura->caiCorrelativo->numero_factura
                                            : "PROF-{$factura->id}";
                                        $paciente = $factura->paciente->persona->nombre_completo ?? 'Sin paciente';
                                        $total = 'L.' . number_format($factura->total, 2);
                                        
                                        return [$factura->id => "{$numero} - {$paciente} - {$total}"];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state) {
                                if ($state) {
                                    $factura = \App\Models\Factura::find($state);
                                    if ($factura) {
                        // Auto-llenar el saldo pendiente basado en la factura y pagos existentes
                        $totalPagado = \App\Models\PagosFactura::where('factura_id', $state)->sum('monto_recibido');
                        $saldoPendiente = $factura->total - $totalPagado;
                        $set('saldo_pendiente', $saldoPendiente);                                        // Auto-detectar estado basado en pagos
                                        if ($totalPagado == 0) {
                                            $set('estado_cuentas_por_cobrar', 'PENDIENTE');
                                        } elseif ($totalPagado >= $factura->total) {
                                            $set('estado_cuentas_por_cobrar', 'PAGADA');
                                        } else {
                                            $set('estado_cuentas_por_cobrar', 'PARCIAL');
                                        }
                                    }
                                }
                            }),

                        Forms\Components\TextInput::make('saldo_pendiente')
                            ->label('Saldo Pendiente')
                            ->prefix('L.')
                            ->numeric()
                            ->step(0.01)
                            ->required()
                            ->helperText('Se calcula automáticamente basado en la factura seleccionada'),

                        Forms\Components\DatePicker::make('fecha_vencimiento')
                            ->label('Fecha de Vencimiento')
                            ->required()
                            ->default(now()->addDays(30)),

                        Forms\Components\Select::make('estado_cuentas_por_cobrar')
                            ->label('Estado')
                            ->options([
                                'PENDIENTE' => 'Pendiente',
                                'VENCIDA' => 'Vencida',
                                'PAGADA' => 'Pagada',
                                'PARCIAL' => 'Pago Parcial',
                                'INCOBRABLE' => 'Incobrable',
                            ])
                            ->default('PENDIENTE')
                            ->required()
                            ->helperText('Se actualiza automáticamente basado en los pagos'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('factura_id')
                    ->label('Número de Factura')
                    ->formatStateUsing(function ($state, $record) {
                        $factura = $record->factura;
                        if (!$factura) return "Factura #{$state}";
                        
                        return $factura->usa_cai && $factura->caiCorrelativo 
                            ? $factura->caiCorrelativo->numero_factura
                            : "PROF-{$factura->id}";
                    })
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('factura.paciente.persona.nombre_completo')
                    ->label('Paciente')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('factura.total')
                    ->label('Total Factura')
                    ->money('HNL')
                    ->alignEnd(),
                    
                TextColumn::make('saldo_pendiente')
                    ->label('Saldo Pendiente')
                    ->money('HNL')
                    ->alignEnd()
                    ->color('danger'),
                    
                TextColumn::make('pagos_realizados')
                    ->label('Pagado')
                    ->formatStateUsing(function ($state, $record) {
                        $totalPagado = \App\Models\PagosFactura::where('factura_id', $record->factura_id)->sum('monto_recibido');
                        return 'L.' . number_format($totalPagado, 2);
                    })
                    ->alignEnd()
                    ->color('success'),
                    
                TextColumn::make('fecha_vencimiento')
                    ->label('Vencimiento')
                    ->date()
                    ->sortable()
                    ->color(fn($record) => $record->fecha_vencimiento < now() && $record->estado_cuentas_por_cobrar !== 'PAGADA' ? 'danger' : null),
                    
                TextColumn::make('estado_cuentas_por_cobrar')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match($state) {
                        'PENDIENTE' => 'warning',
                        'VENCIDA' => 'danger', 
                        'PAGADA' => 'success',
                        'PARCIAL' => 'info',
                        'INCOBRABLE' => 'gray',
                        default => 'gray'
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('pagar')
                    ->label('Pagar')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    ->visible(fn ($record) => $record->saldo_pendiente > 0)
                    ->form([
                        Forms\Components\Section::make('Información de la Factura')
                            ->schema([
                                Forms\Components\Placeholder::make('factura_info')
                                    ->label('Factura')
                                    ->content(function ($record) {
                                        $numero = $record->factura->usa_cai && $record->factura->caiCorrelativo 
                                            ? $record->factura->caiCorrelativo->numero_factura
                                            : "PROF-{$record->factura->id}";
                                        return "{$numero} - {$record->factura->paciente->persona->nombre_completo}";
                                    }),
                                Forms\Components\Placeholder::make('montos_info')
                                    ->label('Montos')
                                    ->content(function ($record) {
                                        $total = $record->factura->total;
                                        $pagado = $record->factura->montoPagado();
                                        $pendiente = $record->saldo_pendiente;
                                        return "Total: L." . number_format($total, 2) . 
                                               " | Pagado: L." . number_format($pagado, 2) . 
                                               " | Pendiente: L." . number_format($pendiente, 2);
                                    }),
                            ]),
                        Forms\Components\Section::make('Procesar Pago')
                            ->schema([
                                Forms\Components\Select::make('tipo_pago_id')
                                    ->label('Tipo de Pago')
                                    ->options(\App\Models\TipoPago::all()->pluck('nombre', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->default(1),
                                Forms\Components\TextInput::make('monto_recibido')
                                    ->label('Monto a Pagar')
                                    ->prefix('L.')
                                    ->numeric()
                                    ->step(0.01)
                                    ->required()
                                    ->default(fn ($record) => $record->saldo_pendiente)
                                    ->rules(['min:0.01'])
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set, $get, $record) {
                                        if ($state > $record->saldo_pendiente) {
                                            $set('monto_recibido', $record->saldo_pendiente);
                                        }
                                    })
                                    ->helperText(fn ($record) => "Máximo: L." . number_format($record->saldo_pendiente, 2)),
                                Forms\Components\DatePicker::make('fecha_pago')
                                    ->label('Fecha de Pago')
                                    ->default(now())
                                    ->required(),
                            ])
                    ])
                    ->action(function (array $data, $record): void {
                        try {
                            // Crear el pago
                            \App\Models\PagosFactura::create([
                                'factura_id' => $record->factura_id,
                                'paciente_id' => $record->factura->paciente_id,
                                'centro_id' => $record->factura->centro_id,
                                'tipo_pago_id' => $data['tipo_pago_id'],
                                'monto_recibido' => $data['monto_recibido'],
                                'monto_devolucion' => 0,
                                'fecha_pago' => $data['fecha_pago'],
                                'created_by' => \Illuminate\Support\Facades\Auth::id(),
                            ]);
                            
                            // El observer de Pagos_Factura se encarga de actualizar automáticamente
                            // el estado de la factura y la cuenta por cobrar
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Pago procesado exitosamente')
                                ->body("Pago de L." . number_format($data['monto_recibido'], 2) . " procesado correctamente.")
                                ->success()
                                ->send();
                                
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Error al procesar pago')
                                ->body("Error: " . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Procesar Pago')
                    ->modalDescription(fn ($record) => "Procesar pago para la factura de {$record->factura->paciente->persona->nombre_completo}")
                    ->modalSubmitActionLabel('Procesar Pago'),
                    
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado_cuentas_por_cobrar')
                    ->options([
                        'PENDIENTE' => 'Pendiente',
                        'VENCIDA' => 'Vencida',
                        'PARCIAL' => 'Pago Parcial',
                    ]),
                    
                Tables\Filters\Filter::make('vencidas')
                    ->label('Solo Vencidas')
                    ->query(fn (Builder $query): Builder => $query->where('fecha_vencimiento', '<', now()))
                    ->toggle(),
                    
                Tables\Filters\Filter::make('saldo_alto')
                    ->label('Saldo > L.1,000')
                    ->query(fn (Builder $query): Builder => $query->where('saldo_pendiente', '>', 1000))
                    ->toggle(),
            ])
            ->defaultSort('fecha_vencimiento');
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
            'index' => Pages\ListCuentasPorCobrars::route('/'),
            'create' => Pages\CreateCuentasPorCobrar::route('/create'),
            'edit' => Pages\EditCuentasPorCobrar::route('/{record}/edit'),
            'pagar' => Pages\PagarCuentasPorCobrar::route('/pagar'),
        ];
    }
}
